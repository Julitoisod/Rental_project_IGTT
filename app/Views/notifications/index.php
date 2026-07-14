<?php $this->extend('layout/main'); $this->section('content'); ?>

<style>
    .notif-page-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 8px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        text-decoration: none;
        color: inherit;
        transition: box-shadow .18s, transform .18s;
        border-left: 4px solid #e0e0e0;
    }
    .notif-page-item:hover {
        box-shadow: 0 4px 18px rgba(0,0,0,0.1);
        transform: translateY(-1px);
        color: inherit;
    }
    .notif-page-item.unread  { background: #f0f4ff; border-left-color: #2979ff; }
    .notif-page-item.success { border-left-color: #2e7d32; }
    .notif-page-item.warning { border-left-color: #f57f17; }
    .notif-page-item.danger  { border-left-color: #c62828; }
    .notif-page-item.info    { border-left-color: #1565c0; }

    .notif-icon-lg {
        width: 44px; height: 44px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }
    .notif-icon-lg.info    { background: #e3f2fd; color: #1565c0; }
    .notif-icon-lg.success { background: #e8f5e9; color: #2e7d32; }
    .notif-icon-lg.warning { background: #fff8e1; color: #f57f17; }
    .notif-icon-lg.danger  { background: #fce4ec; color: #c62828; }

    .notif-unread-dot {
        width: 9px; height: 9px; border-radius: 50%;
        background: #2979ff; flex-shrink: 0; margin-top: 6px;
    }
</style>

<div class="card-section">
    <div class="section-title d-flex align-items-center">
        <i class="bi bi-bell-fill text-primary"></i> Notifications
        <form action="<?= base_url(role_url_prefix() . '/notifications/mark-all-read') ?>" method="POST" class="ms-auto">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check-all"></i> Mark All as Read
            </button>
        </form>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-bell-slash" style="font-size:3rem;"></i>
            <p class="mt-2">No notifications yet.</p>
        </div>
    <?php else: ?>
        <?php
        $typeIcon = [
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'danger'  => 'bi-x-circle-fill',
            'info'    => 'bi-info-circle-fill',
        ];
        ?>
        <?php foreach ($notifications as $n): ?>
            <a href="<?= base_url(role_url_prefix() . '/notifications/read/' . $n['id']) ?>"
               class="notif-page-item <?= $n['is_read'] ? '' : 'unread' ?> <?= esc($n['type']) ?>">

                <div class="notif-icon-lg <?= esc($n['type']) ?>">
                    <i class="bi <?= $typeIcon[$n['type']] ?? 'bi-bell' ?>"></i>
                </div>

                <div style="flex:1; min-width:0;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <strong style="font-size:0.92rem;"><?= esc($n['title']) ?></strong>
                        <?php if (! $n['is_read']): ?>
                            <span class="badge bg-primary" style="font-size:0.62rem;">New</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:0.84rem; color:#555;"><?= esc($n['message']) ?></div>
                    <div style="font-size:0.72rem; color:#aaa; margin-top:4px;">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                    </div>
                </div>

                <?php if (! $n['is_read']): ?>
                    <div class="notif-unread-dot"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="mt-3"><?= $pager->links() ?></div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
