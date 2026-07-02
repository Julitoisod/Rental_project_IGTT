<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['rental_id', 'user_id', 'item_id', 'rating', 'comment'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'rental_id' => 'required|integer',
        'user_id'   => 'required|integer',
        'item_id'   => 'required|integer',
        'rating'    => 'required|integer|greater_than[0]|less_than[6]',
        'comment'   => 'permit_empty|max_length[1000]',
    ];

    /** Returns average rating and count for a given item. */
    public function getItemStats(int $itemId): array
    {
        $row = $this->selectAvg('rating', 'avg_rating')
                    ->selectCount('id', 'total')
                    ->where('item_id', $itemId)
                    ->first();
        return [
            'avg'   => $row ? round((float)$row['avg_rating'], 1) : 0,
            'total' => $row ? (int)$row['total'] : 0,
        ];
    }

    /** Bulk fetch avg ratings keyed by item_id for an array of item IDs. */
    public function getAvgRatingsForItems(array $itemIds): array
    {
        if (empty($itemIds)) return [];
        $rows = $this->db->table('reviews')
            ->select('item_id, AVG(rating) as avg_rating, COUNT(id) as total')
            ->whereIn('item_id', $itemIds)
            ->groupBy('item_id')
            ->get()->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['item_id']] = [
                'avg'   => round((float)$r['avg_rating'], 1),
                'total' => (int)$r['total'],
            ];
        }
        return $map;
    }

    /** Get all reviews for an item with reviewer name. */
    public function getForItem(int $itemId): array
    {
        return $this->db->table('reviews')
            ->select('reviews.*, users.name as reviewer_name')
            ->join('users', 'users.id = reviews.user_id')
            ->where('reviews.item_id', $itemId)
            ->orderBy('reviews.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** Check if a rental has already been reviewed. */
    public function existsForRental(int $rentalId): bool
    {
        return $this->where('rental_id', $rentalId)->countAllResults() > 0;
    }
}
