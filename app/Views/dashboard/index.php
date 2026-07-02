<?php $this->extend('layout/main'); $this->section('content'); ?>
<?php
$role = session()->get('role');
$urlPrefix = match($role) {
    'snd_admin' => '/admin',
    'owner' => '/owner',
    'customer' => '/customer',
    default => '',
};
?>

<style>
    /* Fix modal to properly overlay the page */
    #itemModal {
        z-index: 1060;
    }
    #itemModal .modal-dialog {
        z-index: 1065;
    }
    .modal-backdrop {
        z-index: 1055 !important;
        background-color: #000 !important;
        opacity: 0.6 !important;
    }
    #itemModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }

    .item-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.14);
    }
    .item-card .card-img-wrap {
        position: relative;
        width: 100%;
        padding-top: 65%;
        background: linear-gradient(135deg, #f8f8f8 0%, #f0f0f0 100%);
        overflow: hidden;
    }
    .item-card .card-img-wrap img {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
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
        background: rgba(156,11,9,0.85);
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .item-card .card-body {
        padding: 16px;
    }
    .item-card .item-name {
        font-weight: 700;
        font-size: 1rem;
        color: #1a1a2e;
        margin-bottom: 4px;
    }
    .item-card .item-desc {
        font-size: 0.78rem;
        color: #8a8fa3;
        margin-bottom: 10px;
        line-height: 1.4;
    }
    .item-card .price-tag {
        font-weight: 700;
        font-size: 1.05rem;
        color: #9c0b09;
    }
    .item-card .qty-tag {
        font-size: 0.78rem;
        color: #666;
    }
</style>

<div class="card-section">
    <div class="section-title d-flex align-items-center">
        <i class="bi bi-box-seam text-primary"></i> All Items
        <?php if (in_array($role, ['snd_admin', 'owner'])): ?>
            <a href="<?= base_url($urlPrefix . '/items/create') ?>" class="btn btn-primary btn-sm ms-auto">
                <i class="bi bi-plus-lg"></i> Add Item
            </a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-box-seam" style="font-size:3rem;"></i>
            <p class="mt-2">No items available.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($items as $item):
                $rOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?: ['hours' => 12, 'price' => 0];
                $rOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?: ['hours' => 24, 'price' => 0];
            ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="item-card cursor-pointer" onclick="showItemModal(<?= $item['id'] ?>)"
                         data-item-id="<?= $item['id'] ?>"
                         data-item-name="<?= esc($item['name']) ?>"
                         data-item-desc="<?= esc($item['description'] ?? '') ?>"
                         data-item-cat="<?= esc($item['category_name'] ?? '') ?>"
                         data-item-qty="<?= $item['quantity'] ?>"
                         data-item-img="<?= $item['image'] ? base_url('uploads/items/' . $item['image']) : '' ?>"
                         data-rate1hours="<?= $rOpt1['hours'] ?? 12 ?>"
                         data-rate1price="<?= $rOpt1['price'] ?? 0 ?>"
                         data-rate2hours="<?= $rOpt2['hours'] ?? 24 ?>"
                         data-rate2price="<?= $rOpt2['price'] ?? 0 ?>"
                         data-item-details="<?= esc($item['rental_details'] ?? '') ?>">
                        <div class="card-img-wrap">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= base_url('uploads/items/' . $item['image']) ?>" alt="<?= esc($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-img"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                            <span class="category-badge"><?= esc($item['category_name'] ?? '—') ?></span>
                            <?php if ($item['quantity'] > 0): ?>
                                <span class="badge bg-success stock-badge">Available</span>
                            <?php else: ?>
                                <span class="badge bg-secondary stock-badge">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="item-name"><?= esc($item['name']) ?></div>
                            <div class="item-desc">
                                <?php if (!empty($item['description'])): ?>
                                    <?= esc(mb_substr($item['description'], 0, 50)) ?><?= mb_strlen($item['description'] ?? '') > 50 ? '…' : '' ?>
                                <?php else: ?>
                                    <span class="fst-italic">No description</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="price-tag">₱<?= number_format($rOpt1['price'] ?? 0) ?><small>/<?= $rOpt1['hours'] ?? 12 ?>hrs</small></span>
                                <?php if ($role === 'customer' && $item['quantity'] > 0): ?>
                                    <a href="<?= base_url('customer/rentals/create?item_id=' . $item['id']) ?>" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                                        <i class="bi bi-cart-plus"></i> Rent
                                    </a>
                                <?php else: ?>
                                    <span class="qty-tag"><?= $item['quantity'] ?> pcs</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Item Detail Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="box-shadow: 0 10px 50px rgba(0,0,0,0.3);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-box-seam"></i> <span id="modalItemName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div id="modalItemImg" class="bg-light rounded text-center py-5">
                            <i class="bi bi-image fs-1 text-secondary"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <p class="text-muted mb-2"><i class="bi bi-tag"></i> <span id="modalItemCat" class="badge bg-secondary"></span></p>
                        <p class="mb-3" id="modalItemDesc"></p>
                        <div class="mb-3">
                            <strong>Available:</strong> <span id="modalItemQty"></span> pcs
                        </div>
                        <div class="mb-3">
                            <strong>Rental Rates:</strong>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <small class="text-muted"><span id="modalRate1Hours">12</span> Hours</small><br>
                                        <strong class="text-primary">₱<span id="modalRate1Price">0</span></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <small class="text-muted"><span id="modalRate2Hours">24</span> Hours</small><br>
                                        <strong class="text-primary">₱<span id="modalRate2Price">0</span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="modalItemDetails" class="alert alert-info py-2" style="display:none;">
                            <strong>Rental Terms:</strong><br>
                            <small id="modalDetailsText"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php if ($role === 'customer'): ?>
                    <a href="#" id="modalRentBtn" class="btn btn-primary w-100">
                        <i class="bi bi-cart-plus"></i> Rent This Item
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showItemModal(itemId) {
    // Find the card element
    const card = document.querySelector('.item-card[data-item-id="' + itemId + '"]');
    if (!card) {
        console.error('Item card not found for ID:', itemId);
        return;
    }

    // Populate modal data
    document.getElementById('modalItemName').textContent = card.dataset.itemName || '';
    document.getElementById('modalItemCat').textContent = card.dataset.itemCat || '';
    document.getElementById('modalItemQty').textContent = card.dataset.itemQty || '0';
    document.getElementById('modalRate1Price').textContent = parseInt(card.dataset.rate1price || 0).toLocaleString();
    document.getElementById('modalRate2Price').textContent = parseInt(card.dataset.rate2price || 0).toLocaleString();
    document.getElementById('modalRate1Hours').textContent = card.dataset.rate1hours || '12';
    document.getElementById('modalRate2Hours').textContent = card.dataset.rate2hours || '24';

    const desc = card.dataset.itemDesc;
    document.getElementById('modalItemDesc').textContent = desc || 'No description available.';

    // Image handling
    const imgContainer = document.getElementById('modalItemImg');
    const imgUrl = card.dataset.itemImg;
    if (imgUrl && imgUrl.trim() !== '') {
        imgContainer.innerHTML = '<img src="' + imgUrl + '" class="img-fluid rounded" alt="' + (card.dataset.itemName || '') + '">';
    } else {
        imgContainer.innerHTML = '<i class="bi bi-image fs-1 text-secondary"></i>';
    }

    // Rental details
    const details = card.dataset.itemDetails;
    const detailsDiv = document.getElementById('modalItemDetails');
    const detailsText = document.getElementById('modalDetailsText');
    if (details && details.trim() !== '') {
        detailsText.textContent = details;
        detailsDiv.style.display = 'block';
    } else {
        detailsDiv.style.display = 'none';
    }

    // Update rent button link
    var rentBtn = document.getElementById('modalRentBtn');
    if (rentBtn) {
        rentBtn.href = '<?= base_url('customer/rentals/create?item_id=') ?>' + itemId;
    }

    // Show modal using Bootstrap 5 API
    var modalEl = document.getElementById('itemModal');
    if (modalEl) {
        // Move modal to body to avoid z-index/positioning issues with parent containers
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
</script>

<style>
.cursor-pointer { cursor: pointer; }
</style>

<?php $this->endSection(); ?>