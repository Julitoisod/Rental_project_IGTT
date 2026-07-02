<?php

namespace App\Controllers;

use App\Models\RentalModel;
use App\Models\ItemModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\ReviewModel;
use App\Models\WalletModel;

class RentalController extends BaseController
{
    protected RentalModel $model;
    protected NotificationModel $notif;

    public function __construct()
    {
        $this->model = new RentalModel();
        $this->notif = new NotificationModel();
    }

    /**
     * Get the role-based URL prefix for redirects.
     */
    private function rentalsUrl(): string
    {
        $role = session()->get('role');
        return match ($role) {
            'snd_admin' => '/admin/rentals',
            'owner'     => '/owner/rentals',
            'customer'  => '/customer/rentals',
            default     => '/customer/rentals',
        };
    }

    private function itemsUrl(): string
    {
        $role = session()->get('role');
        return match ($role) {
            'snd_admin' => '/admin/items',
            'owner'     => '/owner/items',
            'customer'  => '/customer/dashboard',
            default     => '/customer/dashboard',
        };
    }

    /**
     * True when the current OWNER is trying to act on a rental for an item
     * they do not own. Admins are never blocked. Relies on 'item_owner_id'
     * from RentalModel::getWithRelations().
     */
    private function isForeignOwnerRental(array $rental): bool
    {
        return session()->get('role') === 'owner'
            && (int) ($rental['item_owner_id'] ?? 0) !== (int) session()->get('user_id');
    }

    public function index()
    {
        $today  = date('Y-m-d');
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        // Auto-update overdue
        $this->model->where('status', 'ongoing')->where('return_date <', $today)
            ->set(['status' => 'overdue'])->update();

        $search = $this->request->getGet('q');
        $status = $this->request->getGet('status');
        $builder = $this->model->getWithRelations();

        // Customers only see their own rentals
        if ($role === 'customer') {
            $builder->where('rentals.user_id', $userId);
        }

        // Owners only see rentals for their own items
        if ($role === 'owner') {
            $builder->where('items.owner_id', $userId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('items.name', $search)
                ->groupEnd();
        }
        if ($status) {
            $builder->where('rentals.status', $status);
        }

        $rentals = $builder->orderBy('rentals.id', 'DESC')->paginate(10);

        // Build a set of reviewed rental IDs so the view can hide/show the Rate button
        $reviewedIds = [];
        if (! empty($rentals)) {
            $rentalIds = array_column($rentals, 'id');
            $rows = (new ReviewModel())->db->table('reviews')
                ->select('rental_id')
                ->whereIn('rental_id', $rentalIds)
                ->get()->getResultArray();
            $reviewedIds = array_column($rows, 'rental_id');
        }

        $data = [
            'pageTitle'   => 'Rentals',
            'rentals'     => $rentals,
            'pager'       => $this->model->pager,
            'search'      => $search,
            'status'      => $status,
            'reviewedIds' => $reviewedIds,
        ];
        return view('rentals/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        $userId = session()->get('user_id');

        // ── Verification gate for customers — redirect to profile if not verified ──
        if ($role === 'customer') {
            $currentUser = (new UserModel())->find($userId);
            $emailVerified = !empty($currentUser['email_verified_at']);
            $idVerified    = !empty($currentUser['is_id_verified']);

            if (!$emailVerified || !$idVerified) {
                $missing = [];
                if (!$emailVerified) $missing[] = 'Email';
                if (!$idVerified) $missing[] = 'ID';
                return redirect()->to('/customer/profile')->with(
                    'error',
                    'Please verify your account (' . implode(' & ', $missing) . ') before renting. Complete verification below.'
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $users = (new UserModel())->whereIn('role', ['customer', 'owner'])->findAll();
        $items = (new ItemModel())->where('quantity >', 0)->findAll();
        $selectedItemId = $this->request->getGet('item_id');

        // Get current user info for customer display
        $currentUser = null;
        if ($role === 'customer') {
            $currentUser = (new UserModel())->find($userId);
        }

        // Get wallet balance for customer
        $walletBalance = 0;
        if ($role === 'customer') {
            $walletModel = new WalletModel();
            $walletBalance = $walletModel->getBalance((int)$userId);
        }

        // Get owner payment gateways for GCash option
        $paymentGateways = [];

        return view('rentals/create', [
            'pageTitle' => 'Rent Item',
            'users' => $users,
            'items' => $items,
            'selectedItemId' => $selectedItemId,
            'currentUser' => $currentUser,
            'walletBalance' => $walletBalance,
            'paymentGateways' => $paymentGateways,
        ]);
    }

    public function store()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        // ── Verification gate for customers ──────────────────────────────────
        if ($role === 'customer') {
            $currentUser   = (new UserModel())->find($userId);
            $emailVerified = !empty($currentUser['email_verified_at']);
            $idVerified    = !empty($currentUser['is_id_verified']);
            if (!$emailVerified || !$idVerified) {
                return redirect()->back()->withInput()->with(
                    'error',
                    'Your account must be fully verified (Email + ID) before you can rent or book. Please go to <strong>My Profile</strong> to complete verification.'
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        $rules = [
            'item_id'     => 'required|integer',
            'rent_date'   => 'required|valid_date',
            'return_date' => 'required|valid_date',
            'terms_accepted' => 'required|in_list[1]',
        ];

        // Owners/admins must pick a user; customers use their own ID
        if (in_array($role, ['snd_admin', 'owner'])) {
            $rules['user_id'] = 'required|integer';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $targetUserId = in_array($role, ['snd_admin', 'owner'])
            ? $this->request->getPost('user_id')
            : $userId;

        $itemModel = new ItemModel();
        $item      = $itemModel->find($this->request->getPost('item_id'));

        if (! $item) {
            return redirect()->back()->withInput()->with('error', 'Item not found.');
        }

        // Compute true total units: quantity is decremented for pending/ongoing but not for booked.
        // Recover the original pool size by adding back units currently rented out.
        $rentedOutCount = (new RentalModel())->where('item_id', $item['id'])->whereIn('status', ['pending', 'ongoing'])->countAllResults();
        $totalUnits     = $item['quantity'] + $rentedOutCount;

        if ($totalUnits < 1) {
            return redirect()->back()->withInput()->with('error', 'Item is out of stock.');
        }

        // Get rate options from item
        $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true);
        $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true);

        if (empty($rateOpt1) || empty($rateOpt2) || !isset($rateOpt1['price']) || !isset($rateOpt2['price'])) {
            return redirect()->back()->withInput()->with('error', 'Item rental rates not configured.');
        }

        // Verify rental duration
        $rentDate   = $this->request->getPost('rent_date');
        $returnDate = $this->request->getPost('return_date');
        $actualHours = (int) floor((strtotime($returnDate) - strtotime($rentDate)) / 3600);

        if ($actualHours < 1) {
            return redirect()->back()->withInput()->with('error', 'Return time must be after rent time.');
        }

        // Apply new pricing logic
        $totalCost = 0;
        $rentalHours = $actualHours;

        if ($actualHours <= 12) {
            // 12 hours or under
            $totalCost = (float) $rateOpt1['price'];
        } elseif ($actualHours <= 24) {
            // More than 12 hours, up to 24 hours
            $totalCost = (float) $rateOpt2['price'];
        } else {
            // Over 24 hours - calculate 24hr periods + remaining
            $fullDays = floor($actualHours / 24);
            $remainingHours = $actualHours % 24;

            $totalCost = $fullDays * (float) $rateOpt2['price'];

            if ($remainingHours > 0) {
                if ($remainingHours <= 12) {
                    // Remaining is 12 hours or less - add 12-hour rate
                    $totalCost += (float) $rateOpt1['price'];
                } else {
                    // Remaining is more than 12 hours - add another 24-hour rate
                    $totalCost += (float) $rateOpt2['price'];
                }
            }
        }

        // Determine effective rate option for record keeping
        // 1 = 12hr rate, 2 = 24hr rate, 3 = custom/multi-day rate
        if ($actualHours <= 12) {
            $rateOption = 1;
        } elseif ($actualHours <= 24) {
            $rateOption = 2;
        } else {
            $rateOption = 3; // Multi-day rental
        }

        // Determine if this is a booking or a rental request
        $isBooking  = $this->request->getPost('rent_type') === 'booking';
        $status     = $isBooking ? 'booked' : 'pending';
        // Booking fee = 20% deposit (rounded up); 0 for regular rentals
        $bookingFee = $isBooking ? (int) ceil($totalCost * 0.20) : 0;

        // ── Wallet payment check ──────────────────────────────────────────────
        $payWithWallet = $this->request->getPost('pay_with_wallet') === '1';
        $amountToCharge = $isBooking ? $bookingFee : $totalCost;

        // Bookings require wallet payment
        if ($isBooking && !$payWithWallet && $role === 'customer') {
            return redirect()->back()->withInput()->with('error', 'Booking requires wallet payment for the 20% deposit fee.');
        }

        if ($payWithWallet && $role === 'customer') {
            $walletModel = new WalletModel();
            $walletBalance = $walletModel->getBalance((int)$targetUserId);
            if ($walletBalance < $amountToCharge) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "Insufficient wallet balance. You have ₱" . number_format($walletBalance, 2) .
                    " but need ₱" . number_format($amountToCharge, 2) . ". Please top up first."
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // ── Time-slot conflict check ──────────────────────────────────────────
        $conflicts = $this->model->getTimeSlotConflicts($item['id'], $rentDate, $returnDate);
        if ($conflicts >= $totalUnits) {
            return redirect()->back()->withInput()->with(
                'error',
                "'{$item['name']}' is already fully reserved for that time slot. Please choose a different time."
            );
        }
        // ─────────────────────────────────────────────────────────────────────

        $this->model->save([
            'user_id'     => $targetUserId,
            'item_id'     => $item['id'],
            'rent_date'   => $rentDate,
            'return_date' => $returnDate,
            'status'      => $status,
            'total_cost'  => $totalCost,
            'booking_fee' => $bookingFee,
            'rate_option' => $rateOption,
        ]);

        $rentalIdNew = $this->model->getInsertID();

        // ── Handle GCash/external payment reference upload ────────────────────
        $payWithWallet  = $this->request->getPost('pay_with_wallet') === '1';
        $referenceFile  = $this->request->getFile('payment_reference');
        $hasReference   = $referenceFile && $referenceFile->isValid() && ! $referenceFile->hasMoved();

        if (! $payWithWallet && $hasReference && $role === 'customer' && ! $isBooking) {
            $refFilename = $referenceFile->getRandomName();
            $referenceFile->move(FCPATH . 'uploads/payment_refs', $refFilename);
            $this->model->update($rentalIdNew, [
                'status'            => 'awaiting_payment',
                'payment_reference' => $refFilename,
            ]);
            $status = 'awaiting_payment';
        }
        // ─────────────────────────────────────────────────────────────────────

        // ── Deduct wallet if customer chose to pay with wallet ────────────────
        if ($payWithWallet && $role === 'customer') {
            $walletModel = new WalletModel();
            $payLabel = $isBooking ? "Booking fee for Rental #{$rentalIdNew}" : "Payment for Rental #{$rentalIdNew}";
            $charged = $walletModel->deduct((int)$targetUserId, $amountToCharge, $payLabel, "Rental #{$rentalIdNew}");

            // If the debit failed (e.g. a concurrent charge drained the balance),
            // undo the rental instead of leaving an unpaid record behind.
            if ($charged === false) {
                $this->model->delete($rentalIdNew);
                return redirect()->back()->withInput()->with(
                    'error',
                    'Payment could not be completed — your wallet balance was insufficient. Please top up and try again.'
                );
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Bookings are future reservations — do NOT decrement qty yet.
        // awaiting_payment also does not decrement (not yet confirmed).
        if (! $isBooking && $status !== 'awaiting_payment') {
            $itemModel->update($item['id'], ['quantity' => $item['quantity'] - 1]);
        }

        // ── Notifications ──
        $rentalId   = $rentalIdNew;
        $rentalLink = '/rentals/' . $rentalId;
        $userModel  = new UserModel();

        if ($status === 'awaiting_payment') {
            $requesterName = session()->get('user_name');
            $this->notif->notify(
                $targetUserId,
                'Payment Proof Submitted 📎',
                "Your payment proof for '{$item['name']}' has been submitted. Waiting for owner confirmation.",
                'info',
                $rentalLink
            );
            $managers = $userModel->whereIn('role', ['owner', 'snd_admin'])->findAll();
            $mgrIds   = array_column($managers, 'id');
            if ($mgrIds) {
                $this->notif->notify(
                    $mgrIds,
                    'New Payment Proof 💳',
                    "{$requesterName} uploaded a payment proof for '{$item['name']}'. Please confirm.",
                    'warning',
                    $rentalLink
                );
            }
            $from = $this->request->getPost('from');
            return ($from === 'items' ? redirect()->to($this->itemsUrl()) : redirect()->to($this->rentalsUrl()))
                ->with('success', "Payment proof submitted! Waiting for owner to confirm your rental.");
        }

        if ($isBooking) {
            $walletNote = ($payWithWallet && $role === 'customer') ? " Booking fee of ₱" . number_format($bookingFee) . " paid via wallet." : " Booking fee: ₱" . number_format($bookingFee) . " (20% deposit).";
            $this->notif->notify(
                $targetUserId,
                'Booking Confirmed 📅',
                "Your booking for '{$item['name']}' ({$rentalHours} hours) has been placed.{$walletNote} Est. total: ₱" . number_format($totalCost),
                'info',
                $rentalLink
            );
            $managers = $userModel->whereIn('role', ['owner', 'snd_admin'])->findAll();
            $mgrIds   = array_column($managers, 'id');
            if ($mgrIds) {
                $requesterName = session()->get('user_name');
                $this->notif->notify(
                    $mgrIds,
                    'New Booking',
                    "{$requesterName} booked '{$item['name']}' for {$rentalHours} hour(s). Total: ₱" . number_format($totalCost),
                    'info',
                    $rentalLink
                );
            }
            $from = $this->request->getPost('from');
            $msg  = "Booking placed! Booking fee: ₱" . number_format($bookingFee) . " | Est. total: ₱" . number_format($totalCost);
            return $from === 'items'
                ? redirect()->to($this->itemsUrl())->with('success', $msg)
                : redirect()->to($this->rentalsUrl())->with('success', $msg);
        }

        // Notify the customer their request was received
        $this->notif->notify(
            $targetUserId,
            'Rental Request Submitted',
            "Your request for '{$item['name']}' ({$rentalHours} hours) - ₱" . number_format($totalCost) . " is pending approval.",
            'info',
            $rentalLink
        );

        // Notify all owners & admins of a new request
        $managers = $userModel->whereIn('role', ['owner', 'snd_admin'])->findAll();
        $mgrIds   = array_column($managers, 'id');
        if ($mgrIds) {
            $requesterName = session()->get('user_name');
            $this->notif->notify(
                $mgrIds,
                'New Rental Request',
                "{$requesterName} requested '{$item['name']}' for {$rentalHours} hour(s). Total: ₱" . number_format($totalCost),
                'warning',
                $rentalLink
            );
        }

        // Redirect back to items if coming from modal, otherwise rentals
        $from = $this->request->getPost('from');
        if ($from === 'items') {
            return redirect()->to($this->itemsUrl())->with('success', "Rental request sent! Total: ₱" . number_format($totalCost));
        }
        return redirect()->to($this->rentalsUrl())->with('success', "Rental created. Total: ₱" . number_format($totalCost));
    }

    public function show(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental) return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');

        // IDOR guard: customers may only view their own rentals; owners only
        // rentals for their own items. Admins can view all.
        if (session()->get('role') === 'customer' && (int) $rental['user_id'] !== (int) session()->get('user_id')) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');
        }
        if ($this->isForeignOwnerRental($rental)) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');
        }

        $reviewModel = new ReviewModel();
        $review = $reviewModel->where('rental_id', $id)->first();

        return view('rentals/show', [
            'pageTitle' => 'Rental Detail',
            'rental'    => $rental,
            'review'    => $review,
        ]);
    }

    public function detailJson(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental) return $this->response->setJSON(['error' => 'Rental not found.']);

        // IDOR guard: customers may only view their own rentals; owners only
        // rentals for their own items. Admins can view all.
        if (session()->get('role') === 'customer' && (int) $rental['user_id'] !== (int) session()->get('user_id')) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Rental not found.']);
        }
        if ($this->isForeignOwnerRental($rental)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Rental not found.']);
        }

        $reviewModel = new ReviewModel();
        $review = $reviewModel->where('rental_id', $id)->first();

        return $this->response->setJSON([
            'id'                => $rental['id'],
            'user_name'         => $rental['user_name'],
            'item_name'         => $rental['item_name'],
            'rent_date'         => $rental['rent_date'],
            'return_date'       => $rental['return_date'],
            'status'            => $rental['status'],
            'total_cost'        => $rental['total_cost'],
            'booking_fee'       => $rental['booking_fee'] ?? 0,
            'overstay_charge'   => $rental['overstay_charge'] ?? 0,
            'actual_return_time'=> $rental['actual_return_time'] ?? null,
            'payment_reference' => $rental['payment_reference'] ?? null,
            'review'            => $review ? [
                'rating'  => $review['rating'],
                'comment' => $review['comment'],
                'date'    => date('M d, Y', strtotime($review['created_at'])),
            ] : null,
        ]);
    }

    /**
     * Owner confirms a payment proof → moves rental from awaiting_payment → pending and decrements qty.
     */
    public function confirmPayment(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental || $rental['status'] !== 'awaiting_payment') {
            return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found or not awaiting payment.');
        }
        if ($this->isForeignOwnerRental($rental)) {
            return redirect()->to($this->rentalsUrl())->with('error', 'You can only manage rentals for your own items.');
        }

        $itemModel = new ItemModel();
        $item      = $itemModel->find($rental['item_id']);

        $this->model->update($id, ['status' => 'pending']);

        // Now decrement quantity (was held back during awaiting_payment)
        $itemModel->update($item['id'], ['quantity' => $item['quantity'] - 1]);

        $this->notif->notify(
            $rental['user_id'],
            'Payment Confirmed ✅',
            "Your payment for '{$rental['item_name']}' has been confirmed! Your rental is now pending activation.",
            'success',
            '/rentals/' . $id
        );

        return redirect()->to($this->rentalsUrl())->with('success', 'Payment confirmed. Rental is now pending.');
    }

    public function activate(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if ($rental && $this->isForeignOwnerRental($rental)) {
            return redirect()->to($this->rentalsUrl())->with('error', 'You can only manage rentals for your own items.');
        }
        if ($rental && in_array($rental['status'], ['pending', 'booked'])) {
            $this->model->update($id, ['status' => 'ongoing']);

            // Bookings didn't decrement qty — do it now on physical pickup
            if ($rental['status'] === 'booked') {
                $itemModel = new ItemModel();
                $item      = $itemModel->find($rental['item_id']);
                $itemModel->update($item['id'], ['quantity' => $item['quantity'] - 1]);
            }

            // Notify the customer
            $this->notif->notify(
                $rental['user_id'],
                'Rental Approved! ✓',
                "Your rental for '{$rental['item_name']}' has been approved and is now ongoing.",
                'success',
                '/rentals/' . $id
            );
        }
        return redirect()->to($this->rentalsUrl())->with('success', 'Rental approved and marked as Ongoing.');
    }

    public function confirmReturn(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental) return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');
        if ($this->isForeignOwnerRental($rental)) {
            return redirect()->to($this->rentalsUrl())->with('error', 'You can only manage rentals for your own items.');
        }

        return view('rentals/return', ['pageTitle' => 'Return Item', 'rental' => $rental]);
    }

    public function returnInfo(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental) return $this->response->setJSON(['error' => 'Rental not found.']);
        if ($this->isForeignOwnerRental($rental)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Not your item.']);
        }

        $item = (new ItemModel())->find($rental['item_id']);
        $today = date('Y-m-d H:i:s');
        $elapsedHours = (int) ceil((strtotime($today) - strtotime($rental['rent_date'])) / 3600);

        $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?: ['hours' => 12, 'price' => 350];
        $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?: ['hours' => 24, 'price' => 500];

        $bookedOption = $rental['rate_option'];
        $bookedHours = $bookedOption == 1 ? $rateOpt1['hours'] : $rateOpt2['hours'];
        $bookedCost = $bookedOption == 1 ? $rateOpt1['price'] : $rateOpt2['price'];

        $newTotal = $bookedCost;
        $overstayCharge = 0;
        $autoExtended = false;

        if ($elapsedHours > $bookedHours) {
            if ($bookedOption == 1 && $elapsedHours <= $rateOpt2['hours']) {
                $newTotal = $rateOpt2['price'];
                $autoExtended = true;
            } elseif ($elapsedHours > $rateOpt2['hours']) {
                $overstayHours = $elapsedHours - $rateOpt2['hours'];
                $overstayCharge = $overstayHours * (float) ($item['overstay_charge'] ?? 100);
                $newTotal = $rateOpt2['price'] + $overstayCharge;
            }
        }

        return $this->response->setJSON([
            'id'             => $rental['id'],
            'user_name'      => $rental['user_name'],
            'item_name'      => $rental['item_name'],
            'rent_date'      => $rental['rent_date'],
            'return_date'    => $rental['return_date'],
            'today'          => $today,
            'base_cost'      => $bookedCost,
            'total_payable'  => $newTotal,
            'overstay_charge'=> $overstayCharge,
            'auto_extended'  => $autoExtended,
            'elapsed_hours'  => $elapsedHours,
            'booked_hours'   => $bookedHours,
            'is_late'        => $elapsedHours > $rateOpt2['hours'],
            'late_hours'     => max(0, $elapsedHours - $rateOpt2['hours']),
        ]);
    }

    public function processReturn(int $id)
    {
        $rental = $this->model->find($id);
        if (! $rental) return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');

        $item = (new ItemModel())->find($rental['item_id']);
        if (! $item) return redirect()->to($this->rentalsUrl())->with('error', 'Item not found.');
        if (session()->get('role') === 'owner' && (int) $item['owner_id'] !== (int) session()->get('user_id')) {
            return redirect()->to($this->rentalsUrl())->with('error', 'You can only manage rentals for your own items.');
        }

        // Get actual return time (now)
        $actualReturnTime = date('Y-m-d H:i:s');
        $rentalStartTime = $rental['rent_date'];
        
        // Calculate hours elapsed from start time
        $elapsedHours = (int) ceil((strtotime($actualReturnTime) - strtotime($rentalStartTime)) / 3600);
        
        // Get rate options
        $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?? ['hours' => 12, 'price' => 350];
        $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?? ['hours' => 24, 'price' => 500];
        
        $bookedOption = $rental['rate_option'];
        $bookedHours = $bookedOption === 1 ? $rateOpt1['hours'] : $rateOpt2['hours'];
        $bookedCost = $bookedOption === 1 ? $rateOpt1['price'] : $rateOpt2['price'];
        
        $newTotal = $bookedCost;
        $overstayCharge = 0;
        $msg = 'Item returned successfully. ';
        
        // Auto-extension and overstay logic
        if ($elapsedHours > $bookedHours) {
            // Exceeded the booked period
            if ($bookedOption === 1 && $elapsedHours <= $rateOpt2['hours']) {
                // Auto-extend from Option 1 to Option 2
                $newTotal = $rateOpt2['price'];
                $msg .= "Auto-extended to {$rateOpt2['hours']}-hour rate (₱" . number_format($rateOpt2['price']) . "). ";
            } elseif ($elapsedHours > $rateOpt2['hours']) {
                // Exceeded both options - charge overstay fee
                $overstayHours = $elapsedHours - $rateOpt2['hours'];
                $overstayCharge = $overstayHours * (float) $item['overstay_charge'];
                $newTotal = $rateOpt2['price'] + $overstayCharge;
                $msg .= "Extended beyond {$rateOpt2['hours']} hours. Overstay charge: ₱" . number_format($overstayCharge) . ". ";
            }
        }

        $this->model->update($id, [
            'status'              => 'returned',
            'total_cost'          => $newTotal,
            'actual_return_time'  => $actualReturnTime,
            'overstay_charge'     => $overstayCharge,
        ]);

        // Restore quantity
        $itemModel = new ItemModel();
        $item      = $itemModel->find($rental['item_id']);
        $itemModel->update($item['id'], ['quantity' => $item['quantity'] + 1]);

        // ── Notifications ──
        $rentalFull = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if ($rentalFull) {
            $customerMsg = "Your rental for '{$rentalFull['item_name']}' has been marked as returned.";
            if ($overstayCharge > 0) {
                $customerMsg .= " Overstay charge applied: ₱" . number_format($overstayCharge);
            }
            $this->notif->notify(
                $rentalFull['user_id'],
                'Rental Returned',
                $customerMsg,
                $overstayCharge > 0 ? 'warning' : 'success',
                '/rentals/' . $id
            );

            // Notify managers
            $userModel = new UserModel();
            $mgrIds = array_column($userModel->whereIn('role', ['owner', 'snd_admin'])->findAll(), 'id');
            if ($mgrIds) {
                $this->notif->notify(
                    $mgrIds,
                    'Item Returned',
                    "{$rentalFull['user_name']} returned '{$rentalFull['item_name']}'.",
                    'info',
                    '/rentals/' . $id
                );
            }
        }

        return redirect()->to($this->rentalsUrl())->with('success', $msg . "New total: ₱" . number_format($newTotal));
    }

    public function delete(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if ($rental && $this->isForeignOwnerRental($rental)) {
            return redirect()->to($this->rentalsUrl())->with('error', 'You can only manage rentals for your own items.');
        }
        if ($rental) {
            // Only pending rentals decremented qty — restore it
            // Bookings did NOT decrement qty, so no restore needed
            if ($rental['status'] === 'pending') {
                $itemModel = new ItemModel();
                $item      = $itemModel->find($rental['item_id']);
                $itemModel->update($item['id'], ['quantity' => $item['quantity'] + 1]);
            }

            if (in_array($rental['status'], ['pending', 'booked'])) {
                $this->notif->notify(
                    $rental['user_id'],
                    $rental['status'] === 'booked' ? 'Booking Cancelled' : 'Rental Request Rejected',
                    "Your " . ($rental['status'] === 'booked' ? 'booking' : 'rental request') . " for '{$rental['item_name']}' was cancelled.",
                    'danger',
                    '/rentals'
                );
            }
        }
        $this->model->delete($id);
        return redirect()->to($this->rentalsUrl())->with('success', 'Cancelled and deleted.');
    }

    public function cancelBooking(int $id)
    {
        $rental = $this->model->getWithRelations()->where('rentals.id', $id)->first();
        if (! $rental || $rental['status'] !== 'booked') {
            return redirect()->to($this->rentalsUrl())->with('error', 'Booking not found or already processed.');
        }
        // Only the booking owner or managers can cancel
        $role   = session()->get('role');
        $userId = session()->get('user_id');
        if ($role === 'customer' && $rental['user_id'] != $userId) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Unauthorized.');
        }

        // Refund the wallet-paid booking deposit (bookings require wallet payment).
        $bookingFee = (float) ($rental['booking_fee'] ?? 0);
        $refunded   = false;
        if ($bookingFee > 0) {
            $refunded = (new WalletModel())->credit(
                (int) $rental['user_id'],
                $bookingFee,
                'refund',
                "Refund for cancelled Rental #{$id}",
                "Rental #{$id}"
            ) !== false;
        }

        // Bookings did NOT decrement qty — no stock restore needed
        $this->model->delete($id);

        // Notify
        $refundNote = $refunded
            ? " Your ₱" . number_format($bookingFee, 2) . " deposit has been refunded to your wallet."
            : "";
        $this->notif->notify(
            $rental['user_id'],
            'Booking Cancelled',
            "Your booking for '{$rental['item_name']}' has been cancelled.{$refundNote}",
            'warning',
            '/rentals'
        );

        $msg = 'Booking cancelled.' . ($refunded ? " Deposit of ₱" . number_format($bookingFee, 2) . " refunded." : "");
        return redirect()->to($this->rentalsUrl())->with('success', $msg);
    }

    /**
     * AJAX: Check if an item is available for a given time slot.
     * Returns JSON {available, conflict_count, max_units, message}
     */
    public function checkAvailability()
    {
        $itemId     = (int) $this->request->getGet('item_id');
        $rentDate   = $this->request->getGet('rent_date');
        $returnDate = $this->request->getGet('return_date');

        if (! $itemId || ! $rentDate || ! $returnDate) {
            return $this->response->setJSON(['available' => false, 'message' => 'Missing parameters.']);
        }

        // Convert ISO 8601 format (2026-04-16T12:00) to MySQL format (2026-04-16 12:00:00)
        $rentDate   = str_replace('T', ' ', $rentDate) . ':00';
        if (strlen($rentDate) === 16) $rentDate .= ':00'; // Handle missing seconds
        
        $returnDate = str_replace('T', ' ', $returnDate) . ':00';
        if (strlen($returnDate) === 16) $returnDate .= ':00'; // Handle missing seconds

        if ($returnDate <= $rentDate) {
            return $this->response->setJSON(['available' => false, 'message' => 'Return must be after rent date.']);
        }

        $item = (new ItemModel())->find($itemId);
        if (! $item) {
            return $this->response->setJSON(['available' => false, 'message' => 'Item not found.']);
        }

        // Compute true total units: quantity is decremented for pending/ongoing rentals,
        // so we add those back to recover the original pool size.
        $rentedOutCount = $this->model->where('item_id', $itemId)->whereIn('status', ['pending', 'ongoing'])->countAllResults();
        $totalUnits     = $item['quantity'] + $rentedOutCount;

        $conflicts = $this->model->getTimeSlotConflicts($itemId, $rentDate, $returnDate);
        $available = $conflicts < $totalUnits;

        return $this->response->setJSON([
            'available'      => $available,
            'conflict_count' => $conflicts,
            'max_units'      => $totalUnits,
            'message'        => $available
                ? 'Available for this time slot!'
                : 'Fully reserved for this time slot. Please choose a different time.',
        ]);
    }

    /**
     * AJAX: Get booked and available dates for an item within a date range.
     * Returns JSON with available_dates and booked_dates arrays.
     */
    public function checkAvailabilityRange()
    {
        $itemId = (int) $this->request->getGet('item_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        if (! $itemId || ! $startDate || ! $endDate) {
            return $this->response->setJSON(['error' => 'Missing parameters.']);
        }

        $item = (new ItemModel())->find($itemId);
        if (! $item) {
            return $this->response->setJSON(['error' => 'Item not found.']);
        }

        // Get all active bookings that OVERLAP with the date range
        // Two ranges [A,B] and [C,D] overlap when: A < D && C < B
        $bookings = $this->model
            ->where('item_id', $itemId)
            ->whereIn('status', ['booked', 'pending', 'ongoing'])
            ->where('rent_date <', $endDate . ' 23:59:59')
            ->where('return_date >', $startDate . ' 00:00:00')
            ->select('rent_date, return_date, status')
            ->findAll();

        // Generate list of booked periods
        $bookedDates = [];
        foreach ($bookings as $booking) {
            $bookedDates[] = [
                'rent_date' => $booking['rent_date'],
                'return_date' => $booking['return_date'],
                'status' => $booking['status']
            ];
        }

        // Generate list of available days (simple daily check)
        $availableDates = [];
        $current = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->modify('+1 day'); // Include end date

        while ($current < $end) {
            $dateStr = $current->format('Y-m-d');
            $dayStart = $dateStr . ' 00:00:00';
            $dayEnd = $dateStr . ' 23:59:59';
            
            // Check if this day has any conflicts
            $conflicts = $this->model
                ->where('item_id', $itemId)
                ->whereIn('status', ['booked', 'pending', 'ongoing'])
                ->where('rent_date <', $dayEnd)
                ->where('return_date >', $dayStart)
                ->countAllResults();

            if ($conflicts === 0) {
                $availableDates[] = $dateStr;
            }

            $current->modify('+1 day');
        }

        return $this->response->setJSON([
            'available_dates' => $availableDates,
            'booked_dates' => $bookedDates,
            'item_quantity' => $item['quantity']
        ]);
    }
}
