<?php

namespace App\Controllers;

use App\Models\RentalModel;
use App\Models\ItemModel;
use App\Models\UserModel;
use App\Models\ReviewModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $rental = new RentalModel();
        $item   = new ItemModel();
        $user   = new UserModel();

        // Update overdue statuses automatically
        $today = date('Y-m-d');
        $rental->where('status', 'ongoing')->where('return_date <', $today)
            ->set(['status' => 'overdue'])->update();

        $role   = session()->get('role');
        $userId = session()->get('user_id');

        // Load all items for the dashboard
        $items = $item->select('items.*, categories.name as category_name')
                      ->join('categories', 'categories.id = items.category_id', 'left')
                      ->orderBy('items.created_at', 'DESC')
                      ->findAll();

        if ($role === 'customer') {
            $walletModel   = new \App\Models\WalletModel();
            $walletBalance = $walletModel->getBalance($userId);

            $activeTracker = $rental->select('rentals.*, items.name as item_name')
                                    ->join('items', 'items.id = rentals.item_id')
                                    ->where('rentals.user_id', $userId)
                                    ->where('rentals.status', 'ongoing')
                                    ->orderBy('rentals.return_date', 'ASC')
                                    ->first();

            $data = [
                'pageTitle'      => 'Available Units',
                'walletBalance'  => $walletBalance,
                'activeTracker'  => $activeTracker,
                'items'          => $items,
                'totalRentals'   => $rental->where('user_id', $userId)->countAllResults(),
                'activeRentals'  => $rental->where('user_id', $userId)->where('status', 'ongoing')->countAllResults(),
                'returned'       => $rental->where('user_id', $userId)->where('status', 'returned')->countAllResults(),
                'overdue'        => $rental->where('user_id', $userId)->where('status', 'overdue')->countAllResults(),
                'totalItems'     => $item->where('quantity >', 0)->countAllResults(),
                'totalUsers'     => null,
                'recentRentals'  => $rental->getWithRelations()->where('rentals.user_id', $userId)
                                        ->orderBy('rentals.created_at', 'DESC')->paginate(5),
                'chartDates'     => '[]',
                'chartRevenue'   => '[]',
                'recentReviews'  => [],
            ];
        } else {
            // Revenue Chart (Last 7 Days)
            $chartDates = [];
            $chartRevenue = [];
            $db = \Config\Database::connect();
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                // Using short format for display
                $chartDates[] = date('M d', strtotime($date));
                $daily = $db->table('rentals')->selectSum('total_cost')
                            ->like('rent_date', $date, 'after')
                            ->get()->getRowArray();
                $chartRevenue[] = (float)($daily['total_cost'] ?? 0);
            }

            // Recent Reviews
            $recentReviews = $db->table('reviews')
                ->select('reviews.*, users.name as user_name, items.name as item_name')
                ->join('users', 'users.id = reviews.user_id', 'left')
                ->join('items', 'items.id = reviews.item_id', 'left')
                ->orderBy('reviews.created_at', 'DESC')
                ->limit(5)
                ->get()->getResultArray();

            $data = [
                'pageTitle'      => 'Dashboard',
                'items'          => $items,
                'totalRentals'   => $rental->countAll(),
                'activeRentals'  => $rental->where('status', 'ongoing')->countAllResults(),
                'returned'       => $rental->where('status', 'returned')->countAllResults(),
                'overdue'        => $rental->where('status', 'overdue')->countAllResults(),
                'totalItems'     => $item->countAll(),
                'totalUsers'     => ($role === 'snd_admin') ? $user->countAll() : null,
                'recentRentals'  => $rental->getWithRelations()->orderBy('rentals.created_at', 'DESC')->paginate(5),
                'chartDates'     => json_encode($chartDates),
                'chartRevenue'   => json_encode($chartRevenue),
                'recentReviews'  => $recentReviews,
            ];
        }

        return view('dashboard/index', $data);
    }
}
