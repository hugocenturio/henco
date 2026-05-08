<?php
include 'header.php';
$page_title = 'Home';
include 'template.php';

// Ensure the session is started
session_start();

// Get user ID from session and ensure it is an integer
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// Check if the user is an admin
$is_admin = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);

// Function to get total orders and total revenue
function getTotalOrdersAndRevenue($mysqli, $is_admin, $user_id = null) {
    if ($is_admin) {
        $stmt = $mysqli->prepare('SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders');
    } else {
        $stmt = $mysqli->prepare('SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : ['total_orders' => 0, 'total_revenue' => 0];
}

// Get total orders and revenue
$data = getTotalOrdersAndRevenue($mysqli, $is_admin, $user_id);
$total_orders = $data['total_orders'];
$total_revenue = $data['total_revenue'];

// Function to get the best selling product
function getBestSellingProduct($mysqli, $is_admin, $user_id = null) {
    if ($is_admin) {
        $stmt = $mysqli->prepare('
            SELECT p.name, SUM(oi.quantity) AS total_quantity 
            FROM order_items oi 
            INNER JOIN products p ON oi.product_id = p.id 
            GROUP BY oi.product_id 
            ORDER BY total_quantity DESC 
            LIMIT 1
        ');
    } else {
        $stmt = $mysqli->prepare('
            SELECT p.name, SUM(oi.quantity) AS total_quantity 
            FROM order_items oi 
            INNER JOIN products p ON oi.product_id = p.id 
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ?
            GROUP BY oi.product_id 
            ORDER BY total_quantity DESC 
            LIMIT 1
        ');
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// Get the best selling product
$best_selling_product = getBestSellingProduct($mysqli, $is_admin, $user_id);

// Function to get the user with the most orders
function getTopUserOrders($mysqli) {
    $stmt = $mysqli->prepare('
        SELECT u.username, COUNT(o.id) AS total_orders
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id
        GROUP BY o.user_id
        ORDER BY total_orders DESC
        LIMIT 1
    ');
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// Get the user with the most orders if admin
$top_user_orders = $is_admin ? getTopUserOrders($mysqli) : null;

// Function to get the user with the highest revenue
function getTopUserRevenue($mysqli) {
    $stmt = $mysqli->prepare('
        SELECT u.username, SUM(o.total_amount) AS total_revenue
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id
        GROUP BY o.user_id
        ORDER BY total_revenue DESC
        LIMIT 1
    ');
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// Get the user with the highest revenue if admin
$top_user_revenue = $is_admin ? getTopUserRevenue($mysqli) : null;

// Function to get the client with the highest revenue
function getTopClientRevenue($mysqli) {
    $stmt = $mysqli->prepare('
        SELECT c.name, SUM(o.total_amount) AS total_revenue
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        GROUP BY o.client_id
        ORDER BY total_revenue DESC
        LIMIT 1
    ');
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// Get the client with the highest revenue if admin
$top_client_revenue = $is_admin ? getTopClientRevenue($mysqli) : null;

// Function to get orders by month
function getOrdersByMonth($mysqli, $is_admin, $user_id = null) {
    if ($is_admin) {
        $stmt = $mysqli->prepare('
            SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders
            FROM orders
            GROUP BY month
            ORDER BY month ASC
        ');
    } else {
        $stmt = $mysqli->prepare('
            SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders
            FROM orders
            WHERE user_id = ?
            GROUP BY month
            ORDER BY month ASC
        ');
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders_by_month = [];
    while ($row = $result->fetch_assoc()) {
        $orders_by_month[] = $row;
    }
    return $orders_by_month;
}

// Get orders by month
$orders_by_month = getOrdersByMonth($mysqli, $is_admin, $user_id);
$orders_map = [];
foreach ($orders_by_month as $data) {
    $orders_map[$data['month']] = (int)$data['total_orders'];
}

// Function to get top 5 sellers
function getTopSellers($mysqli) {
    $stmt = $mysqli->prepare('
        SELECT u.username, COUNT(o.id) AS total_sales
        FROM users u
        INNER JOIN orders o ON u.user_id = o.user_id
        GROUP BY u.username
        ORDER BY total_sales DESC
        LIMIT 5
    ');
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get top 5 sellers
$top_sellers = getTopSellers($mysqli);
$seller_names = [];
$sales_counts = [];
foreach ($top_sellers as $seller) {
    $seller_names[] = $seller['username'];
    $sales_counts[] = $seller['total_sales'];
}

// Prepare arrays for the orders chart
$months = [];
$orders = [];
$currentDate = new DateTime();
for ($i = 11; $i >= 0; $i--) {
    $date = (clone $currentDate)->modify("-$i month");
    $month_key = $date->format('Y-m');
    $formatter = new IntlDateFormatter('pt_PT', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy');
    $month_name = ucfirst($formatter->format($date));
    $months[] = $month_name;
    $orders[] = $orders_map[$month_key] ?? 0;
}

// Query for distinct clients by month for each salesman
$sql_distinct_by_salesman = "
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') AS month_key,
        u.username,
        COUNT(DISTINCT o.client_id) AS distinct_clients
    FROM orders o
    INNER JOIN users u ON o.user_id = u.user_id
    GROUP BY month_key, u.user_id
    ORDER BY month_key ASC
";
$result_distinct = $mysqli->query($sql_distinct_by_salesman);

// Build array in the format salesman_clients_data['joao']['2025-01'] = 10
$salesman_clients_data = [];
$all_usernames = []; // to populate the dropdown
if ($result_distinct) {
    while ($row = $result_distinct->fetch_assoc()) {
        $mKey = $row['month_key'];   // e.g., "2025-01"
        $uname = $row['username'];    // e.g., "joao"
        $count = (int)$row['distinct_clients'];
        if (!isset($salesman_clients_data[$uname])) {
            $salesman_clients_data[$uname] = [];
            $all_usernames[] = $uname;
        }
        $salesman_clients_data[$uname][$mKey] = $count;
    }
}
// Remove duplicates and sort
$all_usernames = array_unique($all_usernames);
sort($all_usernames);

// Build array with the last 12 months in the format [ ['key'=>'2025-01','label'=>'Janeiro 2025'], ... ]
$lastMonths = [];
$today = new DateTime();
for ($i = 11; $i >= 0; $i--) {
    $dt = (clone $today)->modify("-$i month");
    $mKey = $dt->format('Y-m');
    $formatter = new IntlDateFormatter('pt_PT', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy');
    $mLabel = ucfirst($formatter->format($dt));
    $lastMonths[] = [
        'key' => $mKey,
        'label' => $mLabel
    ];
}

// Close the database connection
$mysqli->close();
?>

<!-- ================== HTML ================== -->
<div class="row">
    <!-- Card 1: Total Orders -->
    <?php if ($total_orders !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-1 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="totalOrders">Total Orders</h3>    
                    <div class="d-inline-block">
                        <h2 class="text-white"><?php echo number_format($total_orders); ?></h2>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-shopping-cart"></i></span>        
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 2: Total Revenue -->
    <?php if ($total_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-2 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="totalRevenue">Total Revenue</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white" data-translate="">€</h2> 
                        <?php echo number_format($total_revenue, 2, ',', '.'); ?>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-dollar-sign"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 3: Best Selling Product -->
    <?php if ($best_selling_product !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-3 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="bestSellingProduct">Best Selling Product</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white">
                            <?php echo htmlspecialchars($best_selling_product['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="card-text-sub text-white">
                            <span data-translate="quantitySold">Quantity Sold: </span> 
                            <?php echo number_format($best_selling_product['total_quantity']); ?>
                        </p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-box"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 4: User with Most Orders (Admin Only) -->
    <?php if ($is_admin && $top_user_orders !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-4 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topUserByOrders">Top User by Orders</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white">
                            <?php echo htmlspecialchars($top_user_orders['username'], ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="card-text-sub text-white">
                            <span data-translate="totalOrders">Total Orders</span>:
                            <?php echo number_format($top_user_orders['total_orders']); ?>
                        </p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-user"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 5: User with Highest Revenue (Admin Only) -->
    <?php if ($is_admin && $top_user_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-5 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topUserByRevenue">Top User by Revenue</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white">
                            <?php echo htmlspecialchars($top_user_revenue['username'], ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="card-text-sub text-white">
                            <span data-translate="revenue">Revenue</span>: 
                            € <?php echo number_format($top_user_revenue['total_revenue'], 2, ',', '.'); ?>
                        </p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-money-bill"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 6: Client with Highest Revenue (Admin Only) -->
    <?php if ($is_admin && $top_client_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-8 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topClientByRevenue">Top Client by Revenue</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white">
                            <?php echo htmlspecialchars($top_client_revenue['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="card-text-sub text-white">
                            <span data-translate="revenue">Revenue</span>:
                            € <?php echo number_format($top_client_revenue['total_revenue'], 2, ',', '.'); ?>
                        </p>
                    </div>
                    <span class="float-right display-5 opacity-5">
                        <i class="fa fa-hand-holding-usd"></i>
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- =========================
     Chart Section
   ========================= -->
<?php if ($is_admin): ?>
    <?php if (!empty($orders)): ?>
        <div class="row">
            <!-- Orders per Month Chart -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title" data-translate="ordersPerMonth">Orders per Month</h5>
                        <div class="chart-container mt-4">
                            <canvas id="ordersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 5 Sellers Chart -->
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title" data-translate="topSellers">Top 5 Sellers</h5>
                        <div class="chart-container mt-4">
                            <canvas id="topSellersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW: Filter by Salesman + Distinct Clients Chart -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title" data-translate="distinctClientsByMonthBySalesman">Distinct Clients By Month (by Salesman)</h5>
                        
                        <!-- Dropdown to select the salesman -->
                        <div class="mb-3">
                            <label for="salesmanSelect" class="form-label" data-translate="selectSalesman">Select Salesman:</label>
                            <select id="salesmanSelect" class="form-select w-auto">
                                <option value="" disabled selected data-translate="Select">-- Select --</option>
                                <?php foreach ($all_usernames as $uname): ?>
                                    <option value="<?php echo htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($uname, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Chart -->
                        <div class="chart-container mt-4">
                            <canvas id="distinctClientsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    // If using a translation system
    if (typeof loadTranslations === 'function') {
        await loadTranslations(document.body.getAttribute('data-locale') || 'en');
    }

    // ==========================
    // Orders per Month Chart
    // ==========================
    const months = <?php echo json_encode($months); ?>; 
    const ordersData = <?php echo json_encode($orders); ?>;

    const ctxOrders = document.getElementById('ordersChart')?.getContext('2d');
    if (ctxOrders) {
        new Chart(ctxOrders, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: getTranslation('ordersPerMonth') || 'Orders per Month',
                    data: ordersData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 10,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#333'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: getTranslation('totalOrders') || 'Total Orders'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: getTranslation('months') || 'Months'
                        }
                    }
                }
            }
        });
    }

    // ==========================
    // Top 5 Sellers Chart
    // ==========================
    const sellerNames = <?php echo json_encode($seller_names); ?>;
    const salesCounts = <?php echo json_encode($sales_counts); ?>;

    const ctxTopSellers = document.getElementById('topSellersChart')?.getContext('2d');
    if (ctxTopSellers) {
        new Chart(ctxTopSellers, {
            type: 'bar',
            data: {
                labels: sellerNames,
                datasets: [{
                    label: getTranslation('totalSales') || 'Total Sales',
                    data: salesCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: getTranslation('topSellers') || 'Top Sellers'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return (getTranslation('sales') || 'Sales') + ': ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: getTranslation('totalSales') || 'Total Sales'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: getTranslation('sellers') || 'Sellers'
                        }
                    }
                }
            }
        });
    }

    // ==========================
    // Distinct Clients By Month (by Salesman) Chart
    // ==========================
    const lastMonths = <?php echo json_encode($lastMonths); ?>; 
    const salesmanClientsData = <?php echo json_encode($salesman_clients_data); ?>;

    const chartKeys = lastMonths.map(m => m.key);    // ["2025-01", "2025-02", ...]
    const chartLabels = lastMonths.map(m => m.label);  // ["Janeiro 2025", "Fevereiro 2025", ...]

    const ctxDistinct = document.getElementById('distinctClientsChart')?.getContext('2d');
    if (ctxDistinct) {
        const distinctChart = new Chart(ctxDistinct, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: getTranslation('distinctClients'),
                    data: [], 
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: getTranslation('distinctClientsByMonth')
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const salesmanSelect = document.getElementById('salesmanSelect');
        salesmanSelect.addEventListener('change', function() {
            const selectedSalesman = this.value; 
            const dataMap = salesmanClientsData[selectedSalesman] || {};

            const newData = chartKeys.map(mKey => {
                return dataMap[mKey] ? dataMap[mKey] : 0;
            });

            distinctChart.data.datasets[0].label = getTranslation('distinctClients') + ' - ' + selectedSalesman;
            distinctChart.data.datasets[0].data = newData;

            distinctChart.update();
        });
    }
});
</script>


<?php include 'footer.php'; ?>
