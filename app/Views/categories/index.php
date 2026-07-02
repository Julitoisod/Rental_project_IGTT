<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="card-section">
    <div class="section-title">
        <i class="bi bi-tags text-primary"></i> Categories
        <a href="<?= base_url('categories/create') ?>" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>#</th><th>Name</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No categories.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= esc($c['name']) ?></td>
                        <td>
                            <a href="<?= base_url('categories/edit/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?= base_url('categories/delete/' . $c['id']) ?>" method="POST" class="d-inline"
                                  data-confirm="Delete this category? This cannot be undone."
                                  data-confirm-type="danger"
                                  data-confirm-title="Delete Category"
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

<?php $this->endSection(); ?>
