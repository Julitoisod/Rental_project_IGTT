<?php $this->extend('layout/main'); $this->section('content'); ?>
<?php
$role = session()->get('role');
$urlPrefix = match($role) {
    'snd_admin' => 'admin',
    'owner'     => 'owner',
    'customer'  => 'customer',
    default     => 'customer',
};
?>

<div class="card-section">
    <div class="section-title">
        <i class="bi bi-clipboard-check text-primary"></i> Rentals
        <?php if (session()->get('role') === 'admin'): ?>
            <a href="<?= base_url($urlPrefix . '/rentals/create') ?>" class="btn btn-primary btn-sm ms-auto">
                <i class="bi bi-plus-lg"></i> New Rental
            </a>
        <?php endif; ?>
    </div>

    <!-- Search + Filter -->
    <form class="row g-2 mb-3" method="GET" action="<?= base_url($urlPrefix . '/rentals') ?>">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Search user / item…" value="<?= esc($search ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['awaiting_payment', 'booked', 'pending', 'ongoing', 'returned', 'overdue'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="<?= base_url($urlPrefix . '/rentals') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Item</th>
                    <th>Rent Date</th><th>Return Date</th>
                    <th>Status</th><th>Total</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rentals)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No rentals found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rentals as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= esc($r['user_name']) ?></td>
                        <td><?= esc($r['item_name']) ?></td>
                        <td><?= $r['rent_date'] ?></td>
                        <td><?= $r['return_date'] ?></td>
                        <td>
                            <span class="badge badge-<?= $r['status'] ?> px-2 py-1">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'awaiting_payment'): ?>
                                <div><strong>₱<?= number_format($r['total_cost']) ?></strong></div>
                                <div class="text-muted small">Awaiting payment proof</div>
                            <?php elseif ($r['status'] === 'booked'): ?>
                                <div><strong>₱<?= number_format($r['booking_fee']) ?></strong></div>
                                <div class="text-muted small">Booking fee (20%)</div>
                                <div class="text-muted small">Est. total: ₱<?= number_format($r['total_cost']) ?></div>
                            <?php else: ?>
                                ₱<?= number_format($r['total_cost']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info"
                                    onclick="openDetailModal(<?= $r['id'] ?>)" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary view-profile-btn"
                                    title="View Customer Profile"
                                    data-user-id="<?= $r['user_id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customerProfileModal">
                                    <i class="bi bi-person-badge"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
                                <?php if ($r['status'] === 'awaiting_payment'): ?>
                                    <?php if (!empty($r['payment_reference'])): ?>
                                        <a href="<?= base_url('uploads/payment_refs/' . $r['payment_reference']) ?>"
                                           target="_blank" class="btn btn-sm btn-outline-info" title="View Payment Proof">
                                            <i class="bi bi-image"></i> Proof
                                        </a>
                                    <?php endif; ?>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/confirm-payment') ?>" method="POST" class="d-inline"
                                          data-confirm="Confirm this payment and mark rental as pending?"
                                          data-confirm-type="info"
                                          data-confirm-title="Confirm Payment"
                                          data-confirm-btn="Yes, Confirm">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-success" title="Confirm Payment">
                                            <i class="bi bi-credit-card-2-front"></i> Confirm
                                        </button>
                                    </form>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/delete') ?>" method="POST" class="d-inline"
                                          data-confirm="Reject and delete this rental? This cannot be undone."
                                          data-confirm-type="danger"
                                          data-confirm-title="Reject Payment"
                                          data-confirm-btn="Yes, Reject">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Reject Payment">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($r['status'] === 'booked'): ?>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/activate') ?>" method="POST" class="d-inline"
                                          data-confirm="Confirm this booking and mark as ongoing? Stock will be decremented."
                                          data-confirm-type="info"
                                          data-confirm-title="Confirm Booking"
                                          data-confirm-btn="Yes, Confirm">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-primary" title="Confirm Booking">
                                            <i class="bi bi-calendar-check"></i> Confirm
                                        </button>
                                    </form>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/cancel-booking') ?>" method="POST" class="d-inline"
                                          data-confirm="Cancel this booking?"
                                          data-confirm-type="danger"
                                          data-confirm-title="Cancel Booking"
                                          data-confirm-btn="Yes, Cancel">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Cancel Booking">
                                            <i class="bi bi-x-lg"></i> Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/activate') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/delete') ?>" method="POST" class="d-inline"
                                          data-confirm="Reject and delete this rental? This cannot be undone."
                                          data-confirm-type="danger"
                                          data-confirm-title="Reject Rental"
                                          data-confirm-btn="Yes, Reject">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Reject">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (in_array($r['status'], ['ongoing', 'overdue'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            onclick="openReturnModal(<?= $r['id'] ?>)" title="Return">
                                        <i class="bi bi-box-arrow-in-left"></i> Return
                                    </button>
                                <?php endif; ?>
                                <?php if (in_array($r['status'], ['returned', 'cancelled'])): ?>
                                    <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/delete') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete Rental">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (session()->get('role') === 'customer' && $r['status'] === 'booked' && $r['user_id'] == session()->get('user_id')): ?>
                                <form action="<?= base_url($urlPrefix . '/rentals/' . $r['id'] . '/cancel-booking') ?>" method="POST" class="d-inline"
                                      data-confirm="Cancel your booking? This cannot be undone."
                                      data-confirm-type="danger"
                                      data-confirm-title="Cancel Booking"
                                      data-confirm-btn="Yes, Cancel">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" title="Cancel Booking">
                                        <i class="bi bi-x-lg"></i> Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if (session()->get('role') === 'customer' && $r['status'] === 'returned' && $r['user_id'] == session()->get('user_id') && !in_array($r['id'], $reviewedIds ?? [])): ?>
                                <a href="<?= base_url($urlPrefix . '/rentals/' . $r['id']) ?>#review-form"
                                   class="btn btn-sm btn-warning" title="Leave a Review">
                                    <i class="bi bi-star-fill"></i> Rate
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager->links() ?>
</div>


<!-- Customer Profile Modal -->
<div class="modal fade" id="customerProfileModal" tabindex="-1" aria-labelledby="customerProfileModalLabel" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">

      <!-- Header -->
      <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #0d2c8a, #2979ff); padding: 1.5rem 1.5rem 1rem;">
        <div class="d-flex align-items-center gap-3 w-100">
          <div id="modalAvatar" style="width:56px;height:56px;border-radius:50%;background:#fff3;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;overflow:hidden;flex-shrink:0;"></div>
          <div>
            <h5 class="modal-title mb-0 text-white fw-bold" id="customerProfileModalLabel">Customer Profile</h5>
            <small id="modalRole" class="text-white-50"></small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body p-0">
        <!-- Loading state -->
        <div id="modalLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2 mb-0">Loading profile…</p>
        </div>

        <!-- Content -->
        <div id="modalContent" class="d-none">
          <!-- Verification banner -->
          <div id="modalVerifyBanner" class="px-4 pt-3 pb-2"></div>

          <!-- Info grid -->
          <div class="row g-0 px-4 pb-4">
            <div class="col-md-6 pe-md-3">
              <div class="mb-3">
                <label class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Full Name</label>
                <div id="modalName" class="fw-semibold mt-1" style="color:#1a1a2e;"></div>
              </div>
              <div class="mb-3">
                <label class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Email</label>
                <div id="modalEmail" class="mt-1"></div>
              </div>
              <div class="mb-3">
                <label class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Phone</label>
                <div id="modalPhone" class="mt-1"></div>
              </div>
              <div class="mb-3">
                <label class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Member Since</label>
                <div id="modalJoined" class="mt-1"></div>
              </div>
            </div>
            <div class="col-md-6 ps-md-3">
              <!-- Stats -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <div class="rounded-3 text-center py-3" style="background:linear-gradient(135deg,#0d2c8a,#2979ff);">
                    <div id="modalTotalRentals" class="text-white fw-bold fs-4">—</div>
                    <div class="text-white-50 small">Total Rentals</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="rounded-3 text-center py-3" style="background:linear-gradient(135deg,#0f5132,#20c997);">
                    <div id="modalActiveRentals" class="text-white fw-bold fs-4">—</div>
                    <div class="text-white-50 small">Active Rentals</div>
                  </div>
                </div>
              </div>
              <!-- Valid ID -->
              <div id="modalIdSection" class="d-none">
                <label class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.05em;">Valid ID</label>
                <div class="mt-1 mb-2">
                  <span id="modalIdType" class="badge bg-primary me-1"></span>
                  <span id="modalIdVerified"></span>
                </div>
                <a id="modalIdImage" href="#" target="_blank">
                  <img id="modalIdThumb" src="" style="max-width:100%;border-radius:8px;border:1px solid #e1e4e8;cursor:pointer;" alt="Valid ID">
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Error state -->
        <div id="modalError" class="d-none text-center py-5">
          <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
          <p class="text-muted mt-2">Could not load profile.</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer border-0 pt-0">
        <a id="modalFullProfileLink" href="#" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Profile
        </a>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- Rental Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" data-bs-backdrop="false" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
    <div class="modal-content border-0" style="border-radius:18px; overflow:hidden;">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#0d2c8a,#2979ff);padding:1.2rem 1.5rem;">
        <h5 class="modal-title text-white fw-bold mb-0">
          <i class="bi bi-receipt me-2"></i>Rental #<span id="dtlId"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <div id="dtlLoading" class="text-center py-4">
          <div class="spinner-border text-primary"></div>
          <div class="text-muted mt-2">Loading…</div>
        </div>
        <div id="dtlContent" class="d-none">
          <table class="table table-borderless mb-2" style="font-size:0.9rem;">
            <tr><th width="130">User</th><td id="dtlUser"></td></tr>
            <tr><th>Item</th><td id="dtlItem"></td></tr>
            <tr><th>Rent Date</th><td id="dtlRentDate"></td></tr>
            <tr><th>Return Date</th><td id="dtlReturnDate"></td></tr>
            <tr><th>Status</th><td id="dtlStatus"></td></tr>
            <tr id="dtlActualReturnRow" class="d-none"><th>Returned At</th><td id="dtlActualReturn"></td></tr>
            <tr id="dtlOverstayRow" class="d-none"><th>Overstay</th><td id="dtlOverstay" class="text-danger fw-bold"></td></tr>
          </table>
          <div class="p-3 rounded mb-3" style="background:#f0f4ff;border:1px solid #c5d4f5;">
            <div class="d-flex justify-content-between align-items-center">
              <strong style="color:#0d2c8a;" id="dtlCostLabel">Total Cost</strong>
              <strong class="fs-5" style="color:#0d2c8a;" id="dtlTotal"></strong>
            </div>
            <div id="dtlBookingFeeRow" class="d-none mt-1" style="font-size:0.82rem;">
              <span class="text-muted">Booking fee (20%): </span><strong id="dtlBookingFee"></strong>
            </div>
          </div>
          <div id="dtlReview" class="d-none mb-3 p-3 rounded" style="background:#fffde7;border:1px solid #fff9c4;">
            <div class="fw-semibold mb-1"><i class="bi bi-star-fill text-warning me-1"></i>Customer Review</div>
            <div id="dtlStars" class="mb-1"></div>
            <div id="dtlComment" class="text-muted small fst-italic"></div>
            <div id="dtlReviewDate" class="text-muted small mt-1"></div>
          </div>
        </div>
        <div id="dtlError" class="alert alert-danger d-none"></div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Return Item Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" data-bs-backdrop="false" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
    <div class="modal-content border-0" style="border-radius:18px; overflow:hidden;">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#0d2c8a,#2979ff);padding:1.2rem 1.5rem;">
        <h5 class="modal-title text-white fw-bold mb-0">
          <i class="bi bi-box-arrow-in-left me-2"></i>Process Return #<span id="retId"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <div id="retLoading" class="text-center py-4">
          <div class="spinner-border text-primary"></div>
          <div class="text-muted mt-2">Loading return info…</div>
        </div>
        <div id="retContent" class="d-none">
          <table class="table table-borderless mb-2" style="font-size:0.9rem;">
            <tr><th width="140">User</th><td id="retUser"></td></tr>
            <tr><th>Item</th><td id="retItem"></td></tr>
            <tr><th>Rent Date</th><td id="retRentDate"></td></tr>
            <tr><th>Due Date</th><td id="retDueDate"></td></tr>
            <tr><th>Now</th><td id="retNow"></td></tr>
            <tr><th>Base Cost</th><td id="retBaseCost"></td></tr>
          </table>
          <div id="retAutoExtend" class="alert alert-info py-2 d-none" style="font-size:0.85rem;">
            <i class="bi bi-arrow-up-circle me-1"></i> Auto-extended to longer rate option.
          </div>
          <div id="retLateInfo" class="alert alert-warning py-2 d-none" style="font-size:0.85rem;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong id="retLateHours"></strong> hour(s) overdue. Overstay charge: <strong id="retOverstay"></strong>
          </div>
          <div class="p-3 rounded mb-3" style="background:#f0f4ff;border:1px solid #c5d4f5;">
            <div class="d-flex justify-content-between align-items-center">
              <strong style="color:#0d2c8a;">Total Payable</strong>
              <strong class="fs-5" style="color:#0d2c8a;" id="retTotal"></strong>
            </div>
          </div>
          <form id="retForm" method="POST">
            <?= csrf_field() ?>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-fill" style="background:linear-gradient(90deg,#0d2c8a,#2979ff);border:none;">
                <i class="bi bi-check-circle me-1"></i> Confirm Return
              </button>
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
        <div id="retError" class="alert alert-danger d-none"></div>
      </div>
    </div>
  </div>
</div>

<script>
// Nuke stray modal backdrops
document.addEventListener('hidden.bs.modal', function() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});

function openDetailModal(rentalId) {
    document.getElementById('dtlId').textContent = rentalId;
    document.getElementById('dtlLoading').classList.remove('d-none');
    document.getElementById('dtlContent').classList.add('d-none');
    document.getElementById('dtlError').classList.add('d-none');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();

    fetch(`<?= base_url($urlPrefix . '/rentals') ?>/${rentalId}/detail.json`)
        .then(r => r.json())
        .then(d => {
            if (d.error) { document.getElementById('dtlError').textContent = d.error; document.getElementById('dtlError').classList.remove('d-none'); document.getElementById('dtlLoading').classList.add('d-none'); return; }
            document.getElementById('dtlUser').textContent = d.user_name;
            document.getElementById('dtlItem').textContent = d.item_name;
            document.getElementById('dtlRentDate').textContent = d.rent_date;
            document.getElementById('dtlReturnDate').textContent = d.return_date;

            const statusColors = {booked:'#ffc107',pending:'#17a2b8',ongoing:'#28a745',returned:'#6c757d',overdue:'#dc3545',awaiting_payment:'#fd7e14'};
            document.getElementById('dtlStatus').innerHTML = `<span class="badge px-2 py-1" style="background:${statusColors[d.status]||'#6c757d'}">${d.status.charAt(0).toUpperCase()+d.status.slice(1)}</span>`;

            if (d.status === 'booked') {
                document.getElementById('dtlCostLabel').textContent = 'Est. Total';
                document.getElementById('dtlBookingFeeRow').classList.remove('d-none');
                document.getElementById('dtlBookingFee').textContent = '₱' + Number(d.booking_fee).toLocaleString();
            } else {
                document.getElementById('dtlCostLabel').textContent = 'Total Cost';
                document.getElementById('dtlBookingFeeRow').classList.add('d-none');
            }
            document.getElementById('dtlTotal').textContent = '₱' + Number(d.total_cost).toLocaleString();

            if (d.actual_return_time) {
                document.getElementById('dtlActualReturn').textContent = d.actual_return_time;
                document.getElementById('dtlActualReturnRow').classList.remove('d-none');
            } else { document.getElementById('dtlActualReturnRow').classList.add('d-none'); }

            if (d.overstay_charge > 0) {
                document.getElementById('dtlOverstay').textContent = '₱' + Number(d.overstay_charge).toLocaleString();
                document.getElementById('dtlOverstayRow').classList.remove('d-none');
            } else { document.getElementById('dtlOverstayRow').classList.add('d-none'); }

            if (d.review) {
                let stars = '';
                for (let s = 1; s <= 5; s++) stars += `<i class="bi bi-star${s <= d.review.rating ? '-fill' : ''} text-warning"></i>`;
                stars += ` <span class="fw-bold ms-1">${d.review.rating}/5</span>`;
                document.getElementById('dtlStars').innerHTML = stars;
                document.getElementById('dtlComment').textContent = d.review.comment ? '"' + d.review.comment + '"' : '';
                document.getElementById('dtlReviewDate').textContent = d.review.date;
                document.getElementById('dtlReview').classList.remove('d-none');
            } else { document.getElementById('dtlReview').classList.add('d-none'); }

            document.getElementById('dtlLoading').classList.add('d-none');
            document.getElementById('dtlContent').classList.remove('d-none');
        })
        .catch(() => {
            document.getElementById('dtlError').textContent = 'Failed to load rental details.';
            document.getElementById('dtlError').classList.remove('d-none');
            document.getElementById('dtlLoading').classList.add('d-none');
        });
}

function openReturnModal(rentalId) {
    document.getElementById('retId').textContent = rentalId;
    document.getElementById('retLoading').classList.remove('d-none');
    document.getElementById('retContent').classList.add('d-none');
    document.getElementById('retError').classList.add('d-none');

    bootstrap.Modal.getOrCreateInstance(document.getElementById('returnModal')).show();

    fetch(`<?= base_url($urlPrefix . '/rentals') ?>/${rentalId}/return-info`)
        .then(r => r.json())
        .then(d => {
            if (d.error) { document.getElementById('retError').textContent = d.error; document.getElementById('retError').classList.remove('d-none'); document.getElementById('retLoading').classList.add('d-none'); return; }
            document.getElementById('retUser').textContent = d.user_name;
            document.getElementById('retItem').textContent = d.item_name;
            document.getElementById('retRentDate').textContent = d.rent_date;
            document.getElementById('retDueDate').textContent = d.return_date;
            document.getElementById('retNow').textContent = d.today;
            document.getElementById('retBaseCost').textContent = '₱' + Number(d.base_cost).toLocaleString();
            document.getElementById('retTotal').textContent = '₱' + Number(d.total_payable).toLocaleString();
            document.getElementById('retForm').action = `<?= base_url($urlPrefix . '/rentals') ?>/${rentalId}/return`;

            const extDiv = document.getElementById('retAutoExtend');
            d.auto_extended ? extDiv.classList.remove('d-none') : extDiv.classList.add('d-none');

            const lateDiv = document.getElementById('retLateInfo');
            if (d.is_late) {
                document.getElementById('retLateHours').textContent = d.late_hours;
                document.getElementById('retOverstay').textContent = '₱' + Number(d.overstay_charge).toLocaleString();
                lateDiv.classList.remove('d-none');
            } else { lateDiv.classList.add('d-none'); }

            document.getElementById('retLoading').classList.add('d-none');
            document.getElementById('retContent').classList.remove('d-none');
        })
        .catch(() => {
            document.getElementById('retError').textContent = 'Failed to load return info.';
            document.getElementById('retError').classList.remove('d-none');
            document.getElementById('retLoading').classList.add('d-none');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const modal      = document.getElementById('customerProfileModal');
    const loading    = document.getElementById('modalLoading');
    const content    = document.getElementById('modalContent');
    const errorEl    = document.getElementById('modalError');
    const baseUrl    = '<?= base_url() ?>';
    const profileUrl = '<?= base_url(role_url_prefix() . '/users') ?>';

    document.querySelectorAll('.view-profile-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const userId = this.dataset.userId;

            // Reset
            loading.classList.remove('d-none');
            content.classList.add('d-none');
            errorEl.classList.add('d-none');
            document.getElementById('modalAvatar').innerHTML = '…';

            fetch(`${profileUrl}/${userId}/profile.json`)
                .then(r => r.json())
                .then(u => {
                    // Avatar
                    const avatarEl = document.getElementById('modalAvatar');
                    if (u.avatar_url) {
                        avatarEl.innerHTML = `<img src="${u.avatar_url}" style="width:100%;height:100%;object-fit:cover;">`;
                    } else {
                        avatarEl.textContent = u.name.charAt(0).toUpperCase();
                    }

                    // Header
                    document.getElementById('modalRole').textContent = u.role.charAt(0).toUpperCase() + u.role.slice(1);

                    // Info
                    document.getElementById('modalName').textContent  = u.name;
                    document.getElementById('modalEmail').textContent = u.email;
                    document.getElementById('modalPhone').textContent = u.phone || 'Not provided';

                    // Joined date
                    const joined = new Date(u.created_at);
                    document.getElementById('modalJoined').textContent = joined.toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'});

                    // Stats
                    document.getElementById('modalTotalRentals').textContent  = u.total_rentals;
                    document.getElementById('modalActiveRentals').textContent = u.active_rentals;

                    // Verification banner
                    const banner = document.getElementById('modalVerifyBanner');
                    if (u.is_id_verified) {
                        banner.innerHTML = `<div class="alert alert-success py-2 mb-0 d-flex align-items-center gap-2"><i class="bi bi-patch-check-fill"></i> <span>ID Verified</span> <small class="text-muted ms-auto">${u.id_verified_at ? new Date(u.id_verified_at).toLocaleDateString() : ''}</small></div>`;
                    } else if (u.valid_id) {
                        banner.innerHTML = `<div class="alert alert-warning py-2 mb-0 d-flex align-items-center gap-2"><i class="bi bi-clock-history"></i> ID Pending Verification</div>`;
                    } else {
                        banner.innerHTML = `<div class="alert alert-secondary py-2 mb-0 d-flex align-items-center gap-2"><i class="bi bi-x-circle"></i> No ID submitted</div>`;
                    }

                    // Valid ID section
                    const idSection = document.getElementById('modalIdSection');
                    if (u.valid_id_url) {
                        idSection.classList.remove('d-none');
                        document.getElementById('modalIdType').textContent    = u.valid_id_type || 'Valid ID';
                        document.getElementById('modalIdVerified').innerHTML  = u.is_id_verified
                            ? '<span class="badge bg-success"><i class="bi bi-check-lg"></i> Verified</span>'
                            : '<span class="badge bg-warning text-dark">Pending</span>';
                        document.getElementById('modalIdThumb').src           = u.valid_id_url;
                        document.getElementById('modalIdImage').href          = u.valid_id_url;
                    } else {
                        idSection.classList.add('d-none');
                    }

                    // Full profile link
                    document.getElementById('modalFullProfileLink').href = `${profileUrl}/${userId}/profile`;

                    loading.classList.add('d-none');
                    content.classList.remove('d-none');
                })
                .catch(() => {
                    loading.classList.add('d-none');
                    errorEl.classList.remove('d-none');
                });
        });
    });
});
</script>

<?php $this->endSection(); ?>
