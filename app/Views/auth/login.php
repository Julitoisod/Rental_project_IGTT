<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; Islands Getaway Travel and Tours</title>
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

        .login-card {
            display: flex;
            width: 860px;
            max-width: 98vw;
            min-height: 480px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(156, 11, 9, 0.2);
        }

        /* ── Left Panel ── */
        .login-left {
            width: 42%;
            background: linear-gradient(160deg, #9c0b09 0%, #7a0807 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        /* Wave decorations */
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
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            z-index: 1;
        }

        .login-left .logo-wrap img {
            width: 104px;
            height: 104px;
            object-fit: contain;
            border-radius: 50%;
        }

        .login-left h2 {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            z-index: 1;
        }

        .login-left p {
            color: rgba(255,255,255,0.82);
            font-size: 0.82rem;
            text-align: center;
            line-height: 1.6;
            z-index: 1;
            padding: 0 10px;
        }

        .tagline {
            margin-top: 14px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 7px 16px;
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 1;
        }

        /* ── Right Panel ── */
        .login-right {
            width: 58%;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 50px;
        }

        .login-right h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .login-right .subtitle {
            color: #8a8fa3;
            font-size: 0.85rem;
            margin-bottom: 30px;
        }

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
            border-bottom-color: #9c0b09;
            box-shadow: none;
            background: transparent;
        }

        .btn-login {
            background: linear-gradient(90deg, #9c0b09, #b80d0b);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 11px 0;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
        }

        .btn-login:hover { opacity: 0.88; color: #fff; transform: translateY(-1px); }

        .divider-text {
            text-align: center;
            color: #c0c4d4;
            font-size: 0.78rem;
            margin: 18px 0 6px;
        }

        .alert-custom {
            font-size: 0.83rem;
            border-radius: 8px;
            padding: 9px 14px;
            margin-bottom: 18px;
            width: 100%;
        }

        /* Responsive */
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
            <img src="<?= base_url('img/logo.png') ?>" alt="IGTT Logo"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:2.8rem;font-weight:900;color:#9c0b09;line-height:1\'>IGTT</span>'">
        </div>

        <h2>Welcome to<br>Islands Getaway</h2>
        <p>Your smart platform for managing rentals — equipment, vehicles, rooms &amp; more.</p>
        <div class="tagline">Travel &amp; Tours</div>
        <div style="margin-top:12px;background:rgba(255,255,255,0.18);border-radius:20px;padding:5px 16px;color:#fff;font-size:0.78rem;font-weight:600;z-index:1;display:flex;align-items:center;gap:6px"><i class="bi bi-person-fill"></i> Customer Portal</div>
    </div>

    <!-- ── RIGHT PANEL ── -->
    <div class="login-right">
        <h3>Sign In</h3>
        <p class="subtitle">Enter your credentials to continue</p>

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

        <form action="<?= base_url('login') ?>" method="POST" style="width: 100%;">
            <?= csrf_field() ?>

            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="Email" value="<?= old('email') ?>" required autofocus>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
            </div>

            <div class="form-floating mb-1 position-relative">
                <input type="password" class="form-control pe-5" id="password" name="password"
                       placeholder="Password" required>
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                <button type="button" id="togglePassword"
                        style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;color:#9098b8;cursor:pointer;padding:0;z-index:10;font-size:1rem;"
                        onclick="var i=document.getElementById('password');var ic=document.getElementById('toggleIcon');if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>

            <div class="divider-text">─────────────────</div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="mt-3" style="text-align:center;">
            <a href="<?= base_url('forgot-password') ?>" style="color:#9c0b09; font-size:0.85rem; font-weight:600; text-decoration:none;">
                <i class="bi bi-key me-1"></i> Forgot Password?
            </a>
        </p>

        <p class="mt-3" style="font-size:0.84rem; color:#8a8fa3; text-align:center;">
            Don't have an account?
            <a href="<?= base_url('register') ?>" style="color:#9c0b09; font-weight:600; text-decoration:none;">Sign Up</a>
        </p>
        <p class="mt-2" style="font-size:0.82rem;">
            <a href="<?= base_url('portal') ?>" style="color:#9c0b09; font-weight:600; text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i> Back to Portal
            </a>
        </p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
