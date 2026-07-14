<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\Models\UserModel;
use App\Models\ReviewModel;
use App\Models\WalletModel;
use App\Models\RentalModel;

class ItemController extends BaseController
{
    /**
     * Predefined rental terms owners tick per item; the checked ones are
     * stored in items.rental_details and shown to customers.
     */
    public const RENTAL_TERM_OPTIONS = [
        'Valid ID required upon pickup',
        "Valid driver's license required",
        'Security deposit required',
        'Helmet must be worn at all times',
        'Fuel not included — return with the same fuel level',
        'Late return is charged per hour (overstay fee)',
        "Any damage or loss is the renter's responsibility",
        'Do not lend the unit to another person',
    ];

    protected ItemModel $model;

    public function __construct()
    {
        $this->model = new ItemModel();
    }

    public function index()
    {
        $role       = session()->get('role');
        $ownerId    = session()->get('user_id');
        $search     = $this->request->getGet('q');
        $filterCat  = $this->request->getGet('category');
        $filterFrom = $this->request->getGet('date_from');
        $filterTo   = $this->request->getGet('date_to');
        $filterAvail= $this->request->getGet('availability'); // 'available' | 'unavailable' | ''
        $sortBy     = $this->request->getGet('sort');         // 'price_asc' | 'price_desc' | 'name'

        $builder = $this->model
            ->select('items.*, categories.name as category_name')
            ->join('categories', 'categories.id = items.category_id', 'left');

        // Owners/admins see only their own items; customers see all items
        if (in_array($role, ['owner', 'snd_admin'])) {
            $builder->where('items.owner_id', $ownerId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('items.name', $search)
                ->orLike('categories.name', $search)
                ->groupEnd();
        }

        if ($filterCat) {
            $builder->where('items.category_id', (int) $filterCat);
        }

        // Availability filter — based on current quantity + any active rentals
        if ($filterAvail === 'available') {
            $builder->where('items.quantity >', 0);
        } elseif ($filterAvail === 'unavailable') {
            $builder->where('items.quantity', 0);
        }

        // Sort
        switch ($sortBy) {
            case 'price_asc':  $builder->orderBy('items.price', 'ASC');  break;
            case 'price_desc': $builder->orderBy('items.price', 'DESC'); break;
            case 'name':       $builder->orderBy('items.name', 'ASC');   break;
            default:           $builder->orderBy('items.id', 'DESC');    break;
        }

        $items = $builder->paginate(10);

        // Filter by available date range — exclude items fully booked during that window
        if ($filterFrom && $filterTo) {
            $fromDt = $filterFrom . ' 00:00:00';
            $toDt   = $filterTo   . ' 23:59:59';
            // For each item in the result, check conflicts
            $rentalModel = new RentalModel();
            $items = array_filter($items, function ($item) use ($rentalModel, $fromDt, $toDt) {
                $conflicts  = $rentalModel->getTimeSlotConflicts($item['id'], $fromDt, $toDt);
                $rentedOut  = $rentalModel->where('item_id', $item['id'])->whereIn('status', ['pending', 'ongoing'])->countAllResults();
                $totalUnits = $item['quantity'] + $rentedOut;
                return $totalUnits > 0 && $conflicts < $totalUnits;
            });
            $items = array_values($items);
        }

        $itemIds = array_column($items, 'id');
        $ratings = (new ReviewModel())->getAvgRatingsForItems($itemIds);

        // Fetch active rental bookings for each item
        $rentalModel = $rentalModel ?? new RentalModel();
        $activeRentals = [];
        foreach ($itemIds as $itemId) {
            $bookings = $rentalModel
                ->where('item_id', $itemId)
                ->whereIn('status', ['booked', 'pending', 'ongoing'])
                ->select('rent_date, return_date, status')
                ->findAll();
            $activeRentals[$itemId] = $bookings;
        }

        $walletBalance = 0;
        $isVerified    = true;
        if ($role === 'customer') {
            $walletBalance = (new WalletModel())->getBalance((int) $ownerId);
            $currentUser   = (new UserModel())->find($ownerId);
            $isVerified    = !empty($currentUser['email_verified_at']) && !empty($currentUser['is_id_verified']);
        }

        $data = [
            'pageTitle'     => 'Inventory / Items',
            'items'         => $items,
            'pager'         => $this->model->pager,
            'search'        => $search,
            'filterCat'     => $filterCat,
            'filterFrom'    => $filterFrom,
            'filterTo'      => $filterTo,
            'filterAvail'   => $filterAvail,
            'sortBy'        => $sortBy,
            'categories'    => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
            'users'         => (new UserModel())->whereIn('role', ['customer', 'owner'])->findAll(),
            'ratings'       => $ratings,
            'walletBalance' => $walletBalance,
            'activeRentals' => $activeRentals,
            'isVerified'    => $isVerified,
        ];
        return view('items/index', $data);
    }

    public function create()
    {
        $cats = (new CategoryModel())->findAll();
        return view('items/create', [
            'pageTitle'   => 'Add Item',
            'categories'  => $cats,
            'termOptions' => self::RENTAL_TERM_OPTIONS,
        ]);
    }

    /**
     * Build the rental_details text from the checked predefined terms.
     */
    private function rentalDetailsFromPost(): ?string
    {
        $checked = array_intersect(
            (array) $this->request->getPost('rental_terms'),
            self::RENTAL_TERM_OPTIONS
        );
        return $checked ? implode(' • ', $checked) : null;
    }

    public function store()
    {
        $rules = [
            'name'        => 'required|min_length[3]|max_length[120]',
            'category_id' => 'required|integer',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
            'rate_option_1_hours' => 'required|integer|greater_than_equal_to[1]',
            'rate_option_1_price' => 'required|numeric|greater_than_equal_to[0]',
            'rate_option_2_hours' => 'required|integer|greater_than_equal_to[1]',
            'rate_option_2_price' => 'required|numeric|greater_than_equal_to[0]',
            'image'       => 'if_exist|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
            'image2'      => 'if_exist|uploaded[image2]|max_size[image2,2048]|is_image[image2]|mime_in[image2,image/jpg,image/jpeg,image/png,image/webp]',
            'image3'      => 'if_exist|uploaded[image3]|max_size[image3,2048]|is_image[image3]|mime_in[image3,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare rental rate options
        $rateOption1 = [
            'hours' => (int) $this->request->getPost('rate_option_1_hours'),
            'price' => (float) $this->request->getPost('rate_option_1_price'),
        ];
        $rateOption2 = [
            'hours' => (int) $this->request->getPost('rate_option_2_hours'),
            'price' => (float) $this->request->getPost('rate_option_2_price'),
        ];

        $data = [
            'owner_id'    => session()->get('user_id'),
            'name'        => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id'),
            'quantity'    => $this->request->getPost('quantity'),
            'price'       => 0,
            'description' => $this->request->getPost('description'),
            'rate_option_1' => json_encode($rateOption1),
            'rate_option_2' => json_encode($rateOption2),
            'overstay_charge' => (float) ($this->request->getPost('overstay_charge') ?? 100),
            'rental_details' => $this->rentalDetailsFromPost(),
        ];

        foreach (['image', 'image2', 'image3'] as $field) {
            $newName = $this->validateImageUpload($this->request->getFile($field));
            if ($newName === 'INVALID') {
                return redirect()->back()->withInput()->with('error', 'Images must be JPG, PNG, GIF, or WEBP and under 5 MB.');
            }
            if ($newName !== null) {
                $this->request->getFile($field)->move(FCPATH . 'uploads/items', $newName);
                $data[$field] = $newName;
            }
        }

        $this->model->save($data);

        return redirect()->to($this->rolePrefix() . '/items')->with('success', 'Item added to inventory.');
    }

    public function edit(int $id)
    {
        $item = $this->model->where('owner_id', session()->get('user_id'))->find($id);
        if (! $item) return redirect()->to($this->rolePrefix() . '/items')->with('error', 'Item not found.');

        $cats = (new CategoryModel())->findAll();
        return view('items/edit', [
            'pageTitle'   => 'Edit Item',
            'item'        => $item,
            'categories'  => $cats,
            'termOptions' => self::RENTAL_TERM_OPTIONS,
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'name'        => 'required|min_length[3]|max_length[120]',
            'category_id' => 'required|integer',
            'quantity'    => 'required|integer|greater_than_equal_to[0]',
            'rate_option_1_hours' => 'required|integer|greater_than_equal_to[1]',
            'rate_option_1_price' => 'required|numeric|greater_than_equal_to[0]',
            'rate_option_2_hours' => 'required|integer|greater_than_equal_to[1]',
            'rate_option_2_price' => 'required|numeric|greater_than_equal_to[0]',
            'image'       => 'if_exist|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
            'image2'      => 'if_exist|uploaded[image2]|max_size[image2,2048]|is_image[image2]|mime_in[image2,image/jpg,image/jpeg,image/png,image/webp]',
            'image3'      => 'if_exist|uploaded[image3]|max_size[image3,2048]|is_image[image3]|mime_in[image3,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id'),
            'quantity'    => $this->request->getPost('quantity'),
            'description' => $this->request->getPost('description'),
            'rate_option_1' => json_encode([
                'hours' => (int) $this->request->getPost('rate_option_1_hours'),
                'price' => (float) $this->request->getPost('rate_option_1_price'),
            ]),
            'rate_option_2' => json_encode([
                'hours' => (int) $this->request->getPost('rate_option_2_hours'),
                'price' => (float) $this->request->getPost('rate_option_2_price'),
            ]),
            'overstay_charge' => (float) ($this->request->getPost('overstay_charge') ?? 100),
            'rental_details' => $this->rentalDetailsFromPost(),
        ];

        $old = $this->model->where('owner_id', session()->get('user_id'))->find($id);
        if (! $old) return redirect()->to($this->rolePrefix() . '/items')->with('error', 'Item not found.');
        foreach (['image', 'image2', 'image3'] as $field) {
            $newName = $this->validateImageUpload($this->request->getFile($field));
            if ($newName === 'INVALID') {
                return redirect()->back()->withInput()->with('error', 'Images must be JPG, PNG, GIF, or WEBP and under 5 MB.');
            }
            if ($newName !== null) {
                // Delete old file if exists
                if (! empty($old[$field]) && is_file(FCPATH . 'uploads/items/' . $old[$field])) {
                    unlink(FCPATH . 'uploads/items/' . $old[$field]);
                }
                $this->request->getFile($field)->move(FCPATH . 'uploads/items', $newName);
                $data[$field] = $newName;
            }
        }

        $this->model->update($id, $data);

        return redirect()->to($this->rolePrefix() . '/items')->with('success', 'Item updated.');
    }

    public function delete(int $id)
    {
        $item = $this->model->where('owner_id', session()->get('user_id'))->find($id);
        if (! $item) return redirect()->to($this->rolePrefix() . '/items')->with('error', 'Item not found.');

        // Block only when live business depends on the item; finished history
        // (returned/cancelled rentals, reviews) is deleted together with it.
        $rentalModel = new RentalModel();
        $active = $rentalModel->where('item_id', $id)
            ->whereIn('status', ['pending', 'booked', 'ongoing', 'overdue', 'awaiting_payment'])
            ->countAllResults();
        if ($active > 0) {
            return redirect()->to($this->rolePrefix() . '/items')->with('error',
                'Cannot delete "' . $item['name'] . '" — it still has ' . $active
                . ' active rental(s)/booking(s). Return or cancel them first.');
        }

        (new ReviewModel())->where('item_id', $id)->delete();
        $rentalModel->where('item_id', $id)->delete();
        $this->model->delete($id);

        return redirect()->to($this->rolePrefix() . '/items')->with('success', 'Item and its rental history deleted.');
    }
}
