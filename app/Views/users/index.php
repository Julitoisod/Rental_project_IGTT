<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="card-section">
    <div class="section-title">
        <i class="bi bi-people text-primary"></i> Users
        <a href="<?= base_url(role_url_prefix() . '/users/create') ?>" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-person-plus"></i> Add User
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= esc($u['name']) ?></td>
                        <td><?= esc($u['email']) ?></td>
                        <td>
                            <?php
                            $badgeClass = ['snd_admin' => 'bg-danger', 'owner' => 'bg-warning text-dark', 'customer' => 'bg-primary'];
                            $roleLabel  = ['snd_admin' => 'SND Admin', 'owner' => 'Rental Owner', 'customer' => 'Customer'];
                            ?>
                            <span class="badge <?= $badgeClass[$u['role']] ?? 'bg-secondary' ?>">
                                <?= $roleLabel[$u['role']] ?? ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url(role_url_prefix() . '/users/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?= base_url(role_url_prefix() . '/users/delete/' . $u['id']) ?>" method="POST" class="d-inline"
                                  data-confirm="Delete this user? This cannot be undone."
                                  data-confirm-type="danger"
                                  data-confirm-title="Delete User"
                                  data-confirm-btn="Yes, Delete">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager->links() ?>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger mt-3">
        <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $this->endSection(); ?>
