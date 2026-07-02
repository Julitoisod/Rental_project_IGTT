<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In &mdash; SND Rental System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: #fde8e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            display: flex;
            width: 860px;
            max-width: 98vw;
            min-height: 480px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(74, 0, 0, 0.25);
        }

        .login-left {
            width: 42%;
            background: linear-gradient(160deg, #4a0000 0%, #8b0000 55%, #c62828 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before,
        .login-left::after {
            content: '';
            position: absolute;
            right: -60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .login-left::before { width: 220px; height: 220px; top: -60px; }
        .login-left::after  { width: 280px; height: 280px; bottom: -80px; }

        .wave-mid {
            position: absolute;
            right: -40px;
            top: 50%;
            transform: translateY(-50%);
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.055);
        }

        .login-left .logo-wrap {
            background: #fff;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
            z-index: 1;
        }

        .login-left .logo-wrap img {
            width: 104px;
            height: 104px;
            object-fit: contain;
            border-radius: 50%;
        }

        .login-left h2 { color: #fff; font-size: 1.35rem; font-weight: 700; text-align: center; margin-bottom: 8px; z-index: 1; }
        .login-left p  { color: rgba(255,255,255,0.82); font-size: 0.82rem; text-align: center; line-height: 1.6; z-index: 1; padding: 0 10px; }

        .tagline {
            margin-top: 14px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 7px 16px;
            color: #ffcdd2;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 1;
        }

        .portal-badge {
            margin-top: 12px;
            background: rgba(255,255,255,0.18);
            border-radius: 20px;
            padding: 5px 16px;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .login-right {
            width: 58%;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 50px;
        }

        .login-right h3 { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .login-right .subtitle { color: #8a8fa3; font-size: 0.85rem; margin-bottom: 30px; }

        .form-floating label { color: #9a9a9a; font-size: 0.88rem; }

        .form-floating .form-control {
            border: none;
            border-bottom: 2px solid #e0e3ef;
            border-radius: 0;
            padding-left: 0;
            background: transparent;
            color: #1a1a2e;
            font-size: 0.95rem;
            transition: border-color 0.25s;
        }

        .form-floating .form-control:focus {
            border-bottom-color: #c62828;
            box-shadow: none;
            background: transparent;
        }

        .btn-login {
            background: linear-gradient(90deg, #4a0000, #c62828);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 11px 0;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 10px;
            transition: opacity 0.2s;
        }

        .btn-login:hover { opacity: 0.88; color: #fff; }

        .alert-custom {
            font-size: 0.83rem;
            border-radius: 8px;
            padding: 9px 14px;
            margin-bottom: 18px;
            width: 100%;
        }

        .link-red { color: #c62828; font-weight: 600; text-decoration: none; }
        .link-red:hover { text-decoration: underline; color: #8b0000; }

        @media (max-width: 680px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 40px 28px; }
            .login-card { width: 98vw; }
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- ── LEFT PANEL ── -->
    <div class="login-left">
        <div class="wave-mid"></div>
        <div class="logo-wrap">
            <img src="<?= base_url('img/logo.png') ?>" alt="SND IT Solutions Logo"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:2.8rem;font-weight:900;color:#c62828;line-height:1\'>SND</span>'">
        </div>
        <h2>SND Admin<br>Portal</h2>
        <p>Full system access: users, categories, inventory, and system settings.</p>
        <div class="tagline">We Fix, Print, Build &amp; Code!</div>
        <div class="portal-badge"><i class="bi bi-shield-lock-fill"></i> Admin Only</div>
    </div>

    <!-- ── RIGHT PANEL ── -->
    <div class="login-right">
        <h3>Admin Sign In</h3>
        <p class="subtitle">Sign in with your SND Administrator account</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-custom">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-custom">
                <i class="bi bi-check-circle-fill me-1"></i>
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-custom">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <div><i class="bi bi-dot"></i> <?= esc($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/login') ?>" method="POST" style="width: 100%;">
            <?= csrf_field() ?>

            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="Email" value="<?= old('email') ?>" required autofocus>
                <label for="email"><i class="bi bi-envelope me-1"></i> Admin Email</label>
            </div>

            <div class="form-floating mb-3 position-relative">
                <input type="password" class="form-control pe-5" id="password" name="password"
                       placeholder="Password" required>
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                <button type="button" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;color:#9098b8;cursor:pointer;padding:0;z-index:10;font-size:1rem;"
                        onclick="(function(b){var i=document.getElementById('password');var ic=b.querySelector('i');if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}})(this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-shield-lock me-2"></i> Admin Sign In
            </button>
        </form>

        <p class="mt-3" style="text-align:center;">
            <a href="<?= base_url('forgot-password') ?>" class="link-red" style="font-size:0.85rem;">
                <i class="bi bi-key me-1"></i> Forgot Password?
            </a>
        </p>

        <p class="mt-3 mb-0" style="font-size:0.84rem; color:#8a8fa3; text-align:center;">
            Need an admin account?
            <a href="<?= base_url('admin/register') ?>" class="link-red">Register here</a>
        </p>
        <p class="mt-2" style="font-size:0.82rem;">
            <a href="<?= base_url('portal') ?>" class="link-red">
                <i class="bi bi-arrow-left me-1"></i> Back to Portal Selection
            </a>
        </p>
    </div>

</div>

</body>
</html>
