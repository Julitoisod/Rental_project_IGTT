<?php

namespace App\Controllers;

use App\Models\RentalModel;

class StoreWalletController extends BaseController
{
    public function index()
    {
        $ownerId = (int) session()->get('user_id');
        $model   = new RentalModel();

        $filters = [
            'status'    => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
            'q'         => $this->request->getGet('q'),
        ];

        $transactions = $model->getStoreTransactions($ownerId, $filters);

        // Summary totals
        $totalEarnings  = 0;
        $totalBookingFee = 0;
        $totalOverstay  = 0;
        $countByStatus  = [];

        foreach ($transactions as $t) {
            if (in_array($t['status'], ['returned', 'ongoing', 'overdue'])) {
                $totalEarnings  += (float) $t['total_cost'];
                $totalOverstay  += (float) ($t['overstay_charge'] ?? 0);
                $totalBookingFee += (float) ($t['booking_fee'] ?? 0);
            }
            $countByStatus[$t['status']] = ($countByStatus[$t['status']] ?? 0) + 1;
        }

        return view('store_wallet/index', [
            'pageTitle'      => 'Store Earnings',
            'transactions'   => $transactions,
            'filters'        => $filters,
            'totalEarnings'  => $totalEarnings,
            'totalBookingFee'=> $totalBookingFee,
            'totalOverstay'  => $totalOverstay,
            'countByStatus'  => $countByStatus,
        ]);
    }
}
