<?php

namespace App\Controllers;

use App\Models\WalletModel;
use App\Models\WalletTopupRequestModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class WalletController extends BaseController
{
    // ── CUSTOMER: wallet overview ──────────────────────────────────────────────

    public function index()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        if ($role !== 'customer') {
            return $this->redirectToDashboard()->with('error', 'Wallet is for customers only.');
        }

        $walletModel  = new WalletModel();
        $requestModel = new WalletTopupRequestModel();

        $balance      = $walletModel->getBalance($userId);
        $history      = $walletModel->getHistory($userId, 50);
        $myRequests   = $requestModel->getByUser($userId);

        return view('wallet/index', [
            'pageTitle'  => 'My Wallet',
            'balance'    => $balance,
            'history'    => $history,
            'myRequests' => $myRequests,
        ]);
    }

    // ── CUSTOMER: show top-up request form ────────────────────────────────────

    public function topUpForm()
    {
        if (session()->get('role') !== 'customer') {
            return $this->redirectToDashboard();
        }

        // Pass payment gateway info so customer knows where to send money
        $paymentGateways = model('App\Models\PaymentGatewayModel')->findAll();

        return view('wallet/topup', [
            'pageTitle'       => 'Top Up Wallet',
            'paymentGateways' => $paymentGateways,
        ]);
    }

    // ── CUSTOMER: submit top-up request (with required proof image) ───────────

    public function topUp()
    {
        $userId = session()->get('user_id');
        if (session()->get('role') !== 'customer') {
            return $this->redirectToDashboard();
        }

        $amount = (float) $this->request->getPost('amount');

        if ($amount < 50) {
            return redirect()->back()->with('error', 'Minimum top-up amount is ₱50.');
        }
        if ($amount > 10000) {
            return redirect()->back()->with('error', 'Maximum top-up per transaction is ₱10,000.');
        }

        // Validate proof of payment image (required)
        $proof = $this->request->getFile('proof_image');
        if (! $proof || ! $proof->isValid() || $proof->hasMoved()) {
            return redirect()->back()->with('error', 'Proof of payment image is required.');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($proof->getMimeType(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Proof must be a JPG, PNG, GIF, or WEBP image.');
        }

        if ($proof->getSizeByUnit('mb') > 5) {
            return redirect()->back()->with('error', 'Proof image must be under 5 MB.');
        }

        // Save the proof image to public/uploads/topup_proofs
        $newName = 'topup_' . $userId . '_' . time() . '.' . $proof->getExtension();
        $proof->move(FCPATH . 'uploads/topup_proofs', $newName);

        // Save the request as pending
        $requestModel = new WalletTopupRequestModel();
        $requestModel->insert([
            'user_id'     => $userId,
            'amount'      => $amount,
            'proof_image' => $newName,
            'status'      => 'pending',
        ]);

        // Notify all snd_admin users of the new request
        $admins = (new UserModel())->where('role', 'snd_admin')->findAll();
        foreach ($admins as $admin) {
            (new NotificationModel())->notify(
                $admin['id'],
                'New Wallet Top-Up Request',
                session()->get('user_name') . ' requested a ₱' . number_format($amount, 2) . ' wallet top-up. Please review.',
                'info',
                '/admin/wallet/requests'
            );
        }

        return redirect()->to('/wallet')
            ->with('success', 'Top-up request submitted! Please wait for admin approval.');
    }

    // ── ADMIN: list all top-up requests ──────────────────────────────────────

    public function topupRequests()
    {
        if (session()->get('role') !== 'snd_admin') {
            return $this->redirectToDashboard()->with('error', 'Unauthorized.');
        }

        $requestModel = new WalletTopupRequestModel();

        return view('wallet/admin_requests', [
            'pageTitle' => 'Wallet Top-Up Requests',
            'requests'  => $requestModel->getAllWithUser(),
        ]);
    }

    // ── ADMIN: approve a top-up request ──────────────────────────────────────

    public function approveTopup(int $id)
    {
        if (session()->get('role') !== 'snd_admin') {
            return $this->redirectToDashboard()->with('error', 'Unauthorized.');
        }

        $requestModel = new WalletTopupRequestModel();
        $req = $requestModel->find($id);

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->to('/admin/wallet/requests')->with('error', 'Request not found or already actioned.');
        }

        // Credit the customer's wallet
        $walletModel = new WalletModel();
        $newBalance  = $walletModel->credit(
            $req['user_id'],
            (float) $req['amount'],
            'top_up',
            'Admin-approved top-up',
            'TOPUP-' . $id
        );

        if ($newBalance === false) {
            return redirect()->to('/admin/wallet/requests')->with('error', 'Failed to credit wallet.');
        }

        // Mark request as approved
        $requestModel->update($id, [
            'status'      => 'approved',
            'reviewed_by' => session()->get('user_id'),
        ]);

        // Notify the customer
        (new NotificationModel())->notify(
            $req['user_id'],
            'Top-Up Approved ✓',
            '₱' . number_format($req['amount'], 2) . ' has been credited to your wallet. New balance: ₱' . number_format($newBalance, 2),
            'success',
            '/wallet'
        );

        return redirect()->to('/admin/wallet/requests')
            ->with('success', '₱' . number_format($req['amount'], 2) . ' approved and credited to customer.');
    }

    // ── ADMIN: reject a top-up request ───────────────────────────────────────

    public function rejectTopup(int $id)
    {
        if (session()->get('role') !== 'snd_admin') {
            return $this->redirectToDashboard()->with('error', 'Unauthorized.');
        }

        $requestModel = new WalletTopupRequestModel();
        $req = $requestModel->find($id);

        if (! $req || $req['status'] !== 'pending') {
            return redirect()->to('/admin/wallet/requests')->with('error', 'Request not found or already actioned.');
        }

        $adminNote = trim($this->request->getPost('admin_note') ?? '');

        // Mark as rejected
        $requestModel->update($id, [
            'status'      => 'rejected',
            'reviewed_by' => session()->get('user_id'),
            'admin_note'  => $adminNote ?: null,
        ]);

        // Notify the customer
        $msg = '₱' . number_format($req['amount'], 2) . ' top-up request was rejected.';
        if ($adminNote) {
            $msg .= ' Reason: ' . $adminNote;
        }

        (new NotificationModel())->notify(
            $req['user_id'],
            'Top-Up Request Rejected',
            $msg,
            'danger',
            '/wallet'
        );

        return redirect()->to('/admin/wallet/requests')
            ->with('success', 'Top-up request #' . $id . ' rejected.');
    }
}
