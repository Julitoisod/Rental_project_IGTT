<?php
$role = session()->get('role');
$formAction = $role === 'customer' ? 'customer/rentals/store' : ($role === 'owner' ? 'owner/rentals/store' : 'admin/rentals/store');
$this->extend('layout/main'); $this->section('content');
?>

<div class="row justify-content-center"><div class="col-lg-7">
<div class="card-section">
    <div class="section-title"><i class="bi bi-clipboard-plus text-primary"></i> Rent Item</div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url($formAction) ?>" method="POST" id="rentalForm" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if ($role === 'customer'): ?>
        <input type="hidden" name="user_id" value="<?= session()->get('user_id') ?>">
        <?php else: ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Customer / User</label>
            <select name="user_id" class="form-select" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= old('user_id') == $u['id'] ? 'selected' : '' ?>>
                        <?= esc($u['name']) ?> (<?= esc($u['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Item</label>
            <select name="item_id" class="form-select" id="itemSelect" required>
                <option value="">-- Select Item --</option>
                <?php foreach ($items as $item):
                    $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true);
                    $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true);
                    $isSelected = (old('item_id') == $item['id']) || (isset($selectedItemId) && $selectedItemId == $item['id']);
                ?>
                    <option value="<?= $item['id'] ?>"
                            data-rate-opt1="<?= htmlspecialchars(json_encode($rateOpt1)) ?>"
                            data-rate-opt2="<?= htmlspecialchars(json_encode($rateOpt2)) ?>"
                            data-rental-details="<?= esc($item['rental_details'] ?? '') ?>"
                            <?= $isSelected ? 'selected' : '' ?>>
                        <?= esc($item['name']) ?> (<?= $item['quantity'] ?> available)
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Rental rates depend on item owner's configuration</small>
        </div>

        <!-- Rental Rate Info -->
        <div id="rateOptionsContainer" style="display:none;" class="card mb-3 border-info">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Rental Rate Options</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <strong>12 Hours</strong><br>
                            <span class="text-primary">₱<span id="rate12Price">--</span></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <strong>24 Hours</strong><br>
                            <span class="text-primary">₱<span id="rate24Price">--</span></span>
                        </div>
                    </div>
                </div>
                <div id="rentalDetailsDisplay" class="alert alert-info py-2 px-3" style="display:none;">
                    <strong>Owner's Rental Terms:</strong>
                    <p id="rentalDetailsText" class="mb-0"></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Rent Date</label>
                <input type="datetime-local" name="rent_date" id="rentDate" class="form-control"
                       value="<?= old('rent_date') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Return Date</label>
                <input type="datetime-local" name="return_date" id="returnDate" class="form-control"
                       value="<?= old('return_date') ?>" required>
            </div>
        </div>

        <!-- Cost preview -->
        <div class="alert alert-info py-2 px-3 mb-3" id="costPreview" style="display:none">
            <i class="bi bi-calculator me-1"></i>
            <strong id="daysText">0 hours</strong> &times;
            ₱ <span id="priceText">0</span> =
            <strong>₱ <span id="totalText">0</span></strong>
        </div>

        <!-- Terms and Conditions -->
        <div class="card border-primary mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Terms and Conditions</h6>
            </div>
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                <pre class="text-muted mb-2" style="white-space: pre-wrap; font-family: inherit; font-size: 0.875rem;">1. RENTAL PERIOD: The rental period begins at the agreed start time and ends at the agreed return time. Late returns may incur additional charges.

2. PAYMENT: Payment must be made in full before the rental period begins. Any damage or late fees will be charged to the customer's account.

3. SECURITY DEPOSIT: A security deposit may be required and will be refunded upon safe return of the item in good condition.

4. LIABILITY: The customer is responsible for any damage, loss, or theft of the rented item during the rental period.

5. CANCELLATION: Cancellations must be made at least 24 hours before the rental start time for a full refund.

6. ITEM CONDITION: The customer must inspect the item upon receipt and report any existing damage immediately.

7. USE OF ITEM: The item must be used in accordance with its intended purpose and any instructions provided by the owner.

8. RETURN: The item must be returned in the same condition as received, barring normal wear and tear.

9. PROHIBITED USE: The item may not be used for any illegal purpose or in a manner that voids warranty or insurance.

10. AGREEMENT: By checking the box below, you acknowledge that you have read, understood, and agree to these terms.</pre>
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required>
            <label class="form-check-label" for="termsAccepted">
                I have read and agree to the <strong>Terms and Conditions</strong> <span class="text-danger">*</span>
            </label>
        </div>

        <!-- Hidden fields for payment -->
        <input type="hidden" name="pay_with_wallet" id="payWithWalletField" value="0">
        <input type="hidden" name="payment_method" id="paymentMethodField" value="">

        <div class="d-flex gap-2">
            <?php if ($role === 'customer'): ?>
            <button type="button" class="btn btn-primary" id="openPaymentBtn"><i class="bi bi-credit-card"></i> Proceed to Payment</button>
            <?php else: ?>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Rental</button>
            <?php endif; ?>
            <a href="<?= base_url(($role === 'customer' ? 'customer' : ($role === 'owner' ? 'owner' : 'admin')) . '/rentals') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>

<?php if ($role === 'customer'): ?>
<!-- Payment Method Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Choose Payment Method</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary -->
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Amount:</span>
                        <strong class="text-primary fs-5">₱<span id="modalTotal">0</span></strong>
                    </div>
                </div>

                <!-- Payment Options -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="payment-option border rounded-3 p-3 text-center h-100" id="optWallet" onclick="selectPayment('wallet')">
                            <i class="bi bi-wallet2 fs-2 text-primary d-block mb-2"></i>
                            <strong>Wallet</strong>
                            <div class="mt-1">
                                <small class="text-muted">Balance:</small><br>
                                <span class="fw-bold text-success">₱<?= number_format($walletBalance ?? 0, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="payment-option border rounded-3 p-3 text-center h-100" id="optGcash" onclick="selectPayment('gcash')">
                            <i class="bi bi-phone fs-2 text-primary d-block mb-2"></i>
                            <strong>GCash</strong>
                            <div class="mt-1">
                                <small class="text-muted">Upload proof</small><br>
                                <span class="fw-bold text-info">Screenshot</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wallet warning -->
                <div class="alert alert-warning py-2 mb-3" id="modalWalletWarning" style="display:none;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Insufficient balance. <a href="<?= base_url('customer/wallet/topup') ?>">Top up now</a>
                </div>

                <!-- GCash upload section -->
                <div id="modalGcashUpload" style="display:none;">
                    <label class="form-label fw-semibold"><i class="bi bi-upload me-1"></i>Upload GCash Payment Proof</label>
                    <input type="file" id="modalPaymentFile" class="form-control" accept="image/*">
                    <small class="form-text text-muted">Screenshot of your GCash payment confirmation</small>
                    <div id="gcashPreview" class="mt-2" style="display:none;">
                        <img id="gcashPreviewImg" src="" class="img-fluid rounded border" style="max-height: 150px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-lg px-4" id="confirmPayBtn" disabled onclick="confirmPayment()">
                    <i class="bi bi-check-circle me-1"></i> Confirm & Rent
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.payment-option {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #dee2e6 !important;
}
.payment-option:hover {
    border-color: #0d6efd !important;
    background: #f8f9ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13,110,253,0.15);
}
.payment-option.selected {
    border-color: #0d6efd !important;
    background: #e8f0fe;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.2);
}
</style>
<?php endif; ?>

<?php $this->endSection(); ?>
<?php $this->section('scripts'); ?>
<script>
function updateRateOptions() {
    const itemSel = document.getElementById('itemSelect');
    const container = document.getElementById('rateOptionsContainer');
    const detailsDisplay = document.getElementById('rentalDetailsDisplay');
    const detailsText = document.getElementById('rentalDetailsText');

    if (!itemSel.value) { container.style.display = 'none'; return; }

    const selected = itemSel.selectedOptions[0];
    const rateOpt1 = JSON.parse(selected.dataset.rateOpt1 || '{}');
    const rateOpt2 = JSON.parse(selected.dataset.rateOpt2 || '{}');
    const rentalDetails = selected.dataset.rentalDetails || '';

    document.getElementById('rate12Price').textContent = parseInt(rateOpt1.price).toLocaleString('en-PH');
    document.getElementById('rate24Price').textContent = parseInt(rateOpt2.price).toLocaleString('en-PH');

    if (rentalDetails) {
        detailsText.textContent = rentalDetails;
        detailsDisplay.style.display = 'block';
    } else {
        detailsDisplay.style.display = 'none';
    }
    container.style.display = 'block';
    recalculate();
}

function recalculate() {
    const itemSel = document.getElementById('itemSelect');
    const rentDateEl = document.getElementById('rentDate');
    const returnDateEl = document.getElementById('returnDate');
    const preview = document.getElementById('costPreview');

    if (!itemSel.value || !rentDateEl.value || !returnDateEl.value) { preview.style.display = 'none'; return; }

    const selected = itemSel.selectedOptions[0];
    const rate12 = JSON.parse(selected.dataset.rateOpt1 || '{}');
    const rate24 = JSON.parse(selected.dataset.rateOpt2 || '{}');

    const rentDate = new Date(rentDateEl.value);
    const returnDate = new Date(returnDateEl.value);
    const diffMs = returnDate - rentDate;
    const hours = Math.max(0, Math.floor(diffMs / (1000 * 60 * 60)));

    if (hours <= 0) { preview.style.display = 'none'; return; }

    let totalPrice = 0;
    let priceBreakdown = [];

    if (hours <= 12) {
        totalPrice = parseFloat(rate12.price);
        priceBreakdown.push(hours + ' hour(s) @ 12hr rate');
    } else if (hours <= 24) {
        totalPrice = parseFloat(rate24.price);
        priceBreakdown.push(hours + ' hour(s) @ 24hr rate');
    } else {
        const fullDays = Math.floor(hours / 24);
        const remainingHours = hours % 24;
        totalPrice = fullDays * parseFloat(rate24.price);
        if (remainingHours === 0) {
            priceBreakdown.push(fullDays + ' x 24hr');
        } else if (remainingHours <= 12) {
            totalPrice += parseFloat(rate12.price);
            priceBreakdown.push(fullDays + ' x 24hr + ' + remainingHours + 'hr');
        } else {
            totalPrice += parseFloat(rate24.price);
            priceBreakdown.push(fullDays + ' x 24hr + ' + remainingHours + 'hr');
        }
    }

    document.getElementById('daysText').textContent = hours + ' hour' + (hours > 1 ? 's' : '');
    document.getElementById('priceText').textContent = priceBreakdown.join(' + ');
    document.getElementById('totalText').textContent = totalPrice.toLocaleString('en-PH');
    preview.style.display = 'block';
}

document.getElementById('itemSelect')?.addEventListener('change', updateRateOptions);
document.getElementById('rentDate')?.addEventListener('change', recalculate);
document.getElementById('returnDate')?.addEventListener('change', recalculate);

const rentDateEl = document.getElementById('rentDate');
const returnDateEl = document.getElementById('returnDate');
const _pad = n => String(n).padStart(2, '0');
function _nowLocal() {
    const d = new Date();
    return d.getFullYear() + '-' + _pad(d.getMonth()+1) + '-' + _pad(d.getDate()) + 'T' + _pad(d.getHours()) + ':' + _pad(d.getMinutes());
}
if (!rentDateEl.value) rentDateEl.value = _nowLocal();
rentDateEl.min = _nowLocal();
returnDateEl.min = _nowLocal();
updateRateOptions();

<?php if ($role === 'customer'): ?>
// ── Payment Modal Logic ──
const walletBalance = <?= json_encode((float)($walletBalance ?? 0)) ?>;
let selectedPayment = null;

document.getElementById('openPaymentBtn').addEventListener('click', function() {
    // Validate form first
    const form = document.getElementById('rentalForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Set total in modal
    const totalText = document.getElementById('totalText').textContent;
    document.getElementById('modalTotal').textContent = totalText;

    // Reset modal state
    selectedPayment = null;
    document.getElementById('optWallet').classList.remove('selected');
    document.getElementById('optGcash').classList.remove('selected');
    document.getElementById('modalGcashUpload').style.display = 'none';
    document.getElementById('modalWalletWarning').style.display = 'none';
    document.getElementById('confirmPayBtn').disabled = true;

    // Move modal to body for proper overlay
    const modalEl = document.getElementById('paymentModal');
    if (modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
});

function selectPayment(method) {
    selectedPayment = method;

    // Update UI
    document.getElementById('optWallet').classList.toggle('selected', method === 'wallet');
    document.getElementById('optGcash').classList.toggle('selected', method === 'gcash');

    if (method === 'wallet') {
        document.getElementById('modalGcashUpload').style.display = 'none';
        const total = parseFloat((document.getElementById('totalText').textContent || '0').replace(/,/g, ''));
        if (walletBalance < total) {
            document.getElementById('modalWalletWarning').style.display = 'block';
            document.getElementById('confirmPayBtn').disabled = true;
        } else {
            document.getElementById('modalWalletWarning').style.display = 'none';
            document.getElementById('confirmPayBtn').disabled = false;
        }
    } else if (method === 'gcash') {
        document.getElementById('modalWalletWarning').style.display = 'none';
        document.getElementById('modalGcashUpload').style.display = 'block';
        // Enable confirm only if file is selected
        checkGcashFile();
    }
}

// GCash file preview and validation
document.getElementById('modalPaymentFile')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('gcashPreviewImg').src = e.target.result;
            document.getElementById('gcashPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('gcashPreview').style.display = 'none';
    }
    checkGcashFile();
});

function checkGcashFile() {
    const fileInput = document.getElementById('modalPaymentFile');
    document.getElementById('confirmPayBtn').disabled = !(fileInput && fileInput.files.length > 0);
}

function confirmPayment() {
    const form = document.getElementById('rentalForm');

    if (selectedPayment === 'wallet') {
        document.getElementById('payWithWalletField').value = '1';
        document.getElementById('paymentMethodField').value = 'wallet';
    } else if (selectedPayment === 'gcash') {
        document.getElementById('payWithWalletField').value = '0';
        document.getElementById('paymentMethodField').value = 'gcash';
        // Move the file to the form's file input
        const modalFile = document.getElementById('modalPaymentFile');
        if (modalFile.files.length > 0) {
            // Create a new file input in the form with the selected file
            const dt = new DataTransfer();
            dt.items.add(modalFile.files[0]);
            let formFileInput = document.getElementById('paymentReference');
            if (!formFileInput) {
                formFileInput = document.createElement('input');
                formFileInput.type = 'file';
                formFileInput.name = 'payment_reference';
                formFileInput.id = 'paymentReference';
                formFileInput.style.display = 'none';
                form.appendChild(formFileInput);
            }
            formFileInput.files = dt.files;
        }
    }

    // Close modal and submit form
    const modalEl = document.getElementById('paymentModal');
    bootstrap.Modal.getInstance(modalEl)?.hide();
    form.submit();
}
<?php endif; ?>
</script>
<?php $this->endSection(); ?>
