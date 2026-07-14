<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card-section">
    <div class="section-title"><i class="bi bi-pencil-square text-primary"></i> Edit User</div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url(role_url_prefix() . '/users/update/' . $user['id']) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Name</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $user['name'])) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $user['email'])) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">New Password <small class="text-muted">(leave blank to keep)</small></label>
            <input type="password" name="password" class="form-control" minlength="6">
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select" required>
                <option value="snd_admin" <?= (old('role', $user['role']) === 'snd_admin') ? 'selected' : '' ?>>SND Admin</option>
                <option value="owner"     <?= (old('role', $user['role']) === 'owner')     ? 'selected' : '' ?>>Rental Owner</option>
                <option value="customer" <?= (old('role', $user['role']) === 'customer')  ? 'selected' : '' ?>>Customer</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
            <a href="<?= base_url(role_url_prefix() . '/users') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
</div>

<?php $this->endSection(); ?>
