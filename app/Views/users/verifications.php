<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="card-section">
    <div class="section-title mb-3">
        <i class="bi bi-patch-check text-primary"></i> ID Verifications
        <span class="badge bg-secondary ms-2"><?= count($customers) ?></span>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($customers)): ?>
        <div class="text-muted fst-italic py-3">No customers have uploaded a valid ID yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Customer</th>
                        <th>ID Type</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($customers as $c):
                        $isVerified = ! empty($c['is_id_verified']);
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (! empty($c['avatar'])): ?>
                                    <img src="<?= base_url('uploads/avatars/' . esc($c['avatar'])) ?>"
                                         style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:36px; height:36px; border-radius:50%; background:var(--sidebar-bg);
                                                display:flex; align-items:center; justify-content:center;
                                                font-size:1rem; font-weight:700; color:#fff;">
                                        <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= esc($c['name']) ?></div>
                                    <div class="text-muted small"><?= esc($c['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (! empty($c['valid_id_type'])): ?>
                                <span class="badge bg-secondary"><?= esc($c['valid_id_type']) ?></span>
                            <?php else: ?>
                                <span class="text-muted fst-italic small">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isVerified): ?>
                                <span class="badge bg-success px-2">
                                    <i class="bi bi-patch-check-fill me-1"></i>Verified
                                </span>
                                <?php if (! empty($c['id_verified_at'])): ?>
                                    <div class="text-muted" style="font-size:.72rem;"><?= date('M d, Y', strtotime($c['id_verified_at'])) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark px-2">
                                    <i class="bi bi-hourglass-split me-1"></i>Pending Review
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url(role_url_prefix() . '/users/' . $c['id'] . '/profile') ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person-lines-fill me-1"></i>View &amp; Verify
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
