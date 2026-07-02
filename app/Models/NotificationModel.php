<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'title', 'message', 'type', 'link', 'is_read', 'created_at'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Send a notification to one or more user IDs.
     */
    public function notify(int|array $userIds, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $rows    = [];
        $now     = date('Y-m-d H:i:s');

        foreach ($userIds as $uid) {
            $rows[] = [
                'user_id'    => $uid,
                'title'      => $title,
                'message'    => $message,
                'type'       => $type,
                'link'       => $link,
                'is_read'    => 0,
                'created_at' => $now,
            ];
        }

        $this->insertBatch($rows);
    }

    /**
     * Count unread notifications for a user.
     */
    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }

    /**
     * Get recent notifications for a user (for dropdown).
     */
    public function getRecent(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(int $id, int $userId): void
    {
        $this->where('id', $id)->where('user_id', $userId)->set('is_read', 1)->update();
    }

    /**
     * Mark all as read for a user.
     */
    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->where('is_read', 0)->set('is_read', 1)->update();
    }
}
