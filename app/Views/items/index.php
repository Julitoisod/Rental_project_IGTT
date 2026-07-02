<?php $this->extend('layout/main'); $this->section('content'); ?>
<?php
$role = session()->get('role');
$canManage = in_array($role, ['snd_admin', 'owner']);
$urlPrefix = match($role) {
    'snd_admin' => '/admin',
    'owner' => '/owner',
    'customer' => '/customer',
    default => '',
};
?>

<style>
    .item-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.14);
    }
    .item-card .card-img-wrap {
        position: relative;
        width: 100%;
        padding-top: 65%;
        background: linear-gradient(135deg, #e8eaf6 0%, #f5f5f5 100%);
        overflow: hidden;
    }
    .item-card .card-img-wrap img {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .item-card:hover .card-img-wrap img {
        transform: scale(1.05);
    }
    .item-card .card-img-wrap .no-img {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #bbb;
    }
    .item-card .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }
    .item-card .category-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        background: rgba(0,0,0,0.55);
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 10px;
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }
    .item-card .card-body {
        padding: 16px 18px 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .item-card .item-name {
        font-weight: 700;
        font-size: 1rem;
        color: #1a1a2e;
        margin-bottom: 2px;
    }
    .item-card .item-desc {
        font-size: 0.78rem;
        color: #8a8fa3;
        margin-bottom: 10px;
        line-height: 1.4;
        flex: 1;
    }
    .item-card .price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    .item-card .price-tag {
        font-weight: 700;
        font-size: 1.05rem;
        color: #0d2c8a;
    }
    .item-card .qty-tag {
        font-size: 0.78rem;
        color: #666;
    }
    .item-card .star-row {
        font-size: 0.78rem;
        margin-top: 4px;
    }
    .item-card .card-actions {
        display: flex;
        gap: 6px;
        padding: 0 18px 14px;
    }
    .item-card .card-actions .btn {
        flex: 1;
        font-size: 0.78rem;
        padding: 5px 0;
    }

    /* View toggle */
    .view-toggle .btn { font-size: 0.82rem; padding: 4px 10px; }
    .view-toggle .btn.active { background: #0d2c8a; color: #fff; border-color: #0d2c8a; }
</style>

<div class="card-section">
    <div class="section-title d-flex align-items-center">
        <i class="bi bi-box-seam text-primary"></i> Items / Inventory

        <!-- View Toggle -->
        <div class="view-toggle btn-group ms-3" role="group">
            <button type="button" class="btn btn-sm btn-outline-secondary active" data-view="grid" title="Grid View">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-view="list" title="List View">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>

        <?php if ($canManage): ?>
            <a href="<?= base_url($urlPrefix . '/items/create') ?>" class="btn btn-primary btn-sm ms-auto">
                <i class="bi bi-plus-lg"></i> Add Item
            </a>
        <?php endif; ?>
    </div>

    <!-- Search & Filter bar -->
    <form class="mb-4" method="GET" action="<?= base_url('items') ?>" id="filterForm">
        <!-- Row 1: keyword + search btn -->
        <div class="row g-2 align-items-end mb-2">
            <div class="col">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Search by name or category…" value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Search</button>
                <?php if ($search || $filterCat || $filterFrom || $filterTo || $filterAvail || $sortBy): ?>
                    <a href="<?= base_url('items') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#advFilters" aria-expanded="false">
                    <i class="bi bi-funnel"></i> Filters
                    <?php if ($filterCat || $filterFrom || $filterTo || $filterAvail || $sortBy): ?>
                        <span class="badge bg-primary ms-1"><?= count(array_filter([$filterCat, $filterFrom, $filterTo, $filterAvail, $sortBy])) ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Row 2: collapsible advanced filters -->
        <div class="collapse <?= ($filterCat || $filterFrom || $filterTo || $filterAvail || $sortBy) ? 'show' : '' ?>" id="advFilters">
            <div class="card card-body py-3 px-3 mb-2" style="background:#f8f9fb; border:1px solid #e3e6f0;">
                <div class="row g-2 align-items-end">
                    <!-- Category -->
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label form-label-sm mb-1 fw-semibold text-muted" style="font-size:.75rem;">
                            <i class="bi bi-tag"></i> Category
                        </label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filterCat == $cat['id']) ? 'selected' : '' ?>>
                                    <?= esc($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Available From -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label form-label-sm mb-1 fw-semibold text-muted" style="font-size:.75rem;">
                            <i class="bi bi-calendar-event"></i> Available From
                        </label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="<?= esc($filterFrom ?? '') ?>" min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Available To -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label form-label-sm mb-1 fw-semibold text-muted" style="font-size:.75rem;">
                            <i class="bi bi-calendar-event-fill"></i> Available To
                        </label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="<?= esc($filterTo ?? '') ?>" min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Availability Status -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label form-label-sm mb-1 fw-semibold text-muted" style="font-size:.75rem;">
                            <i class="bi bi-circle-half"></i> Status
                        </label>
                        <select name="availability" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="available"   <?= ($filterAvail === 'available')   ? 'selected' : '' ?>>Available Now</option>
                            <option value="unavailable" <?= ($filterAvail === 'unavailable') ? 'selected' : '' ?>>Currently Out</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label form-label-sm mb-1 fw-semibold text-muted" style="font-size:.75rem;">
                            <i class="bi bi-sort-down"></i> Sort By
                        </label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="">Latest</option>
                            <option value="name"       <?= ($sortBy === 'name')       ? 'selected' : '' ?>>Name A–Z</option>
                            <option value="price_asc"  <?= ($sortBy === 'price_asc')  ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- ═══ GRID VIEW (default) ═══ -->
    <div id="gridView">
        <?php if (empty($items)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-box-seam" style="font-size:3rem;"></i>
                <p class="mt-2">No items found.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($items as $item): 
                    $rOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?: ['hours' => 12, 'price' => 350];
                    $rOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?: ['hours' => 24, 'price' => 500];
                    $itemRating = $ratings[$item['id']] ?? ['avg' => 0, 'total' => 0];
                    
                    // Check for active rentals on the current date
                    $currentRentals = $activeRentals[$item['id']] ?? [];
                    $now = new DateTime('now');
                    $hasCurrentBooking = false;
                    foreach ($currentRentals as $rental) {
                        $rentDate = new DateTime($rental['rent_date']);
                        $returnDate = new DateTime($rental['return_date']);
                        if ($now >= $rentDate && $now <= $returnDate) {
                            $hasCurrentBooking = true;
                            break;
                        }
                    }
                ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="item-card" style="cursor:pointer"
                         onclick="if(event.target.closest('.card-actions'))return; openItemModal(this)"
                         data-id="<?= $item['id'] ?>"
                         data-name="<?= esc($item['name']) ?>"
                         data-price="<?= $item['price'] ?>"
                         data-qty="<?= $item['quantity'] ?>"
                         data-rate-opt1='<?= json_encode($rOpt1) ?>'
                         data-rate-opt2='<?= json_encode($rOpt2) ?>'
                         data-overstay="<?= esc($item['overstay_charge'] ?? 100) ?>"
                         data-rental-details="<?= esc($item['rental_details'] ?? '') ?>"
                         data-avg-rating="<?= $itemRating['avg'] ?>"
                         data-total-reviews="<?= $itemRating['total'] ?>"
                         data-category="<?= esc($item['category_name'] ?? '—') ?>"
                         data-desc="<?= esc($item['description'] ?? '') ?>"
                         data-img="<?= ! empty($item['image'])  ? base_url('uploads/items/' . $item['image'])  : '' ?>"
                         data-img2="<?= ! empty($item['image2']) ? base_url('uploads/items/' . $item['image2']) : '' ?>"
                         data-img3="<?= ! empty($item['image3']) ? base_url('uploads/items/' . $item['image3']) : '' ?>">
                        <div class="card-img-wrap">
                            <?php if (! empty($item['image'])): ?>
                                <img src="<?= base_url('uploads/items/' . $item['image']) ?>" alt="<?= esc($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-img"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                            <span class="category-badge"><?= esc($item['category_name'] ?? '—') ?></span>
                            <?php if ($hasCurrentBooking): ?>
                                <span class="badge bg-warning text-dark stock-badge"><i class="bi bi-calendar-x"></i> Booked</span>
                            <?php elseif ($item['quantity'] > 0): ?>
                                <span class="badge bg-success stock-badge"><i class="bi bi-check-circle"></i> Available</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark stock-badge"><i class="bi bi-info-circle"></i> See Details</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="item-name"><?= esc($item['name']) ?></div>
                            <div class="item-desc">
                                <?php if (! empty($item['description'])): ?>
                                    <?= esc(mb_substr($item['description'], 0, 70)) ?><?= mb_strlen($item['description'] ?? '') > 70 ? '…' : '' ?>
                                <?php else: ?>
                                    <span class="fst-italic">No description</span>
                                <?php endif; ?>
                            </div>
                            <div class="price-row">
                                <span class="price-tag">₱<?= number_format($rOpt1['price']) ?><small>/<?= $rOpt1['hours'] ?>hrs</small></span>
                                <span class="qty-tag"><i class="bi bi-box"></i> <?= $item['quantity'] ?> pcs</span>
                            </div>
                            <?php if ($itemRating['total'] > 0): ?>
                            <div class="star-row"><?php
                                $avg = $itemRating['avg'];
                                for ($s = 1; $s <= 5; $s++):
                                    if ($avg >= $s) echo '<i class="bi bi-star-fill text-warning"></i>';
                                    elseif ($avg >= $s - 0.5) echo '<i class="bi bi-star-half text-warning"></i>';
                                    else echo '<i class="bi bi-star text-warning"></i>';
                                endfor;
                            ?><small class="ms-1 text-muted"><?= $itemRating['avg'] ?> (<?= $itemRating['total'] ?>)</small></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($canManage): ?>
                        <div class="card-actions" onclick="event.stopPropagation()">
                            <a href="<?= base_url('items/edit/' . $item['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="<?= base_url('items/delete/' . $item['id']) ?>" method="POST" style="flex:1"
                                  data-confirm="Delete this item? This cannot be undone."
                                  data-confirm-type="danger"
                                  data-confirm-title="Delete Item"
                                  data-confirm-btn="Yes, Delete">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="card-actions" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-sm btn-outline-info w-100"
                                    onclick="event.stopPropagation(); event.stopImmediatePropagation(); openAvailabilityChecker(<?= $item['id'] ?>, '<?= esc($item['name']) ?>')">
                                <i class="bi bi-calendar-check"></i> Check Available Dates
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ═══ LIST VIEW (hidden by default) ═══ -->
    <div id="listView" style="display:none;">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Qty</th><th>Rental Rates</th><th>Stock</th>
                        <?php if ($canManage): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No items found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): 
                            // Check for active rentals on the current date
                            $currentRentals = $activeRentals[$item['id']] ?? [];
                            $now = new DateTime('now');
                            $hasCurrentBooking = false;
                            foreach ($currentRentals as $rental) {
                                $rentDate = new DateTime($rental['rent_date']);
                                $returnDate = new DateTime($rental['return_date']);
                                if ($now >= $rentDate && $now <= $returnDate) {
                                    $hasCurrentBooking = true;
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td>
                                <?php if (! empty($item['image'])): ?>
                                    <img src="<?= base_url('uploads/items/' . $item['image']) ?>"
                                         alt="<?= esc($item['name']) ?>"
                                         style="width:48px; height:48px; object-fit:cover; border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;border-radius:8px;background:#e8eaf6;display:flex;align-items:center;justify-content:center;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($item['name']) ?></strong>
                                <?php if (! empty($item['description'])): ?>
                                    <br><small class="text-muted"><?= esc(mb_substr($item['description'], 0, 50)) ?><?= mb_strlen($item['description'] ?? '') > 50 ? '…' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($item['category_name'] ?? '—') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <?php
                                $lr1 = json_decode($item['rate_option_1'] ?? '{}', true) ?: ['hours' => 12, 'price' => 350];
                                $lr2 = json_decode($item['rate_option_2'] ?? '{}', true) ?: ['hours' => 24, 'price' => 500];
                            ?>
                            <td>
                                <div>₱<?= number_format($lr1['price']) ?> / <?= $lr1['hours'] ?>hrs</div>
                                <div>₱<?= number_format($lr2['price']) ?> / <?= $lr2['hours'] ?>hrs</div>
                            </td>
                            <td>
                                <?php if ($hasCurrentBooking): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-calendar-x"></i> Booked Today</span>
                                <?php elseif ($item['quantity'] > 0): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark"><i class="bi bi-info-circle"></i> See Details</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($canManage): ?>
                            <td>
                                <a href="<?= base_url('items/edit/' . $item['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= base_url('items/delete/' . $item['id']) ?>" method="POST" class="d-inline"
                                      data-confirm="Delete this item? This cannot be undone."
                                      data-confirm-type="danger"
                                      data-confirm-title="Delete Item"
                                      data-confirm-btn="Yes, Delete">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                            <?php elseif (!empty($currentRentals)): ?>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="openAvailabilityChecker(<?= $item['id'] ?>, '<?= esc($item['name']) ?>')">
                                    <i class="bi bi-calendar-check"></i> Check Dates
                                </button>
                            </td>
                            <?php else: ?>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="openItemModalById(<?= $item['id'] ?>)">
                                    <i class="bi bi-calendar-check"></i> Rent
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <?= $pager->links() ?>
    </div>
</div>

<script>
document.querySelectorAll('.view-toggle .btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-toggle .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const view = this.dataset.view;
        document.getElementById('gridView').style.display = view === 'grid' ? '' : 'none';
        document.getElementById('listView').style.display = view === 'list' ? '' : 'none';
        localStorage.setItem('itemsView', view);
    });
});
const saved = localStorage.getItem('itemsView');
if (saved === 'list') {
    document.querySelector('[data-view="list"]').click();
}
</script>

<!-- ═══ ITEM DETAIL + RENTAL REQUEST MODAL ═══ -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-modal="true" role="dialog" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:500px; margin-top:80px;">
        <div class="modal-content border-0" style="border-radius:18px; max-height:calc(100vh - 100px); overflow:hidden;">

            <!-- ── Image Gallery ── -->
            <div id="galleryWrap" style="position:relative; width:100%; background:#f8f9fa; border-radius:18px 18px 0 0; text-align:center;"
                 onmouseenter="document.getElementById('arrowPrev').style.opacity='1'; document.getElementById('arrowNext').style.opacity='1';"
                 onmouseleave="document.getElementById('arrowPrev').style.opacity='0'; document.getElementById('arrowNext').style.opacity='0';">

                <!-- Main image -->
                <img id="modalImg" src="" alt=""
                     style="display:none; max-width:100%; max-height:120px; width:auto; height:auto; border-radius:18px 18px 0 0; object-fit:contain; padding:8px;">
                <div id="modalImgPlaceholder"
                     style="width:100%; height:90px; display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:#bbb; background:#f8f9fa; border-radius:18px 18px 0 0;">
                    <i class="bi bi-image"></i>
                </div>

                <!-- Prev arrow -->
                <button id="arrowPrev" onclick="galleryNav(-1)" type="button"
                        style="position:absolute; left:14px; top:50%; transform:translateY(-50%); width:44px; height:44px; border-radius:50%; background:rgba(0,0,0,0.45); border:none; color:#fff; font-size:1.4rem; display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:opacity .2s; z-index:10;">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <!-- Next arrow -->
                <button id="arrowNext" onclick="galleryNav(1)" type="button"
                        style="position:absolute; right:14px; top:50%; transform:translateY(-50%); width:44px; height:44px; border-radius:50%; background:rgba(0,0,0,0.45); border:none; color:#fff; font-size:1.4rem; display:flex; align-items:center; justify-content:center; cursor:pointer; opacity:0; transition:opacity .2s; z-index:10;">
                    <i class="bi bi-chevron-right"></i>
                </button>

                <!-- Close button (dark for light bg) -->
                <button type="button" class="btn-close"
                        style="position:absolute; top:12px; right:14px; z-index:20; background-color:rgba(0,0,0,0.15); border-radius:50%; padding:8px;"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-2">
                <!-- Item name & badge -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 id="modalItemName" class="fw-bold mb-0" style="color:#0d2c8a;"></h5>
                        <span id="modalCategory" class="text-muted" style="font-size:0.82rem;"></span>
                        <div id="modalAvgRating" class="mt-1" style="font-size:0.82rem;"></div>
                    </div>
                    <span id="modalStockBadge" class="badge bg-success fs-6 ms-2"></span>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <div style="background:#f0f4ff; border-radius:10px; padding:12px 16px;">
                            <div style="font-size:0.72rem; color:#8a8fa3; text-transform:uppercase; letter-spacing:.5px;">Rate Option 1</div>
                            <div id="modalRate1" style="font-size:1rem; font-weight:800; color:#0d2c8a;"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background:#fff8f0; border-radius:10px; padding:12px 16px;">
                            <div style="font-size:0.72rem; color:#8a8fa3; text-transform:uppercase; letter-spacing:.5px;">Rate Option 2</div>
                            <div id="modalRate2" style="font-size:1rem; font-weight:800; color:#e65100;"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background:#f0fff4; border-radius:10px; padding:12px 16px;">
                            <div style="font-size:0.72rem; color:#8a8fa3; text-transform:uppercase; letter-spacing:.5px;">Stock</div>
                            <div id="modalQty" style="font-size:1.2rem; font-weight:800; color:#2e7d32;"></div>
                        </div>
                    </div>
                </div>

                <p id="modalDesc" class="text-muted" style="font-size:0.88rem; line-height:1.6;"></p>

                <hr class="my-3">

                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-plus text-primary me-1"></i> Request This Item</h6>

                <?php if (session()->get('role') === 'customer' && !($isVerified ?? true)): ?>
                <!-- ── Verification required notice ── -->
                <div class="alert alert-warning d-flex gap-3 align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-4 mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="fw-bold mb-1">Account Verification Required</div>
                        <p class="mb-2" style="font-size:0.85rem;">You must verify your <strong>Email</strong> and <strong>ID</strong> before you can rent or book any item.</p>
                        <a href="<?= base_url('profile') ?>" class="btn btn-sm btn-warning fw-semibold">
                            <i class="bi bi-person-check me-1"></i> Go to My Profile &amp; Verify
                        </a>
                    </div>
                </div>
                <?php else: ?>

                <form action="<?= base_url('rentals/store') ?>" method="POST" id="modalRentalForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="from" value="items">
                    <input type="hidden" name="item_id" id="modalItemId">

                    <?php if (in_array(session()->get('role'), ['snd_admin', 'owner'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php foreach (($users ?? []) as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Rate Option Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Rental Duration</label>
                        <div id="modalRateOptions" class="d-flex gap-2 flex-wrap">
                            <!-- Populated by JS when modal opens -->
                        </div>
                    </div>

                    <!-- Rental Terms -->
                    <div id="modalRentalTerms" class="alert alert-warning py-2 px-3 mb-3 small" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> <span id="modalRentalTermsText"></span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rent Date & Time</label>
                            <input type="datetime-local" name="rent_date" id="modalRentDate" class="form-control"
                                   oninput="recalcModal()" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Return Date & Time</label>
                            <input type="datetime-local" name="return_date" id="modalReturnDate" class="form-control"
                                   oninput="recalcModal()" required>
                        </div>
                    </div>

                    <!-- Live cost preview -->
                    <div id="modalCostPreview" class="alert alert-info py-2 px-3 mb-3" style="display:none;">
                        <div>
                            <i class="bi bi-calculator me-1"></i>
                            <strong id="modalDays">0</strong><span id="modalPriceInline"></span> =
                            <strong>₱ <span id="modalTotal">0</span></strong>
                        </div>
                        <div class="mt-1 border-top pt-1 small">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>
                            Booking fee <span class="text-muted">(20% deposit if booking)</span>:
                            <strong class="text-primary">₱ <span id="modalBookingFee">0</span></strong>
                        </div>
                    </div>

                    <!-- Time-slot availability indicator -->
                    <div id="modalAvailStatus" class="mb-3" style="display:none;"></div>

                    <?php if (session()->get('role') === 'customer'): ?>
                    <!-- Wallet payment option -->
                    <div class="mb-3 p-3 rounded" style="background:#f0f4ff;border:1px solid #c5d4f5;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-wallet2 text-primary me-1"></i>
                                <span class="fw-semibold" style="font-size:0.9rem;">Pay with Wallet</span>
                                <span class="text-muted ms-2" style="font-size:0.82rem;">
                                    Balance: <strong>₱<?= number_format($walletBalance, 2) ?></strong>
                                </span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="pay_with_wallet"
                                       id="modalPayWallet" value="1" role="switch"
                                       onchange="updateWalletPayUI()">
                            </div>
                        </div>
                        <div id="walletPayNote" class="mt-2 small text-muted" style="display:none;">
                            <i class="bi bi-info-circle me-1"></i>
                            <span id="walletPayLabel">Amount will be deducted from your wallet.</span>
                        </div>
                        <div id="walletInsufficientMsg" class="mt-2 small text-danger fw-semibold" style="display:none;">
                            <i class="bi bi-exclamation-triangle me-1"></i> Insufficient wallet balance.
                            <a href="<?= base_url('wallet/topup') ?>" target="_blank">Top up now</a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <input type="hidden" name="rent_type" id="modalRentType" value="rental">
                    <!-- Hidden payment reference file (filled by payment modal) -->
                    <input type="file" name="payment_reference" id="modalPaymentRefFile" style="display:none;" accept="image/*">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary flex-fill" id="modalBookBtn"
                                onclick="handleBooking()">
                            <i class="bi bi-calendar-check me-1"></i> Book
                        </button>
                        <button type="button" class="btn btn-primary flex-fill" id="modalSubmitBtn"
                                onclick="handleRentNow()">
                            <i class="bi bi-send me-1"></i> Rent Now
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
                <?php endif; // end verification check ?>
            </div>

        </div>
    </div>
</div>

<!-- ═══ PAYMENT GATEWAY MODAL ═══ -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="false" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-credit-card-2-front text-primary me-2"></i>Complete Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="cancelPayment()"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">
                    Pay via the owner's payment method below, then upload a screenshot/photo of your payment as proof.
                </p>

                <!-- Amount to pay -->
                <div class="alert alert-info p-2 mb-3" style="font-size:.9rem;">
                    <i class="bi bi-cash-coin me-1"></i>
                    Amount to pay: <strong id="pmtAmount">₱0</strong>
                </div>

                <!-- Gateway list -->
                <div id="pmtGatewayList" class="mb-3">
                    <div class="text-center text-muted py-3">
                        <div class="spinner-border spinner-border-sm me-1"></div> Loading payment options…
                    </div>
                </div>

                <!-- Upload proof -->
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.88rem;">
                        <i class="bi bi-image me-1"></i> Upload Payment Screenshot / Proof <span class="text-danger">*</span>
                    </label>
                    <input type="file" id="pmtRefInput" class="form-control form-control-sm" accept="image/*"
                           onchange="previewProof(this)">
                    <div id="pmtProofPreview" class="mt-2" style="display:none;">
                        <img id="pmtProofImg" src="" alt="Proof Preview"
                             style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #ddd;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="cancelPayment()">Cancel</button>
                <button type="button" class="btn btn-success" id="pmtSubmitBtn" onclick="submitWithPayment()">
                    <i class="bi bi-check-circle me-1"></i> Submit Rental + Proof
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ AVAILABILITY CHECKER MODAL ═══ -->
<div class="modal fade" id="availabilityCheckModal" tabindex="-1" aria-labelledby="availCheckLabel" aria-modal="true" role="dialog" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content border-0" style="border-radius:18px;">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title" id="availCheckLabel">
                        <i class="bi bi-calendar-range text-primary me-2"></i>Check Availability
                    </h5>
                    <small class="text-muted">for <strong id="availCheckItemName"></strong></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" id="availCheckStartDate" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" id="availCheckEndDate" class="form-control" required>
                    </div>
                </div>
                <button type="button" class="btn btn-primary w-100" onclick="checkAvailability()">
                    <i class="bi bi-search me-2"></i>Check Available Dates
                </button>
                <div id="availCheckResults" class="mt-3"></div>
                <input type="hidden" id="availCheckItemId">
            </div>
        </div>
    </div>
</div>

<script>
// Returns current local datetime in YYYY-MM-DDTHH:MM format
function nowLocal() {
    const d = new Date();
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

// ── Nuke all modal backdrops on load and events ──
document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
document.addEventListener('hidden.bs.modal', function() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});
document.addEventListener('shown.bs.modal', function() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
});

// ── Open item modal manually (avoids data-bs-toggle conflict with child buttons) ──
function openItemModal(card) {
    const modalEl = document.getElementById('itemModal');
    modalEl._triggerCard = card;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show(card);
}
function openItemModalById(itemId) {
    const card = document.querySelector('.item-card[data-id="' + itemId + '"]');
    if (card) openItemModal(card);
}

// ── Item Modal (runs AFTER modal HTML exists) ──
const itemModal = document.getElementById('itemModal');
if (itemModal) {
    itemModal.addEventListener('show.bs.modal', function(e) {
        const card = e.relatedTarget || itemModal._triggerCard;
        itemModal._triggerCard = null;
        if (!card) return;

        const name     = card.dataset.name;
        const price    = parseInt(card.dataset.price);
        const qty      = parseInt(card.dataset.qty);
        const category = card.dataset.category;
        const desc     = card.dataset.desc;
        const img      = card.dataset.img;
        const img2     = card.dataset.img2;
        const img3     = card.dataset.img3;
        const itemId   = card.dataset.id;
        const rateOpt1 = JSON.parse(card.dataset.rateOpt1 || '{"hours":12,"price":350}');
        const rateOpt2 = JSON.parse(card.dataset.rateOpt2 || '{"hours":24,"price":500}');
        const overstay = card.dataset.overstay || 100;
        const rentalDetails = card.dataset.rentalDetails || '';
        const avgRating    = parseFloat(card.dataset.avgRating || 0);
        const totalReviews = parseInt(card.dataset.totalReviews || 0);

        document.getElementById('modalItemId').value           = itemId;
        document.getElementById('modalItemName').textContent   = name;
        document.getElementById('modalCategory').textContent   = category;
        document.getElementById('modalRate1').innerHTML        = '₱' + parseInt(rateOpt1.price).toLocaleString() + '<small>/' + rateOpt1.hours + 'hrs</small>';
        document.getElementById('modalRate2').innerHTML        = '₱' + parseInt(rateOpt2.price).toLocaleString() + '<small>/' + rateOpt2.hours + 'hrs</small>';
        document.getElementById('modalQty').textContent        = qty + ' pcs';
        document.getElementById('modalDesc').textContent       = desc || 'No description available.';

        // Populate star rating in modal
        const ratingEl = document.getElementById('modalAvgRating');
        if (ratingEl) {
            if (totalReviews > 0) {
                let stars = '';
                for (let s = 1; s <= 5; s++) {
                    if (avgRating >= s) stars += '<i class="bi bi-star-fill text-warning"></i>';
                    else if (avgRating >= s - 0.5) stars += '<i class="bi bi-star-half text-warning"></i>';
                    else stars += '<i class="bi bi-star text-warning"></i>';
                }
                ratingEl.innerHTML = stars + ` <small class="text-muted">${avgRating} (${totalReviews} review${totalReviews > 1 ? 's' : ''})</small>`;
                ratingEl.style.display = '';
            } else {
                ratingEl.style.display = 'none';
            }
        }

        // Populate rate option buttons
        const rateOptionsDiv = document.getElementById('modalRateOptions');
        rateOptionsDiv.innerHTML = `
            <label class="flex-fill" style="cursor:pointer;">
                <input type="radio" name="rate_option" value="1" style="display:none;" checked
                       onchange="onRateChange(1, ${rateOpt1.hours}, ${rateOpt1.price})">
                <div class="rate-opt-btn selected border rounded p-2 text-center" id="rateBtn1"
                     onclick="document.querySelector('[name=rate_option][value=\\'1\\']').click()" 
                     style="border: 2px solid #0d2c8a !important; background:#f0f4ff;">
                    <div class="fw-bold text-primary">${rateOpt1.hours} Hours</div>
                    <div class="fw-bold">₱${parseInt(rateOpt1.price).toLocaleString()}</div>
                </div>
            </label>
            <label class="flex-fill" style="cursor:pointer;">
                <input type="radio" name="rate_option" value="2" style="display:none;"
                       onchange="onRateChange(2, ${rateOpt2.hours}, ${rateOpt2.price})">
                <div class="rate-opt-btn border rounded p-2 text-center" id="rateBtn2"
                     onclick="document.querySelector('[name=rate_option][value=\\'2\\']').click()"
                     style="border: 2px solid #dee2e6 !important;">
                    <div class="fw-bold">${rateOpt2.hours} Hours</div>
                    <div class="fw-bold">₱${parseInt(rateOpt2.price).toLocaleString()}</div>
                </div>
            </label>
        `;

        // Show rental terms if set
        const termsDiv = document.getElementById('modalRentalTerms');
        if (rentalDetails) {
            document.getElementById('modalRentalTermsText').textContent = rentalDetails;
            termsDiv.style.display = '';
        } else {
            termsDiv.style.display = 'none';
        }

        // Auto-set return date based on selected rate option (default opt1)
        itemModal._selectedRateHours = rateOpt1.hours;
        itemModal._selectedRatePrice = rateOpt1.price;

        const imgEl = document.getElementById('modalImg');
        const placeholder = document.getElementById('modalImgPlaceholder');

        // Build gallery images array (filter empty)
        const allImgs = [img, img2, img3].filter(Boolean);
        itemModal._imgs  = allImgs;
        itemModal._imgIdx = 0;

        function setMainImage(idx) {
            itemModal._imgIdx = idx;
            const src = allImgs[idx];
            imgEl.src = src;
            imgEl.style.display = '';
            placeholder.style.display = 'none';
        }

        // Set main header image (first available)
        if (allImgs.length > 0) {
            imgEl.src = allImgs[0];
            imgEl.style.display = '';
            placeholder.style.display = 'none';
            imgEl.onerror = function() {
                imgEl.style.display = 'none';
                placeholder.style.display = '';
            };
        } else {
            imgEl.style.display = 'none';
            placeholder.style.display = '';
        }

        // Show/hide arrows
        document.getElementById('arrowPrev').style.display = allImgs.length > 1 ? 'flex' : 'none';
        document.getElementById('arrowNext').style.display = allImgs.length > 1 ? 'flex' : 'none';

        const stockBadge = document.getElementById('modalStockBadge');
        stockBadge.textContent = qty > 0 ? 'Available' : 'Not Available';
        stockBadge.className   = 'badge ' + (qty > 0 ? 'bg-success' : 'bg-danger') + ' fs-6';

        const unavailable = qty < 1;
        document.getElementById('modalSubmitBtn').disabled = unavailable;
        document.getElementById('modalBookBtn').disabled   = unavailable;

        // Always reset rent date to current time when modal opens (unless dates are pre-set)
        const now = nowLocal();
        const rentInput   = document.getElementById('modalRentDate');
        const returnInput = document.getElementById('modalReturnDate');
        rentInput.min     = now;
        returnInput.min   = now;

        if (itemModal._pendingDates) {
            rentInput.value = itemModal._pendingDates.rent;
            returnInput.value = itemModal._pendingDates.ret;
            itemModal._pendingDates = null;
        } else {
            rentInput.value = now;
            // Auto-set return date based on selected rate option 1
            const autoReturn = new Date(new Date(now).getTime() + rateOpt1.hours * 3600000);
            const _pad = n => String(n).padStart(2,'0');
            returnInput.value = `${autoReturn.getFullYear()}-${_pad(autoReturn.getMonth()+1)}-${_pad(autoReturn.getDate())}T${_pad(autoReturn.getHours())}:${_pad(autoReturn.getMinutes())}`;
        }

        itemModal._price = price;
        itemModal._rateOpt1 = rateOpt1;
        itemModal._rateOpt2 = rateOpt2;
        itemModal._overstay = parseFloat(overstay);
        recalcModal();
    });

    // Accessibility: blur focused element before modal hides
    itemModal.addEventListener('hide.bs.modal', function() {
        const focused = itemModal.querySelector(':focus');
        if (focused) focused.blur();
    });
}

function onRateChange(optNum, hours, price) {
    const m = document.getElementById('itemModal');
    m._selectedRateHours = hours;
    m._selectedRatePrice = price;

    // Update button styles
    document.getElementById('rateBtn1').style.cssText = optNum === 1
        ? 'border: 2px solid #0d2c8a !important; background:#f0f4ff;'
        : 'border: 2px solid #dee2e6 !important; background:#fff;';
    document.getElementById('rateBtn2').style.cssText = optNum === 2
        ? 'border: 2px solid #0d2c8a !important; background:#f0f4ff;'
        : 'border: 2px solid #dee2e6 !important; background:#fff;';

    // Auto-update return date based on selected hours
    const rentDate = document.getElementById('modalRentDate').value;
    if (rentDate) {
        const autoReturn = new Date(new Date(rentDate).getTime() + hours * 3600000);
        const _pad = n => String(n).padStart(2,'0');
        document.getElementById('modalReturnDate').value =
            `${autoReturn.getFullYear()}-${_pad(autoReturn.getMonth()+1)}-${_pad(autoReturn.getDate())}T${_pad(autoReturn.getHours())}:${_pad(autoReturn.getMinutes())}`;
    }
    recalcModal();
}

function galleryNav(dir) {
    const m = document.getElementById('itemModal');
    if (!m || !m._imgs || m._imgs.length < 2) return;
    const total = m._imgs.length;
    const newIdx = (m._imgIdx + dir + total) % total;
    const imgEl = document.getElementById('modalImg');
    imgEl.src = m._imgs[newIdx];
    imgEl.style.display = '';
    m._imgIdx = newIdx;
}

function recalcModal() {
    const m = document.getElementById('itemModal');
    if (!m) return;
    const rateOpt1   = m._rateOpt1 || {hours:12,price:350};
    const rateOpt2   = m._rateOpt2 || {hours:24,price:500};
    const overstay   = m._overstay || 100;
    const selectedPrice = m._selectedRatePrice || rateOpt1.price;
    const selectedHours = m._selectedRateHours || rateOpt1.hours;
    const itemId     = document.getElementById('modalItemId').value;
    const rentDate   = document.getElementById('modalRentDate').value;
    const returnDate = document.getElementById('modalReturnDate').value;
    const preview    = document.getElementById('modalCostPreview');
    const availDiv   = document.getElementById('modalAvailStatus');

    if (rentDate && returnDate && returnDate > rentDate) {
        const hours = Math.ceil((new Date(returnDate) - new Date(rentDate)) / 3600000);
        let total = selectedPrice;
        let rateLabel = selectedHours + 'hrs';

        // If return is later than selected option, auto-extend logic
        if (hours > rateOpt2.hours) {
            const extraHours = hours - rateOpt2.hours;
            total = rateOpt2.price + (extraHours * overstay);
            rateLabel = rateOpt2.hours + 'hrs + overstay';
        } else if (hours > rateOpt1.hours && selectedHours === rateOpt1.hours) {
            total = rateOpt2.price;
            rateLabel = rateOpt2.hours + 'hrs (auto-extended)';
        }

        const bookingFee = Math.ceil(total * 0.20);
        document.getElementById('modalDays').textContent        = hours + ' (' + rateLabel + ')';
        document.getElementById('modalTotal').textContent       = total.toLocaleString();
        document.getElementById('modalPriceInline').textContent = '';
        document.getElementById('modalBookingFee').textContent  = bookingFee.toLocaleString();
        preview.style.display = '';

        // Live availability check (debounced 600ms)
        if (availDiv) {
            availDiv.innerHTML   = '<span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Checking availability...</span>';
            availDiv.style.display = '';
        }
        clearTimeout(window._availTimer);
        window._availTimer = setTimeout(() => _checkTimeSlot(itemId, rentDate, returnDate), 600);
    } else {
        preview.style.display = 'none';
        if (availDiv) availDiv.style.display = 'none';
    }
}

function _checkTimeSlot(itemId, rentDate, returnDate) {
    const availDiv = document.getElementById('modalAvailStatus');
    const bookBtn  = document.getElementById('modalBookBtn');
    const rentBtn  = document.getElementById('modalSubmitBtn');
    const url = `<?= base_url('rentals/check-availability') ?>?item_id=${itemId}&rent_date=${encodeURIComponent(rentDate)}&return_date=${encodeURIComponent(returnDate)}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!availDiv) return;
            if (data.available) {
                availDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success small fw-semibold">Available for this time slot</span>';
                if (bookBtn) bookBtn.disabled = false;
                if (rentBtn) rentBtn.disabled = false;
            } else {
                availDiv.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger small fw-semibold">' + (data.message || 'Fully reserved for this time.') + '</span>';
                if (bookBtn) bookBtn.disabled = true;
                if (rentBtn) rentBtn.disabled = true;
            }
        })
        .catch(() => {
            if (availDiv) availDiv.innerHTML = '<span class="text-muted small">Could not check availability.</span>';
        });
}

// Availability Checker Modal Function
function openAvailabilityChecker(itemId, itemName) {
    const itemModalEl = document.getElementById('itemModal');

    const showChecker = () => {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('availabilityCheckModal'));
        document.getElementById('availCheckItemId').value = itemId;
        document.getElementById('availCheckItemName').textContent = itemName;
        document.getElementById('availCheckStartDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('availCheckEndDate').value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        document.getElementById('availCheckResults').innerHTML = '';
        modal.show();
    };

    // If item modal is open, wait for it to fully hide before showing checker
    const existing = itemModalEl ? bootstrap.Modal.getInstance(itemModalEl) : null;
    if (existing && itemModalEl.classList.contains('show')) {
        itemModalEl.addEventListener('hidden.bs.modal', function handler() {
            itemModalEl.removeEventListener('hidden.bs.modal', handler);
            showChecker();
        });
        existing.hide();
    } else {
        showChecker();
    }
}

// Open availability checker from the rental modal
function openAvailabilityCheckerFromModal() {
    const itemId = document.getElementById('modalItemId').value;
    const itemName = document.getElementById('modalItemName').textContent;
    
    if (!itemId) {
        alert('Item not found');
        return;
    }
    
    // Hide the rental modal then open checker (openAvailabilityChecker handles the transition)
    openAvailabilityChecker(itemId, itemName);
}

function checkAvailability() {
    const itemId = document.getElementById('availCheckItemId').value;
    const startDate = document.getElementById('availCheckStartDate').value;
    const endDate = document.getElementById('availCheckEndDate').value;
    const resultsDiv = document.getElementById('availCheckResults');
    
    if (!itemId || !startDate || !endDate) {
        alert('Please fill in all date fields');
        return;
    }
    
    resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading calendar...</div>';
    
    const url = `<?= base_url('rentals/check-availability-range') ?>?item_id=${itemId}&start_date=${startDate}&end_date=${endDate}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            
            // Build booked dates set for quick lookup (using string comparison to avoid timezone issues)
            const bookedSet = new Set();
            if (data.booked_dates && data.booked_dates.length > 0) {
                data.booked_dates.forEach(period => {
                    // Extract just YYYY-MM-DD from the datetime strings
                    const rentDateStr = period.rent_date.substring(0, 10); // "2026-04-13"
                    const returnDateStr = period.return_date.substring(0, 10); // "2026-04-19"
                    
                    // Use string-based date comparison (works correctly without timezone issues)
                    let currentDate = rentDateStr; // Start with rent date
                    while (currentDate <= returnDateStr) {
                        bookedSet.add(currentDate);
                        
                        // Increment date by 1 day using string manipulation
                        const parts = currentDate.split('-');
                        let year = parseInt(parts[0]);
                        let month = parseInt(parts[1]);
                        let day = parseInt(parts[2]) + 1;
                        
                        // Handle month/year overflow
                        if (day > 31 || (day > 30 && [4,6,9,11].includes(month)) || (day > 28 && month === 2) || (day > 29 && month === 2 && year % 4 !== 0)) {
                            day = 1;
                            month++;
                            if (month > 12) {
                                month = 1;
                                year++;
                            }
                        }
                        
                        currentDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    }
                });
            }
            
            // Build calendar
            let html = '<div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">';
            html += '<div style="margin-bottom: 20px;">';
            html += '<div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 15px;">';
            html += '<div><span style="display: inline-block; width: 20px; height: 20px; background: white; border: 2px solid #ddd; border-radius: 4px; margin-right: 5px;"></span><small>Available</small></div>';
            html += '<div><span style="display: inline-block; width: 20px; height: 20px; background: #dc3545; border-radius: 4px; margin-right: 5px;"></span><small>Not Available</small></div>';
            html += '</div>';
            
            // Generate calendar grid
            html += '<div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 15px;">';
            const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayLabels.forEach(day => {
                html += `<div style="text-align: center; font-weight: bold; font-size: 0.8rem; padding: 8px 0; color: #666;">${day}</div>`;
            });
            
            // Use string-based date iteration to avoid timezone issues
            let currentDisplay = startDate; // Start with YYYY-MM-DD format
            
            // Padding for first day of month
            const startDateObj = new Date(startDate + 'T00:00:00');
            const firstDay = new Date(startDateObj.getFullYear(), startDateObj.getMonth(), 1);
            for (let i = 0; i < firstDay.getDay(); i++) {
                html += '<div style="padding: 8px;"></div>';
            }
            
            // Calendar days - using string-based iteration
            while (currentDisplay <= endDate) {
                const isBooked = bookedSet.has(currentDisplay);
                
                // Get day of week
                const tempDate = new Date(currentDisplay + 'T00:00:00');
                const dayOfWeek = tempDate.getDay();
                const dayNum = parseInt(currentDisplay.substring(8, 10)); // Extract day number
                
                const bgColor = isBooked ? '#dc3545' : '#fff';
                const textColor = isBooked ? '#fff' : '#333';
                const cursor = isBooked ? 'not-allowed' : 'pointer';
                const onClick = isBooked ? '' : `onclick="selectCalendarDate('${currentDisplay}')"`;
                
                html += `
                    <div style="
                        padding: 8px;
                        background: ${bgColor};
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        text-align: center;
                        font-size: 0.9rem;
                        cursor: ${cursor};
                        transition: all 0.2s;
                    " ${onClick}
                    ${!isBooked ? 'onmouseover="this.style.background=\'#e8f4f8\'; this.style.borderColor=\'#0d7b8a\';" onmouseout="this.style.background=\'#fff\'; this.style.borderColor=\'#ddd\';"' : ''}
                    title="${isBooked ? 'Not Available' : 'Available - Click to select'}"
                    >
                        <strong style="color: ${textColor};">${dayNum}</strong>
                    </div>
                `;
                
                // Increment date by 1 day
                const parts = currentDisplay.split('-');
                let year = parseInt(parts[0]);
                let month = parseInt(parts[1]);
                let day = parseInt(parts[2]) + 1;
                
                // Handle month/year overflow
                if (day > 31 || (day > 30 && [4,6,9,11].includes(month)) || (day > 28 && month === 2) || (day > 29 && month === 2 && year % 4 !== 0)) {
                    day = 1;
                    month++;
                    if (month > 12) {
                        month = 1;
                        year++;
                    }
                }
                
                currentDisplay = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            }
            html += '</div>';
            
            // Selected dates display
            html += '<div class="alert alert-info" style="margin-bottom: 15px;">';
            html += '<strong>Selected Dates:</strong> <span id="selectedDatesDisplay">None</span>';
            html += '</div>';
            
            // Proceed button
            html += '<button type="button" class="btn btn-primary w-100" onclick="proceedWithSelectedDates(' + itemId + ')">';
            html += '<i class="bi bi-calendar-check me-2"></i>Proceed with Selected Dates</button>';
            html += '</div>';
            
            resultsDiv.innerHTML = html;
            
            // Initialize selected dates
            window.calendarSelectedStart = null;
            window.calendarSelectedEnd = null;
        })
        .catch(err => {
            resultsDiv.innerHTML = '<div class="alert alert-danger">Error loading calendar. Please try again.</div>';
        });
}

// Select date on calendar
function selectCalendarDate(dateStr) {
    // Single click sets both start and end to same date
    window.calendarSelectedStart = dateStr;
    window.calendarSelectedEnd = dateStr;
    updateCalendarDisplay();
}

function updateCalendarDisplay() {
    const display = document.getElementById('selectedDatesDisplay');
    if (window.calendarSelectedStart && window.calendarSelectedEnd) {
        const startDisplay = new Date(window.calendarSelectedStart).toLocaleDateString();
        const endDisplay = new Date(window.calendarSelectedEnd).toLocaleDateString();
        display.textContent = `${startDisplay} to ${endDisplay}`;
    } else {
        display.textContent = 'None';
    }
}

function proceedWithSelectedDates(itemId) {
    if (!window.calendarSelectedStart || !window.calendarSelectedEnd) {
        alert('Please select dates from the calendar');
        return;
    }
    
    const availEl = document.getElementById('availabilityCheckModal');
    const availInst = bootstrap.Modal.getInstance(availEl);
    
    availEl.addEventListener('hidden.bs.modal', function handler() {
        availEl.removeEventListener('hidden.bs.modal', handler);
        
        // Store dates to apply after modal opens
        const modalEl = document.getElementById('itemModal');
        modalEl._pendingDates = {
            rent: window.calendarSelectedStart + 'T00:00',
            ret: window.calendarSelectedEnd + 'T23:59'
        };
        
        // Open the item modal with card data
        const card = document.querySelector('.item-card[data-id="' + itemId + '"]');
        openItemModal(card);
    });
    
    availInst.hide();
}

function proceedWithRental(itemId, startDate, endDate) {
    const availEl = document.getElementById('availabilityCheckModal');
    const availInst = bootstrap.Modal.getInstance(availEl);
    
    availEl.addEventListener('hidden.bs.modal', function handler() {
        availEl.removeEventListener('hidden.bs.modal', handler);
        
        // Store dates to apply after modal opens
        const modalEl = document.getElementById('itemModal');
        modalEl._pendingDates = {
            rent: startDate + 'T00:00',
            ret: endDate + 'T23:59'
        };
        
        // Open the item modal with card data
        const card = document.querySelector('.item-card[data-id="' + itemId + '"]');
        openItemModal(card);
    });
    
    availInst.hide();
}

<?php if (session()->get('role') === 'customer'): ?>
const _walletBalance = <?= json_encode((float)($walletBalance ?? 0)) ?>;

function handleBooking() {
    const walletChecked = document.getElementById('modalPayWallet')?.checked;
    if (!walletChecked) {
        alert('Booking requires wallet payment. Please toggle "Pay with Wallet" to pay the 20% booking fee.');
        return;
    }
    const feeEl = document.getElementById('modalBookingFee');
    const fee = feeEl ? parseFloat(feeEl.textContent.replace(/,/g,'')) : 0;
    if (_walletBalance < fee) {
        alert('Insufficient wallet balance for booking fee (₱' + fee.toLocaleString() + '). Please top up first.');
        return;
    }
    document.getElementById('modalRentType').value = 'booking';
    document.getElementById('modalRentalForm').submit();
}

function updateWalletPayUI() {
    const checked = document.getElementById('modalPayWallet')?.checked;
    const noteDiv  = document.getElementById('walletPayNote');
    const insuffDiv = document.getElementById('walletInsufficientMsg');
    const labelEl  = document.getElementById('walletPayLabel');
    if (!checked) {
        if (noteDiv) noteDiv.style.display = 'none';
        if (insuffDiv) insuffDiv.style.display = 'none';
        return;
    }
    // Get current estimated amount
    const totalEl = document.getElementById('modalTotal');
    const feeEl   = document.getElementById('modalBookingFee');
    const total   = totalEl ? parseFloat(totalEl.textContent.replace(/,/g,'')) : 0;
    const fee     = feeEl   ? parseFloat(feeEl.textContent.replace(/,/g,'')) : 0;
    // We don't know yet if it's booking or rental on the UI, show both
    const amountBooking = fee > 0 ? fee : Math.ceil(total * 0.20);
    if (noteDiv) {
        noteDiv.style.display = '';
        if (labelEl) labelEl.textContent = 'Booking: ₱' + amountBooking.toLocaleString() + ' deducted | Rental now: ₱' + total.toLocaleString() + ' deducted from wallet.';
    }
    if (insuffDiv) {
        insuffDiv.style.display = _walletBalance < total ? '' : 'none';
    }
}

// ── Payment Modal Logic ───────────────────────────────────────────────────────
function handleRentNow() {
    document.getElementById('modalRentType').value = 'rental';
    const walletChecked = document.getElementById('modalPayWallet')?.checked;
    // If paying with wallet, submit directly
    if (walletChecked) {
        document.getElementById('modalRentalForm').submit();
        return;
    }
    // Otherwise load payment gateways and show payment modal
    const itemId   = document.getElementById('modalItemId').value;
    const totalEl  = document.getElementById('modalTotal');
    const total    = totalEl ? totalEl.textContent.replace(/,/g,'') : '0';
    document.getElementById('pmtAmount').textContent = '₱' + parseFloat(total).toLocaleString();

    // Clear previous state
    document.getElementById('pmtRefInput').value = '';
    document.getElementById('pmtProofPreview').style.display = 'none';

    // Load gateways
    const listDiv = document.getElementById('pmtGatewayList');
    listDiv.innerHTML = '<div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm me-1"></div> Loading…</div>';

    fetch(`<?= base_url('payment-gateways/for-item') ?>/` + itemId)
        .then(r => r.json())
        .then(data => {
            if (!data.gateways || data.gateways.length === 0) {
                listDiv.innerHTML = '<div class="alert alert-warning py-2"><i class="bi bi-exclamation-triangle me-1"></i> No payment options set up by the owner yet. Please contact the owner directly.</div>';
                return;
            }
            let html = '<div class="row g-2">';
            data.gateways.forEach(gw => {
                html += `<div class="col-12">
                    <div style="border:1px solid #e3e6f0;border-radius:10px;padding:12px 14px;background:#f8f9fb;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong style="font-size:.9rem;">${gw.name}</strong>
                            <span class="badge" style="font-size:.68rem;background:#e3eafd;color:#0d2c8a;">${gw.type}</span>
                        </div>
                        <div style="font-size:.85rem;color:#444;"><i class="bi bi-phone me-1"></i>${gw.account_number}</div>
                        ${gw.qr_url ? `<img src="${gw.qr_url}" alt="QR" style="margin-top:8px;width:120px;height:120px;object-fit:contain;border-radius:8px;border:1px solid #ddd;padding:4px;background:#fff;">` : ''}
                    </div>
                </div>`;
            });
            html += '</div>';
            listDiv.innerHTML = html;
        })
        .catch(() => {
            listDiv.innerHTML = '<div class="alert alert-danger py-2">Could not load payment options.</div>';
        });

    // Hide item modal, show payment modal
    const itemEl = document.getElementById('itemModal');
    const itemModalInst = bootstrap.Modal.getInstance(itemEl);
    if (itemModalInst) {
        itemEl.addEventListener('hidden.bs.modal', function handler() {
            itemEl.removeEventListener('hidden.bs.modal', handler);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('paymentModal')).show();
        });
        itemModalInst.hide();
    } else {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('paymentModal')).show();
    }
}

function cancelPayment() {
    const pmtEl = document.getElementById('paymentModal');
    const itemId = document.getElementById('modalItemId').value;
    pmtEl.addEventListener('hidden.bs.modal', function handler() {
        pmtEl.removeEventListener('hidden.bs.modal', handler);
        const card = document.querySelector('.item-card[data-id="' + itemId + '"]');
        openItemModal(card);
    });
}

function previewProof(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('pmtProofImg').src = e.target.result;
        document.getElementById('pmtProofPreview').style.display = '';
    };
    reader.readAsDataURL(file);
}

function submitWithPayment() {
    const refInput  = document.getElementById('pmtRefInput');
    const hiddenFile = document.getElementById('modalPaymentRefFile');
    if (!refInput.files || !refInput.files[0]) {
        alert('Please upload your payment screenshot/proof before submitting.');
        return;
    }

    // Transfer the file to the hidden input on the actual form via DataTransfer
    const dt = new DataTransfer();
    dt.items.add(refInput.files[0]);
    hiddenFile.files = dt.files;

    // Close payment modal and submit form
    const pmtEl2 = document.getElementById('paymentModal');
    pmtEl2.addEventListener('hidden.bs.modal', function handler() {
        pmtEl2.removeEventListener('hidden.bs.modal', handler);
        document.getElementById('pmtSubmitBtn').disabled = true;
        document.getElementById('pmtSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting…';
        document.getElementById('modalRentalForm').submit();
    });
    bootstrap.Modal.getInstance(pmtEl2).hide();
}
// ─────────────────────────────────────────────────────────────────────────────
<?php endif; ?>
</script>

<?php $this->endSection(); ?>
