<?php $this->extend('layout/main'); $this->section('content'); ?>

<div class="card-section mb-4">
    <div class="section-title">
        <i class="bi bi-bar-chart-line text-primary"></i> Rental Reports
        <a href="<?= base_url('reports/export/csv?start_date=' . $startDate . '&end_date=' . $endDate) ?>"
           class="btn btn-sm btn-success ms-auto">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
    </div>

    <!-- Date filter -->
    <form class="row g-2 mb-4" method="GET" action="<?= base_url('reports') ?>">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm"
                   value="<?= esc($startDate) ?>" placeholder="From">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm"
                   value="<?= esc($endDate) ?>" placeholder="To">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary btn-sm w-100">
                <i class="bi bi-search"></i> Generate
            </button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-sm w-100">
                <i class="bi bi-arrow-counterclockwise"></i> All Time
            </a>
        </div>
    </form>

    <!-- ── Summary Cards ── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rounded-3 p-3 text-center text-white" style="background:linear-gradient(135deg,#0d2c8a,#2979ff);">
                <div class="small opacity-75">Total Rentals</div>
                <div class="fs-2 fw-bold"><?= count($rentals) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rounded-3 p-3 text-center text-white" style="background:linear-gradient(135deg,#0f5132,#20c997);">
                <div class="small opacity-75">Total Revenue</div>
                <div class="fs-5 fw-bold mt-1">₱<?= number_format($totalRevenue) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rounded-3 p-3 text-center text-white" style="background:linear-gradient(135deg,#7a2c00,#fb8c00);">
                <div class="small opacity-75">Avg. Rental Days</div>
                <div class="fs-2 fw-bold"><?= $avgDuration ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rounded-3 p-3 text-center text-white" style="background:linear-gradient(135deg,#5b2d8e,#ab47bc);">
                <div class="small opacity-75">Overdue</div>
                <div class="fs-2 fw-bold"><?= $statusCounts['overdue'] ?? 0 ?></div>
            </div>
        </div>
    </div>

    <!-- Status breakdown pills -->
    <?php if (!empty($statusCounts)): ?>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="fw-semibold small text-muted me-1 align-self-center">Status breakdown:</span>
        <?php foreach ($statusCounts as $s => $cnt): ?>
            <span class="badge badge-<?= $s ?> px-3 py-2" style="font-size:.82rem;">
                <?= ucfirst(str_replace('_', ' ', $s)) ?>: <?= $cnt ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Monthly Trend Chart ── -->
    <?php if (!empty($monthlyTrend)): ?>
    <div class="border rounded-3 p-3 mb-4">
        <div class="fw-semibold mb-3"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Monthly Revenue Trend</div>
        <canvas id="trendChart" height="90"></canvas>
    </div>
    <?php endif; ?>

    <!-- ── 2-column analytics ── -->
    <div class="row g-4 mb-4">

        <!-- Most Rented Items -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-3">
                    <i class="bi bi-trophy text-warning me-2"></i>Most Rented Items
                </div>
                <?php if (empty($mostRentedItems)): ?>
                    <p class="text-muted small mb-0">No data for this period.</p>
                <?php else: ?>
                    <?php
                        $maxRentals = max(array_column($mostRentedItems, 'rental_count'));
                        $medals = ['🥇','🥈','🥉'];
                    ?>
                    <?php foreach ($mostRentedItems as $i => $item): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">
                                <?= $medals[$i] ?? ($i+1).'. ' ?>
                                <?= esc($item['item_name']) ?>
                                <?php if ($item['category_name']): ?>
                                    <span class="text-muted fw-normal">(<?= esc($item['category_name']) ?>)</span>
                                <?php endif; ?>
                            </span>
                            <span class="small text-muted"><?= $item['rental_count'] ?> rentals · ₱<?= number_format($item['total_revenue']) ?></span>
                        </div>
                        <div class="progress" style="height:7px;border-radius:4px;">
                            <div class="progress-bar" style="width:<?= round($item['rental_count'] / $maxRentals * 100) ?>%;background:linear-gradient(90deg,#0d2c8a,#2979ff);"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-3">
                    <i class="bi bi-people-fill text-success me-2"></i>Top Customers
                </div>
                <?php if (empty($topCustomers)): ?>
                    <p class="text-muted small mb-0">No data for this period.</p>
                <?php else: ?>
                    <?php $maxSpent = max(array_column($topCustomers, 'total_spent') ?: [1]); ?>
                    <?php foreach ($topCustomers as $i => $c): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">
                                <?= ($i+1) ?>. <?= esc($c['user_name']) ?>
                            </span>
                            <span class="small text-muted"><?= $c['rental_count'] ?> rentals · ₱<?= number_format($c['total_spent']) ?></span>
                        </div>
                        <div class="progress" style="height:7px;border-radius:4px;">
                            <div class="progress-bar bg-success" style="width:<?= round($c['total_spent'] / $maxSpent * 100) ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Revenue by Category -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-3">
                    <i class="bi bi-tags-fill text-info me-2"></i>Revenue by Category
                </div>
                <?php if (empty($revenueByCategory)): ?>
                    <p class="text-muted small mb-0">No data for this period.</p>
                <?php else: ?>
                    <?php
                        $grandTotal = array_sum(array_column($revenueByCategory, 'total_revenue')) ?: 1;
                        $catColors  = ['#2979ff','#20c997','#fb8c00','#ab47bc','#f06292','#26c6da','#ffb300'];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Category</th><th class="text-end">Rentals</th><th class="text-end">Revenue</th><th class="text-end">Share</th></tr></thead>
                            <tbody>
                            <?php foreach ($revenueByCategory as $i => $cat): ?>
                                <tr>
                                    <td>
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= $catColors[$i % count($catColors)] ?>;margin-right:6px;"></span>
                                        <?= esc($cat['category_name']) ?>
                                    </td>
                                    <td class="text-end"><?= $cat['rental_count'] ?></td>
                                    <td class="text-end">₱<?= number_format($cat['total_revenue']) ?></td>
                                    <td class="text-end"><?= round($cat['total_revenue'] / $grandTotal * 100, 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Summary Table -->
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold mb-3">
                    <i class="bi bi-pie-chart-fill text-danger me-2"></i>Status Summary
                </div>
                <?php if (empty($statusCounts)): ?>
                    <p class="text-muted small mb-0">No data for this period.</p>
                <?php else: ?>
                    <?php $totalCount = array_sum($statusCounts) ?: 1; ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Status</th><th class="text-end">Count</th><th class="text-end">Share</th></tr></thead>
                            <tbody>
                            <?php foreach ($statusCounts as $s => $cnt): ?>
                                <tr>
                                    <td><span class="badge badge-<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></span></td>
                                    <td class="text-end"><?= $cnt ?></td>
                                    <td class="text-end"><?= round($cnt / $totalCount * 100, 1) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Full Rentals Table ── -->
    <div class="fw-semibold mb-2"><i class="bi bi-table text-secondary me-2"></i>All Rentals in Period</div>
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Item</th>
                    <th>Rent Date</th><th>Return Date</th>
                    <th>Status</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rentals)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No data for selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($rentals as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= esc($r['user_name']) ?></td>
                        <td><?= esc($r['item_name']) ?></td>
                        <td><?= $r['rent_date'] ?></td>
                        <td><?= $r['return_date'] ?></td>
                        <td>
                            <span class="badge badge-<?= $r['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                            </span>
                        </td>
                        <td>₱<?= number_format($r['total_cost']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (! empty($rentals)): ?>
            <tfoot>
                <tr class="table-primary fw-bold">
                    <td colspan="6" class="text-end">Total Revenue</td>
                    <td>₱<?= number_format($totalRevenue) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if (!empty($monthlyTrend)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels   = <?= json_encode(array_column($monthlyTrend, 'month')) ?>;
    const revenue  = <?= json_encode(array_map('floatval', array_column($monthlyTrend, 'revenue'))) ?>;
    const counts   = <?= json_encode(array_map('intval', array_column($monthlyTrend, 'rental_count'))) ?>;

    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Revenue (₱)',
                    data: revenue,
                    backgroundColor: 'rgba(41,121,255,.75)',
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Rentals',
                    data: counts,
                    type: 'line',
                    borderColor: '#20c997',
                    backgroundColor: 'rgba(32,201,151,.15)',
                    pointBackgroundColor: '#20c997',
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label === 'Revenue (₱)'
                            ? `₱${ctx.parsed.y.toLocaleString()}`
                            : `${ctx.parsed.y} rentals`
                    }
                }
            },
            scales: {
                y:  { type: 'linear', position: 'left',  ticks: { callback: v => '₱' + v.toLocaleString() }, grid: { color: '#f0f0f0' } },
                y2: { type: 'linear', position: 'right', ticks: { stepSize: 1 }, grid: { display: false } }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php $this->endSection(); ?>

