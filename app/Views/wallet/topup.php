<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center"><div class="col-md-7">

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2 mb-3"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Payment gateway instructions -->
<?php if (!empty($paymentGateways)): ?>
<div class="card-section mb-4">
    <div class="section-title">
        <i class="bi bi-send text-primary"></i> Step 1 — Send Your Payment
    </div>
    <p class="text-muted small mb-3">Send your desired amount to any of the payment channels below, then upload the proof of payment below.</p>
    <div class="row g-3">
        <?php foreach ($paymentGateways as $gw): ?>
        <div class="col-md-6">
            <div class="border rounded-3 p-3 d-flex align-items-start gap-3">
                <?php if ($gw['qr_code']): ?>
                    <img src="<?= base_url('uploads/qrcodes/' . esc($gw['qr_code'])) ?>" alt="<?= esc($gw['name']) ?>" style="width:44px;height:44px;object-fit:contain;border-radius:8px;">
                <?php else: ?>
                    <div style="width:44px;height:44px;border-radius:8px;background:#e8eeff;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-credit-card fs-4 text-primary"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="fw-semibold"><?= esc($gw['name']) ?></div>
                    <div class="small text-muted"><?= esc($gw['account_number']) ?></div>
                    <div class="small text-secondary text-capitalize"><?= esc($gw['type']) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Top-up request form -->
<div class="card-section">
    <div class="section-title">
        <i class="bi bi-upload text-primary"></i> Step 2 — Submit Your Request
    </div>

    <form method="POST" action="<?= base_url(role_url_prefix() . '/wallet/topup') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Amount Sent <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" name="amount" class="form-control form-control-lg"
                       min="50" max="10000" step="0.01" placeholder="e.g. 500"
                       value="<?= old('amount') ?>" required>
            </div>
            <div class="form-text">Min: ₱50 &nbsp;|&nbsp; Max: ₱10,000 per transaction</div>
        </div>

        <!-- Quick amount buttons -->
        <div class="d-flex gap-2 flex-wrap mb-4">
            <?php foreach ([100, 200, 500, 1000, 2000, 5000] as $preset): ?>
            <button type="button" class="btn btn-outline-primary btn-sm"
                    onclick="document.querySelector('[name=amount]').value=<?= $preset ?>">
                ₱<?= number_format($preset) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Proof of Payment <span class="text-danger">*</span></label>
            <input type="file" name="proof_image" class="form-control" accept="image/*" required id="proofInput">
            <div class="form-text">Upload a screenshot of your GCash/bank transfer. JPG, PNG, WEBP — max 5 MB.</div>
            <!-- Image preview -->
            <div id="proofPreviewWrap" class="mt-2 d-none">
                <img id="proofPreview" src="#" alt="Preview" class="rounded border" style="max-height:220px;max-width:100%;object-fit:contain;">
            </div>
        </div>

        <div class="alert alert-info py-2 small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Your wallet will be credited <strong>after admin review and approval</strong>. This usually takes a few minutes during business hours.
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send me-1"></i> Submit Request
            </button>
            <a href="<?= base_url(role_url_prefix() . '/wallet') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

</div></div>

<script>
document.getElementById('proofInput').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('proofPreview').src = e.target.result;
            document.getElementById('proofPreviewWrap').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php $this->endSection(); ?>
