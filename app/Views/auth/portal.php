<?php
// ── Shared view helpers ──────────────────────────────────────────
if (!function_exists('portal_stars')) {
    /** Render a 5-star rating as bootstrap icons. */
    function portal_stars(float $avg): string
    {
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($avg >= $i - 0.25) {
                $out .= '<i class="bi bi-star-fill"></i>';
            } elseif ($avg >= $i - 0.75) {
                $out .= '<i class="bi bi-star-half"></i>';
            } else {
                $out .= '<i class="bi bi-star"></i>';
            }
        }
        return $out;
    }
}

/** Resolve display price + unit (flat price column, else JSON rate options). */
$displayPriceOf = static function (array $item): array {
    $price = (float) ($item['price'] ?? 0);
    $unit  = '/day';
    if ($price <= 0) {
        foreach (['rate_option_1', 'rate_option_2'] as $key) {
            $opt = json_decode($item[$key] ?? '{}', true);
            if (is_array($opt) && (float) ($opt['price'] ?? 0) > 0) {
                $price = (float) $opt['price'];
                $unit  = !empty($opt['hours']) ? '/' . (int) $opt['hours'] . 'hrs' : '';
                break;
            }
        }
    }
    return [$price, $unit];
};

$ratings    = $ratings    ?? [];
$rentCounts = $rentCounts ?? [];
$isFiltered = !empty($searchQ) || !empty($activeCat);

// Hero collage: up to 3 real item photos
$heroPhotos = [];
foreach (array_merge($recommendedItems ?? [], $items) as $hi) {
    if (!empty($hi['image']) && !in_array($hi['image'], array_column($heroPhotos, 'image'), true)) {
        $heroPhotos[] = $hi;
        if (count($heroPhotos) === 3) break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IGTT Rentals — Browse &amp; Rent Equipment</title>
    <meta name="description" content="Browse rental equipment from Islands Getaway Travel and Tours. Compare prices, check availability, and book online.">
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #9c0b09;
            --primary-dark: #7a0807;
            --primary-deeper: #5e0605;
            --primary-tint: #fdf1f1;
            --white: #ffffff;
            --page-bg: #f4f4f6;
            --line: #e7e7ec;
            --ink: #1d1d22;
            --muted: #5c5c66;
            --star: #f6a723;
            --ok: #1b7c3d;
            --radius: 12px;
            --z-sticky: 100;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { background: var(--page-bg); font-family: 'Outfit', 'Segoe UI', sans-serif; color: var(--ink); min-height: 100vh; }
        img { max-width: 100%; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 16px; }

        /* Motion never gates visibility: transform only, content readable at every frame */
        @keyframes riseIn { from { transform: translateY(16px); } to { transform: translateY(0); } }
        .rise { animation: riseIn .55s cubic-bezier(.22,1,.36,1) both; }
        .rise-2 { animation-delay: .12s; }
        .rise-3 { animation-delay: .24s; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        /* ── Utility strip ── */
        .util-strip { background: var(--primary-deeper); color: rgba(255,255,255,.85); font-size: .74rem; }
        .util-inner { max-width: 1200px; margin: 0 auto; padding: 0 16px; height: 34px; display: flex; align-items: center; gap: 16px; }
        .util-inner a { color: rgba(255,255,255,.85); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .util-inner a:hover { color: var(--white); }
        .util-right { margin-left: auto; display: flex; gap: 16px; }

        /* ── Navbar (sticky) ── */
        .shop-header { position: sticky; top: 0; z-index: var(--z-sticky); background: var(--primary); box-shadow: 0 3px 14px rgba(94,6,5,.35); }
        .shop-nav { max-width: 1200px; margin: 0 auto; padding: 12px 16px; display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .shop-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .shop-brand img { width: 44px; height: 44px; object-fit: contain; }
        .shop-brand b { color: var(--white); font-size: 1.2rem; font-weight: 800; letter-spacing: -.3px; white-space: nowrap; }
        .shop-brand small { display: block; color: rgba(255,255,255,.75); font-size: .62rem; font-weight: 600; letter-spacing: .4px; }
        .shop-search { flex: 1 1 300px; display: flex; background: var(--white); border-radius: 8px; overflow: hidden; min-width: 0; }
        .shop-search input { flex: 1; min-width: 0; border: 0; outline: 0; height: 42px; padding: 0 14px; font: inherit; font-size: .9rem; color: var(--ink); }
        .shop-search input::placeholder { color: #757580; }
        .shop-search button { border: 0; cursor: pointer; background: var(--primary-dark); color: var(--white); padding: 0 22px; font-size: 1.05rem; transition: background .15s; }
        .shop-search button:hover { background: var(--primary-deeper); }
        .shop-actions { display: flex; gap: 10px; margin-left: auto; }
        .btn-nav { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; font-size: .84rem; font-weight: 700; text-decoration: none; white-space: nowrap; transition: transform .15s, background .15s, color .15s; }
        .btn-nav:active { transform: scale(.97); }
        .btn-nav.ghost { color: var(--white); border: 2px solid rgba(255,255,255,.85); }
        .btn-nav.ghost:hover { background: var(--white); color: var(--primary); }
        .btn-nav.solid { background: var(--white); color: var(--primary); }
        .btn-nav.solid:hover { background: #ffe9e9; }

        /* ── Category rail ── */
        .cat-rail { background: var(--white); border-bottom: 1px solid var(--line); overflow-x: auto; scrollbar-width: none; }
        .cat-rail::-webkit-scrollbar { display: none; }
        .cat-rail-inner { max-width: 1200px; margin: 0 auto; padding: 10px 16px; display: flex; gap: 6px; }
        .cat-chip { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 999px; border: 1.5px solid var(--line); background: var(--white); color: var(--ink); font-size: .8rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: border-color .15s, background .15s, color .15s; }
        .cat-chip:hover { border-color: var(--primary); color: var(--primary); }
        .cat-chip.active { background: var(--primary); border-color: var(--primary); color: var(--white); }

        /* ── Hero banner ── */
        .hero-zone { padding: 20px 0 8px; }
        .hero-banner { background: linear-gradient(120deg, var(--primary) 30%, var(--primary-dark)); border-radius: 16px; padding: 34px 36px; display: flex; align-items: center; gap: 28px; overflow: hidden; position: relative; }
        .hero-copy { flex: 1 1 340px; position: relative; }
        .hero-copy h1 { color: var(--white); font-size: clamp(1.5rem, 1.1rem + 2vw, 2.3rem); font-weight: 800; line-height: 1.15; letter-spacing: -.02em; text-wrap: balance; margin-bottom: 10px; }
        .hero-copy p { color: rgba(255,255,255,.88); font-size: .95rem; max-width: 46ch; margin-bottom: 18px; }
        .hero-ctas { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-hero { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; border-radius: 9px; font-weight: 700; font-size: .88rem; text-decoration: none; transition: transform .15s; }
        .btn-hero:hover { transform: translateY(-2px); }
        .btn-hero.light { background: var(--white); color: var(--primary); }
        .btn-hero.line { border: 2px solid rgba(255,255,255,.9); color: var(--white); }
        .hero-collage { display: flex; gap: 12px; flex: 0 1 380px; }
        .hero-collage a { display: block; border-radius: 12px; overflow: hidden; background: rgba(255,255,255,.12); flex: 1; aspect-ratio: 3/4; box-shadow: 0 10px 30px rgba(0,0,0,.28); }
        .hero-collage a:nth-child(2) { transform: translateY(14px); }
        .hero-collage img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s; }
        .hero-collage a:hover img { transform: scale(1.06); }

        /* ── Trust strip ── */
        .trust-strip { padding: 14px 0 4px; }
        .trust-inner { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
        .trust-item { display: flex; align-items: center; gap: 10px; font-size: .8rem; font-weight: 600; color: var(--ink); }
        .trust-item i { color: var(--primary); font-size: 1.15rem; }
        .trust-item small { display: block; font-weight: 500; color: var(--muted); font-size: .72rem; }

        /* ── Sections ── */
        section { padding: 26px 0 6px; }
        .sec-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .sec-head h2 { font-size: 1.25rem; font-weight: 800; letter-spacing: -.01em; }
        .sec-head .count { font-size: .8rem; color: var(--muted); font-weight: 600; }

        /* ── Filter bar ── */
        .filter-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 10px 14px; margin-bottom: 16px; }
        .filter-bar .lbl { font-size: .74rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
        .avail-toggle { display: flex; border: 1.5px solid var(--line); border-radius: 8px; overflow: hidden; }
        .avail-toggle a { padding: 7px 14px; font-size: .78rem; font-weight: 700; color: var(--ink); text-decoration: none; background: var(--white); white-space: nowrap; transition: background .15s; }
        .avail-toggle a.active { background: var(--primary); color: var(--white); }
        .avail-toggle a:not(.active):hover { background: var(--primary-tint); }
        .sort-select { border: 1.5px solid var(--line); border-radius: 8px; padding: 7px 10px; font: inherit; font-size: .78rem; font-weight: 700; color: var(--ink); background: var(--white); cursor: pointer; }
        .sort-select:focus { outline: 2px solid var(--primary); outline-offset: 1px; }
        .chip { background: var(--primary-tint); color: var(--primary); border-radius: 999px; padding: 5px 12px; font-size: .74rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .chip a { color: var(--primary); text-decoration: none; line-height: 1; font-size: .85rem; }

        /* ── Product cards ── */
        .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }
        .prod-card { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; text-decoration: none; color: inherit; display: flex; flex-direction: column; transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s; }
        .prod-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(29,29,34,.12); border-color: #d9d9e0; }
        .prod-img { aspect-ratio: 1/1; background: #fafafa; position: relative; overflow: hidden; }
        .prod-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
        .prod-card:hover .prod-img img { transform: scale(1.05); }
        .prod-img .noimg { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #c9c9d2; font-size: 2.4rem; }
        .flag { position: absolute; top: 8px; left: 8px; font-size: .62rem; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 4px 9px; border-radius: 6px; color: var(--white); background: var(--ok); }
        .flag.out { background: #6e6e78; }
        .prod-body { padding: 12px 12px 14px; display: flex; flex-direction: column; gap: 5px; flex: 1; }
        .prod-name { font-size: .86rem; font-weight: 700; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.3em; }
        .prod-cat { font-size: .7rem; color: var(--muted); font-weight: 600; }
        .prod-rate { display: flex; align-items: center; gap: 6px; font-size: .72rem; color: var(--muted); }
        .prod-rate .stars { color: var(--star); font-size: .72rem; letter-spacing: 1px; }
        .prod-price { margin-top: auto; padding-top: 4px; font-size: 1.05rem; font-weight: 800; color: var(--primary); }
        .prod-price .unit { font-size: .7rem; font-weight: 600; color: var(--muted); }
        .prod-meta { display: flex; justify-content: space-between; font-size: .7rem; color: var(--muted); font-weight: 600; }
        .empty-state { text-align: center; padding: 70px 20px; color: var(--muted); background: var(--white); border: 1px dashed var(--line); border-radius: var(--radius); }
        .empty-state i { font-size: 3rem; display: block; margin-bottom: 12px; color: #c9c9d2; }

        /* ── Featured rail ── */
        .feat-rail { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(200px, 230px); gap: 14px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: thin; }

        /* ── Reviews rail ── */
        .rev-rail { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(250px, 300px); gap: 14px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: thin; }
        .rev-card { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); padding: 16px; display: flex; flex-direction: column; gap: 8px; }
        .rev-top { display: flex; align-items: center; gap: 10px; }
        .rev-ava { width: 38px; height: 38px; border-radius: 50%; background: var(--primary-tint); color: var(--primary); font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rev-name { font-size: .82rem; font-weight: 700; }
        .rev-item { font-size: .7rem; color: var(--muted); font-weight: 600; }
        .rev-stars { color: var(--star); font-size: .75rem; letter-spacing: 1.5px; }
        .rev-text { font-size: .8rem; color: #3c3c44; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        /* ── How it works ── */
        .hiw { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0; background: var(--white); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
        .hiw-step { padding: 22px 20px; display: flex; gap: 14px; align-items: flex-start; }
        .hiw-step + .hiw-step { border-left: 1px solid var(--line); }
        .hiw-num { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: var(--white); font-weight: 800; font-size: .95rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .hiw-step h3 { font-size: .88rem; font-weight: 700; margin-bottom: 4px; }
        .hiw-step p { font-size: .76rem; color: var(--muted); line-height: 1.5; }

        /* ── CTA ── */
        .cta { margin: 34px 0 40px; background: linear-gradient(120deg, var(--primary), var(--primary-dark)); border-radius: 16px; padding: 38px 40px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .cta h2 { color: var(--white); font-size: 1.45rem; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
        .cta p { color: rgba(255,255,255,.85); font-size: .9rem; max-width: 48ch; }
        .cta-btns { display: flex; gap: 12px; flex-wrap: wrap; }

        /* ── Footer ── */
        .shop-footer { background: #191a1f; color: rgba(255,255,255,.62); padding: 40px 0 0; }
        .foot-grid { max-width: 1200px; margin: 0 auto; padding: 0 16px 28px; display: grid; grid-template-columns: 2fr 1fr 1fr 1.3fr; gap: 30px; border-bottom: 1px solid rgba(255,255,255,.1); }
        .foot-grid h3 { color: var(--white); font-size: .95rem; font-weight: 800; margin-bottom: 8px; }
        .foot-grid h4 { color: var(--white); font-size: .74rem; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; margin-bottom: 12px; }
        .foot-grid p, .foot-grid li, .foot-grid a { font-size: .8rem; line-height: 1.7; color: rgba(255,255,255,.62); }
        .foot-grid ul { list-style: none; }
        .foot-grid a { text-decoration: none; transition: color .15s; }
        .foot-grid a:hover { color: var(--white); }
        .foot-brand img { width: 52px; margin-bottom: 10px; }
        .foot-social { display: flex; gap: 10px; margin-top: 14px; }
        .foot-social a { width: 38px; height: 38px; border-radius: 9px; background: var(--primary); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: transform .15s; }
        .foot-social a:hover { transform: translateY(-2px); color: var(--white); }
        .foot-bottom { max-width: 1200px; margin: 0 auto; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: .74rem; color: rgba(255,255,255,.4); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .hero-collage { display: none; }
            .foot-grid { grid-template-columns: 1fr 1fr; }
            .hiw { grid-template-columns: 1fr 1fr; }
            .hiw-step + .hiw-step { border-left: 0; }
            .hiw-step:nth-child(even) { border-left: 1px solid var(--line); }
            .hiw-step:nth-child(n+3) { border-top: 1px solid var(--line); }
        }
        @media (max-width: 640px) {
            .util-right { display: none; }
            .shop-search { order: 3; flex-basis: 100%; }
            .shop-actions { margin-left: auto; }
            .btn-nav { padding: 9px 13px; font-size: .78rem; }
            .prod-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .hero-banner { padding: 26px 22px; }
            .hiw { grid-template-columns: 1fr; }
            .hiw-step { border-left: 0 !important; }
            .hiw-step + .hiw-step { border-top: 1px solid var(--line); }
            .foot-grid { grid-template-columns: 1fr; }
            .cta { padding: 28px 24px; }
        }
    </style>
</head>
<body>

<!-- ── Utility strip ── -->
<div class="util-strip">
    <div class="util-inner">
        <a href="<?= base_url('login') ?>"><i class="bi bi-person-fill"></i> Customer</a>
        <a href="<?= base_url('owner/login') ?>"><i class="bi bi-shop"></i> Owner</a>
        <a href="<?= base_url('admin/login') ?>"><i class="bi bi-shield-lock-fill"></i> Admin</a>
        <div class="util-right">
            <a href="tel:+639381598187"><i class="bi bi-telephone-fill"></i> +63 938 159 8187</a>
            <span><i class="bi bi-clock"></i> Mon–Sun, 7AM–8PM</span>
        </div>
    </div>
</div>

<!-- ── Header / search ── -->
<header class="shop-header">
    <nav class="shop-nav">
        <a class="shop-brand" href="<?= base_url('portal') ?>">
            <img src="<?= base_url('img/logo.png') ?>" alt="IGTT logo" onerror="this.style.display='none'">
            <span>
                <b>IGTT Rentals</b>
                <small>Islands Getaway Travel &amp; Tours</small>
            </span>
        </a>
        <form class="shop-search" method="get" action="<?= base_url('portal') ?>" role="search">
            <input type="text" name="q" placeholder="Search for equipment, gear, vehicles…" value="<?= esc($searchQ ?? '') ?>" aria-label="Search rental items">
            <?php if (!empty($activeCat)): ?><input type="hidden" name="category" value="<?= esc($activeCat) ?>"><?php endif; ?>
            <?php if (!empty($activeSort) && $activeSort !== 'newest'): ?><input type="hidden" name="sort" value="<?= esc($activeSort) ?>"><?php endif; ?>
            <?php if (!empty($activeAvail) && $activeAvail !== 'available'): ?><input type="hidden" name="availability" value="<?= esc($activeAvail) ?>"><?php endif; ?>
            <button type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
        </form>
        <div class="shop-actions">
            <a href="<?= base_url('login') ?>" class="btn-nav ghost"><i class="bi bi-person"></i>Sign In</a>
            <a href="<?= base_url('register') ?>" class="btn-nav solid"><i class="bi bi-person-plus"></i>Register</a>
        </div>
    </nav>
</header>

<!-- ── Category rail ── -->
<?php
    $catBase = array_filter([
        'q'            => $searchQ ?? '',
        'sort'         => $activeSort ?? 'newest',
        'availability' => $activeAvail ?? 'available',
    ], fn ($v) => $v !== '' && $v !== 'newest' && $v !== 'available');
    $allUrl = $catBase ? base_url('portal') . '?' . http_build_query($catBase) : base_url('portal');
?>
<div class="cat-rail">
    <div class="cat-rail-inner">
        <a class="cat-chip <?= !$activeCat ? 'active' : '' ?>" href="<?= $allUrl ?>"><i class="bi bi-grid-fill"></i>All Items</a>
        <?php foreach ($categories as $cat):
            $catUrl = base_url('portal') . '?' . http_build_query(array_merge($catBase, ['category' => $cat['id']]));
        ?>
        <a class="cat-chip <?= $activeCat == $cat['id'] ? 'active' : '' ?>" href="<?= $catUrl ?>">
            <i class="bi bi-tag"></i><?= esc($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<main class="wrap">

    <?php if (!$isFiltered): ?>
    <!-- ── Hero banner ── -->
    <div class="hero-zone">
        <div class="hero-banner rise">
            <div class="hero-copy">
                <h1>Rent the gear you need, right here on the island.</h1>
                <p>Browse equipment from local owners in Dinagat Islands — compare prices, check availability, and book online with your IGTT wallet.</p>
                <div class="hero-ctas">
                    <a class="btn-hero light" href="#browse"><i class="bi bi-bag"></i>Start Browsing</a>
                    <a class="btn-hero line" href="<?= base_url('register') ?>"><i class="bi bi-person-plus"></i>Create Free Account</a>
                </div>
            </div>
            <?php if ($heroPhotos !== []): ?>
            <div class="hero-collage" aria-hidden="true">
                <?php foreach ($heroPhotos as $hp): ?>
                <a href="<?= base_url('login') ?>" tabindex="-1">
                    <img src="<?= base_url('uploads/items/' . esc($hp['image'])) ?>" alt="" loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Trust strip ── -->
    <div class="trust-strip rise rise-2">
        <div class="trust-inner">
            <div class="trust-item"><i class="bi bi-wallet2"></i><span>Wallet Payments<small>Top up once, pay instantly</small></span></div>
            <div class="trust-item"><i class="bi bi-patch-check-fill"></i><span>Verified Owners<small>ID-checked rental providers</small></span></div>
            <div class="trust-item"><i class="bi bi-star-fill"></i><span>Real Reviews<small>Rated by actual renters</small></span></div>
            <div class="trust-item"><i class="bi bi-bell-fill"></i><span>Booking Alerts<small>Reminders before due dates</small></span></div>
        </div>
    </div>

    <?php if (!empty($recommendedItems)): ?>
    <!-- ── Featured rail ── -->
    <section class="rise rise-3">
        <div class="sec-head">
            <h2><i class="bi bi-lightning-charge-fill" style="color:var(--star)"></i> Just Arrived</h2>
            <span class="count">Newest available items</span>
        </div>
        <div class="feat-rail">
            <?php foreach ($recommendedItems as $item):
                [$price, $unit] = $displayPriceOf($item);
                $r = $ratings[(int) $item['id']] ?? null;
            ?>
            <a class="prod-card" href="<?= base_url('login') ?>" title="Sign in to rent <?= esc($item['name']) ?>">
                <div class="prod-img">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= base_url('uploads/items/' . esc($item['image'])) ?>" alt="<?= esc($item['name']) ?>" loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="noimg" style="display:none"><i class="bi bi-image"></i></div>
                    <?php else: ?>
                        <div class="noimg"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                    <span class="flag">New</span>
                </div>
                <div class="prod-body">
                    <div class="prod-name"><?= esc($item['name']) ?></div>
                    <?php if ($r): ?>
                    <div class="prod-rate"><span class="stars"><?= portal_stars($r['avg']) ?></span> <?= $r['avg'] ?> (<?= $r['total'] ?>)</div>
                    <?php endif; ?>
                    <div class="prod-price">
                        <?php if ($price > 0): ?>₱<?= number_format($price, 2) ?> <span class="unit"><?= esc($unit) ?></span>
                        <?php else: ?>Ask for price<?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php endif; /* !$isFiltered */ ?>

    <!-- ── Main grid ── -->
    <section id="browse">
        <div class="sec-head">
            <?php
                $activeCatName = null;
                if ($activeCat) {
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $activeCat) { $activeCatName = $cat['name']; break; }
                    }
                }
            ?>
            <h2>
                <?php if ($activeCatName !== null): ?>
                    <?= esc($activeCatName) ?>
                <?php elseif (!empty($searchQ)): ?>
                    Results for &ldquo;<?= esc($searchQ) ?>&rdquo;
                <?php else: ?>
                    All Rental Items
                <?php endif; ?>
            </h2>
            <span class="count"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></span>
        </div>

        <?php
            $qp = array_filter([
                'q'            => $searchQ ?? '',
                'category'     => $activeCat ?? '',
                'sort'         => $activeSort ?? 'newest',
                'availability' => $activeAvail ?? 'available',
            ], fn ($v) => $v !== '' && $v !== null);
            $sortLabels = [
                'newest'     => 'Newest',
                'name_asc'   => 'Name A–Z',
                'name_desc'  => 'Name Z–A',
                'price_asc'  => 'Price: Low–High',
                'price_desc' => 'Price: High–Low',
            ];
            $portalUrl = function (array $override, array $base): string {
                $params = array_filter(array_merge($base, $override), fn ($v) => $v !== '' && $v !== 'newest' && $v !== 'available');
                return base_url('portal') . ($params ? '?' . http_build_query($params) : '') . '#browse';
            };
        ?>
        <div class="filter-bar">
            <span class="lbl">Show</span>
            <div class="avail-toggle">
                <a href="<?= $portalUrl(['availability' => 'available'], $qp) ?>" class="<?= ($activeAvail ?? 'available') === 'available' ? 'active' : '' ?>">Available Only</a>
                <a href="<?= $portalUrl(['availability' => 'all'], $qp) ?>" class="<?= ($activeAvail ?? '') === 'all' ? 'active' : '' ?>">All Items</a>
            </div>
            <span class="lbl">Sort</span>
            <select class="sort-select" onchange="location.href=this.value" aria-label="Sort items">
                <?php foreach ($sortLabels as $val => $label): ?>
                <option value="<?= $portalUrl(['sort' => $val], $qp) ?>" <?= ($activeSort ?? 'newest') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($searchQ)): ?>
            <span class="chip"><i class="bi bi-search"></i>&ldquo;<?= esc($searchQ) ?>&rdquo;
                <a href="<?= $portalUrl(['q' => ''], $qp) ?>" title="Clear search"><i class="bi bi-x-lg"></i></a>
            </span>
            <?php endif; ?>
            <?php if (!empty($activeCat)): foreach ($categories as $cat): if ($cat['id'] == $activeCat): ?>
            <span class="chip"><i class="bi bi-tag"></i><?= esc($cat['name']) ?>
                <a href="<?= $portalUrl(['category' => ''], $qp) ?>" title="Clear category"><i class="bi bi-x-lg"></i></a>
            </span>
            <?php endif; endforeach; endif; ?>
        </div>

        <?php if (!empty($items)): ?>
        <div class="prod-grid">
            <?php foreach ($items as $item):
                $available = (int) $item['quantity'] > 0;
                [$price, $unit] = $displayPriceOf($item);
                $r    = $ratings[(int) $item['id']] ?? null;
                $sold = $rentCounts[(int) $item['id']] ?? 0;
            ?>
            <a class="prod-card" href="<?= base_url('login') ?>" title="Sign in to rent <?= esc($item['name']) ?>">
                <div class="prod-img">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= base_url('uploads/items/' . esc($item['image'])) ?>" alt="<?= esc($item['name']) ?>" loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="noimg" style="display:none"><i class="bi bi-image"></i></div>
                    <?php else: ?>
                        <div class="noimg"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                    <span class="flag <?= $available ? '' : 'out' ?>"><?= $available ? 'Available' : 'Out of Stock' ?></span>
                </div>
                <div class="prod-body">
                    <div class="prod-name"><?= esc($item['name']) ?></div>
                    <?php if (!empty($item['category_name'])): ?>
                    <div class="prod-cat"><i class="bi bi-tag"></i> <?= esc($item['category_name']) ?></div>
                    <?php endif; ?>
                    <?php if ($r): ?>
                    <div class="prod-rate"><span class="stars"><?= portal_stars($r['avg']) ?></span> <?= $r['avg'] ?> (<?= $r['total'] ?>)</div>
                    <?php endif; ?>
                    <div class="prod-price">
                        <?php if ($price > 0): ?>₱<?= number_format($price, 2) ?> <span class="unit"><?= esc($unit) ?></span>
                        <?php else: ?>Ask for price<?php endif; ?>
                    </div>
                    <div class="prod-meta">
                        <span><i class="bi bi-box-seam"></i> <?= (int) $item['quantity'] ?> in stock</span>
                        <?php if ($sold > 0): ?><span><?= $sold ?> rented</span><?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <p>No items match your search. Try a different keyword or category.</p>
        </div>
        <?php endif; ?>
    </section>

    <?php if (!$isFiltered && !empty($reviews)): ?>
    <!-- ── Reviews rail ── -->
    <section>
        <div class="sec-head">
            <h2>What renters say</h2>
            <span class="count"><?= count($reviews) ?> recent review<?= count($reviews) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="rev-rail">
            <?php foreach ($reviews as $rev): ?>
            <div class="rev-card">
                <div class="rev-top">
                    <div class="rev-ava"><?= esc(strtoupper(mb_substr($rev['user_name'] ?? '?', 0, 1))) ?></div>
                    <div>
                        <div class="rev-name"><?= esc($rev['user_name'] ?? 'Renter') ?></div>
                        <div class="rev-item">rented <?= esc($rev['item_name'] ?? 'an item') ?></div>
                    </div>
                </div>
                <div class="rev-stars"><?= portal_stars((float) ($rev['rating'] ?? 0)) ?></div>
                <?php if (!empty($rev['comment'])): ?>
                <p class="rev-text"><?= esc($rev['comment']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!$isFiltered): ?>
    <!-- ── How it works ── -->
    <section>
        <div class="sec-head"><h2>How renting works</h2></div>
        <div class="hiw">
            <div class="hiw-step">
                <div class="hiw-num">1</div>
                <div><h3>Create an account</h3><p>Sign up free as a customer, or register as a rental owner.</p></div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">2</div>
                <div><h3>Pick your item</h3><p>Filter by category, price, or availability and choose your dates.</p></div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">3</div>
                <div><h3>Book &amp; pay</h3><p>Confirm the booking and pay securely with your wallet or a gateway.</p></div>
            </div>
            <div class="hiw-step">
                <div class="hiw-num">4</div>
                <div><h3>Pick up &amp; return</h3><p>Collect on the agreed date and return on time to avoid overstay fees.</p></div>
            </div>
        </div>
    </section>

    <!-- ── CTA ── -->
    <div class="cta">
        <div>
            <h2>Ready to start renting?</h2>
            <p>Join customers and owners across Dinagat Islands already booking through IGTT.</p>
        </div>
        <div class="cta-btns">
            <a href="<?= base_url('register') ?>" class="btn-hero light"><i class="bi bi-person-plus"></i>Create Account</a>
            <a href="<?= base_url('login') ?>" class="btn-hero line"><i class="bi bi-box-arrow-in-right"></i>Sign In</a>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- ── Footer ── -->
<footer class="shop-footer">
    <div class="foot-grid">
        <div class="foot-brand">
            <img src="<?= base_url('img/logo.png') ?>" alt="IGTT logo" onerror="this.style.display='none'">
            <h3>Islands Getaway Travel and Tours</h3>
            <p>Your local platform for equipment rentals in Dinagat Islands. Browse, book, and manage rentals with ease — powered by IGTT Services.</p>
            <div class="foot-social">
                <a href="#" title="Facebook" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" title="Instagram" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="mailto:islandgetawaytravelandtour@gmail.com" title="Email" aria-label="Email"><i class="bi bi-envelope-fill"></i></a>
                <a href="tel:+639381598187" title="Phone" aria-label="Phone"><i class="bi bi-telephone-fill"></i></a>
            </div>
        </div>
        <div>
            <h4>Portals</h4>
            <ul>
                <li><a href="<?= base_url('login') ?>">Customer Sign In</a></li>
                <li><a href="<?= base_url('register') ?>">Create Account</a></li>
                <li><a href="<?= base_url('owner/login') ?>">Owner Sign In</a></li>
                <li><a href="<?= base_url('owner/register') ?>">Register as Owner</a></li>
                <li><a href="<?= base_url('admin/login') ?>">Admin Portal</a></li>
            </ul>
        </div>
        <div>
            <h4>Explore</h4>
            <ul>
                <li><a href="#browse">Browse Items</a></li>
                <li><a href="<?= base_url('reviews') ?>">All Reviews</a></li>
                <?php foreach (array_slice($categories, 0, 3) as $cat): ?>
                <li><a href="<?= base_url('portal') . '?category=' . $cat['id'] ?>"><?= esc($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Contact</h4>
            <ul>
                <li><i class="bi bi-geo-alt"></i> Brgy. Don Ruben, San Jose, Dinagat Islands, PH</li>
                <li><a href="mailto:islandgetawaytravelandtour@gmail.com"><i class="bi bi-envelope"></i> islandgetawaytravelandtour@gmail.com</a></li>
                <li><a href="tel:+639381598187"><i class="bi bi-telephone"></i> +63 938 159 8187</a></li>
                <li><i class="bi bi-clock"></i> Mon–Sun, 7AM–8PM</li>
            </ul>
        </div>
    </div>
    <div class="foot-bottom">
        <span>&copy; <?= date('Y') ?> IGTT Services — We Fix, Print, Build &amp; Code!</span>
        <span><i class="bi bi-shield-check"></i> Secure &middot; <i class="bi bi-phone"></i> Mobile Ready</span>
    </div>
</footer>

</body>
</html>
