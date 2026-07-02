<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IGTT — Browse Items</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #9c0b09;
            --primary-dark: #7a0807;
            --primary-light: #b80d0b;
            --accent: #ffffff;
            --accent-dark: #f0f0f0;
            --white: #ffffff;
            --light: #ffffff;
            --gray-100: #f8f8f8;
            --gray-200: #eee;
            --gray-300: #ddd;
            --gray-500: #888;
            --gray-700: #444;
            --gray-900: #222;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { margin: 0 !important; padding: 0 !important; }
        body { background: var(--light); font-family: 'Outfit', 'Segoe UI', sans-serif; min-height: 100vh; padding-top: 72px; color: var(--gray-900); scroll-behavior: smooth; }

        /* ── Animations ── */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .fade-in { animation: fadeInUp 0.6s ease-out forwards; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* ── Navbar ── */
        .snd-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            background: var(--primary);
            padding: 0 24px; display: flex; align-items: center; gap: 16px;
            height: 72px; box-shadow: 0 4px 20px rgba(156,11,9,0.3);
        }
        .snd-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; flex-shrink: 0; }
        .snd-brand img { width: 48px; height: 48px; border-radius: 0; object-fit: contain; background: transparent; padding: 2px; }
        .snd-brand span { color: var(--white); font-size: 1.15rem; font-weight: 800; white-space: nowrap; letter-spacing: -0.5px; }
        .nav-divider { width: 1px; height: 32px; background: rgba(255,255,255,0.3); flex-shrink: 0; }
        .snd-search { flex: 1; max-width: 520px; display: flex; border-radius: 12px; overflow: hidden; background: var(--white); box-shadow: 0 2px 12px rgba(0,0,0,0.15); }
        .snd-search input { flex: 1; border: none; padding: 0 16px; font-size: 0.9rem; outline: none; color: var(--gray-900); height: 44px; }
        .snd-search button { background: var(--accent); border: none; padding: 0 20px; color: var(--primary); font-size: 1.1rem; cursor: pointer; transition: all 0.2s; }
        .snd-search button:hover { background: var(--accent-dark); }
        .nav-actions { display: flex; align-items: center; gap: 10px; margin-left: auto; flex-shrink: 0; }
        .nav-btn { padding: 10px 20px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
        .nav-btn-outline { background: transparent; border: 2px solid var(--accent); color: var(--accent); }
        .nav-btn-outline:hover { background: var(--accent); color: var(--primary); }
        .nav-btn-solid { background: var(--accent); color: var(--primary); }
        .nav-btn-solid:hover { background: var(--accent-dark); }

        /* ── Sub-nav ── */
        .sub-nav { background: var(--primary-dark); display: flex; align-items: center; gap: 4px; padding: 0 24px; height: 44px; overflow-x: auto; scrollbar-width: none; }
        .sub-nav::-webkit-scrollbar { display: none; }
        .sub-nav a { color: rgba(255,255,255,0.75); font-size: 0.78rem; text-decoration: none; padding: 0 14px; height: 44px; display: flex; align-items: center; gap: 6px; white-space: nowrap; transition: all 0.2s; border-bottom: 3px solid transparent; }
        .sub-nav a:hover { color: var(--accent); border-bottom-color: var(--accent); }
        .sub-nav .sep { color: rgba(255,255,255,0.25); font-size: 0.7rem; }

        /* ── Hero ── */
        .hero { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 48px 24px; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M50 50 L50 0 L100 50 Z' fill='%23ffffff' fill-opacity='0.08'/%3E%3Ccircle cx='0' cy='100' r='30' fill='%23ffffff' fill-opacity='0.05'/%3E%3Crect x='80' y='20' width='40' height='40' fill='%23ffffff' fill-opacity='0.05' transform='rotate(45 100 40)'/%3E%3C/svg%3E"); }
        .hero-inner { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 24px; position: relative; }
        .hero-logo-wrap img { width: 100%; height: 100%; border-radius: 0; object-fit: contain; }
        .hero-text h1 { color: var(--white); font-size: 1.8rem; font-weight: 800; margin-bottom: 6px; letter-spacing: -0.5px; animation: slideInUp 0.8s ease both; }
        .hero-text p { color: rgba(255,255,255,0.8); font-size: 0.9rem; max-width: 400px; animation: slideInUp 0.8s ease 0.3s both; }
        .hero-badges { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; animation: slideInUp 0.8s ease 0.5s both; }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        .hero-logo-wrap { width: 90px; height: 90px; border-radius: 0; background: transparent; display: flex; align-items: center; justify-content: center; flex-shrink: 0; animation: fadeInScale 0.6s ease both; }
        .hero-badge { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 20px; padding: 4px 12px; color: var(--white); font-size: 0.7rem; font-weight: 600; display: flex; align-items: center; gap: 5px; backdrop-filter: blur(4px); }
        /* ── Category Tabs ── */
        .cat-bar { background: var(--white); box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow-x: auto; scrollbar-width: none; }
        .cat-bar::-webkit-scrollbar { display: none; }
        .cat-inner { display: flex; max-width: 1200px; margin: 0 auto; padding: 0 16px; }
        .cat-tab { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 14px 20px 12px; text-decoration: none; color: var(--gray-700); font-size: 0.75rem; font-weight: 700; border-bottom: 4px solid transparent; white-space: nowrap; transition: all 0.2s; flex-shrink: 0; }
        .cat-tab:hover, .cat-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        .cat-tab .cat-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; background: var(--gray-100); transition: all 0.2s; }
        .cat-tab.active .cat-icon { background: var(--primary); color: var(--white); box-shadow: 0 4px 12px rgba(156,11,9,0.3); }

        /* ── Filter Bar ── */
        .filter-bar { background: var(--white); border-bottom: 1px solid var(--gray-200); padding: 12px 0; }
        .filter-inner { max-width: 1200px; margin: 0 auto; padding: 0 16px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .filter-label { font-size: 0.75rem; font-weight: 700; color: var(--gray-500); white-space: nowrap; text-transform: uppercase; letter-spacing: 0.5px; }
        .avail-toggle { display: flex; border: 2px solid var(--gray-200); border-radius: 10px; overflow: hidden; }
        .avail-toggle a { padding: 6px 14px; font-size: 0.75rem; font-weight: 700; color: var(--gray-700); text-decoration: none; background: var(--white); transition: all 0.18s; white-space: nowrap; }
        .avail-toggle a.active { background: var(--primary); color: var(--white); }
        .avail-toggle a:not(.active):hover { background: var(--gray-100); }
        .sort-select { border: 2px solid var(--gray-200); border-radius: 10px; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; color: var(--gray-900); background: var(--white); cursor: pointer; outline: none; }
        .sort-select:focus { border-color: var(--primary); }
        .filter-sep { width: 1px; height: 24px; background: var(--gray-200); flex-shrink: 0; }
        .active-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .filter-chip { background: var(--accent); color: var(--primary); border-radius: 20px; padding: 4px 12px; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 5px; }
        .filter-chip a { color: var(--primary); text-decoration: none; font-size: 0.75rem; line-height: 1; }
        .filter-chip a:hover { color: var(--primary-dark); }

        /* ── Page Wrap ── */
        .page-wrap { max-width: 1200px; margin: 0 auto; padding: 32px 16px 56px; }
        .section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-size: 1.15rem; font-weight: 800; color: var(--gray-900); display: flex; align-items: center; gap: 8px; }
        .section-title span.accent { display: inline-block; width: 5px; height: 22px; background: var(--primary); border-radius: 3px; }

        /* ── Item Grid ── */
        .item-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
        .item-card { background: var(--white); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: all 0.25s; cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; border: 2px solid transparent; }
        .item-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(156,11,9,0.15); border-color: var(--accent); color: inherit; }
        .item-img-wrap { width: 100%; aspect-ratio: 1/1; overflow: hidden; background: linear-gradient(135deg, var(--gray-100), var(--white)); position: relative; }
        .item-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .item-card:hover .item-img-wrap img { transform: scale(1.08); }
        .item-badge { position: absolute; top: 10px; left: 10px; background: var(--primary); color: var(--white); font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(156,11,9,0.3); text-transform: uppercase; letter-spacing: 0.5px; }
        .item-badge.out { background: var(--gray-500); }
        .no-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--gray-100), var(--white)); color: var(--gray-300); font-size: 2.5rem; }
        .item-info { padding: 14px 12px; flex: 1; display: flex; flex-direction: column; }
        .item-name { font-size: 0.85rem; font-weight: 700; color: var(--gray-900); line-height: 1.4; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .item-category { font-size: 0.68rem; color: var(--gray-500); margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .item-price { font-size: 1rem; font-weight: 800; color: var(--primary); margin-top: auto; }
        .item-price .unit { font-size: 0.7rem; font-weight: 600; color: var(--gray-700); margin-left: 2px; }
        .item-qty { font-size: 0.68rem; color: var(--gray-500); margin-top: 4px; font-weight: 600; }
        .empty-state { text-align: center; padding: 80px 20px; color: var(--gray-500); }
        .empty-state i { font-size: 3.5rem; margin-bottom: 16px; display: block; color: var(--gray-300); }
        .empty-state p { font-size: 0.95rem; }

        /* ── Shared Section ── */
        .s-wrap { padding: 48px 24px; }
        .s-wrap-white { background: var(--white); }
        .s-inner { max-width: 1200px; margin: 0 auto; }
        .s-head { text-align: center; margin-bottom: 28px; }
        .s-tag { display: inline-block; background: var(--accent); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 5px 14px; border-radius: 20px; margin-bottom: 8px; letter-spacing: 1px; text-transform: uppercase; box-shadow: 0 2px 8px rgba(156,11,9,0.2); }
        .s-head h2 { font-size: 1.4rem; font-weight: 800; color: var(--gray-900); margin-bottom: 6px; letter-spacing: -0.3px; }
        .s-head p { font-size: 0.85rem; color: var(--gray-500); }

        /* ── How It Works ── */
        .hiw-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .hiw-step { text-align: center; padding: 20px 16px; position: relative; background: var(--white); border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: all 0.25s; }
        .hiw-step:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(156,11,9,0.12); }
        .hiw-step::after { content: ''; position: absolute; top: 50%; right: -10px; width: 20px; height: 2px; background: var(--gray-200); transform: translateY(-50%); }
        .hiw-step:last-child::after { display: none; }
        .hiw-num { width: 48px; height: 48px; border-radius: 14px; background: var(--primary); color: var(--white); font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; box-shadow: 0 6px 16px rgba(156,11,9,0.3); }
        .hiw-step h4 { font-size: 0.9rem; font-weight: 700; color: var(--gray-900); margin-bottom: 6px; }
        .hiw-step p { font-size: 0.75rem; color: var(--gray-500); line-height: 1.5; }

        /* ── Features ── */
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .feature-card { background: var(--white); border-radius: 16px; padding: 20px; display: flex; gap: 14px; align-items: flex-start; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: all 0.25s; border: 2px solid transparent; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(156,11,9,0.1); border-color: var(--accent); }
        .feature-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .fi-red { background: var(--primary); color: var(--white); }
        .fi-yellow { background: var(--accent); color: var(--primary); }
        .fi-dark { background: var(--gray-900); color: var(--white); }
        .fi-blue { background: #e8f0ff; color: #1a52cc; }
        .fi-green { background: #e8f5e9; color: #2e7d32; }
        .fi-purple { background: #f3e5f5; color: #6a1b9a; }
        .fi-amber { background: #fff8e1; color: #f57f17; }
        .fi-teal { background: #e0f2f1; color: #00695c; }
        .feature-text h4 { font-size: 0.9rem; font-weight: 700; color: var(--gray-900); margin-bottom: 4px; }
        .feature-text p { font-size: 0.75rem; color: var(--gray-500); line-height: 1.5; margin: 0; }

        /* ── Recommended ── */
        .rec-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .rec-card { background: var(--white); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: all 0.25s; border: 2px solid transparent; }
        .rec-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(156,11,9,0.12); border-color: var(--accent); }
        .rec-img { width: 100%; height: 120px; object-fit: cover; background: var(--gray-100); }
        .rec-info { padding: 14px; }
        .rec-name { font-size: 0.85rem; font-weight: 700; color: var(--gray-900); margin-bottom: 4px; }
        .rec-cat { font-size: 0.65rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 10px; }
        .rec-price { font-size: 1rem; font-weight: 800; color: var(--primary); }
        .rec-btn { display: block; text-align: center; padding: 10px; background: var(--primary); color: var(--white); text-decoration: none; font-weight: 700; font-size: 0.8rem; transition: all 0.2s; }
        .rec-btn:hover { background: var(--primary-dark); color: var(--accent); }

        /* ── CTA ── */
        .cta-wrap { padding: 0 24px 64px; }
        .cta-inner { max-width: 1200px; margin: 0 auto; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 24px; padding: 48px 56px; display: flex; align-items: center; justify-content: space-between; gap: 32px; position: relative; overflow: hidden; box-shadow: 0 12px 40px rgba(156,11,9,0.3); }
        .cta-inner::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='40' cy='40' r='30' fill='%23ffffff' fill-opacity='0.1'/%3E%3Crect x='10' y='10' width='20' height='20' fill='%23ffffff' fill-opacity='0.05' transform='rotate(20 20 20)'/%3E%3C/svg%3E"); }
        .cta-text { position: relative; }
        .cta-text h2 { color: var(--white); font-size: 1.6rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.3px; }
        .cta-text p { color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0; max-width: 400px; }
        .cta-btns { display: flex; gap: 12px; flex-shrink: 0; position: relative; }
        .cta-btn-white { background: var(--accent); color: var(--primary); padding: 14px 28px; border-radius: 12px; font-size: 0.9rem; font-weight: 800; text-decoration: none; white-space: nowrap; transition: all 0.2s; box-shadow: 0 4px 16px rgba(156,11,9,0.25); }
        .cta-btn-white:hover { background: var(--white); transform: translateY(-2px); }
        .cta-btn-ghost { border: 2px solid var(--white); color: var(--white); padding: 14px 28px; border-radius: 12px; font-size: 0.9rem; font-weight: 700; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
        .cta-btn-ghost:hover { background: var(--white); color: var(--primary); }

        /* ── Footer ── */
        .snd-footer { background: var(--gray-900); padding: 40px 24px 0; }
        .footer-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 32px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .footer-brand img { width: 56px; height: 56px; border-radius: 0; background: transparent; padding: 2px; object-fit: contain; margin-bottom: 12px; display: block; }
        .footer-brand h3 { color: var(--white); font-size: 1rem; font-weight: 800; margin-bottom: 6px; }
        .footer-brand p { color: rgba(255,255,255,0.5); font-size: 0.8rem; line-height: 1.6; margin: 0 0 14px; }
        .footer-social { display: flex; gap: 10px; }
        .footer-social a { width: 40px; height: 40px; border-radius: 10px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: var(--white); font-size: 1rem; text-decoration: none; transition: all 0.2s; }
        .footer-social a:hover { background: var(--accent); color: var(--primary); transform: translateY(-2px); }
        .footer-col h4 { color: var(--accent); font-size: 0.75rem; font-weight: 800; margin-bottom: 12px; letter-spacing: 1px; text-transform: uppercase; }
        .footer-col ul { list-style: none; padding: 0; margin: 0; }
        .footer-col li { margin-bottom: 8px; }
        .footer-col a { color: rgba(255,255,255,0.6); font-size: 0.8rem; text-decoration: none; transition: color 0.2s; }
        .footer-col a:hover { color: var(--accent); }
        .footer-bottom { max-width: 1200px; margin: 0 auto; padding: 16px 0; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .footer-bottom > span { color: rgba(255,255,255,0.35); font-size: 0.75rem; }
        .badge-pills { display: flex; gap: 8px; }
        .badge-pill { background: rgba(255,255,255,0.08); border-radius: 20px; padding: 4px 12px; color: rgba(255,255,255,0.5); font-size: 0.68rem; }

        /* ── Responsive ── */
        @media (max-width: 1100px) { .item-grid { grid-template-columns: repeat(4,1fr); } .rec-grid { grid-template-columns: repeat(3,1fr); } .footer-inner { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 860px)  { .item-grid { grid-template-columns: repeat(3,1fr); } .rec-grid { grid-template-columns: repeat(2,1fr); } .features-grid { grid-template-columns: repeat(2,1fr); } .hiw-grid { grid-template-columns: repeat(2,1fr); } .hiw-step::after { display: none; } .cta-inner { flex-direction: column; text-align: center; } .cta-btns { justify-content: center; } }
        @media (max-width: 580px)  { .item-grid { grid-template-columns: repeat(2,1fr); } .rec-grid { grid-template-columns: 1fr; } .snd-search { display: none; } .features-grid { grid-template-columns: 1fr; } .hiw-grid { grid-template-columns: 1fr; } .footer-inner { grid-template-columns: 1fr; } .footer-bottom { flex-direction: column; text-align: center; } .hero-text h1 { font-size: 1.4rem; } .hero { padding: 32px 16px; } }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="snd-navbar">
    <a class="snd-brand" href="<?= base_url('portal') ?>">
        <img src="<?= base_url('img/logo.png') ?>" alt="IGTT" onerror="this.style.display='none'">
        <span>IGTT</span>
    </a>
    <div class="nav-divider"></div>
    <form class="snd-search" method="get" action="<?= base_url('portal') ?>">
        <input type="text" name="q" placeholder="Search rental items..." value="<?= esc($searchQ ?? '') ?>">
        <?php if (!empty($activeCat)): ?>
        <input type="hidden" name="category" value="<?= esc($activeCat) ?>">
        <?php endif; ?>
        <?php if (!empty($activeSort) && $activeSort !== 'newest'): ?>
        <input type="hidden" name="sort" value="<?= esc($activeSort) ?>">
        <?php endif; ?>
        <?php if (!empty($activeAvail) && $activeAvail !== 'available'): ?>
        <input type="hidden" name="availability" value="<?= esc($activeAvail) ?>">
        <?php endif; ?>
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
    <div class="nav-actions">
        <a href="<?= base_url('login') ?>" class="nav-btn nav-btn-outline"><i class="bi bi-person me-1"></i>Sign In</a>
        <a href="<?= base_url('register') ?>" class="nav-btn nav-btn-solid"><i class="bi bi-person-plus me-1"></i>Register</a>
    </div>
</nav>

<!-- ── Sub-nav ── -->
<div class="sub-nav">
    <a href="<?= base_url('login') ?>"><i class="bi bi-person-fill"></i> Customer Portal</a>
    <span class="sep">|</span>
    <a href="<?= base_url('owner/login') ?>"><i class="bi bi-shop"></i> Owner Portal</a>
    <span class="sep">|</span>
    <a href="<?= base_url('admin/login') ?>"><i class="bi bi-shield-lock-fill"></i> Admin Portal</a>
    <span class="sep">|</span>
    <a href="<?= base_url('register') ?>"><i class="bi bi-person-plus"></i> Create Account</a>
</div>

<!-- ── Hero ── -->
<div class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <h1>Islands Getaway Travel and Tours </h1>
            <p>Browse available rental items &mdash; sign in to start renting</p>   
        </div>
    </div>
</div>

<!-- ── Category Tabs ── -->
<div class="cat-bar">
    <div class="cat-inner">
        <?php
            $catBase = array_filter([
                'q'            => $searchQ ?? '',
                'sort'         => $activeSort ?? 'newest',
                'availability' => $activeAvail ?? 'available',
            ], fn($v) => $v !== '' && $v !== 'newest' && $v !== 'available');
            $allUrl = $catBase ? base_url('portal') . '?' . http_build_query($catBase) : base_url('portal');
        ?>
        <a class="cat-tab <?= !$activeCat ? 'active' : '' ?>" href="<?= $allUrl ?>">
            <div class="cat-icon"><i class="bi bi-grid-fill"></i></div>
            All
        </a>
        <?php foreach ($categories as $cat):
            $catParams = array_merge($catBase, ['category' => $cat['id']]);
            $catUrl = base_url('portal') . '?' . http_build_query($catParams);
        ?>
        <a class="cat-tab <?= $activeCat == $cat['id'] ? 'active' : '' ?>"
           href="<?= $catUrl ?>">
            <div class="cat-icon"><i class="bi bi-tag-fill"></i></div>
            <?= esc($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Filter Bar ── -->
<?php
    // Build a base URL preserving existing params but allowing override
    $qp = array_filter([
        'q'            => $searchQ ?? '',
        'category'     => $activeCat ?? '',
        'sort'         => $activeSort ?? 'newest',
        'availability' => $activeAvail ?? 'available',
    ], fn($v) => $v !== '' && $v !== null);
    $sortLabels = [
        'newest'     => 'Newest',
        'name_asc'   => 'Name A–Z',
        'name_desc'  => 'Name Z–A',
        'price_asc'  => 'Price: Low–High',
        'price_desc' => 'Price: High–Low',
    ];
    $portalUrl = function(array $override = [], array $base = []) use (&$portalUrl): string {
        $params = array_merge($base, $override);
        $params = array_filter($params, fn($v) => $v !== '' && $v !== 'newest' && $v !== 'available');
        return base_url('portal') . ($params ? '?' . http_build_query($params) : '');
    };
?>
<div class="filter-bar">
    <div class="filter-inner">
        <span class="filter-label">Show:</span>
        <div class="avail-toggle">
            <a href="<?= $portalUrl(['availability' => 'available'], $qp) ?>"
               class="<?= ($activeAvail ?? 'available') === 'available' ? 'active' : '' ?>">
               Available Only
            </a>
            <a href="<?= $portalUrl(['availability' => 'all'], $qp) ?>"
               class="<?= ($activeAvail ?? '') === 'all' ? 'active' : '' ?>">
               All Items
            </a>
        </div>
        <div class="filter-sep"></div>
        <span class="filter-label">Sort:</span>
        <select class="sort-select" onchange="location.href=this.value">
            <?php foreach ($sortLabels as $val => $label):
                $opt = $portalUrl(['sort' => $val], $qp);
            ?>
            <option value="<?= $opt ?>" <?= ($activeSort ?? 'newest') === $val ? 'selected' : '' ?>>
                <?= $label ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($searchQ)): ?>
        <div class="filter-sep"></div>
        <div class="active-filters">
            <span class="filter-chip">
                <i class="bi bi-search"></i> "<?= esc($searchQ) ?>"
                <a href="<?= $portalUrl(['q' => ''], $qp) ?>" title="Clear search"><i class="bi bi-x"></i></a>
            </span>
        </div>
        <?php endif; ?>
        <?php if (!empty($activeCat)): ?>
            <?php foreach ($categories as $cat): if ($cat['id'] == $activeCat): ?>
            <div class="active-filters">
                <span class="filter-chip">
                    <i class="bi bi-tag"></i> <?= esc($cat['name']) ?>
                    <a href="<?= $portalUrl(['category' => ''], $qp) ?>" title="Clear category"><i class="bi bi-x"></i></a>
                </span>
            </div>
            <?php endif; endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── Recommended section removed ── -->

<!-- ── Item Grid ── -->
<div class="page-wrap">
    <div class="section-head">
        <div class="section-title">
            <span class="accent"></span>
            <?php if ($activeCat): ?>
                <?php foreach ($categories as $cat): if ($cat['id'] == $activeCat): ?>
                    <?= esc($cat['name']) ?> Items
                <?php endif; endforeach; ?>
            <?php else: ?>
                Available Rental Items
            <?php endif; ?>
        </div>
        <span style="font-size:0.75rem;color:#9098b8;">
            <?= count($items) ?> item<?= count($items) != 1 ? 's' : '' ?> found
        </span>
    </div>

    <?php if (!empty($items)): ?>
    <div class="item-grid">
        <?php foreach ($items as $item): ?>
        <?php $available = (int)$item['quantity'] > 0; ?>
        <?php
            $displayPrice = (float) ($item['price'] ?? 0);
            $displayUnit  = '/day';

            if ($displayPrice <= 0) {
                $rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true);
                $rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true);

                if (is_array($rateOpt1) && (float) ($rateOpt1['price'] ?? 0) > 0) {
                    $displayPrice = (float) $rateOpt1['price'];
                    $displayUnit  = !empty($rateOpt1['hours']) ? '/' . (int) $rateOpt1['hours'] . 'hrs' : '';
                } elseif (is_array($rateOpt2) && (float) ($rateOpt2['price'] ?? 0) > 0) {
                    $displayPrice = (float) $rateOpt2['price'];
                    $displayUnit  = !empty($rateOpt2['hours']) ? '/' . (int) $rateOpt2['hours'] . 'hrs' : '';
                }
            }
        ?>
        <a class="item-card" href="<?= base_url('login') ?>" title="Sign in to rent <?= esc($item['name']) ?>">
            <div class="item-img-wrap">
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= base_url('uploads/items/' . esc($item['image'])) ?>"
                         alt="<?= esc($item['name']) ?>" loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div class="no-img-placeholder" style="display:none"><i class="bi bi-image"></i></div>
                <?php else: ?>
                    <div class="no-img-placeholder"><i class="bi bi-image"></i></div>
                <?php endif; ?>
                <span class="item-badge <?= $available ? '' : 'out' ?>">
                    <?= $available ? 'Available' : 'Out of Stock' ?>
                </span>
            </div>
            <div class="item-info">
                <div class="item-name"><?= esc($item['name']) ?></div>
                <?php if (!empty($item['category_name'])): ?>
                <div class="item-category"><i class="bi bi-tag me-1"></i><?= esc($item['category_name']) ?></div>
                <?php endif; ?>
                <div class="item-price">
                    <?php if ($displayPrice > 0): ?>
                        &#8369;<?= number_format($displayPrice, 2) ?>
                        <?php if ($displayUnit !== ''): ?>
                            <span class="unit"><?= esc($displayUnit) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        Ask for price
                    <?php endif; ?>
                </div>
                <div class="item-qty">
                    <i class="bi bi-box-seam me-1"></i><?= (int)$item['quantity'] ?> unit<?= (int)$item['quantity'] != 1 ? 's' : '' ?> left
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-box-seam"></i>
        <p>No rental items available right now. Check back soon!</p>
    </div>
    <?php endif; ?>
</div>

<!-- ── How It Works ── -->
<section class="s-wrap s-wrap-white">
    <div class="s-inner">
        <div class="s-head">
            <span class="s-tag">Simple Process</span>
            <h2>How It Works</h2>
            <p>Rent anything in four easy steps</p>
        </div>
        <div class="hiw-grid">
            <div class="hiw-step">
                <div class="hiw-num">1</div>
                <h4>Create Account</h4>
                <p>Sign up for free as a Customer or register as a Rental Owner in minutes.</p>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">2</div>
                <h4>Browse Items</h4>
                <p>Explore rental items across categories. Filter by type, price, or availability.</p>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">3</div>
                <h4>Book &amp; Pay</h4>
                <p>Select your rental period, confirm the booking, and pay securely via wallet or gateway.</p>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">4</div>
                <h4>Pick Up &amp; Return</h4>
                <p>Collect your item on the agreed date and return it on time to avoid overstay charges.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Features ── -->
<section class="s-wrap">
    <div class="s-inner">
        <div class="s-head">
            <span class="s-tag">Why Islands Getaway Travel and Tours</span>
            <h2>Everything You Need</h2>
            <p>Built for customers, owners, and administrators alike</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon fi-blue"><i class="bi bi-wallet2"></i></div>
                <div class="feature-text">
                    <h4>Wallet Payments</h4>
                    <p>Top up your in-app wallet and pay for rentals instantly — no cash handling needed.</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-green"><i class="bi bi-bell-fill"></i></div>
                <div class="feature-text">
                    <h4>Real-Time Notifications</h4>
                    <p>Get instant alerts for booking confirmations, due dates, and payment receipts.</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-red"><i class="bi bi-shield-lock-fill"></i></div>
                <div class="feature-text">
                    <h4>Role-Based Access</h4>
                    <p>Separate portals for Customers, Owners, and Admins ensure secure, focused workflows.</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-purple"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="feature-text">
                    <h4>Owner Reports</h4>
                    <p>Rental owners get detailed reports on inventory performance, earnings, and occupancy rates.</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-amber"><i class="bi bi-star-fill"></i></div>
                <div class="feature-text">
                    <h4>Reviews &amp; Ratings</h4>
                    <p>Customers can leave reviews after each rental, helping others make informed decisions.</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-teal"><i class="bi bi-phone"></i></div>
                <div class="feature-text">
                    <h4>Mobile Friendly</h4>
                    <p>Fully responsive design works seamlessly on any device — phone, tablet, or desktop.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ── CTA Banner ── -->
<div class="cta-wrap">
    <div class="cta-inner">
        <div class="cta-text">
            <h2>Ready to Start Renting?</h2>
            <p>Join hundreds of customers and owners already using Islands Getaway Travel and Tours today.</p>
        </div>
        <div class="cta-btns">
            <a href="<?= base_url('register') ?>" class="cta-btn-white">
                <i class="bi bi-person-plus me-1"></i> Create Account
            </a>
            <a href="<?= base_url('login') ?>" class="cta-btn-ghost">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        </div>
    </div>
</div>

<!-- ── Footer ── -->
<footer class="snd-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="<?= base_url('img/logo.png') ?>" alt="IGTT" onerror="this.style.display='none'">
            <h3>Islands Getaway Travel and Tours</h3>
            <p>Your all-in-one platform for rental management. Browse, book, and manage rentals with ease — powered by IGTT Services.</p>
            <div class="footer-social">
                <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" title="Email"><i class="bi bi-envelope-fill"></i></a>
                <a href="#" title="Phone"><i class="bi bi-telephone-fill"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Portals</h4>
            <ul>
                <li><a href="<?= base_url('login') ?>"><i class="bi bi-chevron-right me-1"></i>Customer Sign In</a></li>
                <li><a href="<?= base_url('register') ?>"><i class="bi bi-chevron-right me-1"></i>Create Account</a></li>
                <li><a href="<?= base_url('owner/login') ?>"><i class="bi bi-chevron-right me-1"></i>Owner Sign In</a></li>
                <li><a href="<?= base_url('owner/register') ?>"><i class="bi bi-chevron-right me-1"></i>Register as Owner</a></li>
                <li><a href="<?= base_url('admin/login') ?>"><i class="bi bi-chevron-right me-1"></i>Admin Portal</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Features</h4>
            <ul>
                <li><a href="#"><i class="bi bi-chevron-right me-1"></i>Wallet Payments</a></li>
                <li><a href="#"><i class="bi bi-chevron-right me-1"></i>Real-Time Tracking</a></li>
                <li><a href="#"><i class="bi bi-chevron-right me-1"></i>Owner Reports</a></li>
                <li><a href="#"><i class="bi bi-chevron-right me-1"></i>Notifications</a></li>
                <li><a href="#"><i class="bi bi-chevron-right me-1"></i>Reviews &amp; Ratings</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <ul>
                <li><a href="#"><i class="bi bi-geo-alt me-1"></i>Brgy, Don Ruben, San Jose, Dinagat Islands, PH</a></li>
                <li><a href="#"><i class="bi bi-envelope me-1"></i>islandgetawaytravelandtour@gmail.com</a></li>
                <li><a href="#"><i class="bi bi-telephone me-1"></i>+63 9381598187</a></li>
                <li><a href="#"><i class="bi bi-clock me-1"></i>Mon–Sun, 7AM–8PM</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> IGTT Services &mdash; We Fix, Print, Build &amp; Code!</span>
        <div class="badge-pills">
            <span class="badge-pill"><i class="bi bi-shield-check me-1"></i>Secure</span>
            <span class="badge-pill"><i class="bi bi-phone me-1"></i>Mobile Ready</span>
            <span class="badge-pill">CodeIgniter 4</span>
        </div>
    </div>
</footer>

</body>
</html>
