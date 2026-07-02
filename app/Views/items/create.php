<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center"><div class="col-lg-6">
<div class="card-section">
    <div class="section-title"><i class="bi bi-box-seam text-primary"></i> Add Item</div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('items/store') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Item Name</label>
            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                        <?= esc($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Optional item description…"><?= old('description') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Quantity</label>
            <input type="number" name="quantity" class="form-control" value="<?= old('quantity', 1) ?>" min="0" required>
        </div>

        <!-- Rental Rate Options -->
        <div class="card mb-4 border border-secondary">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Rental Rate Options (Owner Customizable)</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Set 2 rental duration options. If rental exceeds the first option, it auto-extends to the second.</p>
                
                <div class="row">
                    <!-- Rate Option 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 1: Duration (Hours)</label>
                            <input type="number" name="rate_option_1_hours" class="form-control" value="<?= old('rate_option_1_hours', 12) ?>" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 1: Price (₱)</label>
                            <input type="number" name="rate_option_1_price" class="form-control" value="<?= old('rate_option_1_price', 350) ?>" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <!-- Rate Option 2 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 2: Duration (Hours)</label>
                            <input type="number" name="rate_option_2_hours" class="form-control" value="<?= old('rate_option_2_hours', 24) ?>" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 2: Price (₱)</label>
                            <input type="number" name="rate_option_2_price" class="form-control" value="<?= old('rate_option_2_price', 500) ?>" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>

                <!-- Overstay Charge -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Overstay Charge per Additional Period (₱)</label>
                    <input type="number" name="overstay_charge" class="form-control" value="<?= old('overstay_charge', 100) ?>" min="0" step="0.01">
                    <small class="form-text text-muted">Charge if not returned after Option 2 duration</small>
                </div>

                <!-- Rental Details -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rental Terms & Details</label>
                    <textarea name="rental_details" class="form-control" rows="3" placeholder="e.g., Deposit required, Late return policy, Damage liability, etc."><?= old('rental_details') ?></textarea>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Item Images <small class="text-muted fw-normal">(up to 3)</small></label>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label text-muted small mb-1">Photo 1 (Main)</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small mb-1">Photo 2</label>
                    <input type="file" name="image2" class="form-control" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small mb-1">Photo 3</label>
                    <input type="file" name="image3" class="form-control" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="form-text">Max 2 MB each. Accepted: JPG, PNG, WEBP</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>

<?php $this->endSection(); ?>
