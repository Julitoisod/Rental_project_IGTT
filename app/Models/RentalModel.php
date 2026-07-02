<?php

namespace App\Models;

use CodeIgniter\Model;

class RentalModel extends Model
{
    protected $table            = 'rentals';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['user_id', 'item_id', 'rent_date', 'return_date', 'status', 'total_cost', 'booking_fee', 'rate_option', 'actual_return_time', 'overstay_charge', 'payment_reference'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'     => 'required|integer',
        'item_id'     => 'required|integer',
        'rent_date'   => 'required|valid_date',
        'return_date' => 'required|valid_date',
        'status'      => 'required|in_list[awaiting_payment,booked,pending,ongoing,returned,overdue]',
        'total_cost'  => 'required|decimal|greater_than_equal_to[0]',
        'booking_fee' => 'permit_empty|decimal|greater_than_equal_to[0]',
    ];

    protected $skipValidation = false;

    public function getWithRelations(?string $startDate = null, ?string $endDate = null)
    {
        $builder = $this->select('rentals.*, users.name as user_name, items.owner_id as item_owner_id, items.name as item_name, items.price as item_price, items.rate_option_1, items.rate_option_2, items.overstay_charge as item_overstay_charge')
            ->join('users', 'users.id = rentals.user_id')
            ->join('items', 'items.id = rentals.item_id');

        if ($startDate !== null && $endDate !== null) {
            $builder->where('rentals.rent_date >=', $startDate . ' 00:00:00')
                ->where('rentals.rent_date <=', $endDate . ' 23:59:59');
        }

        return $builder;
    }

    /**
     * Count active rentals/bookings that overlap with [rentDate, returnDate] for the given item.
     * Two ranges [A,B] and [C,D] overlap when A < D && C < B.
     */
    public function getTimeSlotConflicts(int $itemId, string $rentDate, string $returnDate, ?int $excludeId = null): int
    {
        $q = $this->where('item_id', $itemId)
                  ->whereIn('status', ['booked', 'pending', 'ongoing'])
                  ->where('rent_date <', $returnDate)
                  ->where('return_date >', $rentDate);
        if ($excludeId !== null) {
            $q = $q->where('id !=', $excludeId);
        }
        return $q->countAllResults();
    }

    /**
     * Get all rental payment transactions for a specific owner's items.
     * Returns rentals joined with user, item, and owner info.
     */
    public function getStoreTransactions(int $ownerId, array $filters = []): array
    {
        $builder = $this->db->table('rentals r')
            ->select('r.id, r.status, r.total_cost, r.booking_fee, r.overstay_charge, r.rate_option,
                      r.rent_date, r.return_date, r.actual_return_time, r.created_at,
                      u.name as customer_name, u.email as customer_email,
                      i.name as item_name, i.id as item_id')
            ->join('users u', 'u.id = r.user_id')
            ->join('items i', 'i.id = r.item_id')
            ->where('i.owner_id', $ownerId);

        if (! empty($filters['status'])) {
            $builder->where('r.status', $filters['status']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('r.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $builder->where('r.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if (! empty($filters['q'])) {
            $builder->groupStart()
                ->like('u.name', $filters['q'])
                ->orLike('i.name', $filters['q'])
                ->groupEnd();
        }

        return $builder->orderBy('r.id', 'DESC')->get()->getResultArray();
    }

    /** Top N most-rented items in the period. */
    public function getMostRentedItems(string $startDate, string $endDate, ?int $ownerId = null, int $limit = 8): array
    {
        $builder = $this->db->table('rentals')
            ->select('items.id as item_id, items.name as item_name, categories.name as category_name,
                      COUNT(rentals.id) as rental_count, SUM(rentals.total_cost) as total_revenue')
            ->join('items', 'items.id = rentals.item_id')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->where('rentals.rent_date >=', $startDate)
            ->where('rentals.rent_date <=', $endDate);
        if ($ownerId !== null) {
            $builder->where('items.owner_id', $ownerId);
        }
        return $builder->groupBy('rentals.item_id')
            ->orderBy('rental_count', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /** Revenue grouped by category. */
    public function getRevenueByCategory(string $startDate, string $endDate, ?int $ownerId = null): array
    {
        $builder = $this->db->table('rentals')
            ->select('COALESCE(categories.name, "Uncategorized") as category_name,
                      COUNT(rentals.id) as rental_count, SUM(rentals.total_cost) as total_revenue')
            ->join('items', 'items.id = rentals.item_id')
            ->join('categories', 'categories.id = items.category_id', 'left')
            ->where('rentals.rent_date >=', $startDate)
            ->where('rentals.rent_date <=', $endDate);
        if ($ownerId !== null) {
            $builder->where('items.owner_id', $ownerId);
        }
        return $builder->groupBy('items.category_id')
            ->orderBy('total_revenue', 'DESC')
            ->get()->getResultArray();
    }

    /** Top N customers by rental count. */
    public function getTopCustomers(string $startDate, string $endDate, ?int $ownerId = null, int $limit = 5): array
    {
        $builder = $this->db->table('rentals')
            ->select('users.id as user_id, users.name as user_name,
                      COUNT(rentals.id) as rental_count, SUM(rentals.total_cost) as total_spent')
            ->join('users', 'users.id = rentals.user_id')
            ->join('items', 'items.id = rentals.item_id')
            ->where('rentals.rent_date >=', $startDate)
            ->where('rentals.rent_date <=', $endDate);
        if ($ownerId !== null) {
            $builder->where('items.owner_id', $ownerId);
        }
        return $builder->groupBy('rentals.user_id')
            ->orderBy('rental_count', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    /** Monthly rental count + revenue trend. */
    public function getMonthlyTrend(string $startDate, string $endDate, ?int $ownerId = null): array
    {
        $builder = $this->db->table('rentals')
            ->select("DATE_FORMAT(rentals.rent_date, '%Y-%m') as month,
                      COUNT(rentals.id) as rental_count, SUM(rentals.total_cost) as revenue")
            ->join('items', 'items.id = rentals.item_id')
            ->where('rentals.rent_date >=', $startDate)
            ->where('rentals.rent_date <=', $endDate);
        if ($ownerId !== null) {
            $builder->where('items.owner_id', $ownerId);
        }
        return $builder->groupBy("DATE_FORMAT(rentals.rent_date, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()->getResultArray();
    }
}
