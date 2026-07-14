<?php $this->extend('layout/main'); $this->section('content'); ?>

<style>
    .sw-hero {
        background: linear-gradient(135deg, #1a6c3a 0%, #28a745 100%);
        border-radius: 16px;
        padding: 26px 30px;
        color: #fff;
        margin-bottom: 24px;
    }
    .sw-hero .sw-title {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        opacity: .8;
        margin-bottom: 4px;
    }
    .sw-hero .sw-amount {
        font-size: 2.4rem;
        font-weight: 800;
        letter-spacing: -1px;
    }
    .sw-hero .sw-amount small {
        font-size: 1.1rem;
        font-weight: 500;
    }
    .sw-stat {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 12px 18px;
        text-align: center;
        min-width: 110px;
    }
    .sw-stat .sw-stat-val {
        font-size: 1.3rem;
        font-weight: 700;
    }
    .sw-stat .sw-stat-lbl {
        font-size: 0.72rem;
        opacity: .8;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .sw-filter-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 3px 14px rgba(0,0,0,0.06);
        padding: 18px 22px;
        margin-bottom: 20px;
    }
    .sw-table-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 3px 14px rgba(0,0,0,0.06);
        padding: 22px;
    }
    .sw-table-card .sw-table-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .status-badge {
        font-size: 0.72rem;
        padding: 3px 10px;
        border-radius: 50px;
        font-weight: 600;
    }
    .status-badge.returned { background: #d1e7dd; color: #0f5132; }
    .status-badge.ongoing  { background: #cfe2ff; color: #084298; }
    .status-badge.overdue  { background: #f8d7da; color: #842029; }
    .status-badge.pending  { background: #fff3cd; color: #856404; }
    .status-badge.booked   { background: #e8d5ff; color: #5b2d8e; }
</style>

<div class="content-area">

    <!-- ── Hero Summary ── -->
    <div class="sw-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="sw-title"><i class="bi bi-shop me-1"></i> Total Store Earnings</div>
            <div class="sw-amount"><small>₱</small><?= number_format($totalEarnings, 2) ?></div>
            <div style="font-size:.8rem;opacity:.75;margin-top:4px;">From returned & ongoing rentals</div>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <div class="sw-stat">
                <div class="sw-stat-val">₱<?= number_format($totalBookingFee, 2) ?></div>
                <div class="sw-stat-lbl">Booking Fees</div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-val">₱<?= number_format($totalOverstay, 2) ?></div>
                <div class="sw-stat-lbl">Overstay Charges</div>
            </div>
            <div class="sw-stat">
                <div class="sw-stat-val"><?= count($transactions) ?></div>
                <div class="sw-stat-lbl">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- ── Status Count Pills ── -->
    <?php
    $allStatuses = ['returned', 'ongoing', 'pending', 'booked', 'overdue'];
    $statusColors = [
        'returned' => 'success',
        'ongoing'  => 'primary',
        'pending'  => 'warning',
        'booked'   => 'purple',
        'overdue'  => 'danger',
    ];
    ?>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php foreach ($allStatuses as $s): ?>
            <?php if (!empty($countByStatus[$s])): ?>
            <span class="badge rounded-pill
                <?= $s === 'booked' ? 'text-bg-secondary' : 'text-bg-' . ($statusColors[$s] ?? 'secondary') ?>"
                style="font-size:.78rem;padding:5px 14px;">
                <?= ucfirst($s) ?>: <?= $countByStatus[$s] ?>
            </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ── Filters ── -->
    <div class="sw-filter-card">
        <form method="GET" action="<?= base_url(role_url_prefix() . '/wallet') ?>" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm mb-1" style="font-size:.78rem;font-weight:600;color:#555;">
                    <i class="bi bi-search"></i> Search Customer / Item
                </label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Name or item…" value="<?= esc($filters['q'] ?? '') ?>">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1" style="font-size:.78rem;font-weight:600;color:#555;">
                    <i class="bi bi-circle-half"></i> Status
                </label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach ($allStatuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1" style="font-size:.78rem;font-weight:600;color:#555;">
                    <i class="bi bi-calendar-event"></i> Date From
                </label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1" style="font-size:.78rem;font-weight:600;color:#555;">
                    <i class="bi bi-calendar-event-fill"></i> Date To
                </label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                <?php if (array_filter($filters)): ?>
                    <a href="<?= base_url(role_url_prefix() . '/wallet') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ── Transaction Table ── -->
    <div class="sw-table-card">
        <div class="sw-table-title">
            <i class="bi bi-receipt text-success"></i>
            Payment Transactions
            <span class="badge bg-success ms-1" style="font-size:.7rem;"><?= count($transactions) ?></span>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-receipt" style="font-size:2.5rem;"></i>
                <p class="mt-2">No transactions found.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th>Rent Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Booking Fee</th>
                        <th>Overstay</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.82rem;">#<?= $t['id'] ?></td>
                        <td>
                            <div style="font-weight:600;font-size:.88rem;"><?= esc($t['customer_name']) ?></div>
                            <div style="font-size:.73rem;color:#888;"><?= esc($t['customer_email']) ?></div>
                        </td>
                        <td>
                            <a href="<?= base_url(role_url_prefix() . '/items') ?>" class="text-decoration-none fw-semibold" style="font-size:.88rem;">
                                <?= esc($t['item_name']) ?>
                            </a>
                        </td>
                        <td style="font-size:.82rem;"><?= date('M d, Y g:i A', strtotime($t['rent_date'])) ?></td>
                        <td style="font-size:.82rem;"><?= date('M d, Y g:i A', strtotime($t['return_date'])) ?></td>
                        <td>
                            <span class="status-badge <?= esc($t['status']) ?>"><?= ucfirst($t['status']) ?></span>
                        </td>
                        <td style="font-size:.85rem;">
                            <?= ($t['booking_fee'] > 0) ? '₱' . number_format($t['booking_fee'], 2) : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td style="font-size:.85rem;">
                            <?= ($t['overstay_charge'] > 0) ? '<span class="text-danger">₱' . number_format($t['overstay_charge'], 2) . '</span>' : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="text-end fw-bold" style="color:#1a6c3a;">
                            ₱<?= number_format($t['total_cost'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8f9fb;">
                        <td colspan="6" class="fw-semibold text-end" style="font-size:.88rem;">
                            Totals (returned/ongoing/overdue):
                        </td>
                        <td class="fw-bold" style="font-size:.88rem;">₱<?= number_format($totalBookingFee, 2) ?></td>
                        <td class="fw-bold text-danger" style="font-size:.88rem;">₱<?= number_format($totalOverstay, 2) ?></td>
                        <td class="text-end fw-bold" style="font-size:.95rem;color:#1a6c3a;">
                            ₱<?= number_format($totalEarnings, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php $this->endSection(); ?>
