<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Islands Getaway Travel and Tours' ?></title>
<?php
// Role-based URL prefix for navigation
$role = session()->get('role');
$urlPrefix = match($role) {
    'snd_admin' => '/admin',
    'owner' => '/owner',
    'customer' => '/customer',
    default => '',
};
?>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-w: 240px;
            --sidebar-bg: #9c0b09;
            --sidebar-hover: #b80d0b;
            --topbar-h: 60px;
            --accent: #9c0b09;
        }

        body { background: #f0f4ff; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1040;
            transition: transform .28s ease;
        }

        .sidebar-brand {
            height: var(--topbar-h);
            display: flex; align-items: center; gap: 10px;
            padding: 0 18px;
            background: rgba(0,0,0,0.18);
        }

        .sidebar-brand img {
            width: 36px; height: 36px; border-radius: 50%;
            object-fit: contain; background: #fff;
        }

        .sidebar-brand span {
            color: #fff; font-weight: 700; font-size: 1rem; letter-spacing: .3px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 14px 0; }

        .nav-section {
            color: rgba(255,255,255,0.42);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 14px 20px 4px;
        }

        .sidebar-nav a {
            display: flex; align-items: center; gap: 11px;
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            padding: 10px 20px;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            transition: all .18s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.09);
            border-left-color: #ffe082;
            color: #fff;
        }

        .sidebar-nav a .bi { font-size: 1.1rem; }

        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer a {
            display: flex; align-items: center; gap: 9px;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            text-decoration: none;
        }

        .sidebar-footer a:hover { color: #fff; }

        /* ── Topbar ── */
        #topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h);
            background: #fff;
            display: flex; align-items: center; padding: 0 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            z-index: 1030;
            gap: 14px;
        }

        #topbar .page-title {
            font-size: 1rem; font-weight: 600; color: #1a1a2e; flex: 1;
        }

        #topbar .user-pill {
            display: flex; align-items: center; gap: 8px;
            background: #f0f4ff; border-radius: 50px;
            padding: 5px 14px 5px 8px;
            font-size: 0.82rem; color: #334;
        }

        #topbar .user-pill .avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 0.75rem;
        }

        .badge-role {
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 50px;
        }

        /* ── Main Content ── */
        #main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }

        .content-area { padding: 28px 28px; }

        /* ── Cards ── */
        .stat-card {
            border-radius: 14px;
            color: #fff;
            padding: 22px 20px;
            display: flex; align-items: center; gap: 16px;
        }

        .stat-card .icon-box {
            width: 52px; height: 52px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
        }

        .stat-card h3 { font-size: 2rem; font-weight: 700; margin: 0; }
        .stat-card p  { margin: 0; font-size: 0.82rem; opacity: .85; }

        .card-section {
            background: #fff; border-radius: 14px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.06);
            padding: 22px;
        }

        .card-section .section-title {
            font-size: 1rem; font-weight: 600;
            color: #1a1a2e; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .card-section .section-title .text-primary { color: var(--accent) !important; }
        .card-section .section-title .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        /* Table */
        .table thead th {
            background: #f0f4ff; color: #334;
            font-size: 0.8rem;
            font-weight: 600; letter-spacing: .3px;
            border: none;
        }

        .table tbody td { font-size: 0.88rem; vertical-align: middle; }
        .table-hover tbody tr:hover { background: #f5f8ff; }

        /* Badges */
        .badge-pending  { background: #fff3cd; color: #856404; }
        .badge-ongoing  { background: #cfe2ff; color: #084298; }
        .badge-returned { background: #d1e7dd; color: #0f5132; }
        .badge-overdue  { background: #f8d7da; color: #842029; }
        .badge-booked            { background: #e8d5ff; color: #5b2d8e; }
        .badge-awaiting_payment  { background: #fff0d3; color: #7a4a00; }

        /* Notification Bell */
        .notif-bell {
            position: relative;
            cursor: pointer;
        }
        .notif-bell .bell-btn {
            width: 38px; height: 38px; border-radius: 50%;
            background: #f0f4ff;
            border: none;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            color: #334;
            transition: background .18s;
            position: relative;
        }
        .notif-bell .bell-btn:hover { background: #e0e8ff; }
        .notif-bell .badge-count {
            position: absolute;
            top: -4px; right: -4px;
            background: #e53935;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            min-width: 17px; height: 17px;
            border-radius: 50px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
        }
        .notif-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 340px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.14);
            z-index: 9999;
            display: none;
            overflow: hidden;
        }
        .notif-dropdown.show { display: block; }
        .notif-dropdown .notif-header {
            padding: 14px 18px 10px;
            border-bottom: 1px solid #f0f2f8;
            display: flex; align-items: center; justify-content: space-between;
        }
        .notif-dropdown .notif-header h6 { margin: 0; font-weight: 700; font-size: 0.9rem; }
        .notif-list { max-height: 320px; overflow-y: auto; }
        .notif-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 18px;
            border-bottom: 1px solid #f5f7ff;
            text-decoration: none;
            color: inherit;
            transition: background .15s;
        }
        .notif-item:hover { background: #f5f8ff; }
        .notif-item.unread { background: #f0f4ff; }
        .notif-item .notif-icon {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem; flex-shrink: 0;
        }
        .notif-item .notif-icon.info    { background: #e3f2fd; color: #1565c0; }
        .notif-item .notif-icon.success { background: #e8f5e9; color: #2e7d32; }
        .notif-item .notif-icon.warning { background: #fff8e1; color: #f57f17; }
        .notif-item .notif-icon.danger  { background: #fce4ec; color: #c62828; }
        .notif-item .notif-body { flex: 1; min-width: 0; }
        .notif-item .notif-title { font-size: 0.82rem; font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
        .notif-item .notif-msg   { font-size: 0.76rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .notif-item .notif-time  { font-size: 0.68rem; color: #aaa; marginTop: 2px; }
        .notif-dropdown .notif-footer {
            padding: 10px 18px;
            text-align: center;
            border-top: 1px solid #f0f2f8;
        }
        .notif-dropdown .notif-footer a { font-size: 0.8rem; color: #0d2c8a; }

        /* Mobile toggle */
        #sidebarToggle { display: none; }

        @media (max-width: 900px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
            #sidebarToggle { display: block; }
        }

        /* ── Verification Stepper ── */
        .verif-stepper { display: flex; align-items: flex-start; }
        .verif-step { display: flex; flex-direction: column; align-items: center; min-width: 130px; }
        .verif-circle {
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; border: 3px solid #dee2e6;
            background: #f8f9fa; color: #aaa;
            transition: all .25s; position: relative; z-index: 1;
        }
        .verif-step.step-done    .verif-circle { border-color: #198754; background: #198754; color: #fff; }
        .verif-step.step-pending .verif-circle { border-color: #ffc107; background: #fff8e1; color: #d97706; }
        .verif-step.step-empty   .verif-circle { border-color: #dee2e6; background: #f8f9fa; color: #aaa; }
        .verif-line {
            flex: 1; height: 3px; background: #dee2e6;
            margin-top: 22px; border-radius: 4px; transition: background .3s;
        }
        .verif-line.done { background: #198754; }
        /* ── Loading Screen ── */
        #page-loader {
            position: fixed; inset: 0;
            background: #fff;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            z-index: 99999;
            transition: opacity .35s ease, visibility .35s ease;
        }
        #page-loader.hide { opacity: 0; visibility: hidden; }
        .loader-logo {
            width: 56px; height: 56px; border-radius: 14px;
            background: var(--sidebar-bg);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
            animation: loaderPulse 1.2s ease-in-out infinite;
        }
        .loader-logo i { font-size: 1.8rem; color: #fff; }
        @keyframes loaderPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13,44,138,.3); }
            50%       { transform: scale(1.08); box-shadow: 0 0 0 10px rgba(13,44,138,0); }
        }
        .loader-bar {
            width: 160px; height: 3px;
            background: #e8eeff; border-radius: 99px;
            overflow: hidden;
        }
        .loader-bar-fill {
            height: 100%; width: 40%;
            background: linear-gradient(90deg, #0d2c8a, #2979ff);
            border-radius: 99px;
            animation: loaderSlide 1.1s ease-in-out infinite;
        }
        @keyframes loaderSlide {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }

        /* ── Top Progress Bar ── */
        #nprogress-bar {
            position: fixed; top: 0; left: 0;
            height: 3px; width: 0%;
            background: linear-gradient(90deg, #2979ff, #00e5ff);
            z-index: 999999;
            border-radius: 0 2px 2px 0;
            transition: width .2s ease;
            box-shadow: 0 0 8px #2979ff88;
        }

        /* ── Page-in animation ── */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .content-area { animation: fadeSlideIn .38s ease both; }

        /* ── Stat card hover lift ── */
        .stat-card { transition: transform .18s ease, box-shadow .18s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,0.13); }

        /* ── Card section hover ── */
        .card-section { transition: box-shadow .2s ease; }
        .card-section:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); }

        /* ── Button loading state ── */
        .btn-loading { position: relative; pointer-events: none; color: transparent !important; }
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px; height: 16px;
            border: 2px solid currentColor;
            border-color: #fff transparent #fff transparent;
            border-radius: 50%;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation: btnSpin .65s linear infinite;
            color: #fff;
        }
        @keyframes btnSpin { to { transform: translate(-50%, -50%) rotate(360deg); } }

        /* ── Table row slide-in ── */
        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .table tbody tr {
            animation: rowIn .22s ease both;
        }
        .table tbody tr:nth-child(1)  { animation-delay: .03s; }
        .table tbody tr:nth-child(2)  { animation-delay: .06s; }
        .table tbody tr:nth-child(3)  { animation-delay: .09s; }
        .table tbody tr:nth-child(4)  { animation-delay: .12s; }
        .table tbody tr:nth-child(5)  { animation-delay: .15s; }
        .table tbody tr:nth-child(6)  { animation-delay: .18s; }
        .table tbody tr:nth-child(7)  { animation-delay: .21s; }
        .table tbody tr:nth-child(8)  { animation-delay: .24s; }
        .table tbody tr:nth-child(9)  { animation-delay: .27s; }
        .table tbody tr:nth-child(10) { animation-delay: .30s; }

        /* ── Sidebar link transition ── */
        .sidebar-nav a { transition: all .2s cubic-bezier(.4,0,.2,1); }

        /* ── Alert slide-down ── */
        @keyframes alertIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert { animation: alertIn .3s ease both; }

        /* ── Notification dropdown slide ── */
        @keyframes dropDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .notif-dropdown.show { animation: dropDown .2s ease both; }

        /* ── Custom Confirm Modal ── */
        #snd-confirm-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.35);
            backdrop-filter: blur(3px);
            z-index: 999996;
            opacity: 0; pointer-events: none;
            transition: opacity .2s ease;
        }
        #snd-confirm-backdrop.show { opacity: 1; pointer-events: auto; }
        #snd-confirm-modal {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -56%) scale(0.9);
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.2);
            padding: 32px 36px 28px;
            z-index: 999997;
            min-width: 300px; max-width: 420px; width: 90%;
            text-align: center;
            opacity: 0; pointer-events: none;
            transition: opacity .22s ease, transform .25s cubic-bezier(.34,1.56,.64,1);
        }
        #snd-confirm-modal.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: auto;
        }
        #snd-confirm-modal .cm-icon {
            width: 58px; height: 58px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin: 0 auto 16px;
        }
        #snd-confirm-modal .cm-icon.warn  { background: #fff3cd; color: #d97706; }
        #snd-confirm-modal .cm-icon.danger { background: #fce4ec; color: #c62828; }
        #snd-confirm-modal .cm-icon.info  { background: #e3f2fd; color: #1565c0; }
        #snd-confirm-modal h6 {
            font-size: 1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 8px;
        }
        #snd-confirm-modal p {
            font-size: 0.85rem; color: #666; margin-bottom: 24px; line-height: 1.5;
        }
        #snd-confirm-modal .cm-actions { display: flex; gap: 10px; justify-content: center; }
        #snd-confirm-modal .cm-actions button { min-width: 100px; border-radius: 50px; font-weight: 600; }

        /* ── Flash Toast Popup ── */
        #flash-toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            padding: 32px 44px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .35s ease, transform .4s cubic-bezier(.34,1.56,.64,1);
            min-width: 220px;
            max-width: 380px;
            text-align: center;
        }
        #flash-toast.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: auto;
        }
        #flash-toast.hide-anim {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.85);
            transition: opacity .3s ease, transform .3s ease;
        }
        #flash-toast .ft-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.7rem;
            animation: ftBounce .5s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes ftBounce {
            0% { transform: scale(0); }
            60% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
        #flash-toast .ft-icon.success { background: #e8f5e9; color: #2e7d32; }
        #flash-toast .ft-icon.error { background: #ffebee; color: #c62828; }
        #flash-toast .ft-msg {
            font-size: 0.92rem;
            font-weight: 600;
            color: #1a1a2e;
            line-height: 1.4;
        }
        #flash-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.2);
            z-index: 999998;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }
        #flash-backdrop.show { opacity: 1; pointer-events: auto; }

        /* ── Please Wait Toast ── */
        #please-wait-toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.85);
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            padding: 32px 44px;
            z-index: 999998;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s ease, transform .22s cubic-bezier(.34,1.56,.64,1);
            min-width: 200px;
            text-align: center;
        }
        #please-wait-toast.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: auto;
        }
        #please-wait-toast .pw-spinner {
            width: 42px; height: 42px;
            border: 4px solid #e8eeff;
            border-top-color: #0d2c8a;
            border-radius: 50%;
            animation: pwSpin .75s linear infinite;
        }
        @keyframes pwSpin { to { transform: rotate(360deg); } }
        #please-wait-toast .pw-text {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1a1a2e;
        }
        #please-wait-toast .pw-sub {
            font-size: 0.75rem;
            color: #888;
            margin-top: -8px;
        }
        #pw-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.25);
            backdrop-filter: blur(2px);
            z-index: 999997;
            opacity: 0;
            pointer-events: none;
            transition: opacity .22s ease;
        }
        #pw-backdrop.show { opacity: 1; pointer-events: auto; }

        /* sidebar pending badge */
        .sidebar-count {
            margin-left: auto;
            background: #e53935; color: #fff;
            font-size: 0.62rem; font-weight: 700;
            min-width: 18px; height: 18px; border-radius: 50px;
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0 5px;
        }
    </style>
</head>
<body>

<!-- ── Top Progress Bar ── -->
<div id="nprogress-bar"></div>

<!-- ── Page Loader ── -->
<div id="page-loader">
    <div class="loader-logo"><i class="bi bi-box-seam"></i></div>
    <div class="loader-bar"><div class="loader-bar-fill"></div></div>
</div>

<!-- ── Flash Toast Popup ── -->
<div id="flash-backdrop"></div>
<div id="flash-toast">
    <div class="ft-icon" id="flash-icon"></div>
    <div class="ft-msg" id="flash-msg"></div>
</div>

<!-- ── Please Wait Toast ── -->
<div id="pw-backdrop"></div>
<div id="please-wait-toast">
    <div class="pw-spinner"></div>
    <div class="pw-text">Please Wait...</div>
    <div class="pw-sub" id="pw-sub-text">Processing your request</div>
</div>

<!-- ── Custom Confirm Modal ── -->
<div id="snd-confirm-backdrop"></div>
<div id="snd-confirm-modal">
    <div class="cm-icon warn" id="cm-icon"><i class="bi bi-question-circle-fill"></i></div>
    <h6 id="cm-title">Are you sure?</h6>
    <p id="cm-message">This action cannot be undone.</p>
    <div class="cm-actions">
        <button class="btn btn-outline-secondary btn-sm" id="cm-cancel-btn">Cancel</button>
        <button class="btn btn-primary btn-sm" id="cm-confirm-btn">Confirm</button>
    </div>
</div>

<!-- ── Sidebar ── -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= base_url('img/logo.png') ?>" alt="Logo"
             onerror="this.style.display='none'">
        <span>IGTT</span>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="<?= base_url($urlPrefix . '/dashboard') ?>" class="<?= (current_url() === base_url($urlPrefix . '/dashboard')) ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <?php $role = session()->get('role'); ?>

        <?php if ($role === 'snd_admin'): ?>
            <div class="nav-section">SND Admin</div>
            <a href="<?= base_url($urlPrefix . '/users') ?>" class="<?= str_contains(current_url(), '/users') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Manage Users
            </a>
            <a href="<?= base_url($urlPrefix . '/categories') ?>" class="<?= str_contains(current_url(), '/categories') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Categories
            </a>
        <?php endif; ?>

        <?php if (in_array($role, ['snd_admin', 'owner'])): ?>
            <div class="nav-section">Rental Management</div>
            <a href="<?= base_url($urlPrefix . '/items') ?>" class="<?= str_contains(current_url(), '/items') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Manage Items
            </a>
            <a href="<?= base_url($urlPrefix . '/rentals') ?>" class="<?= str_contains(current_url(), '/rentals') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i> Rentals
            </a>
            <?php
            try {
                $pendingVerif = model('App\Models\UserModel')
                    ->where('role', 'customer')->where('is_id_verified', 0)
                    ->where('valid_id IS NOT NULL', null, false)->where('valid_id !=', '')
                    ->countAllResults();
            } catch (\Throwable $e) { $pendingVerif = 0; }
            ?>
            <?php if ($role === 'snd_admin'): ?>
            <a href="<?= base_url($urlPrefix . '/verifications') ?>" class="<?= str_contains(current_url(), '/verifications') ? 'active' : '' ?>">
                <i class="bi bi-patch-check"></i> Verify IDs
                <?php if ($pendingVerif > 0): ?>
                    <span class="sidebar-count"><?= $pendingVerif ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a href="<?= base_url($urlPrefix . '/reports') ?>" class="<?= str_contains(current_url(), '/reports') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>
            <a href="<?= base_url($urlPrefix . '/payment-gateways') ?>" class="<?= str_contains(current_url(), '/payment-gateways') ? 'active' : '' ?>">
                <i class="bi bi-credit-card-2-front"></i> Payment Gateways
            </a>
            <?php if ($role === 'snd_admin'): ?>
            <?php
            try {
                $pendingTopups = (new \App\Models\WalletTopupRequestModel())->countPending();
            } catch (\Throwable $e) { $pendingTopups = 0; }
            ?>
            <a href="<?= base_url('/admin/wallet/requests') ?>" class="<?= str_contains(current_url(), '/wallet/admin') ? 'active' : '' ?>">
                <i class="bi bi-wallet2"></i> Wallet Top-Ups
                <?php if ($pendingTopups > 0): ?>
                    <span class="sidebar-count"><?= $pendingTopups ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a href="<?= base_url($urlPrefix . '/wallet') ?>" class="<?= str_contains(current_url(), $urlPrefix . '/wallet') ? 'active' : '' ?>">
                <i class="bi bi-wallet2"></i> Store Earnings
            </a>
            <a href="<?= base_url($urlPrefix . '/notifications') ?>" class="<?= str_contains(current_url(), '/notifications') ? 'active' : '' ?>">
                <i class="bi bi-bell"></i> Notifications
            </a>
            <a href="<?= base_url($urlPrefix . '/profile') ?>" class="<?= str_contains(current_url(), '/profile') ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        <?php endif; ?>

        <?php if ($role === 'customer'): ?>
            <div class="nav-section">My Account</div>
            <a href="<?= base_url($urlPrefix . '/rentals') ?>" class="<?= str_contains(current_url(), '/rentals') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i> My Rentals
            </a>
            <a href="<?= base_url($urlPrefix . '/wallet') ?>" class="<?= str_contains(current_url(), '/wallet') ? 'active' : '' ?>">
                <i class="bi bi-wallet2"></i> My Wallet
               
            </a>
            <a href="<?= base_url($urlPrefix . '/notifications') ?>" class="<?= str_contains(current_url(), '/notifications') ? 'active' : '' ?>">
                <i class="bi bi-bell"></i> Notifications
            </a>
           
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</nav>

<!-- ── Topbar ── -->
<div id="topbar">
    <button id="sidebarToggle" class="btn btn-sm btn-light me-2" onclick="document.getElementById('sidebar').classList.toggle('show')">
        <i class="bi bi-list fs-5"></i>
    </button>
    <div class="page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
    <!-- Wallet pill (customers only) -->
    <?php if (session()->get('role') === 'customer'): ?>
    <?php
    try {
        $topbarWalletBal = (new \App\Models\WalletModel())->getBalance(session()->get('user_id'));
    } catch (\Throwable $e) { $topbarWalletBal = 0; }
    ?>
    <a href="<?= base_url($urlPrefix . '/wallet') ?>" class="text-decoration-none" style="display:flex;align-items:center;gap:6px;background:#f0f4ff;border-radius:50px;padding:5px 14px;font-size:0.82rem;color:#0d2c8a;border:1px solid #c5d4f5;" title="My Wallet">
        <i class="bi bi-wallet2"></i>
        <span class="fw-semibold">₱<?= number_format($topbarWalletBal, 2) ?></span>
    </a>
    <?php endif; ?>
    <!-- Notification Bell -->
    <div class="notif-bell" id="notifBell">
        <button class="bell-btn" id="bellBtn" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="badge-count" id="notifCount" style="display:none;">0</span>
        </button>
        <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
                <h6><i class="bi bi-bell-fill me-1 text-primary"></i> Notifications</h6>
                <form action="<?= base_url($urlPrefix . '/notifications/mark-all-read') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link p-0" style="font-size:0.75rem;color:#0d2c8a;">Mark all read</button>
                </form>
            </div>
            <div class="notif-list" id="notifList">
                <div class="text-center text-muted py-4" style="font-size:0.82rem;"><i class="bi bi-bell-slash"></i> No notifications</div>
            </div>
            <div class="notif-footer">
                <a href="<?= base_url($urlPrefix . '/notifications') ?>">View all notifications</a>
            </div>
        </div>
    </div>

    <a href="<?= base_url($urlPrefix . '/profile') ?>" class="user-pill text-decoration-none" style="color:inherit;">
        <?php
        $avatarFile = (new \App\Models\UserModel())->find(session()->get('user_id'))['avatar'] ?? null;
        ?>
        <?php if ($avatarFile): ?>
            <img src="<?= base_url('uploads/avatars/' . esc($avatarFile)) ?>"
                 style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
        <?php else: ?>
            <div class="avatar"><?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?></div>
        <?php endif; ?>
        <span><?= esc(session()->get('user_name') ?? '') ?>
        <?php if (session()->get('id_verified')): ?>
            <i class="bi bi-patch-check-fill" style="color:#1d9bf0;font-size:.85rem;vertical-align:middle;" title="ID Verified"></i>
        <?php endif; ?>
        </span>
        <span class="badge badge-role ms-1
            <?php
            $r = session()->get('role');
            echo $r === 'snd_admin' ? 'bg-danger' : ($r === 'owner' ? 'bg-warning text-dark' : 'bg-primary');
            ?>">
            <?php
            $labels = ['snd_admin' => 'SND Admin', 'owner' => 'Rental Owner', 'customer' => 'Customer'];
            echo $labels[session()->get('role')] ?? ucfirst(session()->get('role') ?? '');
            ?>
        </span>
    </a>
</div>

<!-- ── Content ── -->
<div id="main-content">
    <div class="content-area">

        <?php if (session()->getFlashdata('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showFlashToast('success', <?= json_encode(session()->getFlashdata('success')) ?>);
                });
            </script>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showFlashToast('error', <?= json_encode(session()->getFlashdata('error')) ?>);
                });
            </script>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>

<script>
// ── Notification Bell ──
const bellBtn      = document.getElementById('bellBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifCount   = document.getElementById('notifCount');
const notifList    = document.getElementById('notifList');

const typeIcon = {
    success: 'bi-check-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    danger:  'bi-x-circle-fill',
    info:    'bi-info-circle-fill',
};

function timeAgo(dateStr) {
    const d    = new Date(dateStr);
    const diff = Math.floor((Date.now() - d.getTime()) / 1000);
    if (diff < 60)  return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function fetchNotifications() {
    fetch('<?= base_url($urlPrefix . '/notifications/fetch') ?>')
        .then(r => r.json())
        .then(data => {
            // Update badge
            if (data.unread > 0) {
                notifCount.style.display = '';
                notifCount.textContent   = data.unread > 99 ? '99+' : data.unread;
            } else {
                notifCount.style.display = 'none';
            }

            // Build dropdown list
            if (data.items.length === 0) {
                notifList.innerHTML = '<div class="text-center text-muted py-4" style="font-size:0.82rem;"><i class="bi bi-bell-slash"></i> No notifications</div>';
            } else {
                notifList.innerHTML = data.items.map(n => `
                    <a href="<?= base_url($urlPrefix . '/notifications/read/') ?>${n.id}"
                       class="notif-item ${n.is_read == 0 ? 'unread' : ''}">
                        <div class="notif-icon ${n.type}">
                            <i class="bi ${typeIcon[n.type] || 'bi-info-circle-fill'}"></i>
                        </div>
                        <div class="notif-body">
                            <div class="notif-title">${n.title}</div>
                            <div class="notif-msg">${n.message}</div>
                            <div class="notif-time">${timeAgo(n.created_at)}</div>
                        </div>
                    </a>
                `).join('');
            }
        })
        .catch(() => {});
}

// Toggle dropdown
bellBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    notifDropdown.classList.toggle('show');
    if (notifDropdown.classList.contains('show')) fetchNotifications();
});

// Close on outside click
document.addEventListener('click', function(e) {
    if (!document.getElementById('notifBell').contains(e.target)) {
        notifDropdown.classList.remove('show');
    }
});

// Initial load + poll every 30s
fetchNotifications();
setInterval(fetchNotifications, 30000);

// ── Page Loader ──────────────────────────────────────────
const pageLoader = document.getElementById('page-loader');
const nprogressBar = document.getElementById('nprogress-bar');

// Hide loader once page is ready
window.addEventListener('load', () => {
    pageLoader.classList.add('hide');
    nprogressBar.style.width = '100%';
    setTimeout(() => { nprogressBar.style.opacity = '0'; }, 300);
});

// Show loader + progress bar on navigation
function showLoader() {
    pageLoader.classList.remove('hide');
    nprogressBar.style.opacity = '1';
    nprogressBar.style.width = '0%';
    setTimeout(() => { nprogressBar.style.width = '70%'; }, 50);
}

// ── Custom Confirm Modal ──────────────────────────────────
const cmBackdrop   = document.getElementById('snd-confirm-backdrop');
const cmModal      = document.getElementById('snd-confirm-modal');
const cmTitle      = document.getElementById('cm-title');
const cmMessage    = document.getElementById('cm-message');
const cmIcon       = document.getElementById('cm-icon');
const cmConfirmBtn = document.getElementById('cm-confirm-btn');
const cmCancelBtn  = document.getElementById('cm-cancel-btn');
let cmCallback     = null;

function sndConfirm({ title, message, type, confirmText, confirmClass, onConfirm }) {
    cmTitle.textContent      = title || 'Are you sure?';
    cmMessage.textContent    = message || 'This action cannot be undone.';
    cmIcon.className         = 'cm-icon ' + (type || 'warn');
    cmIcon.innerHTML         = type === 'danger'
        ? '<i class="bi bi-exclamation-triangle-fill"></i>'
        : type === 'info'
        ? '<i class="bi bi-info-circle-fill"></i>'
        : '<i class="bi bi-question-circle-fill"></i>';
    cmConfirmBtn.textContent = confirmText || 'Confirm';
    cmConfirmBtn.className   = 'btn btn-sm ' + (confirmClass || 'btn-primary');
    cmCallback = onConfirm;
    cmBackdrop.classList.add('show');
    cmModal.classList.add('show');
}

function closeCmModal() {
    cmBackdrop.classList.remove('show');
    cmModal.classList.remove('show');
    cmCallback = null;
}

cmConfirmBtn.addEventListener('click', function() {
    const cb = cmCallback;
    closeCmModal();
    if (cb) cb();
});
cmCancelBtn.addEventListener('click', closeCmModal);
cmBackdrop.addEventListener('click', closeCmModal);

// Intercept forms with data-confirm attribute
document.addEventListener('submit', function(e) {
    const form = e.target;
    const msg  = form.getAttribute('data-confirm');
    if (!msg) return;
    if (form._sndConfirmed) { form._sndConfirmed = false; return; }
    e.preventDefault();
    e.stopImmediatePropagation();

    const type = form.getAttribute('data-confirm-type') || 'warn';
    const title = form.getAttribute('data-confirm-title') || 'Are you sure?';
    const confirmText = form.getAttribute('data-confirm-btn') || 'Confirm';
    const confirmClass = type === 'danger' ? 'btn-danger' : type === 'info' ? 'btn-success' : 'btn-warning';

    sndConfirm({
        title, message: msg, type, confirmText, confirmClass,
        onConfirm: () => {
            form._sndConfirmed = true;
            form.submit();
        }
    });
}, true);

// Intercept buttons/links with data-confirm attribute (not forms)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-confirm]:not(form)');
    if (!btn) return;
    if (btn.tagName === 'A' || btn.tagName === 'BUTTON') {
        const msg = btn.getAttribute('data-confirm');
        if (!msg) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        const type = btn.getAttribute('data-confirm-type') || 'warn';
        const title = btn.getAttribute('data-confirm-title') || 'Are you sure?';
        const confirmText = btn.getAttribute('data-confirm-btn') || 'Confirm';
        const confirmClass = type === 'danger' ? 'btn-danger' : 'btn-warning';
        sndConfirm({
            title, message: msg, type, confirmText, confirmClass,
            onConfirm: () => {
                if (btn.tagName === 'A') { window.location.href = btn.href; }
                else { const f = btn.closest('form'); if (f) { btn.removeAttribute('data-confirm'); f.submit(); } }
            }
        });
    }
}, true);

// ── Flash Toast Popup ──────────────────────────────────────
function showFlashToast(type, message) {
    const toast = document.getElementById('flash-toast');
    const backdrop = document.getElementById('flash-backdrop');
    const icon = document.getElementById('flash-icon');
    const msg = document.getElementById('flash-msg');

    icon.className = 'ft-icon ' + type;
    icon.innerHTML = type === 'success'
        ? '<i class="bi bi-check-circle-fill"></i>'
        : '<i class="bi bi-exclamation-triangle-fill"></i>';
    msg.textContent = message;

    toast.classList.remove('hide-anim');
    toast.classList.add('show');
    backdrop.classList.add('show');

    const close = () => {
        toast.classList.add('hide-anim');
        toast.classList.remove('show');
        backdrop.classList.remove('show');
    };

    backdrop.onclick = close;
    setTimeout(close, 3000);
}

// ── Please Wait Toast ─────────────────────────────────────
const pwToast    = document.getElementById('please-wait-toast');
const pwBackdrop = document.getElementById('pw-backdrop');
const pwSubText  = document.getElementById('pw-sub-text');

// Clear please-wait whenever any Bootstrap modal opens (prevents stuck overlays)
document.addEventListener('show.bs.modal', function() {
    pwBackdrop.classList.remove('show');
    pwToast.classList.remove('show');
});

function showPleaseWait(subMsg) {
    pwSubText.textContent = subMsg || 'Processing your request';
    pwBackdrop.classList.add('show');
    pwToast.classList.add('show');
    // Progress bar
    nprogressBar.style.opacity = '1';
    nprogressBar.style.width   = '0%';
    setTimeout(() => { nprogressBar.style.width = '70%'; }, 50);
}

// Trigger on all <a> link clicks (except anchors, external, target_blank, modal-related)
document.addEventListener('click', function(e) {
    const a = e.target.closest('a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript') ||
        a.target === '_blank' || a.hostname !== location.hostname) return;
    // Skip links inside modals or that control modals
    if (e.target.closest('.modal') ||
        a.hasAttribute('data-bs-toggle') ||
        a.hasAttribute('data-bs-dismiss') ||
        a.hasAttribute('data-bs-target')) return;
    showPleaseWait('Loading page...');
    // Let the navigation happen after a brief delay so toast is visible
    e.preventDefault();
    setTimeout(() => { window.location.href = a.href; }, 350);
});

// ── Button loading state on form submit ──────────────────
document.addEventListener('submit', function(e) {
    const form = e.target;
    // Skip forms inside Bootstrap modals
    if (form.closest('.modal')) return;
    const btn  = form.querySelector('[type=submit]');
    const label = btn ? btn.textContent.trim() : '';
    showPleaseWait(label ? 'Submitting: ' + label : 'Submitting form...');

    if (btn) {
        btn.classList.add('btn-loading');
        btn.disabled = true;
    }
});
</script>
</body>
</html>
