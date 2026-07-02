<?php

namespace App\Controllers;

use App\Models\RentalModel;

class ReportController extends BaseController
{
    protected RentalModel $model;

    public function __construct()
    {
        $this->model = new RentalModel();
    }

    public function index()
    {
        $startDate = $this->request->getGet('start_date') ?? '2020-01-01';
        $endDate   = $this->request->getGet('end_date')   ?? date('Y-m-d');

        $role    = session()->get('role');
        $ownerId = ($role === 'owner') ? (int) session()->get('user_id') : null;

        $query = $this->model->getWithRelations($startDate, $endDate)
            ->orderBy('rentals.rent_date', 'ASC');
        if ($ownerId) {
            $query->where('items.owner_id', $ownerId);
        }
        $rentals = $query->findAll();

        $totalRevenue = array_sum(array_column($rentals, 'total_cost'));
        $statusCounts = [];
        $totalDays    = 0;
        $dayCount     = 0;

        foreach ($rentals as $r) {
            $statusCounts[$r['status']] = ($statusCounts[$r['status']] ?? 0) + 1;
            $days = (strtotime($r['return_date']) - strtotime($r['rent_date'])) / 86400;
            if ($days > 0) { $totalDays += $days; $dayCount++; }
        }

        $avgDuration = $dayCount > 0 ? round($totalDays / $dayCount, 1) : 0;

        $data = [
            'pageTitle'         => 'Reports',
            'rentals'           => $rentals,
            'totalRevenue'      => $totalRevenue,
            'statusCounts'      => $statusCounts,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'avgDuration'       => $avgDuration,
            'mostRentedItems'   => $this->model->getMostRentedItems($startDate, $endDate, $ownerId),
            'revenueByCategory' => $this->model->getRevenueByCategory($startDate, $endDate, $ownerId),
            'topCustomers'      => $this->model->getTopCustomers($startDate, $endDate, $ownerId),
            'monthlyTrend'      => $this->model->getMonthlyTrend($startDate, $endDate, $ownerId),
        ];

        return view('reports/index', $data);
    }

    public function exportCSV()
    {
        $startDate = $this->request->getGet('start_date') ?? '2020-01-01';
        $endDate   = $this->request->getGet('end_date')   ?? date('Y-m-d');

        $role    = session()->get('role');
        $ownerId = ($role === 'owner') ? (int) session()->get('user_id') : null;

        $query = $this->model->getWithRelations($startDate, $endDate)
            ->orderBy('rentals.rent_date', 'ASC');
        if ($ownerId) {
            $query->where('items.owner_id', $ownerId);
        }
        $rentals = $query->findAll();

        $filename = 'rental_report_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ['ID', 'User', 'Item', 'Rent Date', 'Return Date', 'Status', 'Total Cost']);

        foreach ($rentals as $r) {
            fputcsv($output, [
                $r['id'],
                $r['user_name'],
                $r['item_name'],
                $r['rent_date'],
                $r['return_date'],
                ucfirst($r['status']),
                number_format($r['total_cost'], 2, '.', ''),
            ]);
        }

        fclose($output);
        exit;
    }
}
