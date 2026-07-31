<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center"><div class="col-lg-7">
<div class="card-section">
    <div class="section-title">
        <i class="bi bi-receipt text-primary"></i> Rental Detail #<?= $rental['id'] ?>
        <div class="ms-auto d-flex gap-2">
            <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
                <a href="<?= base_url(role_url_prefix() . '/users/' . $rental['user_id'] . '/profile?from_rental=' . $rental['id']) ?>"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-person-badge me-1"></i> View Customer Profile
                </a>
                <?php if ($rental['status'] !== 'awaiting_payment'): ?>
                    <a href="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/contract') ?>"
                       target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-file-earmark-text me-1"></i> Print Contract
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= base_url(role_url_prefix() . '/rentals') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <table class="table table-bordered mt-3">
        <tr><th width="180">User</th><td><?= esc($rental['user_name']) ?></td></tr>
        <tr><th>Item</th><td><?= esc($rental['item_name']) ?></td></tr>
        <tr><th>Rent Date</th><td><?= $rental['rent_date'] ?></td></tr>
        <tr><th>Return Date</th><td><?= $rental['return_date'] ?></td></tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-<?= $rental['status'] ?> px-3 py-2 fs-6">
                    <?= ucfirst($rental['status']) ?>
                </span>
            </td>
        </tr>
        <?php if ($rental['status'] === 'booked'): ?>
        <tr>
            <th>Booking Fee</th>
            <td>
                <strong class="text-primary fs-5">₱<?= number_format($rental['booking_fee']) ?></strong>
                <span class="badge bg-secondary ms-2">20% deposit</span>
            </td>
        </tr>
        <tr>
            <th>Est. Total</th>
            <td class="text-muted">₱<?= number_format($rental['total_cost']) ?> <span class="small">(due on activation)</span></td>
        </tr>
        <?php else: ?>
        <tr>
            <th>Total Cost</th>
            <td><strong class="text-primary fs-5">₱<?= number_format($rental['total_cost']) ?></strong></td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
        <div class="d-flex gap-2 mt-3">
            <?php if ($rental['status'] === 'booked'): ?>
                <form action="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/activate') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button class="btn btn-success"><i class="bi bi-play-fill"></i> Activate / Confirm</button>
                </form>
                <form action="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/cancel-booking') ?>" method="POST"
                      data-confirm="Cancel this booking? This cannot be undone."
                      data-confirm-type="danger"
                      data-confirm-title="Cancel Booking"
                      data-confirm-btn="Yes, Cancel">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Cancel Booking</button>
                </form>
            <?php endif; ?>
            <?php if ($rental['status'] === 'pending'): ?>
                <form action="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/activate') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button class="btn btn-success"><i class="bi bi-play-fill"></i> Activate</button>
                </form>
            <?php endif; ?>
            <?php if (in_array($rental['status'], ['ongoing', 'overdue'])): ?>
                <a href="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id'] . '/return') ?>"
                   class="btn btn-warning">
                    <i class="bi bi-box-arrow-in-left"></i> Process Return
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ── Review Section ── -->
    <?php if ($rental['status'] === 'returned'): ?>
    <hr class="my-4">
    <?php if ($review): ?>
        <!-- Existing review -->
        <div class="mt-2">
            <div class="fw-semibold mb-2"><i class="bi bi-star-fill text-warning"></i> Customer Review</div>
            <div class="mb-1">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                    <i class="bi bi-star<?= $s <= $review['rating'] ? '-fill' : '' ?> text-warning"></i>
                <?php endfor; ?>
                <span class="fw-bold ms-1"><?= $review['rating'] ?>/5</span>
            </div>
            <?php if ($review['comment']): ?>
                <p class="text-muted mb-1">"<?= esc($review['comment']) ?>"</p>
            <?php endif; ?>
            <small class="text-muted"><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
            <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
                <form action="<?= base_url(role_url_prefix() . '/reviews/delete/' . $review['id']) ?>" method="POST" class="d-inline ms-2"
                      data-confirm="Delete this review permanently?"
                      data-confirm-type="danger"
                      data-confirm-title="Delete Review"
                      data-confirm-btn="Yes, Delete">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            <?php endif; ?>
        </div>
    <?php elseif (session()->get('role') === 'customer' && (int)$rental['user_id'] === (int)session()->get('user_id')): ?>
        <!-- Review form -->
        <div id="review-form">
            <div class="fw-semibold mb-3"><i class="bi bi-star text-warning"></i> Rate this Rental</div>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <form action="<?= base_url(role_url_prefix() . '/reviews/store/' . $rental['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <!-- Star picker -->
                <div class="mb-3">
                    <div class="star-picker d-flex gap-2 fs-2">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>" class="d-none">
                        <label for="star<?= $s ?>" class="star-label" data-val="<?= $s ?>">
                            <i class="bi bi-star text-warning"></i>
                        </label>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted">Click a star to rate (1–5)</small>
                </div>
                <div class="mb-3">
                    <textarea name="comment" class="form-control" rows="3"
                              placeholder="Share your experience (optional)…" maxlength="1000"></textarea>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-star-fill me-1"></i> Submit Review
                </button>
            </form>
        </div>
        <style>
            .star-picker { flex-direction: row-reverse; justify-content: flex-end; }
            .star-label { cursor: pointer; }
            .star-picker input:checked ~ label .bi,
            .star-picker label:hover ~ label .bi,
            .star-picker label:hover .bi { content: ''; }
            .star-picker input:checked ~ label i.bi-star::before { content: "\F586"; } /* star-fill */
            .star-picker label:hover i.bi-star::before,
            .star-picker label:hover ~ label i.bi-star::before { content: "\F586"; }
        </style>
        <script>
            document.querySelectorAll('.star-label').forEach(label => {
                label.addEventListener('click', () => {
                    const val = parseInt(label.dataset.val);
                    document.querySelectorAll('.star-label i').forEach((icon, idx) => {
                        // labels are rendered 5→1 (reversed), idx 0=star5
                        const starVal = 5 - idx;
                        icon.className = starVal <= val ? 'bi bi-star-fill text-warning' : 'bi bi-star text-warning';
                    });
                });
                label.addEventListener('mouseover', () => {
                    const val = parseInt(label.dataset.val);
                    document.querySelectorAll('.star-label i').forEach((icon, idx) => {
                        const starVal = 5 - idx;
                        icon.className = starVal <= val ? 'bi bi-star-fill text-warning' : 'bi bi-star text-warning';
                    });
                });
                label.addEventListener('mouseout', () => {
                    const checked = document.querySelector('.star-picker input:checked');
                    const val = checked ? parseInt(checked.value) : 0;
                    document.querySelectorAll('.star-label i').forEach((icon, idx) => {
                        const starVal = 5 - idx;
                        icon.className = starVal <= val ? 'bi bi-star-fill text-warning' : 'bi bi-star text-warning';
                    });
                });
            });
        </script>
    <?php endif; ?>
    <?php endif; ?>
</div>
</div></div>

<?php $this->endSection(); ?>
