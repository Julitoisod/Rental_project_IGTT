<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center"><div class="col-lg-6">
<div class="card-section">
    <div class="section-title">
        <i class="bi bi-box-arrow-in-left text-warning"></i> Process Item Return #<?= $rental['id'] ?>
    </div>

    <?php
        $today     = date('Y-m-d H:i:s');
        $isLate    = $today > $rental['return_date'];
        $lateHours = $isLate
            ? (int) ceil((strtotime($today) - strtotime($rental['return_date'])) / 3600)
            : 0;
        // Match the controller's processReturn() — per-hour overstay_charge from item config,
        // falling back to 100/h if the item record didn't carry one.
        $perHourOverstay = isset($rental['item_overstay_charge'])
            ? (float) $rental['item_overstay_charge']
            : 100.0;
        $lateFee   = $isLate ? $lateHours * $perHourOverstay : 0;
        $newTotal  = $rental['total_cost'] + $lateFee;
    ?>

    <table class="table table-borderless mb-3">
        <tr><th width="160">User</th><td><?= esc($rental['user_name']) ?></td></tr>
        <tr><th>Item</th><td><?= esc($rental['item_name']) ?></td></tr>
        <tr><th>Rent Date</th><td><?= $rental['rent_date'] ?></td></tr>
        <tr><th>Due Date</th><td><?= $rental['return_date'] ?></td></tr>
        <tr><th>Today</th><td><?= $today ?></td></tr>
        <tr><th>Base Cost</th><td>₱<?= number_format($rental['total_cost']) ?></td></tr>
        <?php if ($isLate): ?>
            <tr class="table-danger">
                <th>Late Hours</th><td><?= $lateHours ?> hour(s)</td>
            </tr>
            <tr class="table-danger">
                <th>Late Fee (10%)</th><td>+ ₱<?= number_format($lateFee) ?></td>
            </tr>
        <?php endif; ?>
        <tr class="table-info">
            <th><strong>Total Payable</strong></th>
            <td><strong class="fs-5">₱<?= number_format($newTotal) ?></strong></td>
        </tr>
    </table>

    <?php if ($isLate): ?>
        <div class="alert alert-warning py-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            This item is <strong><?= $lateHours ?> hour(s) overdue.</strong> Late fee has been applied.
        </div>
    <?php endif; ?>

    <form action="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/return') ?>" method="POST"
          data-confirm="Confirm the return of this item?"
          data-confirm-type="info"
          data-confirm-title="Confirm Return"
          data-confirm-btn="Yes, Confirm Return">
        <?= csrf_field() ?>
        <div class="d-flex gap-2">
            <button class="btn btn-warning">
                <i class="bi bi-check-circle"></i> Confirm Return
            </button>
            <a href="<?= base_url(role_url_prefix() . '/rentals') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>

<?php $this->endSection(); ?>
