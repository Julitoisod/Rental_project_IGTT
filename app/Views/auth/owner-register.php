<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Register &mdash; SND Rental System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .auth-card {
            display: flex;
            width: 860px;
            max-width: 98vw;
            min-height: 540px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(10, 61, 31, 0.22);
        }

        .auth-left {
            width: 42%;
            background: linear-gradient(160deg, #0a3d1f 0%, #1b5e20 55%, #2e7d32 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before,
        .auth-left::after {
            content: '';
            position: absolute;
            right: -60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .auth-left::before { width: 220px; height: 220px; top: -60px; }
        .auth-left::after  { width: 280px; height: 280px; bottom: -80px; }

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

        .auth-left .logo-wrap {
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

        .auth-left .logo-wrap img {
            width: 104px;
            height: 104px;
            object-fit: contain;
            border-radius: 50%;
        }

        .auth-left h2 { color: #fff; font-size: 1.35rem; font-weight: 700; text-align: center; margin-bottom: 8px; z-index: 1; }
        .auth-left p  { color: rgba(255,255,255,0.82); font-size: 0.82rem; text-align: center; line-height: 1.6; z-index: 1; padding: 0 10px; }

        .tagline {
            margin-top: 14px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 7px 16px;
            color: #c8e6c9;
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

        .auth-right {
            width: 58%;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 42px 50px;
        }

        .auth-right h3 { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .auth-right .subtitle { color: #8a8fa3; font-size: 0.85rem; margin-bottom: 22px; }

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
            border-bottom-color: #2e7d32;
            box-shadow: none;
            background: transparent;
        }

        .btn-register {
            background: linear-gradient(90deg, #0a3d1f, #2e7d32);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 11px 0;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            width: 100%;
            margin-top: 8px;
            transition: opacity 0.2s;
        }

        .btn-register:hover { opacity: 0.88; color: #fff; }

        .password-strength {
            height: 4px;
            border-radius: 4px;
            background: #e0e3ef;
            margin-top: 6px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }

        .strength-text { font-size: 0.72rem; margin-top: 3px; }

        .alert-custom {
            font-size: 0.83rem;
            border-radius: 8px;
            padding: 9px 14px;
            margin-bottom: 16px;
            width: 100%;
        }

        .link-green { color: #2e7d32; font-weight: 600; text-decoration: none; }
        .link-green:hover { text-decoration: underline; color: #1b5e20; }

        @media (max-width: 680px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 40px 28px; }
            .auth-card { width: 98vw; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <!-- ── LEFT PANEL ── -->
    <div class="auth-left">
        <div class="wave-mid"></div>
        <div class="logo-wrap">
            <img src="<?= base_url('img/logo.png') ?>" alt="SND IT Solutions Logo"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:2.8rem;font-weight:900;color:#2e7d32;line-height:1\'>SND</span>'">
        </div>
        <h2>Join as a<br>Rental Owner</h2>
        <p>Register your business and start managing rentals on our platform.</p>
        <div class="tagline">We Fix, Print, Build &amp; Code!</div>
        <div class="portal-badge"><i class="bi bi-shop"></i> Owner Portal</div>
    </div>

    <!-- ── RIGHT PANEL ── -->
    <div class="auth-right">
        <h3>Owner Registration</h3>
        <p class="subtitle">Fill in the details below to register as an owner</p>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-custom">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <div><i class="bi bi-dot"></i> <?= esc($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-custom">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('owner/register') ?>" method="POST" style="width: 100%;">
            <?= csrf_field() ?>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name"
                       placeholder="Full Name" value="<?= old('name') ?>" required autofocus>
                <label for="name"><i class="bi bi-person me-1"></i> Full Name / Business Name</label>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="Email" value="<?= old('email') ?>" required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email Address</label>
            </div>

            <div class="form-floating mb-1 position-relative">
                <input type="password" class="form-control pe-5" id="password" name="password"
                       placeholder="Password" required oninput="checkStrength(this.value)">
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                <button type="button" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;color:#9098b8;cursor:pointer;padding:0;z-index:10;font-size:1rem;"
                        onclick="(function(b){var i=document.getElementById('password');var ic=b.querySelector('i');if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}})(this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div class="password-strength mb-1">
                <div class="password-strength-bar" id="strengthBar"></div>
            </div>
            <div class="strength-text text-muted mb-3" id="strengthText"></div>

            <div class="form-floating mb-4 position-relative">
                <input type="password" class="form-control pe-5" id="password_confirm" name="password_confirm"
                       placeholder="Confirm Password" required>
                <label for="password_confirm"><i class="bi bi-lock-fill me-1"></i> Confirm Password</label>
                <button type="button" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:none;border:none;color:#9098b8;cursor:pointer;padding:0;z-index:10;font-size:1rem;"
                        onclick="(function(b){var i=document.getElementById('password_confirm');var ic=b.querySelector('i');if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}})(this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-register">
                <i class="bi bi-shop me-2"></i> Register as Owner
            </button>
        </form>

        <p class="mt-3 mb-0" style="font-size:0.84rem; color:#8a8fa3;">
            Already have an account?
            <a href="<?= base_url('owner/login') ?>" class="link-green">Sign In</a>
        </p>
        <p class="mt-2" style="font-size:0.82rem;">
            <a href="<?= base_url('portal') ?>" class="link-green">
                <i class="bi bi-arrow-left me-1"></i> Back to Portal Selection
            </a>
        </p>
    </div>

</div>

<script>
function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { w: '0%',   c: 'transparent', t: '' },
        { w: '25%',  c: '#ef5350',     t: '<span style="color:#ef5350">Weak</span>' },
        { w: '50%',  c: '#ff9800',     t: '<span style="color:#ff9800">Fair</span>' },
        { w: '75%',  c: '#fdd835',     t: '<span style="color:#f9a825">Good</span>' },
        { w: '90%',  c: '#66bb6a',     t: '<span style="color:#388e3c">Strong</span>' },
        { w: '100%', c: '#2e7d32',     t: '<span style="color:#1b5e20">Very Strong</span>' },
    ];
    bar.style.width      = levels[score].w;
    bar.style.background = levels[score].c;
    text.innerHTML       = levels[score].t;
}
</script>
</body>
</html>
