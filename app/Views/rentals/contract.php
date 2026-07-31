<?php
$rateOpt1 = json_decode($item['rate_option_1'] ?? '{}', true) ?: null;
$rateOpt2 = json_decode($item['rate_option_2'] ?? '{}', true) ?: null;
$bookedRate = $rental['rate_option'] == 2 ? $rateOpt2 : $rateOpt1;

$terms = array_filter(array_map('trim', explode('•', $item['rental_details'] ?? '')));
if (! $terms) {
    $terms = [
        'Use the item responsibly and in accordance with applicable laws.',
        'Do not allow any unauthorized person to operate or use the item.',
        'Return the item on time and in the same condition as received.',
        'The renter is responsible for any damage, loss, or theft during the rental period.',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Agreement #<?= $rental['id'] ?> &mdash; SND Rental System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e9e2e0;
            font-family: 'Segoe UI', sans-serif;
            color: #1c1412;
        }

        .toolbar {
            position: sticky; top: 0; z-index: 10;
            background: linear-gradient(90deg, #5a0808, #9c0b09);
            color: #fff;
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,.15);
        }
        .toolbar .title { font-weight: 600; font-size: .95rem; }
        .toolbar .actions { display: flex; gap: 10px; }
        .toolbar button, .toolbar a {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.4);
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: .85rem;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar button:hover, .toolbar a:hover { background: rgba(255,255,255,.28); }

        .sheet {
            max-width: 780px;
            margin: 28px auto 60px;
            background: #fff;
            padding: 56px 60px;
            box-shadow: 0 10px 40px rgba(0,0,0,.18);
            font-family: Georgia, 'Times New Roman', serif;
            line-height: 1.6;
        }
        .sheet h1 {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: .04em;
            margin: 0 0 4px;
        }
        .sheet .sub {
            text-align: center;
            color: #6b5a56;
            font-size: .82rem;
            margin-bottom: 28px;
            font-family: 'Segoe UI', sans-serif;
        }
        .intro { font-size: .95rem; margin-bottom: 24px; }
        .parties {
            display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
            margin-bottom: 24px;
        }
        .party h3 {
            font-size: .78rem; text-transform: uppercase; letter-spacing: .08em;
            color: #9c0b09; font-family: 'Segoe UI', sans-serif; margin: 0 0 8px;
        }
        .party p { margin: 0 0 3px; font-size: .92rem; }

        h2.sect {
            font-size: 1rem; margin: 26px 0 10px;
            padding-bottom: 6px; border-bottom: 1px solid #d8cac6;
        }
        table.kv { width: 100%; border-collapse: collapse; font-size: .92rem; }
        table.kv td { padding: 4px 0; vertical-align: top; }
        table.kv td:first-child { width: 220px; color: #6b5a56; }

        ul.terms { margin: 0; padding-left: 22px; font-size: .92rem; }
        ul.terms li { margin-bottom: 5px; }

        .id-block { display: flex; gap: 16px; align-items: flex-start; margin-top: 10px; }
        .id-block img {
            width: 130px; height: 82px; object-fit: cover;
            border: 1px solid #d8cac6; border-radius: 4px;
        }
        .id-missing {
            font-size: .85rem; color: #9c6b67; font-style: italic;
            font-family: 'Segoe UI', sans-serif;
        }

        .signatures {
            display: grid; grid-template-columns: 1fr 1fr; gap: 40px;
            margin-top: 56px;
        }
        .sig-line {
            border-top: 1px solid #1c1412;
            padding-top: 6px;
            font-size: .82rem;
            font-family: 'Segoe UI', sans-serif;
        }
        .sig-space { height: 50px; }

        .footnote {
            margin-top: 40px; font-size: .74rem; color: #9c8b87;
            font-family: 'Segoe UI', sans-serif; text-align: center;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; margin: 0; padding: 30px 40px; max-width: none; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <div class="title"><i class="bi bi-file-earmark-text me-1"></i> Rental Agreement #<?= $rental['id'] ?></div>
    <div class="actions">
        <button type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="<?= base_url(role_url_prefix() . '/rentals/' . $rental['id']) ?>"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="sheet">
    <h1>EQUIPMENT RENTAL AGREEMENT</h1>
    <div class="sub">SND Rental System &middot; Agreement #<?= $rental['id'] ?> &middot; Printed <?= date('F j, Y') ?></div>

    <p class="intro">
        This Rental Agreement ("Agreement") is made and entered into between the parties below, covering the
        rental item and terms described in this document.
    </p>

    <div class="parties">
        <div class="party">
            <h3>Rental Owner</h3>
            <p><strong><?= esc($owner['name'] ?? 'N/A') ?></strong></p>
            <p><?= esc(! empty($owner['phone']) ? $owner['phone'] : 'No phone on file') ?></p>
            <p><?= esc($owner['email'] ?? '') ?></p>
        </div>
        <div class="party">
            <h3>Renter</h3>
            <p><strong><?= esc($renter['name'] ?? 'N/A') ?></strong></p>
            <p><?= esc(! empty($renter['phone']) ? $renter['phone'] : 'No phone on file') ?></p>
            <p><?= esc($renter['email'] ?? '') ?></p>
        </div>
    </div>

    <h2 class="sect">1. Item Details</h2>
    <table class="kv">
        <tr><td>Item</td><td><?= esc($item['name'] ?? $rental['item_name']) ?></td></tr>
        <tr><td>Rental Period</td>
            <td><?= date('M j, Y g:i A', strtotime($rental['rent_date'])) ?> &rarr;
                <?= date('M j, Y g:i A', strtotime($rental['return_date'])) ?></td></tr>
        <?php if ($bookedRate): ?>
        <tr><td>Rate</td><td>₱<?= number_format($bookedRate['price'] ?? 0) ?> / <?= esc($bookedRate['hours'] ?? '') ?> hours</td></tr>
        <?php endif; ?>
    </table>

    <h2 class="sect">2. Payment</h2>
    <table class="kv">
        <tr><td>Total Rental Fee</td><td>₱<?= number_format($rental['total_cost']) ?></td></tr>
        <tr><td>Status</td><td><?= esc(ucfirst(str_replace('_', ' ', $rental['status']))) ?></td></tr>
        <?php if (! empty($rental['payment_reference'])): ?>
        <tr><td>Payment Reference</td><td>On file</td></tr>
        <?php endif; ?>
    </table>

    <h2 class="sect">3. Conditions of Use</h2>
    <ul class="terms">
        <?php foreach ($terms as $term): ?>
            <li><?= esc($term) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2 class="sect">4. Renter Identification</h2>
    <?php if (! empty($renter['valid_id'])): ?>
        <div class="id-block">
            <img src="<?= base_url('uploads/valid_ids/' . esc($renter['valid_id'])) ?>" alt="Renter valid ID">
            <div>
                <p style="margin:0;font-size:.88rem;"><strong>ID Type:</strong> <?= esc($renter['valid_id_type'] ?? 'Not specified') ?></p>
                <p style="margin:4px 0 0;font-size:.88rem;"><strong>Name on ID:</strong> <?= esc($renter['id_name'] ?? $renter['name']) ?></p>
            </div>
        </div>
    <?php else: ?>
        <p class="id-missing">No valid ID on file for this renter — verify a physical ID at handoff.</p>
    <?php endif; ?>

    <h2 class="sect">5. Agreement</h2>
    <p class="intro" style="margin-bottom:0;">
        By signing below, the renter acknowledges that they have read, understood, and agreed to all terms and
        conditions stated in this Agreement, including responsibility for damage, loss, or theft of the item
        during the rental period.
    </p>

    <div class="signatures">
        <div>
            <div class="sig-space"></div>
            <div class="sig-line">Renter Signature &nbsp;&middot;&nbsp; Date: ____________</div>
        </div>
        <div>
            <div class="sig-space"></div>
            <div class="sig-line">Owner Representative &nbsp;&middot;&nbsp; Date: ____________</div>
        </div>
    </div>

    <div class="footnote">Generated by SND Rental System &middot; Rental #<?= $rental['id'] ?></div>
</div>

</body>
</html>
