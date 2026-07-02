<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email &mdash; SND Rental System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: #e8eaf6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .verify-card {
            width: 480px;
            max-width: 96vw;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(21, 56, 150, 0.20);
        }

        .verify-head {
            background: linear-gradient(135deg, #0d2c8a 0%, #2979ff 100%);
            padding: 36px 40px 28px;
            text-align: center;
            color: #fff;
        }

        .verify-head .icon-wrap {
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 16px;
            border: 2px solid rgba(255,255,255,0.35);
        }

        .verify-head h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .verify-head p {
            font-size: .88rem;
            color: rgba(255,255,255,.8);
            margin: 0;
        }

        .verify-body {
            padding: 36px 40px;
        }

        .code-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 28px 0 24px;
        }

        .code-inputs input {
            width: 52px;
            height: 60px;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #0d2c8a;
            border: 2px solid #c5cdf0;
            border-radius: 10px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: #f8f9ff;
        }

        .code-inputs input:focus {
            border-color: #2979ff;
            box-shadow: 0 0 0 3px rgba(41,121,255,.15);
            background: #fff;
        }

        .btn-verify {
            width: 100%;
            padding: 13px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            background: linear-gradient(135deg, #0d2c8a, #2979ff);
            border: none;
            color: #fff;
            transition: opacity .15s;
        }

        .btn-verify:hover { opacity: .88; color: #fff; }

        .resend-wrap {
            text-align: center;
            margin-top: 20px;
            font-size: .88rem;
            color: #666;
        }

        .resend-wrap button {
            background: none;
            border: none;
            color: #2979ff;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            font-size: .88rem;
            text-decoration: underline;
        }

        .resend-wrap button:hover { color: #0d2c8a; }

        .email-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eef0fb;
            border: 1px solid #c5cdf0;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: .85rem;
            font-weight: 600;
            color: #0d2c8a;
        }
    </style>
</head>
<body>

<div class="verify-card">
    <!-- Header -->
    <div class="verify-head">
        <div class="icon-wrap">
            <i class="bi bi-envelope-check-fill"></i>
        </div>
        <h1>Check Your Email</h1>
        <p>We sent a 6-digit verification code to</p>
        <div class="mt-2">
            <span class="email-badge" style="background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.3);color:#fff;">
                <i class="bi bi-envelope"></i> <?= esc($email) ?>
            </span>
        </div>
    </div>

    <!-- Body -->
    <div class="verify-body">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-x-circle-fill"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= session()->getFlashdata('warning') ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-info-circle-fill"></i>
                <div><?= session()->getFlashdata('info') ?></div>
            </div>
        <?php endif; ?>

        <p class="text-muted text-center mb-0" style="font-size:.88rem;">
            Enter the 6-digit code below. It expires in <strong>15 minutes</strong>.
        </p>

        <form action="<?= base_url('verify-email') ?>" method="POST" id="verifyForm">
            <?= csrf_field() ?>
            <input type="hidden" name="code" id="codeHidden">

            <div class="code-inputs" id="codeBoxes">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="code-box" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-verify" id="submitBtn" disabled>
                <i class="bi bi-patch-check-fill me-2"></i> Verify Email
            </button>
        </form>

        <div class="resend-wrap">
            Didn't receive the code?
            <form action="<?= base_url('resend-verify') ?>" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit">Resend Code</button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="<?= base_url('portal') ?>" class="text-muted" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i>Back to portal
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const boxes    = Array.from(document.querySelectorAll('.code-box'));
    const hidden   = document.getElementById('codeHidden');
    const submitBtn = document.getElementById('submitBtn');

    function syncHidden() {
        const val = boxes.map(b => b.value).join('');
        hidden.value = val;
        submitBtn.disabled = val.length < 6;
    }

    boxes.forEach((box, i) => {
        box.addEventListener('input', (e) => {
            // Allow only digits
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();
            if (box.value && i < boxes.length - 1) {
                boxes[i + 1].focus();
            }
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && i > 0) {
                boxes[i - 1].focus();
            }
        });

        // Handle paste on any box
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach((ch, idx) => {
                if (boxes[idx]) boxes[idx].value = ch;
            });
            syncHidden();
            const next = Math.min(pasted.length, boxes.length - 1);
            boxes[next].focus();
        });
    });

    // Auto-submit when all 6 digits are filled
    document.getElementById('verifyForm').addEventListener('input', () => {
        if (boxes.every(b => b.value)) {
            setTimeout(() => document.getElementById('verifyForm').submit(), 300);
        }
    });

    // Focus first box on load
    boxes[0].focus();
})();
</script>

</body>
</html>
