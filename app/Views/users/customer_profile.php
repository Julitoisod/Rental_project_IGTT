<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <?php
    $badgeClass = ['snd_admin' => 'bg-danger', 'owner' => 'bg-warning text-dark', 'customer' => 'bg-primary'];
    $roleLabel  = ['snd_admin' => 'SND Admin', 'owner' => 'Rental Owner', 'customer' => 'Customer'];
    ?>

    <!-- Profile Header -->
    <div class="card-section mb-4">
        <div class="d-flex align-items-center gap-4">
            <div style="flex-shrink:0;">
                <?php if (! empty($user['avatar'])): ?>
                    <img src="<?= base_url('uploads/avatars/' . esc($user['avatar'])) ?>"
                         style="width:88px; height:88px; border-radius:50%; object-fit:cover; border:3px solid #e8eaf6;">
                <?php else: ?>
                    <div style="width:88px; height:88px; border-radius:50%; background:var(--sidebar-bg);
                                display:flex; align-items:center; justify-content:center;
                                font-size:2.2rem; font-weight:700; color:#fff; border:3px solid #e8eaf6;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <h4 class="mb-0 fw-bold"><?= esc($user['name']) ?></h4>
                <div class="text-muted small mb-1"><?= esc($user['email']) ?></div>
                <span class="badge <?= $badgeClass[$user['role']] ?? 'bg-secondary' ?> px-3 py-1">
                    <?= $roleLabel[$user['role']] ?? ucfirst($user['role']) ?>
                </span>
            </div>
            <div class="text-end">
                <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i> Joined <?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                <?php if ($fromRental): ?>
                    <a href="<?= base_url(role_url_prefix() . '/rentals/' . $fromRental) ?>" class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="bi bi-arrow-left"></i> Back to Rental
                    </a>
                <?php else: ?>
                    <a href="<?= base_url(role_url_prefix() . '/rentals') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Verification Status (visible to owner/admin, with action buttons) -->
    <?php
    $hasId      = ! empty($user['valid_id']);
    $isVerified = ! empty($user['is_id_verified']);

    if (! $hasId) {
        $s2class = 'step-empty'; $s2icon = 'bi-upload';
        $s2title = 'No ID Uploaded'; $s2sub = 'Customer has not uploaded an ID';
        $lineOk  = false; $isPending = false;
    } elseif ($isVerified) {
        $s2class = 'step-done'; $s2icon = 'bi-patch-check-fill';
        $s2title = 'ID Verified';
        $s2sub   = 'Verified on ' . (! empty($user['id_verified_at']) ? date('M d, Y', strtotime($user['id_verified_at'])) : '');
        $lineOk  = true; $isPending = false;
    } else {
        $s2class = 'step-pending'; $s2icon = 'bi-hourglass-split';
        $s2title = 'Pending Review'; $s2sub = 'ID uploaded — awaiting owner verification';
        $lineOk  = false; $isPending = true;
    }
    ?>
    <div class="card-section mb-4">
        <div class="section-title">
            <i class="bi bi-patch-check text-primary"></i> Verification Status
            <?php if ($lineOk): ?>
                <span class="badge bg-success ms-2"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
            <?php elseif ($isPending): ?>
                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-2"><i class="bi bi-dash-circle me-1"></i>No ID</span>
            <?php endif; ?>
        </div>
        <div class="verif-stepper mt-3">
            <div class="verif-step step-done">
                <div class="verif-circle"><i class="bi bi-check-lg"></i></div>
                <div class="mt-2 text-center">
                    <div class="fw-semibold" style="font-size:.82rem;">Email Confirmed</div>
                    <div class="text-muted" style="font-size:.72rem;"><?= esc($user['email']) ?></div>
                </div>
            </div>
            <div class="verif-line <?= $lineOk ? 'done' : '' ?>"></div>
            <div class="verif-step <?= $s2class ?>">
                <div class="verif-circle"><i class="bi <?= $s2icon ?>"></i></div>
                <div class="mt-2 text-center">
                    <div class="fw-semibold" style="font-size:.82rem;"><?= $s2title ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= $s2sub ?></div>
                </div>
            </div>
        </div>

        <!-- Verify / Unverify action for owners -->
        <?php if (! empty($user['valid_id'])): ?>
            <div class="d-flex align-items-start gap-3 mt-3 pt-3 border-top flex-wrap">
                <div class="flex-grow-1 small">
                    <div class="mb-1">
                        <span class="text-muted">Account name:</span>
                        <strong class="ms-1"><?= esc($user['name']) ?></strong>
                    </div>
                    <div class="text-muted fst-italic">Check that the name on the ID image above matches the account name before verifying.</div>
                </div>
                <?php if ($lineOk): ?>
                    <form action="<?= base_url(role_url_prefix() . '/users/' . $user['id'] . '/unverify-id') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Remove Verification
                        </button>
                    </form>
                <?php else: ?>
                    <form action="<?= base_url(role_url_prefix() . '/users/' . $user['id'] . '/verify-id') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button class="btn btn-success">
                            <i class="bi bi-patch-check-fill me-1"></i> Verify ID
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle"></i>
                This customer has not uploaded a valid ID yet.
            </div>
        <?php endif; ?>
    </div>

    <!-- Contact Info -->
    <div class="card-section mb-4">
        <div class="section-title">
            <i class="bi bi-person-lines-fill text-primary"></i> Contact Information
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small fw-semibold mb-1">Full Name</div>
                <div><?= esc($user['name']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-semibold mb-1">Email</div>
                <div><?= esc($user['email']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-semibold mb-1">Phone</div>
                <div>
                    <?php if (! empty($user['phone'])): ?>
                        <a href="tel:<?= esc($user['phone']) ?>"><?= esc($user['phone']) ?></a>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Not provided</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Valid ID -->
    <div class="card-section mb-4">
        <div class="section-title">
            <i class="bi bi-card-heading text-primary"></i> Valid ID Verification
        </div>
        <?php if (! empty($user['valid_id'])): ?>
            <div class="d-flex align-items-start gap-4 flex-wrap">
                <?php
                $ext = strtolower(pathinfo($user['valid_id'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])):
                ?>
                    <a href="<?= base_url('uploads/valid_ids/' . esc($user['valid_id'])) ?>" target="_blank">
                        <img src="<?= base_url('uploads/valid_ids/' . esc($user['valid_id'])) ?>"
                             style="max-height:200px; max-width:340px; border-radius:10px; border:1px solid #dee2e6; object-fit:contain; cursor:zoom-in;">
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('uploads/valid_ids/' . esc($user['valid_id'])) ?>" target="_blank"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                    </a>
                <?php endif; ?>
                <div>
                    <?php if (! empty($user['valid_id_type'])): ?>
                        <span class="badge bg-secondary fs-6 px-3 py-2 mb-2 d-block" style="width:fit-content;">
                            <i class="bi bi-card-heading me-1"></i><?= esc($user['valid_id_type']) ?>
                        </span>
                    <?php endif; ?>
                    <div class="text-success small"><i class="bi bi-check-circle-fill me-1"></i> ID uploaded</div>
                    <div class="text-muted small mt-1">Click image to view full size</div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-muted fst-italic">
                <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                No valid ID uploaded yet.
            </div>
        <?php endif; ?>
    </div>

    <!-- Rental History for this customer -->
    <div class="card-section">
        <div class="section-title">
            <i class="bi bi-clipboard-check text-primary"></i> Rental History
            <span class="badge bg-secondary ms-2"><?= count($rentals) ?></span>
        </div>
        <?php if (empty($rentals)): ?>
            <div class="text-muted fst-italic small py-2">No rental records found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>#</th><th>Item</th><th>Rate</th><th>Rent Date</th><th>Return Date</th><th>Status</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $r):
                            $rateOpt = (int)($r['rate_option'] ?? 1);
                            $rateKey = $rateOpt === 2 ? 'rate_option_2' : 'rate_option_1';
                            $rateData = !empty($r[$rateKey]) ? json_decode($r[$rateKey], true) : null;
                            $rateBadgeStyle = $rateOpt === 2
                                ? 'background:#fff3cd; color:#856404;'
                                : 'background:#cfe2ff; color:#084298;';
                        ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= esc($r['item_name']) ?></td>
                            <td>
                                <?php if ($rateData): ?>
                                    <span class="badge" style="<?= $rateBadgeStyle ?>">
                                        ₱<?= number_format($rateData['price']) ?>/<?= $rateData['hours'] ?>hrs
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $r['rent_date'] ?></td>
                            <td><?= $r['return_date'] ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?> px-2"><?= ucfirst($r['status']) ?></span></td>
                            <td>₱<?= number_format($r['total_cost']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
</div>

<?php $this->endSection(); ?>
