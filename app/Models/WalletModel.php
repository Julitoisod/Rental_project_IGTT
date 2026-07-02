<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table         = 'wallet_transactions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'type', 'amount', 'balance_after', 'reference', 'note'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Get the current wallet balance for a user. */
    public function getBalance(int $userId): float
    {
        $user = $this->db->table('users')->select('wallet_balance')
            ->where('id', $userId)->get()->getRowArray();
        return $user ? (float) $user['wallet_balance'] : 0.0;
    }

    /**
     * Credit the wallet (top-up or refund). Returns new balance or false.
     */
    public function credit(int $userId, float $amount, string $type, string $note = '', string $reference = ''): float|false
    {
        if ($amount <= 0) return false;

        $this->db->transStart();

        // Atomic increment — the row lock is held until commit, so the
        // balance_after we read back below cannot be corrupted by a race.
        $this->db->table('users')
            ->set('wallet_balance', 'wallet_balance + ' . $amount, false)
            ->where('id', $userId)
            ->update();

        $newBalance = $this->getBalance($userId);

        $this->insert([
            'user_id'       => $userId,
            'type'          => $type,
            'amount'        => $amount,
            'balance_after' => $newBalance,
            'reference'     => $reference ?: null,
            'note'          => $note ?: null,
        ]);

        $this->db->transComplete();
        return $this->db->transStatus() ? $newBalance : false;
    }

    /**
     * Deduct from wallet. Returns new balance, or false if insufficient funds.
     */
    public function deduct(int $userId, float $amount, string $note = '', string $reference = ''): float|false
    {
        if ($amount <= 0) return false;

        $this->db->transStart();

        // Atomic conditional debit: only succeeds when funds are sufficient.
        // This closes the check-then-act race that allowed double-spending.
        $this->db->table('users')
            ->set('wallet_balance', 'wallet_balance - ' . $amount, false)
            ->where('id', $userId)
            ->where('wallet_balance >=', $amount)
            ->update();

        if ($this->db->affectedRows() < 1) {
            $this->db->transComplete();
            return false; // insufficient funds (or user missing)
        }

        $newBalance = $this->getBalance($userId);

        $this->insert([
            'user_id'       => $userId,
            'type'          => 'deduction',
            'amount'        => $amount,
            'balance_after' => $newBalance,
            'reference'     => $reference ?: null,
            'note'          => $note ?: null,
        ]);

        $this->db->transComplete();
        return $this->db->transStatus() ? $newBalance : false;
    }

    /** Get transaction history for a user, latest first. */
    public function getHistory(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
