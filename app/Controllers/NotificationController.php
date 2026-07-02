<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /** Full notifications page */
    public function index()
    {
        $userId = session()->get('user_id');
        $this->model->markAllRead($userId);

        $notifications = $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('notifications/index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
            'pager'         => $this->model->pager,
        ]);
    }

    /** AJAX: returns unread count + recent list as JSON (for topbar polling) */
    public function fetch()
    {
        $userId  = session()->get('user_id');
        $unread  = $this->model->countUnread($userId);
        $recent  = $this->model->getRecent($userId, 8);

        return $this->response->setJSON([
            'unread' => $unread,
            'items'  => $recent,
        ]);
    }

    /** Mark single notification as read and redirect to its link */
    public function markRead(int $id)
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');
        $notif  = $this->model->where('id', $id)->where('user_id', $userId)->first();

        $prefix = match ($role) {
            'snd_admin' => '/admin',
            'owner'     => '/owner',
            'customer'  => '/customer',
            default     => '/customer',
        };

        if ($notif) {
            $this->model->markRead($id, $userId);
            if ($notif['link']) {
                // Prepend role prefix if the link starts with /rentals or /items (no prefix yet)
                $link = $notif['link'];
                if (preg_match('#^/(rentals|items|wallet)#', $link) && !preg_match('#^/(admin|owner|customer)/#', $link)) {
                    $link = $prefix . $link;
                }
                return redirect()->to($link);
            }
        }

        return redirect()->to($prefix . '/notifications');
    }

    /** Mark all as read (POST) */
    public function markAllRead()
    {
        $role = session()->get('role');
        $prefix = match ($role) {
            'snd_admin' => '/admin',
            'owner'     => '/owner',
            'customer'  => '/customer',
            default     => '/customer',
        };
        $this->model->markAllRead(session()->get('user_id'));
        return redirect()->to($prefix . '/notifications')->with('success', 'All notifications marked as read.');
    }
}
