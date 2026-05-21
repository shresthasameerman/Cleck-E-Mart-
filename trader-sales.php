<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/trader_helpers.php';
require_once __DIR__ . '/lib/oci_db.php';

trader_role_guard();

$userId = (int) current_user_id();
$shopId = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : null;
$shop = null;

// Ensure the user owns this shop (or redirect)
if ($shopId) {
    $shop = trader_shop_for_user($userId, $shopId);
    if (!$shop) {
        redirect('trader-shops.php');
    }
}

$pageTitle = $shopId ? 'Shop Sales Report | Cleck E-Mart' : 'Overall Sales Report | Cleck E-Mart';
$reportTitle = $shopId ? 'Shop Sales Report' : 'Overall Sales Report';
$reportDesc = $shopId ? 'Comprehensive sales analytics for your shop.' : 'Comprehensive sales analytics across all your shops.';

// Fetch Sales Data
$dailySales = [];
$detailedSales = [];
$shopRevenue = [];
$categorySales = [];
$totalRevenue = 0;
$totalItemsSold = 0;

$conn = db_connect();
if ($conn) {
    $whereClause = $shopId ? "p.shop_id = :filter_id" : "s.trader_id = :filter_id";
    $filterId = $shopId ? $shopId : $userId;

    // 1. Daily Sales Trend (Last 30 days)
    $sqlDaily = "SELECT TO_CHAR(o.order_date, 'YYYY-MM-DD') AS sale_date, SUM(oi.quantity * oi.unit_price) AS daily_revenue
                 FROM \"ORDER\" o
                 JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                 JOIN PRODUCT p ON oi.product_id = p.product_id
                 JOIN SHOP s ON p.shop_id = s.shop_id
                 WHERE $whereClause AND o.order_date >= SYSDATE - 30
                 GROUP BY TO_CHAR(o.order_date, 'YYYY-MM-DD')
                 ORDER BY sale_date ASC";
    
    $stmtDaily = oci_parse($conn, $sqlDaily);
    if ($stmtDaily) {
        oci_bind_by_name($stmtDaily, ':filter_id', $filterId, -1, SQLT_INT);
        if (oci_execute($stmtDaily)) {
            while ($row = oci_fetch_assoc($stmtDaily)) {
                $dailySales[] = [
                    'date' => $row['SALE_DATE'],
                    'revenue' => (float) $row['DAILY_REVENUE']
                ];
            }
        }
        oci_free_statement($stmtDaily);
    }

    // 2. Revenue by Shop (Only useful for overall report)
    if (!$shopId) {
        $sqlShop = "SELECT s.shop_name, SUM(oi.quantity * oi.unit_price) AS shop_revenue
                    FROM \"ORDER\" o
                    JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                    JOIN PRODUCT p ON oi.product_id = p.product_id
                    JOIN SHOP s ON p.shop_id = s.shop_id
                    WHERE s.trader_id = :trader_id
                    GROUP BY s.shop_name";
        $stmtShop = oci_parse($conn, $sqlShop);
        if ($stmtShop) {
            oci_bind_by_name($stmtShop, ':trader_id', $userId, -1, SQLT_INT);
            if (oci_execute($stmtShop)) {
                while ($row = oci_fetch_assoc($stmtShop)) {
                    $shopRevenue[] = [
                        'shop_name' => $row['SHOP_NAME'],
                        'revenue' => (float) $row['SHOP_REVENUE']
                    ];
                }
            }
            oci_free_statement($stmtShop);
        }
    }

    // 3. Sales by Category
    $sqlCat = "SELECT c.category_name, SUM(oi.quantity) AS items_sold
                FROM \"ORDER\" o
                JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                JOIN PRODUCT p ON oi.product_id = p.product_id
                JOIN SHOP s ON p.shop_id = s.shop_id
                JOIN CATEGORY c ON p.category_id = c.category_id
                WHERE $whereClause
                GROUP BY c.category_name";
    $stmtCat = oci_parse($conn, $sqlCat);
    if ($stmtCat) {
        oci_bind_by_name($stmtCat, ':filter_id', $filterId, -1, SQLT_INT);
        if (oci_execute($stmtCat)) {
            while ($row = oci_fetch_assoc($stmtCat)) {
                $categorySales[] = [
                    'category_name' => $row['CATEGORY_NAME'],
                    'items_sold' => (int) $row['ITEMS_SOLD']
                ];
            }
        }
        oci_free_statement($stmtCat);
    }

    // 3.5 Top Products for Shop (Only for specific shop report)
    $topProductsShop = [];
    if ($shopId) {
        $sqlTop = "SELECT p.product_name, SUM(oi.quantity) AS total_sold
                   FROM \"ORDER\" o
                   JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                   JOIN PRODUCT p ON oi.product_id = p.product_id
                   WHERE p.shop_id = :shop_id
                   GROUP BY p.product_name
                   ORDER BY total_sold DESC
                   FETCH FIRST 5 ROWS ONLY";
        $stmtTop = oci_parse($conn, $sqlTop);
        if ($stmtTop) {
            oci_bind_by_name($stmtTop, ':shop_id', $shopId, -1, SQLT_INT);
            if (oci_execute($stmtTop)) {
                while ($row = oci_fetch_assoc($stmtTop)) {
                    $topProductsShop[] = [
                        'product_name' => $row['PRODUCT_NAME'],
                        'total_sold' => (int) $row['TOTAL_SOLD']
                    ];
                }
            }
            oci_free_statement($stmtTop);
        }
    }

    // 4. Detailed Sales List
    $sqlDetails = "SELECT o.order_id, TO_CHAR(o.order_date, 'YYYY-MM-DD HH24:MI') AS order_date,
                          p.product_name, s.shop_name, oi.quantity, oi.unit_price, (oi.quantity * oi.unit_price) AS total_price
                   FROM \"ORDER\" o
                   JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                   JOIN PRODUCT p ON oi.product_id = p.product_id
                   JOIN SHOP s ON p.shop_id = s.shop_id
                   WHERE $whereClause
                   ORDER BY o.order_date DESC";
    
    $stmtDetails = oci_parse($conn, $sqlDetails);
    if ($stmtDetails) {
        oci_bind_by_name($stmtDetails, ':filter_id', $filterId, -1, SQLT_INT);
        if (oci_execute($stmtDetails)) {
            while ($row = oci_fetch_assoc($stmtDetails)) {
                $detailedSales[] = [
                    'order_id' => $row['ORDER_ID'],
                    'date' => $row['ORDER_DATE'],
                    'product_name' => $row['PRODUCT_NAME'],
                    'shop_name' => $row['SHOP_NAME'],
                    'quantity' => (int) $row['QUANTITY'],
                    'unit_price' => (float) $row['UNIT_PRICE'],
                    'total_price' => (float) $row['TOTAL_PRICE']
                ];
                $totalRevenue += (float) $row['TOTAL_PRICE'];
                $totalItemsSold += (int) $row['QUANTITY'];
            }
        }
        oci_free_statement($stmtDetails);
    }
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="trader-page">
    <div class="container">
        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Trader Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Analytics and performance.</p>
                </div>

                <div class="admin-tabs">
                    <?php if ($shopId): ?>
                        <a href="trader-shops.php" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Back to My Shops
                        </a>
                        <hr style="border-top: 1px solid rgba(0,0,0,0.1); margin: 0.5rem 0; width: 100%;">
                        <a href="trader-shop-profile.php?shop_id=<?php echo $shopId; ?>" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Shop Profile
                        </a>
                        <a href="trader-dashboard.php?shop_id=<?php echo $shopId; ?>" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            Inventory
                        </a>
                        <a href="trader-orders.php?shop_id=<?php echo $shopId; ?>" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            Orders
                        </a>
                        <a href="trader-sales.php?shop_id=<?php echo $shopId; ?>" class="tab-button active">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                            Sales
                        </a>
                        <a href="trader-add-product.php?shop_id=<?php echo $shopId; ?>" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Add Products
                        </a>
                    <?php else: ?>
                        <a href="trader-profile.php" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            My Profile
                        </a>
                        <a href="trader-shops.php" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            My Shop
                        </a>
                        <a href="trader-orders.php" class="tab-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            All Orders
                        </a>
                        <a href="trader-sales.php" class="tab-button active">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                            All Sales
                        </a>
                        <a href="logout.php" class="tab-button" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            Sign Out
                        </a>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">
                <!-- Header with Download button -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h2 style="font-size: 1.8rem; margin-bottom: 0.25rem;"><?php echo e($reportTitle); ?></h2>
                        <p style="color: var(--color-muted);"><?php echo e($reportDesc); ?></p>
                    </div>
                    <button id="downloadPdfBtn" class="button button--secondary" style="display: flex; gap: 0.5rem; align-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download PDF
                    </button>
                </div>

                <!-- Wrapper for PDF Export -->
                <div id="pdfReportContent" style="background: transparent; padding: 10px;">
                    <!-- Sales KPIs -->
                    <section class="trader-stats" style="margin-bottom: 2rem;">
                        <article class="trader-stat-card">
                            <span class="trader-stat-card__label">Total Revenue</span>
                            <strong class="trader-stat-card__value">£<?php echo number_format($totalRevenue, 2); ?></strong>
                        </article>
                        <article class="trader-stat-card">
                            <span class="trader-stat-card__label">Items Sold</span>
                            <strong class="trader-stat-card__value"><?php echo $totalItemsSold; ?></strong>
                        </article>
                        <article class="trader-stat-card">
                            <span class="trader-stat-card__label">Avg. Value / Item</span>
                            <strong class="trader-stat-card__value">
                                <?php echo $totalItemsSold > 0 ? '£' . number_format($totalRevenue / $totalItemsSold, 2) : '£0.00'; ?>
                            </strong>
                        </article>
                    </section>

                    <!-- Charts Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        
                        <!-- Line Chart (Full Width) -->
                        <section class="admin-section" style="margin-bottom: 0; grid-column: 1 / -1;">
                            <div class="trader-card__header">
                                <div>
                                    <p class="trader-card__eyebrow">Revenue Trend</p>
                                    <h2>Last 30 Days Sales</h2>
                                </div>
                            </div>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="salesTrendChart"></canvas>
                            </div>
                        </section>

                        <!-- Bar Chart (Category Sales) -->
                        <section class="admin-section" style="margin-bottom: 0;">
                            <div class="trader-card__header">
                                <div>
                                    <p class="trader-card__eyebrow">Product Analytics</p>
                                    <h2>Sales by Category</h2>
                                </div>
                            </div>
                            <div style="position: relative; height: 250px; width: 100%;">
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                        </section>

                        <?php if (!$shopId && count($shopRevenue) > 0): ?>
                        <!-- Doughnut Chart (Shop Revenue) - Only for Overall Report -->
                        <section class="admin-section" style="margin-bottom: 0;">
                            <div class="trader-card__header">
                                <div>
                                    <p class="trader-card__eyebrow">Shop Analytics</p>
                                    <h2>Revenue by Shop</h2>
                                </div>
                            </div>
                            <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                                <canvas id="shopRevenueChart"></canvas>
                            </div>
                        </section>
                        <?php elseif ($shopId && count($topProductsShop) > 0): ?>
                        <!-- Polar Area Chart (Top Products) - For Specific Shop -->
                        <section class="admin-section" style="margin-bottom: 0;">
                            <div class="trader-card__header">
                                <div>
                                    <p class="trader-card__eyebrow">Top Performers</p>
                                    <h2>Best Selling Products</h2>
                                </div>
                            </div>
                            <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                                <canvas id="topProductsChart"></canvas>
                            </div>
                        </section>
                        <?php endif; ?>

                    </div>

                    <!-- Sales Detail Table -->
                    <section class="admin-section">
                        <div class="trader-card__header">
                            <div>
                                <p class="trader-card__eyebrow">Detailed Report</p>
                                <h2>All Sales Transactions</h2>
                            </div>
                        </div>
                        <div class="trader-table-wrap">
                            <?php if (empty($detailedSales)): ?>
                                <p class="trader-empty" style="text-align: center; padding: 2rem;">No sales data available yet.</p>
                            <?php else: ?>
                                <table class="trader-table">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Order ID</th>
                                            <?php if (!$shopId): ?>
                                                <th>Shop Name</th>
                                            <?php endif; ?>
                                            <th>Product Name</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detailedSales as $sale): ?>
                                            <tr>
                                                <td style="white-space: nowrap;"><?php echo e($sale['date']); ?></td>
                                                <td>#<?php echo e($sale['order_id']); ?></td>
                                                <?php if (!$shopId): ?>
                                                    <td><?php echo e($sale['shop_name']); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo e($sale['product_name']); ?></td>
                                                <td><?php echo e($sale['quantity']); ?></td>
                                                <td>£<?php echo number_format($sale['unit_price'], 2); ?></td>
                                                <td style="font-weight: 600;">£<?php echo number_format($sale['total_price'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </section>
                </div> <!-- /pdfReportContent -->
            </div>
        </div>
    </div>
</main>

<!-- Include html2pdf.js and Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Shared colors
        const brandGreen = 'rgba(36, 73, 34, 1)';
        const brandGreenLight = 'rgba(36, 73, 34, 0.2)';
        const accentOrange = 'rgba(222, 105, 48, 1)';
        const accentOrangeLight = 'rgba(222, 105, 48, 0.2)';
        const palette = [
            'rgba(36, 73, 34, 0.8)',
            'rgba(222, 105, 48, 0.8)',
            'rgba(106, 136, 97, 0.8)',
            'rgba(217, 160, 64, 0.8)',
            'rgba(45, 55, 72, 0.8)'
        ];

        // 1. Line Chart (Trend)
        const ctxLine = document.getElementById('salesTrendChart');
        if (ctxLine) {
            const dailyData = <?php echo json_encode($dailySales); ?>;
            const labelsLine = dailyData.map(item => item.date);
            const dataLine = dailyData.map(item => item.revenue);
            
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: labelsLine,
                    datasets: [{
                        label: 'Daily Revenue (£)',
                        data: dataLine,
                        backgroundColor: accentOrangeLight,
                        borderColor: accentOrange,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: accentOrange,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 2. Bar Chart (Categories)
        const ctxBar = document.getElementById('categorySalesChart');
        if (ctxBar) {
            const catData = <?php echo json_encode($categorySales); ?>;
            const labelsBar = catData.map(item => item.category_name);
            const dataBar = catData.map(item => item.items_sold);

            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labelsBar,
                    datasets: [{
                        label: 'Items Sold',
                        data: dataBar,
                        backgroundColor: brandGreen,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 3. Doughnut Chart (Shop Revenue) - Only present on global view
        const ctxDoughnut = document.getElementById('shopRevenueChart');
        if (ctxDoughnut) {
            const shopData = <?php echo json_encode($shopRevenue); ?>;
            const labelsDoughnut = shopData.map(item => item.shop_name);
            const dataDoughnut = shopData.map(item => item.revenue);

            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: labelsDoughnut,
                    datasets: [{
                        data: dataDoughnut,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // 4. Polar Area Chart (Top Products) - Only present on specific shop view
        const ctxPolar = document.getElementById('topProductsChart');
        if (ctxPolar) {
            const topData = <?php echo json_encode($topProductsShop ?? []); ?>;
            if (topData.length > 0) {
                const labelsPolar = topData.map(item => item.product_name);
                const dataPolar = topData.map(item => item.total_sold);

                new Chart(ctxPolar, {
                    type: 'polarArea',
                    data: {
                        labels: labelsPolar,
                        datasets: [{
                            data: dataPolar,
                            backgroundColor: [
                                'rgba(36, 73, 34, 0.7)',
                                'rgba(222, 105, 48, 0.7)',
                                'rgba(106, 136, 97, 0.7)',
                                'rgba(217, 160, 64, 0.7)',
                                'rgba(45, 55, 72, 0.7)'
                            ],
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }

        // Handle PDF Download
        const downloadBtn = document.getElementById('downloadPdfBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const element = document.getElementById('pdfReportContent');
                const opt = {
                    margin:       0.3,
                    filename:     '<?php echo $shopId ? "shop-sales-report.pdf" : "overall-sales-report.pdf"; ?>',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true },
                    jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

                const originalText = downloadBtn.innerHTML;
                downloadBtn.innerHTML = 'Generating PDF...';
                downloadBtn.disabled = true;

                html2pdf().set(opt).from(element).save().then(() => {
                    downloadBtn.innerHTML = originalText;
                    downloadBtn.disabled = false;
                });
            });
        }
    });
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
