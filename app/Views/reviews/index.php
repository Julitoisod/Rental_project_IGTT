<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="card-section">
    <div class="section-title">
        <i class="bi bi-star-fill text-warning"></i> Reviews Portal
    </div>

    <?php if (empty($reviews)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-chat-square-text fs-1 mb-3 d-block"></i>
            <p>No reviews yet.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reviews as $rev): ?>
            <div class="col-md-6 col-lg-4">
                <div class="p-3 rounded border shadow-sm" style="background: #fff;">
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:48px; height:48px; background:#f0f4ff; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;" class="me-3">
                            <?php if (!empty($rev['item_image'])): ?>
                                <img src="<?= base_url('uploads/items/'.$rev['item_image']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="bi bi-image text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-fill">
                            <strong style="color: #0d2c8a;"><?= esc($rev['item_name']) ?></strong>
                            <div class="text-warning" style="font-size: 0.85rem;">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $rev['rating']) ? '★' : '☆'; ?>
                            </div>
                        </div>
                    </div>
                    <p class="mb-2 text-muted" style="font-size: 0.9rem;">
                        "<?= esc($rev['comment'] ?? 'No comment') ?>"
                    </p>
                    <div class="text-end" style="font-size: 0.75rem; color: #8a8fa3;">
                        - <?= esc($rev['user_name']) ?> · <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>