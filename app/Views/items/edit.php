<?php $this->extend('layout/main'); $this->section('content'); ?>
<?php
$urlPrefix = match(session()->get('role')) {
    'snd_admin' => '/admin',
    'owner' => '/owner',
    'customer' => '/customer',
    default => '',
};
?>

<div class="row justify-content-center"><div class="col-lg-6">
<div class="card-section">
    <div class="section-title"><i class="bi bi-pencil-square text-primary"></i> Edit Item</div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url($urlPrefix . '/items/update/' . $item['id']) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Item Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?= esc(old('name', $item['name'])) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= old('category_id', $item['category_id']) == $cat['id'] ? 'selected' : '' ?>>
                        <?= esc($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Optional item description…"><?= old('description', $item['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Quantity</label>
            <input type="number" name="quantity" class="form-control"
                   value="<?= old('quantity', $item['quantity']) ?>" min="0" required>
        </div>

        <!-- Rental Rate Options -->
        <div class="card mb-4 border border-secondary">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Rental Rate Options (Owner Customizable)</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Set 2 rental duration options. If rental exceeds the first option, it auto-extends to the second.</p>
                
                <?php 
                    $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?? ['hours' => 12, 'price' => 350];
                    $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?? ['hours' => 24, 'price' => 500];
                ?>
                
                <div class="row">
                    <!-- Rate Option 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 1: Duration (Hours)</label>
                            <input type="number" name="rate_option_1_hours" class="form-control" value="<?= old('rate_option_1_hours', $rateOpt1['hours']) ?>" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 1: Price (₱)</label>
                            <input type="number" name="rate_option_1_price" class="form-control" value="<?= old('rate_option_1_price', $rateOpt1['price']) ?>" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <!-- Rate Option 2 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 2: Duration (Hours)</label>
                            <input type="number" name="rate_option_2_hours" class="form-control" value="<?= old('rate_option_2_hours', $rateOpt2['hours']) ?>" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Option 2: Price (₱)</label>
                            <input type="number" name="rate_option_2_price" class="form-control" value="<?= old('rate_option_2_price', $rateOpt2['price']) ?>" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>

                <!-- Overstay Charge -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Overstay Charge per Additional Period (₱)</label>
                    <input type="number" name="overstay_charge" class="form-control" value="<?= old('overstay_charge', $item['overstay_charge'] ?? 100) ?>" min="0" step="0.01">
                    <small class="form-text text-muted">Charge if not returned after Option 2 duration</small>
                </div>

                <!-- Rental Details -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rental Terms <span class="text-muted fw-normal small">(check the ones that apply — shown to the customer)</span></label>
                    <?php $oldTerms = old('rental_terms') !== null ? (array) old('rental_terms') : null; ?>
                    <?php foreach ($termOptions as $i => $term): ?>
                        <?php $isChecked = $oldTerms !== null
                            ? in_array($term, $oldTerms)
                            : str_contains($item['rental_details'] ?? '', $term); ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="rental_terms[]"
                                   id="term<?= $i ?>" value="<?= esc($term) ?>"
                                   <?= $isChecked ? 'checked' : '' ?>>
                            <label class="form-check-label" for="term<?= $i ?>"><?= esc($term) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Item Images <small class="text-muted fw-normal">(up to 3)</small></label>
            <div class="row g-3">
                <?php foreach ([['image', 'Photo 1 (Main)'], ['image2', 'Photo 2'], ['image3', 'Photo 3']] as [$field, $label]): ?>
                <div class="col-12">
                    <label class="form-label text-muted small mb-1"><?= $label ?></label>
                    <?php if (! empty($item[$field])): ?>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <img src="<?= base_url('uploads/items/' . $item[$field]) ?>" alt=""
                                 style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e0e3ef;">
                            <small class="text-muted">Current image</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="<?= $field ?>" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">Leave empty to keep current image.</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
            <a href="<?= base_url($urlPrefix . '/items') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>

<?php $this->endSection(); ?>
