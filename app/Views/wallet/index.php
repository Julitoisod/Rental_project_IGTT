<?php $this->extend('layout/main'); $this->section('content'); ?>

<style>
    .wallet-hero {
        background: linear-gradient(135deg, #0d2c8a 0%, #1a52cc 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .wallet-hero .balance-label {
        font-size: 0.85rem;
        opacity: .75;
        text-transform: uppercase;
        letter-spacing: .8px;
    }
    .wallet-hero .balance-amount {
        font-size: 2.6rem;
        font-weight: 800;
        letter-spacing: -1px;
        margin: 4px 0 0;
    }
    .wallet-hero .balance-amount small {
        font-size: 1.2rem;
        font-weight: 500;
        opacity: .8;
    }
    .txn-type-top_up  { background: #d1e7dd; color: #0f5132; }
    .txn-type-deduction { background: #f8d7da; color: #842029; }
    .txn-type-refund  { background: #cfe2ff; color: #084298; }
    .txn-icon-top_up    { color: #0f5132; }
    .txn-icon-deduction { color: #842029; }
    .txn-icon-refund    { color: #084298; }
</style>

<!-- Balance Hero -->
<div class="wallet-hero">
    <div>
        <div class="balance-label"><i class="bi bi-wallet2 me-1"></i> Wallet Balance</div>
        <div class="balance-amount"><small>₱</small><?= number_format($balance, 2) ?></div>
    </div>
    <a href="<?= base_url('wallet/topup') ?>" class="btn btn-warning fw-semibold px-4">
        <i class="bi bi-plus-circle-fill me-1"></i> Top Up
    </a>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success py-2 mb-3"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2 mb-3"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Pending Top-Up Requests -->
<?php
$pendingReqs = array_filter($myRequests ?? [], fn($r) => $r['status'] === 'pending');
if (!empty($pendingReqs)):
?>
<div class="card-section mb-4 border-start border-4 border-warning">
    <div class="section-title">
        <i class="bi bi-hourglass-split text-warning"></i> Pending Top-Up Requests
        <span class="badge bg-warning text-dark ms-2"><?= count($pendingReqs) ?></span>
    </div>
    <p class="text-muted small mb-3">The following top-up requests are awaiting admin approval. Your wallet will be credited once approved.</p>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead><tr><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($pendingReqs as $req): ?>
            <tr>
                <td class="text-muted small"><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
                <td class="fw-semibold text-success">₱<?= number_format($req['amount'], 2) ?></td>
                <td><span class="badge badge-pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Past Top-Up Requests -->
<?php
$pastReqs = array_filter($myRequests ?? [], fn($r) => $r['status'] !== 'pending');
if (!empty($pastReqs)):
?>
<div class="card-section mb-4">
    <div class="section-title">
        <i class="bi bi-receipt text-secondary"></i> Top-Up Request History
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Note</th></tr></thead>
            <tbody>
            <?php foreach ($pastReqs as $req): ?>
            <tr>
                <td class="text-muted small"><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
                <td class="fw-semibold">₱<?= number_format($req['amount'], 2) ?></td>
                <td>
                    <?php if ($req['status'] === 'approved'): ?>
                        <span class="badge badge-returned"><i class="bi bi-check-circle me-1"></i>Approved</span>
                    <?php else: ?>
                        <span class="badge badge-overdue"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted small"><?= esc($req['admin_note'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Transaction History -->
<div class="card-section">
    <div class="section-title">
        <i class="bi bi-clock-history text-primary"></i> Transaction History
        <span class="badge bg-secondary ms-2"><?= count($history) ?></span>
    </div>

    <?php if (empty($history)): ?>
        <div class="text-muted fst-italic small py-3">No transactions yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Reference</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $txn): ?>
                    <tr>
                        <td class="text-muted small"><?= $txn['id'] ?></td>
                        <td>
                            <span class="badge txn-type-<?= $txn['type'] ?>">
                                <?php
                                $icons = ['top_up' => 'bi-arrow-down-circle-fill', 'deduction' => 'bi-arrow-up-circle-fill', 'refund' => 'bi-arrow-counterclockwise'];
                                $labels = ['top_up' => 'Top Up', 'deduction' => 'Payment', 'refund' => 'Refund'];
                                ?>
                                <i class="bi <?= $icons[$txn['type']] ?? 'bi-circle' ?> me-1"></i>
                                <?= $labels[$txn['type']] ?? ucfirst($txn['type']) ?>
                            </span>
                        </td>
                        <td class="fw-semibold <?= $txn['type'] === 'deduction' ? 'text-danger' : 'text-success' ?>">
                            <?= $txn['type'] === 'deduction' ? '−' : '+' ?>₱<?= number_format($txn['amount'], 2) ?>
                        </td>
                        <td>₱<?= number_format($txn['balance_after'], 2) ?></td>
                        <td><?= esc($txn['reference'] ?? '—') ?></td>
                        <td class="text-muted small"><?= esc($txn['note'] ?? '—') ?></td>
                        <td class="text-muted small"><?= date('M d, Y H:i', strtotime($txn['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
