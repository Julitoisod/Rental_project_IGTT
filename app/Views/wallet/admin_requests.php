<?php $this->extend('layout/main'); $this->section('content'); ?>

<style>
    .proof-thumb {
        width: 60px; height: 60px; object-fit: cover;
        border-radius: 8px; border: 1px solid #dee2e6;
        cursor: pointer; transition: opacity .15s;
    }
    .proof-thumb:hover { opacity: .8; }
    .status-pending  { background: #fff3cd; color: #856404; }
    .status-approved { background: #d1e7dd; color: #0f5132; }
    .status-rejected { background: #f8d7da; color: #842029; }
</style>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success py-2 mb-3"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2 mb-3"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php
$pending  = array_filter($requests, fn($r) => $r['status'] === 'pending');
$actioned = array_filter($requests, fn($r) => $r['status'] !== 'pending');
$pendingCount = count($pending);
?>

<!-- Pending Requests -->
<div class="card-section mb-4">
    <div class="section-title">
        <i class="bi bi-hourglass-split text-warning"></i> Pending Top-Up Requests
        <span class="badge bg-warning text-dark ms-2"><?= $pendingCount ?></span>
    </div>

    <?php if (empty($pending)): ?>
        <div class="text-muted fst-italic small py-3">No pending top-up requests.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Proof</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pending as $req): ?>
            <tr>
                <td class="text-muted small"><?= $req['id'] ?></td>
                <td>
                    <div class="fw-semibold small"><?= esc($req['user_name']) ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= esc($req['user_email']) ?></div>
                </td>
                <td class="fw-bold text-success">₱<?= number_format($req['amount'], 2) ?></td>
                <td>
                    <?php if ($req['proof_image']): ?>
                        <img src="<?= base_url('uploads/topup_proofs/' . esc($req['proof_image'])) ?>"
                             alt="Proof" class="proof-thumb"
                             onclick="showProof(this.src, '<?= esc($req['user_name']) ?>', '<?= number_format($req['amount'], 2) ?>')">
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted small"><?= date('M d, Y H:i', strtotime($req['created_at'])) ?></td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Approve -->
                        <form method="POST" action="<?= base_url(role_url_prefix() . '/wallet/approve/' . $req['id']) ?>"
                              data-confirm="Approve ₱<?= number_format($req['amount'], 2) ?> top-up for <?= esc(addslashes($req['user_name'])) ?>?"
                              data-confirm-type="info"
                              data-confirm-title="Approve Top-Up"
                              data-confirm-btn="Yes, Approve">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Approve
                            </button>
                        </form>
                        <!-- Reject -->
                        <button type="button" class="btn btn-danger btn-sm px-3"
                                onclick="openRejectModal(<?= $req['id'] ?>, '<?= esc(addslashes($req['user_name'])) ?>', '<?= number_format($req['amount'], 2) ?>')">
                            <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Actioned Requests -->
<?php if (!empty($actioned)): ?>
<div class="card-section">
    <div class="section-title">
        <i class="bi bi-clock-history text-secondary"></i> Previously Actioned
        <span class="badge bg-secondary ms-2"><?= count($actioned) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Proof</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actioned as $req): ?>
            <tr>
                <td class="text-muted small"><?= $req['id'] ?></td>
                <td>
                    <div class="fw-semibold small"><?= esc($req['user_name']) ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= esc($req['user_email']) ?></div>
                </td>
                <td class="fw-semibold">₱<?= number_format($req['amount'], 2) ?></td>
                <td>
                    <?php if ($req['proof_image']): ?>
                        <img src="<?= base_url('uploads/topup_proofs/' . esc($req['proof_image'])) ?>"
                             alt="Proof" class="proof-thumb"
                             onclick="showProof(this.src, '<?= esc($req['user_name']) ?>', '<?= number_format($req['amount'], 2) ?>')">
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge status-<?= $req['status'] ?>">
                        <?php if ($req['status'] === 'approved'): ?>
                            <i class="bi bi-check-circle me-1"></i>Approved
                        <?php else: ?>
                            <i class="bi bi-x-circle me-1"></i>Rejected
                        <?php endif; ?>
                    </span>
                </td>
                <td class="text-muted small"><?= esc($req['admin_note'] ?? '—') ?></td>
                <td class="text-muted small"><?= date('M d, Y H:i', strtotime($req['updated_at'] ?: $req['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-x-circle text-danger me-1"></i> Reject Top-Up Request</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="rejectForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small mb-3">Rejecting <strong id="rejectCustomerName"></strong>'s request for <strong id="rejectAmount"></strong>.</p>
                    <label class="form-label small fw-semibold">Reason (optional)</label>
                    <textarea name="admin_note" class="form-control form-control-sm" rows="3"
                              placeholder="e.g. Invalid proof of payment, amount mismatch…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Proof Image Lightbox Modal -->
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="proofModalTitle">Proof of Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="proofModalImg" src="#" alt="Proof" style="max-width:100%;max-height:75vh;object-fit:contain;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

<script>
function openRejectModal(id, name, amount) {
    document.getElementById('rejectForm').action = '<?= base_url(role_url_prefix() . '/wallet/reject/') ?>' + id;
    document.getElementById('rejectCustomerName').textContent = name;
    document.getElementById('rejectAmount').textContent = '₱' + amount;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showProof(src, name, amount) {
    document.getElementById('proofModalImg').src = src;
    document.getElementById('proofModalTitle').textContent = name + ' — ₱' + amount + ' Proof';
    new bootstrap.Modal(document.getElementById('proofModal')).show();
}
</script>

<?php $this->endSection(); ?>
