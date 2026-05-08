<?php
/** @var bool $isAdmin */
/** @var int|float $totalOrders */
/** @var int|float $totalRevenue */
/** @var array|null $bestSelling */
/** @var array|null $topUserOrders */
/** @var array|null $topUserRevenue */
/** @var array|null $topClientRevenue */
/** @var array $months */
/** @var array $orders */
/** @var array $sellerNames */
/** @var array $salesCounts */
/** @var array $salesmanClientsData */
/** @var array $allUsernames */
/** @var array $lastMonths */
?>
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-1 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="totalOrders">Total Orders</h3>
            <h2 class="text-white d-inline-block"><?= number_format($totalOrders) ?></h2>
            <span class="float-right display-5 opacity-5"><i class="fa fa-shopping-cart"></i></span>
        </div></div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-2 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="totalRevenue">Total Revenue</h3>
            <h2 class="text-white d-inline-block">€ <?= number_format((float) $totalRevenue, 2, ',', '.') ?></h2>
            <span class="float-right display-5 opacity-5"><i class="fa fa-dollar-sign"></i></span>
        </div></div>
    </div>
    <?php if ($bestSelling): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-3 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="bestSellingProduct">Best Selling Product</h3>
            <h4 class="text-white"><?= e($bestSelling['name']) ?></h4>
            <p class="card-text-sub text-white"><span data-translate="quantitySold">Quantity Sold: </span><?= number_format($bestSelling['total_quantity']) ?></p>
            <span class="float-right display-5 opacity-5"><i class="fa fa-box"></i></span>
        </div></div>
    </div>
    <?php endif; ?>

    <?php if ($isAdmin && $topUserOrders): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-4 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="topUserByOrders">Top User by Orders</h3>
            <h4 class="text-white"><?= e($topUserOrders['username']) ?></h4>
            <p class="card-text-sub text-white"><span data-translate="totalOrders">Total Orders</span>: <?= number_format($topUserOrders['total_orders']) ?></p>
            <span class="float-right display-5 opacity-5"><i class="fa fa-user"></i></span>
        </div></div>
    </div>
    <?php endif; ?>

    <?php if ($isAdmin && $topUserRevenue): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-5 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="topUserByRevenue">Top User by Revenue</h3>
            <h4 class="text-white"><?= e($topUserRevenue['username']) ?></h4>
            <p class="card-text-sub text-white"><span data-translate="revenue">Revenue</span>: € <?= number_format((float) $topUserRevenue['total_revenue'], 2, ',', '.') ?></p>
            <span class="float-right display-5 opacity-5"><i class="fa fa-money-bill"></i></span>
        </div></div>
    </div>
    <?php endif; ?>

    <?php if ($isAdmin && $topClientRevenue): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card gradient-8 h-100"><div class="card-body">
            <h3 class="card-title text-white" data-translate="topClientByRevenue">Top Client by Revenue</h3>
            <h4 class="text-white"><?= e($topClientRevenue['name']) ?></h4>
            <p class="card-text-sub text-white"><span data-translate="revenue">Revenue</span>: € <?= number_format((float) $topClientRevenue['total_revenue'], 2, ',', '.') ?></p>
            <span class="float-right display-5 opacity-5"><i class="fa fa-hand-holding-usd"></i></span>
        </div></div>
    </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin && !empty($orders)): ?>
<div class="row">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h5 class="card-title" data-translate="ordersPerMonth">Orders per Month</h5>
            <div class="chart-container mt-4"><canvas id="ordersChart"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm"><div class="card-body">
            <h5 class="card-title" data-translate="topSellers">Top 5 Sellers</h5>
            <div class="chart-container mt-4"><canvas id="topSellersChart"></canvas></div>
        </div></div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card h-100 shadow-sm"><div class="card-body">
            <h5 class="card-title" data-translate="distinctClientsByMonthBySalesman">Distinct Clients By Month (by Salesman)</h5>
            <div class="mb-3">
                <label for="salesmanSelect" class="form-label" data-translate="selectSalesman">Select Salesman:</label>
                <select id="salesmanSelect" class="form-select w-auto">
                    <option value="" disabled selected data-translate="Select">-- Select --</option>
                    <?php foreach ($allUsernames as $u): ?>
                        <option value="<?= e($u) ?>"><?= e($u) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="chart-container mt-4"><canvas id="distinctClientsChart"></canvas></div>
        </div></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    if (typeof loadTranslations === 'function') {
        await loadTranslations(document.body.getAttribute('data-locale') || 'en');
    }
    const months = <?= json_encode($months) ?>;
    const ordersData = <?= json_encode($orders) ?>;
    const sellerNames = <?= json_encode($sellerNames) ?>;
    const salesCounts = <?= json_encode($salesCounts) ?>;
    const lastMonths = <?= json_encode($lastMonths) ?>;
    const salesmanClientsData = <?= json_encode($salesmanClientsData) ?>;

    const ctx = document.getElementById('ordersChart')?.getContext('2d');
    if (ctx) new Chart(ctx, {
        type: 'line',
        data: { labels: months, datasets: [{
            label: getTranslation('ordersPerMonth') || 'Orders per Month',
            data: ordersData, borderColor: 'rgba(75,192,192,1)', borderWidth: 2,
            fill: false, pointStyle: 'circle', pointRadius: 5, pointHoverRadius: 10,
            cubicInterpolationMode: 'monotone', tension: 0.4
        }]},
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const ctxT = document.getElementById('topSellersChart')?.getContext('2d');
    if (ctxT) new Chart(ctxT, {
        type: 'bar',
        data: { labels: sellerNames, datasets: [{
            label: getTranslation('totalSales') || 'Total Sales',
            data: salesCounts,
            backgroundColor: 'rgba(54,162,235,0.5)',
            borderColor: 'rgba(54,162,235,1)', borderWidth: 1
        }]},
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const ctxD = document.getElementById('distinctClientsChart')?.getContext('2d');
    if (ctxD) {
        const chartLabels = lastMonths.map(m => m.label);
        const chartKeys   = lastMonths.map(m => m.key);
        const chart = new Chart(ctxD, {
            type: 'line',
            data: { labels: chartLabels, datasets: [{
                label: getTranslation('distinctClients') || 'Distinct Clients',
                data: [], borderColor: 'rgba(75,192,192,1)', fill: false, tension: 0.4
            }]},
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
        document.getElementById('salesmanSelect').addEventListener('change', function () {
            const map = salesmanClientsData[this.value] || {};
            chart.data.datasets[0].label = (getTranslation('distinctClients') || 'Distinct Clients') + ' - ' + this.value;
            chart.data.datasets[0].data = chartKeys.map(k => map[k] || 0);
            chart.update();
        });
    }
});
</script>
<?php endif; ?>
