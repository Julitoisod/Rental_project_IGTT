<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center"><div class="col-lg-5">
<div class="card-section">
    <div class="section-title"><i class="bi bi-tag text-primary"></i> Add Category</div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url(role_url_prefix() . '/categories/store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Category Name</label>
            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            <a href="<?= base_url(role_url_prefix() . '/categories') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>

<?php $this->endSection(); ?>
