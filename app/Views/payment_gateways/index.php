<?php $this->extend('layout/main'); $this->section('content'); ?>

<style>
    .pg-form-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        padding: 28px 28px 22px;
    }
    .pg-form-card .pg-form-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef0f8;
    }
    .pg-list-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        padding: 28px 28px 22px;
    }
    .pg-list-card .pg-list-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef0f8;
    }
    .gw-item {
        background: #f8f9fb;
        border-radius: 12px;
        border: 1px solid #e8eaf2;
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
    }
    .gw-item .gw-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .gw-item .gw-name {
        font-weight: 700;
        font-size: 0.97rem;
        color: #1a1a2e;
    }
    .gw-item .gw-type-badge {
        font-size: 0.7rem;
        padding: 2px 10px;
        border-radius: 50px;
        background: #e3eafd;
        color: #0d2c8a;
        font-weight: 600;
        white-space: nowrap;
    }
    .gw-item .gw-account {
        font-size: 0.88rem;
        color: #444;
    }
    .gw-item .gw-qr img {
        width: 110px;
        height: 110px;
        object-fit: contain;
        border-radius: 8px;
        border: 1px solid #e0e4f0;
        background: #fff;
        padding: 4px;
    }
    .gw-item .gw-actions {
        display: flex;
        gap: 6px;
        margin-top: 4px;
    }
    .form-label-sm-custom {
        font-size: 0.8rem;
        font-weight: 600;
        color: #555;
        margin-bottom: 4px;
    }
</style>

<div class="content-area">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle-fill me-1"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── LEFT: Create / Edit Form ── -->
        <div class="col-lg-4">
            <div class="pg-form-card">
                <div class="pg-form-title" id="formTitle">
                    <i class="bi bi-plus-circle text-primary me-1"></i> Create Payment Option
                </div>

                <form action="<?= base_url('payment-gateways/store') ?>" method="POST"
                      enctype="multipart/form-data" id="gatewayForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_edit_id" id="editId" value="">
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="mb-3">
                        <label class="form-label form-label-sm-custom">Name</label>
                        <input type="text" name="name" id="fName" class="form-control form-control-sm"
                               placeholder="GCash, BDO, PayPal" value="<?= esc(old('name')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm-custom">Type</label>
                        <select name="type" id="fType" class="form-select form-select-sm" required>
                            <option value="">-- Select Type --</option>
                            <option value="E-Wallet"    <?= old('type') === 'E-Wallet'    ? 'selected' : '' ?>>E-Wallet</option>
                            <option value="Bank"        <?= old('type') === 'Bank'        ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="PayPal"      <?= old('type') === 'PayPal'      ? 'selected' : '' ?>>PayPal</option>
                            <option value="Credit Card" <?= old('type') === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="Other"       <?= old('type') === 'Other'       ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm-custom">Number / Account</label>
                        <input type="text" name="account_number" id="fAccount" class="form-control form-control-sm"
                               placeholder="Enter account number or mobile number"
                               value="<?= esc(old('account_number')) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label form-label-sm-custom">QR Code <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" name="qr_code" id="fQr" class="form-control form-control-sm" accept="image/*">
                        <div id="qrPreviewWrap" class="mt-2" style="display:none;">
                            <img id="qrPreviewImg" src="" alt="QR Preview"
                                 style="width:100px;height:100px;object-fit:contain;border-radius:8px;border:1px solid #ddd;padding:4px;background:#fff;">
                            <br><small class="text-muted">Current QR</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-4" id="cancelEditBtn" style="display:none;" onclick="resetForm()">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-4 ms-auto" id="submitBtn">
                            <i class="bi bi-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── RIGHT: Gateway List ── -->
        <div class="col-lg-8">
            <div class="pg-list-card">
                <div class="pg-list-title">
                    <i class="bi bi-credit-card-2-front text-primary me-1"></i>
                    Your Payment Gateways
                    <span class="badge bg-primary ms-2" style="font-size:.7rem;"><?= count($gateways) ?></span>
                </div>

                <?php if (empty($gateways)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-credit-card" style="font-size:2.5rem;"></i>
                        <p class="mt-2">No payment gateways added yet.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($gateways as $gw): ?>
                        <div class="col-sm-6 col-xl-4">
                            <div class="gw-item">
                                <div class="gw-header">
                                    <span class="gw-name"><?= esc($gw['name']) ?></span>
                                    <span class="gw-type-badge"><?= esc($gw['type']) ?></span>
                                </div>
                                <div class="gw-account">
                                    <i class="bi bi-phone text-muted me-1"></i><?= esc($gw['account_number']) ?>
                                </div>
                                <?php if ($gw['qr_code']): ?>
                                <div class="gw-qr">
                                    <img src="<?= base_url('uploads/qrcodes/' . $gw['qr_code']) ?>"
                                         alt="QR Code">
                                </div>
                                <?php endif; ?>
                                <div class="gw-actions">
                                    <button class="btn btn-sm btn-outline-secondary flex-fill"
                                            onclick="loadEdit(<?= $gw['id'] ?>, '<?= esc($gw['name'], 'js') ?>', '<?= esc($gw['type'], 'js') ?>', '<?= esc($gw['account_number'], 'js') ?>', '<?= $gw['qr_code'] ? base_url('uploads/qrcodes/' . $gw['qr_code']) : '' ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="<?= base_url('payment-gateways/delete/' . $gw['id']) ?>" method="POST" class="flex-fill"
                                          data-confirm="Delete this payment gateway? This cannot be undone."
                                          data-confirm-type="danger"
                                          data-confirm-title="Delete Gateway"
                                          data-confirm-btn="Yes, Delete">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger w-100">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
function loadEdit(id, name, type, account, qrUrl) {
    document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Edit Payment Option';
    document.getElementById('editId').value    = id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('fName').value     = name;
    document.getElementById('fAccount').value  = account;

    // Set the type select
    const sel = document.getElementById('fType');
    for (let o of sel.options) { if (o.value === type) { o.selected = true; break; } }

    // Show existing QR preview if any
    const wrap = document.getElementById('qrPreviewWrap');
    const img  = document.getElementById('qrPreviewImg');
    if (qrUrl) {
        img.src = qrUrl;
        wrap.style.display = '';
    } else {
        wrap.style.display = 'none';
    }

    // Change form action to update
    document.getElementById('gatewayForm').action = `<?= base_url('payment-gateways/update') ?>/` + id;
    document.getElementById('submitBtn').innerHTML = '<i class="bi bi-save me-1"></i> Update';
    document.getElementById('cancelEditBtn').style.display = '';

    // Scroll to form
    document.querySelector('.pg-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetForm() {
    document.getElementById('formTitle').innerHTML = '<i class="bi bi-plus-circle text-primary me-1"></i> Create Payment Option';
    document.getElementById('editId').value = '';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('gatewayForm').reset();
    document.getElementById('gatewayForm').action = '<?= base_url('payment-gateways/store') ?>';
    document.getElementById('submitBtn').innerHTML = '<i class="bi bi-save me-1"></i> Save';
    document.getElementById('cancelEditBtn').style.display = 'none';
    document.getElementById('qrPreviewWrap').style.display = 'none';
}

// Live QR preview on new file select
document.getElementById('fQr').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('qrPreviewImg').src = e.target.result;
        document.getElementById('qrPreviewWrap').style.display = '';
    };
    reader.readAsDataURL(file);
});

// Prevent double-submit
document.getElementById('gatewayForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
});
</script>

<?php $this->endSection(); ?>
