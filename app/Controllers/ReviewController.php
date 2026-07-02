<?php

namespace App\Controllers;

use App\Models\RentalModel;
use App\Models\ReviewModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class ReviewController extends BaseController
{
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

    public function index()
    {
        $db = \Config\Database::connect();
        $reviews = $db->table('reviews')
            ->select('reviews.*, users.name as user_name, items.name as item_name, items.image as item_image')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->join('items', 'items.id = reviews.item_id', 'left')
            ->orderBy('reviews.created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        $data = [
            'pageTitle' => 'Reviews Portal',
            'reviews'   => $reviews,
        ];

        return view('reviews/index', $data);
    }

    public function store(int $rentalId)
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        if ($role !== 'customer') {
            return redirect()->to($this->rentalsUrl())->with('error', 'Only customers can leave reviews.');
        }

        $rentalModel = new RentalModel();
        $rental = $rentalModel->find($rentalId);

        if (! $rental) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Rental not found.');
        }
        if ((int)$rental['user_id'] !== (int)$userId) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Unauthorized.');
        }
        if ($rental['status'] !== 'returned') {
            return redirect()->to($this->rentalsUrl() . '/' . $rentalId)->with('error', 'You can only review returned rentals.');
        }

        $reviewModel = new ReviewModel();
        if ($reviewModel->existsForRental($rentalId)) {
            return redirect()->to($this->rentalsUrl() . '/' . $rentalId)->with('error', 'You have already reviewed this rental.');
        }

        $rating  = (int) $this->request->getPost('rating');
        $comment = trim($this->request->getPost('comment') ?? '');

        if ($rating < 1 || $rating > 5) {
            return redirect()->to($this->rentalsUrl() . '/' . $rentalId)->with('error', 'Please select a star rating (1–5).');
        }

        $reviewModel->insert([
            'rental_id' => $rentalId,
            'user_id'   => $userId,
            'item_id'   => $rental['item_id'],
            'rating'    => $rating,
            'comment'   => $comment ?: null,
        ]);

        // Notify all owners & admins of the new review
        $itemModel = model('App\Models\ItemModel');
        $item      = $itemModel->find($rental['item_id']);
        $itemName  = $item ? $item['name'] : 'an item';
        $managers  = (new UserModel())->whereIn('role', ['snd_admin', 'owner'])->findAll();
        $mgrIds    = array_column($managers, 'id');
        if ($mgrIds) {
            (new NotificationModel())->notify(
                $mgrIds,
                'New Review Submitted ⭐',
                session()->get('user_name') . " rated '{$itemName}' {$rating}/5 stars.",
                'info',
                '/rentals/' . $rentalId
            );
        }

        return redirect()->to($this->rentalsUrl() . '/' . $rentalId)->with('success', 'Thank you for your review!');
    }

    public function delete(int $id)
    {
        $role = session()->get('role');
        if (! in_array($role, ['snd_admin', 'owner'])) {
            return redirect()->to($this->rentalsUrl())->with('error', 'Unauthorized.');
        }

        $reviewModel = new ReviewModel();
        $review = $reviewModel->find($id);
        if (! $review) {
            return redirect()->back()->with('error', 'Review not found.');
        }

        $reviewModel->delete($id);
        return redirect()->back()->with('success', 'Review deleted.');
    }
}
