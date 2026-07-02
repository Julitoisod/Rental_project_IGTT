<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — IGTT</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', 'Segoe UI', sans-serif;
        }
        .card {
            width: 420px;
            max-width: 98vw;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(156,11,9,0.15);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #9c0b09, #b80d0b);
            padding: 30px;
            text-align: center;
        }
        .card-header img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #fff;
            padding: 5px;
            object-fit: contain;
        }
        .card-header h2 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: 15px;
        }
        .card-body {
            padding: 30px;
        }
        .code-display {
            background: #f8f8f8;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .code-display .code {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 8px;
            color: #9c0b09;
        }
        .code-display .hint {
            font-size: 0.8rem;
            color: #888;
            margin-top: 8px;
        }
        .form-control {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #9c0b09;
            box-shadow: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #9c0b09, #b80d0b);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        .link {
            color: #9c0b09;
            font-weight: 600;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <img src="<?= base_url('img/logo.png') ?>" alt="IGTT" onerror="this.style.display='none'">
        <h2>Enter Reset Code</h2>
    </div>
    <div class="card-body">
        <div class="code-display">
            <div class="hint"><i class="bi bi-envelope me-1"></i>Check your email for the 6-digit code, then enter it below.</div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('reset-password') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Reset Code</label>
                <input type="text" name="code" class="form-control text-center"
                       placeholder="Enter the 6-digit code from your email"
                       required pattern="\d{6}" maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                       style="letter-spacing:6px;font-weight:700;">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter new password (min 6 characters)" required minlength="6">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirm" class="form-control"
                       placeholder="Confirm new password" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary mb-3">
                <i class="bi bi-key me-2"></i> Reset Password
            </button>
        </form>

        <p class="text-center text-muted" style="font-size: 0.85rem;">
            Didn't receive the code?
            <a href="<?= base_url('forgot-password') ?>" class="link">Request new code</a>
        </p>
    </div>
</div>

</body>
</html>