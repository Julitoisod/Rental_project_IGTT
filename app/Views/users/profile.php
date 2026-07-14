<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <!-- Profile Header Card -->
    <div class="card-section mb-4">
        <div class="d-flex align-items-center gap-4">
            <!-- Avatar -->
            <div style="position:relative; flex-shrink:0;">
                <?php if (! empty($user['avatar'])): ?>
                    <img src="<?= base_url('uploads/avatars/' . esc($user['avatar'])) ?>"
                         alt="Avatar"
                         style="width:96px; height:96px; border-radius:50%; object-fit:cover; border:3px solid #e8eaf6;">
                <?php else: ?>
                    <div style="width:96px; height:96px; border-radius:50%; background:var(--sidebar-bg);
                                display:flex; align-items:center; justify-content:center;
                                font-size:2.4rem; font-weight:700; color:#fff; border:3px solid #e8eaf6;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div>
                <h4 class="mb-0 fw-bold"><?= esc($user['name']) ?>
                <?php if (! empty($user['is_id_verified'])): ?>
                    <i class="bi bi-patch-check-fill ms-1" style="color:#1d9bf0;font-size:1.1rem;" title="Verified"></i>
                <?php endif; ?>
                </h4>
                <div class="text-muted small mb-1"><?= esc($user['email']) ?></div>
                <?php
                $badgeClass = ['snd_admin' => 'bg-danger', 'owner' => 'bg-warning text-dark', 'customer' => 'bg-primary'];
                $roleLabel  = ['snd_admin' => 'SND Admin', 'owner' => 'Rental Owner', 'customer' => 'Customer'];
                ?>
                <span class="badge <?= $badgeClass[$user['role']] ?? 'bg-secondary' ?> px-3 py-1">
                    <?= $roleLabel[$user['role']] ?? ucfirst($user['role']) ?>
                </span>
            </div>

            <div class="ms-auto text-muted small">
                <i class="bi bi-calendar3 me-1"></i> Joined <?= date('M d, Y', strtotime($user['created_at'])) ?>
            </div>
        </div>
    </div>

    <!-- Verification Status -->
    <?php
    $hasId      = ! empty($user['valid_id']);
    $isVerified = ! empty($user['is_id_verified']);

    if (! $hasId) {
        $s2class = 'step-empty'; $s2icon = 'bi-upload';
        $s2title = 'Upload Required'; $s2sub = 'No valid ID uploaded yet';
        $lineOk  = false; $stepMsg = 'info';
    } elseif ($isVerified) {
        $s2class = 'step-done'; $s2icon = 'bi-patch-check-fill';
        $s2title = 'ID Verified';
        $s2sub   = 'Verified on ' . (! empty($user['id_verified_at']) ? date('M d, Y', strtotime($user['id_verified_at'])) : '');
        $lineOk  = true; $stepMsg = 'success';
    } else {
        $s2class = 'step-pending'; $s2icon = 'bi-hourglass-split';
        $s2title = 'Pending Review'; $s2sub = 'Waiting for owner to verify';
        $lineOk  = false; $stepMsg = 'pending';
    }
    ?>
    <div class="card-section mb-4">
        <div class="section-title">
            <i class="bi bi-patch-check text-primary"></i> Verification Status
        </div>
        <div class="verif-stepper mt-3">
            <!-- Step 1: Email -->
            <div class="verif-step step-done">
                <div class="verif-circle"><i class="bi bi-check-lg"></i></div>
                <div class="mt-2 text-center">
                    <div class="fw-semibold" style="font-size:.82rem;">Email Confirmed</div>
                    <div class="text-muted" style="font-size:.72rem;"><?= esc($user['email']) ?></div>
                </div>
            </div>
            <!-- Connector -->
            <div class="verif-line <?= $lineOk ? 'done' : '' ?>"></div>
            <!-- Step 2: ID -->
            <div class="verif-step <?= $s2class ?>">
                <div class="verif-circle"><i class="bi <?= $s2icon ?>"></i></div>
                <div class="mt-2 text-center">
                    <div class="fw-semibold" style="font-size:.82rem;"><?= $s2title ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= $s2sub ?></div>
                </div>
            </div>
        </div>
        <?php if ($stepMsg === 'success'): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                <i class="bi bi-patch-check-fill fs-5"></i>
                <div><strong>ID Verified!</strong> The owner has confirmed your identity.</div>
            </div>
        <?php elseif ($stepMsg === 'pending'): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                <i class="bi bi-hourglass-split fs-5"></i>
                <div>Your ID has been uploaded. An owner will review and verify it shortly.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary d-flex align-items-center gap-2 mt-3 mb-0 py-2">
                <i class="bi bi-arrow-down-circle fs-5"></i>
                <div>Upload a clear photo of your valid ID below. An owner will review and verify it.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Profile Form -->
    <div class="card-section">
        <div class="section-title">
            <i class="bi bi-person-gear text-primary"></i> Edit Profile
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <?php foreach (session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url(role_url_prefix() . '/profile/update') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-3">
                <!-- Avatar upload -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Profile Photo</label>
                    <div class="d-flex align-items-center gap-3">
                        <div id="avatarPreviewWrap">
                            <?php if (! empty($user['avatar'])): ?>
                                <img id="avatarPreview"
                                     src="<?= base_url('uploads/avatars/' . esc($user['avatar'])) ?>"
                                     style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #e8eaf6;">
                            <?php else: ?>
                                <div id="avatarPreview"
                                     style="width:64px; height:64px; border-radius:50%; background:#0d2c8a;
                                            display:flex; align-items:center; justify-content:center;
                                            font-size:1.5rem; font-weight:700; color:#fff;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   onchange="previewAvatar(event)" style="max-width:260px;">
                            <div class="text-muted" style="font-size:0.75rem; margin-top:4px;">JPG, PNG, GIF or WebP · Max 5 MB</div>
                        </div>
                    </div>
                </div>

                <!-- Name -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= esc(old('name', $user['name'])) ?>" required>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= esc(old('email', $user['email'])) ?>" required>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= esc(old('phone', $user['phone'] ?? '')) ?>"
                           placeholder="+63 912 345 6789">
                </div>

                <!-- Role (read-only) -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    <input type="text" class="form-control bg-light"
                           value="<?= $roleLabel[$user['role']] ?? ucfirst($user['role']) ?>" readonly>
                </div>

                <!-- Valid ID upload -->
                <div class="col-12">
                    <hr class="my-2">
                    <p class="fw-semibold mb-2"><i class="bi bi-card-heading me-1"></i> Valid ID <span class="text-muted fw-normal small">(Driver's License, Passport, etc.)</span></p>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">ID Type</label>
                    <select name="valid_id_type" class="form-select">
                        <option value="">-- Select ID Type --</option>
                        <?php
                        $idTypes = ["Driver's License", 'Passport', 'PhilSys (National ID)', 'SSS ID', 'GSIS ID', 'PRC ID', 'Voter\'s ID', 'Postal ID', 'TIN ID', 'School ID', 'Company ID', 'Other'];
                        foreach ($idTypes as $t):
                            $sel = (old('valid_id_type', $user['valid_id_type'] ?? '') === $t) ? 'selected' : '';
                        ?>
                            <option value="<?= esc($t) ?>" <?= $sel ?>><?= esc($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Upload ID Image</label>
                    <input type="file" name="valid_id" id="validIdInput" class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           onchange="previewValidId(event)">
                    <div class="text-muted" style="font-size:0.75rem; margin-top:4px;">JPG, PNG, GIF or WebP &middot; Max 5 MB</div>
                    <div id="ocrStatus" class="mt-2"></div>
                    <input type="hidden" name="ocr_verified" id="ocrVerifiedInput" value="0">
                </div>

                <?php if (! empty($user['valid_id'])): ?>
                <div class="col-12">
                    <label class="form-label fw-semibold">Current Valid ID</label>
                    <div class="d-flex align-items-center gap-3">
                        <?php
                        $ext = strtolower(pathinfo($user['valid_id'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])):
                        ?>
                            <img id="validIdPreview"
                                 src="<?= base_url('uploads/valid_ids/' . esc($user['valid_id'])) ?>"
                                 style="max-height:140px; max-width:260px; border-radius:10px; border:1px solid #dee2e6; object-fit:contain;">
                        <?php else: ?>
                            <a href="<?= base_url('uploads/valid_ids/' . esc($user['valid_id'])) ?>" target="_blank"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-file-earmark me-1"></i> View file
                            </a>
                        <?php endif; ?>
                        <div>
                            <?php if (! empty($user['valid_id_type'])): ?>
                                <span class="badge bg-secondary"><?= esc($user['valid_id_type']) ?></span>
                            <?php endif; ?>
                            <div class="text-muted small mt-1">Upload a new file above to replace it.</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="col-12">
                    <div id="validIdNewPreview"></div>
                </div>
                <?php endif; ?>

                <!-- Change Password section -->
                <div class="col-12">
                    <hr class="my-2">
                    <p class="fw-semibold mb-2"><i class="bi bi-lock me-1"></i> Change Password <span class="text-muted fw-normal small">(leave blank to keep current)</span></p>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="pwdInput" class="form-control"
                               placeholder="Min. 6 characters" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePwd('pwdInput', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirm" id="pwdConfirm" class="form-control"
                               placeholder="Repeat new password" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePwd('pwdConfirm', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                    <a href="<?= base_url(role_url_prefix() . '/dashboard') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
const _accountName = <?= json_encode($user['name']) ?>;

function _normalize(s) {
    return s.toLowerCase()
        .replace(/0/g, 'o').replace(/1/g, 'i').replace(/\|/g, 'i')
        .replace(/[^a-z\s]/g, '').replace(/\s+/g, ' ').trim();
}

function _nameInText(name, text) {
    const t = _normalize(text);
    const n = _normalize(name);

    // Direct full name match
    if (t.includes(n)) return true;

    // Split name into parts (first, middle, last)
    const parts = n.split(' ').filter(p => p.length >= 2);
    if (parts.length === 0) return false;

    // Check if ANY part of the name (2+ chars) appears in OCR text
    let matchCount = 0;
    for (const p of parts) {
        if (t.includes(p)) matchCount++;
    }
    if (matchCount >= 2) return true;

    // Check if last name or first name is found
    const lastName = parts[parts.length - 1];
    const firstName = parts[0];
    if (lastName.length >= 3 && t.includes(lastName)) return true;
    if (firstName.length >= 4 && t.includes(firstName)) return true;

    // Fuzzy: allow 1-char difference per word
    for (const p of parts) {
        if (p.length < 3) continue;
        for (let i = 0; i <= t.length - p.length; i++) {
            const chunk = t.substr(i, p.length);
            let diff = 0;
            for (let j = 0; j < p.length; j++) {
                if (chunk[j] !== p[j]) diff++;
                if (diff > 1) break;
            }
            if (diff <= 1) return true;
        }
    }
    return false;
}

// Prepare image for OCR - try specific rotation
function _prepareImage(dataUrl, rotation = 0) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            let w = img.width, h = img.height;

            if (rotation === 90 || rotation === 270) {
                canvas.width = h;
                canvas.height = w;
            } else {
                canvas.width = w;
                canvas.height = h;
            }

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate((rotation * Math.PI) / 180);
            ctx.drawImage(img, -w / 2, -h / 2);

            // Enhance: grayscale + contrast boost
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = imageData.data;
            for (let i = 0; i < d.length; i += 4) {
                const gray = 0.299 * d[i] + 0.587 * d[i+1] + 0.114 * d[i+2];
                const val = Math.min(255, Math.max(0, ((gray - 128) * 1.5) + 128));
                d[i] = d[i+1] = d[i+2] = val;
            }
            ctx.putImageData(imageData, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = () => resolve(dataUrl);
        img.src = dataUrl;
    });
}

async function _runOcr(dataUrl) {
    const statusEl = document.getElementById('ocrStatus');
    document.getElementById('ocrVerifiedInput').value = '0';
    statusEl.innerHTML = `<div class="d-flex align-items-center gap-2 text-muted" style="font-size:.8rem;">
        <div class="spinner-border spinner-border-sm" role="status"></div> Scanning ID&hellip; (this may take 15-30 seconds)
    </div>`;

    try {
        // Determine rotations to try based on image orientation
        const img = new Image();
        await new Promise((res, rej) => { img.onload = res; img.onerror = rej; img.src = dataUrl; });
        
        // If portrait, try 90° and 270° first; if landscape, try 0° and 180° first
        let rotations = img.height > img.width * 1.2 ? [90, 270, 0, 180] : [0, 180, 90, 270];

        for (let i = 0; i < rotations.length; i++) {
            const rot = rotations[i];
            statusEl.innerHTML = `<div class="d-flex align-items-center gap-2 text-muted" style="font-size:.8rem;">
                <div class="spinner-border spinner-border-sm" role="status"></div> Scanning ID (attempt ${i+1}/4)&hellip;
            </div>`;

            const enhanced = await _prepareImage(dataUrl, rot);
            const worker = await Tesseract.createWorker('eng');
            const { data: { text } } = await worker.recognize(enhanced);
            await worker.terminate();

            console.log(`[OCR rotation ${rot}°]`, text);

            if (_nameInText(_accountName, text)) {
                document.getElementById('ocrVerifiedInput').value = '1';
                statusEl.innerHTML = `<div class="alert alert-success py-1 px-2 mb-0" style="font-size:.8rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> Name matched — ID will be auto-verified on save.
                </div>`;
                return; // Success! Stop trying
            }
        }

        // None of the rotations matched
        statusEl.innerHTML = `<div class="alert alert-warning py-1 px-2 mb-0" style="font-size:.8rem;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Name not found on ID — owner will review it.
        </div>`;
    } catch (err) {
        console.error('[OCR Error]', err);
        statusEl.innerHTML = `<div class="text-muted" style="font-size:.8rem;">
            <i class="bi bi-info-circle me-1"></i> OCR scan failed — owner will review your ID.
        </div>`;
    }
}

function previewAvatar(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const wrap = document.getElementById('avatarPreviewWrap');
        wrap.innerHTML = `<img id="avatarPreview" src="${ev.target.result}"
            style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #e8eaf6;">`;
    };
    reader.readAsDataURL(file);
}

function previewValidId(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.type !== 'application/pdf') {
        const reader = new FileReader();
        reader.onload = function(ev) {
            let img = document.getElementById('validIdPreview');
            if (img) {
                img.src = ev.target.result;
            } else {
                const wrap = document.getElementById('validIdNewPreview');
                if (wrap) wrap.innerHTML = `<img src="${ev.target.result}"
                    style="max-height:140px;max-width:260px;border-radius:10px;border:1px solid #dee2e6;object-fit:contain;margin-top:6px;">`;
            }
            _runOcr(ev.target.result);
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('ocrVerifiedInput').value = '0';
        document.getElementById('ocrStatus').innerHTML = `<div class="text-muted" style="font-size:.8rem;">
            <i class="bi bi-info-circle me-1"></i> PDF uploaded — owner will review your ID.
        </div>`;
    }
}

function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('i').className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>

<?php $this->endSection(); ?>
