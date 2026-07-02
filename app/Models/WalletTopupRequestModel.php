<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletTopupRequestModel extends Model
{
    protected $table         = 'wallet_topup_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'amount', 'proof_image', 'status', 'admin_note', 'reviewed_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** All pending requests, newest first, joined with user name/email. */
    public function getPending(): array
    {
        return $this->db->table('wallet_topup_requests r')
            ->select('r.*, u.name AS user_name, u.email AS user_email')
            ->join('users u', 'u.id = r.user_id')
            ->where('r.status', 'pending')
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** All requests (any status), newest first, joined with user info. */
    public function getAllWithUser(): array
    {
        return $this->db->table('wallet_topup_requests r')
            ->select('r.*, u.name AS user_name, u.email AS user_email')
            ->join('users u', 'u.id = r.user_id')
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** Requests for a specific customer. */
    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /** Count of unreviewed (pending) requests — used in sidebar badge. */
    public function countPending(): int
    {
        return $this->where('status', 'pending')->countAllResults();
    }
}
