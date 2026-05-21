<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth_helpers.php';

// Only ADMIN can access
require_login(['ADMIN']);

// Handle form submission for approving/rejecting products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (isset($_POST['product_id'])) {
        $productId = (int) $_POST['product_id'];
        
        if ($action === 'approve' && $productId > 0) {
            if (!db_is_offline()) {
                db_execute("UPDATE PRODUCT SET product_verification_status = 'APPROVED' WHERE product_id = :id", ['id' => $productId]);
            } else {
                offline_update_product_status($productId, 'APPROVED');
            }
            set_flash('success', 'Product approved successfully.');
        } elseif ($action === 'reject' && $productId > 0) {
            if (!db_is_offline()) {
                db_execute("UPDATE PRODUCT SET product_verification_status = 'REJECTED' WHERE product_id = :id", ['id' => $productId]);
            } else {
                offline_update_product_status($productId, 'REJECTED');
            }
            set_flash('success', 'Product rejected.');
        }
        redirect('admin-dashboard.php');
    }
    

    if (isset($_POST['shop_id'])) {
        $shopId = (int) $_POST['shop_id'];
        
        if ($action === 'approve_shop' && $shopId > 0) {
            if (!db_is_offline()) {
                db_execute("UPDATE SHOP SET shop_status = 'ACTIVE' WHERE shop_id = :id", ['id' => $shopId]);
            } else {
                offline_update_shop_status($shopId, 'ACTIVE');
            }
            set_flash('success', 'Shop approved successfully.');
        } elseif ($action === 'reject_shop' && $shopId > 0) {
            if (!db_is_offline()) {
                db_execute("UPDATE SHOP SET shop_status = 'REJECTED' WHERE shop_id = :id", ['id' => $shopId]);
            } else {
                offline_update_shop_status($shopId, 'REJECTED');
            }
            set_flash('success', 'Shop rejected.');
        }
        redirect('admin-dashboard.php');
    }
}

// Fetch pending products
$pendingProducts = [];
if (!db_is_offline()) {
    $pendingProducts = db_fetch_all("
        SELECT p.product_id, p.product_name, p.price, p.product_verification_status, s.shop_name
        FROM PRODUCT p
        JOIN SHOP s ON p.shop_id = s.shop_id
        WHERE p.product_verification_status = 'PENDING_VERIFICATION'
    ");
} else {
    $pendingProducts = offline_get_pending_products();
}


// Fetch pending shops
$pendingShops = [];
if (!db_is_offline()) {
    $pendingShops = db_fetch_all("
        SELECT s.shop_id, s.shop_name, s.shop_status, u.first_name, u.last_name, u.email
        FROM SHOP s
        JOIN TRADER t ON s.trader_id = t.trader_id
        JOIN \"USER\" u ON t.trader_id = u.user_id
        WHERE s.shop_status = 'PENDING_APPROVAL'
    ");
} else {
    $pendingShops = offline_get_pending_shops();
    // Add user info if missing
    foreach ($pendingShops as &$shop) {
        if (!isset($shop['FIRST_NAME'])) {
            $shop['FIRST_NAME'] = 'Unknown';
            $shop['LAST_NAME'] = 'Trader';
            $shop['EMAIL'] = 'N/A';
        }
    }
}

// Fetch Overview Metrics
$totalRevenue = 0;
$totalOrders = 0;
$activeTraders = 0;
$totalCustomers = 0;
$recentOrders = [];
$revenueByTrader = [];

if (!db_is_offline()) {
    try {
        $row = db_fetch_one("SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) AS total FROM ORDER_ITEM oi JOIN \"ORDER\" o ON o.order_id = oi.order_id WHERE o.order_status IN ('PAID', 'COLLECTED')");
        $totalRevenue = (float) ($row['TOTAL'] ?? 0);

        $row = db_fetch_one('SELECT COUNT(*) AS total FROM "ORDER"');
        $totalOrders = (int) ($row['TOTAL'] ?? 0);

        $row = db_fetch_one("SELECT COUNT(*) AS total FROM \"USER\" WHERE \"ROLE\" = 'TRADER'");
        $activeTraders = (int) ($row['TOTAL'] ?? 0);

        $row = db_fetch_one("SELECT COUNT(*) AS total FROM \"USER\" WHERE \"ROLE\" = 'CUSTOMER'");
        $totalCustomers = (int) ($row['TOTAL'] ?? 0);

        $recentOrders = db_fetch_all("
            SELECT o.order_id, u.first_name || ' ' || u.last_name AS customer_name,
                   (SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) FROM ORDER_ITEM oi WHERE oi.order_id = o.order_id) AS order_total,
                   o.order_status
            FROM \"ORDER\" o
            JOIN CUSTOMER c ON o.customer_id = c.customer_id
            JOIN \"USER\" u ON c.customer_id = u.user_id
            ORDER BY o.order_date DESC
            FETCH FIRST 5 ROWS ONLY
        ");

        $revenueByTrader = db_fetch_all("
            SELECT u.first_name || ' ' || u.last_name AS trader_name,
                   NVL(SUM(CASE WHEN o.order_status IN ('COLLECTED', 'PAID', 'READY') THEN (oi.quantity * oi.unit_price) ELSE 0 END), 0) AS total_revenue
            FROM \"USER\" u
            JOIN TRADER t ON u.user_id = t.trader_id
            LEFT JOIN SHOP s ON t.trader_id = s.trader_id
            LEFT JOIN PRODUCT p ON s.shop_id = p.shop_id
            LEFT JOIN ORDER_ITEM oi ON p.product_id = oi.product_id
            LEFT JOIN \"ORDER\" o ON oi.order_id = o.order_id
            GROUP BY u.first_name, u.last_name
            ORDER BY total_revenue DESC
        ");

        $weeklyRevenueData = db_fetch_all("
            SELECT TO_CHAR(o.order_date, 'YYYY-MM-DD') as date_str,
                   SUM(oi.quantity * oi.unit_price) as daily_total
            FROM \"ORDER\" o
            JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
            WHERE o.order_status IN ('PAID', 'COLLECTED', 'DELIVERED')
              AND o.order_date >= TRUNC(SYSDATE) - 6
            GROUP BY TO_CHAR(o.order_date, 'YYYY-MM-DD')
        ");
        
        $chartData = [];
        $maxTotal = 0;
        
        // Initialize last 7 days
        $dayMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime("-$i days"));
            $dayMap[$dateStr] = [
                'day' => $dayName,
                'total' => 0,
                'percent' => 0
            ];
        }
        
        foreach ($weeklyRevenueData as $row) {
            $dStr = $row['DATE_STR'] ?? $row['date_str'];
            $total = (float)($row['DAILY_TOTAL'] ?? $row['daily_total']);
            if (isset($dayMap[$dStr])) {
                $dayMap[$dStr]['total'] += $total;
            }
        }
        
        foreach ($dayMap as $data) {
            if ($data['total'] > $maxTotal) {
                $maxTotal = $data['total'];
            }
            $chartData[] = $data;
        }
        
        foreach ($chartData as &$c) {
            $c['percent'] = $maxTotal > 0 ? ($c['total'] / $maxTotal) * 100 : 0;
        }
        unset($c);

        $allOrders = db_fetch_all("
            SELECT o.order_id, u.first_name || ' ' || u.last_name AS customer_name,
                   (SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) FROM ORDER_ITEM oi WHERE oi.order_id = o.order_id) AS order_total,
                   o.order_status, o.order_date
            FROM \"ORDER\" o
            JOIN CUSTOMER c ON o.customer_id = c.customer_id
            JOIN \"USER\" u ON c.customer_id = u.user_id
            ORDER BY o.order_date DESC
        ");

        $allOrderItems = db_fetch_all("
            SELECT oi.order_id, p.product_name, oi.quantity, oi.unit_price, s.shop_name
            FROM ORDER_ITEM oi
            JOIN PRODUCT p ON oi.product_id = p.product_id
            JOIN SHOP s ON p.shop_id = s.shop_id
        ");
        $itemsByOrder = [];
        foreach ($allOrderItems as $item) {
            $orderId = $item['ORDER_ID'] ?? $item['order_id'];
            $itemsByOrder[$orderId][] = $item;
        }

        $allTraders = db_fetch_all("
            SELECT u.user_id, u.first_name, u.last_name, u.email, t.brand_name 
            FROM \"USER\" u
            JOIN TRADER t ON u.user_id = t.trader_id
            WHERE u.role = 'TRADER'
            ORDER BY u.created_at DESC
        ");

    } catch (Throwable $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
} else {
    // Offline mode mock data
    $totalRevenue = 12450.00;
    $totalOrders = 842;
    $activeTraders = 24;
    $totalCustomers = 1204;
    $recentOrders = [
        ['ORDER_ID' => 1024, 'CUSTOMER_NAME' => 'John Doe', 'ORDER_TOTAL' => 45.00, 'ORDER_STATUS' => 'PROCESSING'],
        ['ORDER_ID' => 1023, 'CUSTOMER_NAME' => 'Jane Smith', 'ORDER_TOTAL' => 12.50, 'ORDER_STATUS' => 'DELIVERED'],
    ];
    $revenueByTrader = [
        ['TRADER_NAME' => 'Green Farms', 'TOTAL_REVENUE' => 4200],
        ['TRADER_NAME' => 'Fresh Catch', 'TOTAL_REVENUE' => 3850],
        ['TRADER_NAME' => 'Daily Bread', 'TOTAL_REVENUE' => 2100],
    ];
    $chartData = [
        ['day' => 'Mon', 'percent' => 40, 'total' => 240],
        ['day' => 'Tue', 'percent' => 60, 'total' => 360],
        ['day' => 'Wed', 'percent' => 50, 'total' => 300],
        ['day' => 'Thu', 'percent' => 80, 'total' => 480],
        ['day' => 'Fri', 'percent' => 70, 'total' => 420],
        ['day' => 'Sat', 'percent' => 90, 'total' => 540],
        ['day' => 'Sun', 'percent' => 75, 'total' => 450],
    ];
    $allOrders = $recentOrders;
    
    $itemsByOrder = [];
    $itemsByOrder[1024] = [
        ['PRODUCT_NAME' => 'Mock Product A', 'QUANTITY' => 2, 'UNIT_PRICE' => 10, 'SHOP_NAME' => 'Mock Shop'],
        ['PRODUCT_NAME' => 'Mock Product B', 'QUANTITY' => 1, 'UNIT_PRICE' => 25, 'SHOP_NAME' => 'Mock Shop']
    ];
    $itemsByOrder[1023] = [
        ['PRODUCT_NAME' => 'Mock Product C', 'QUANTITY' => 1, 'UNIT_PRICE' => 12.50, 'SHOP_NAME' => 'Mock Shop']
    ];
    
    // Fallback if db is offline
    $allTraders = [];
    $data = offline_load();
    foreach ($data['users'] as $u) {
        if (strtoupper((string)$u['role']) === 'TRADER') {
            $allTraders[] = [
                'USER_ID' => $u['user_id'],
                'FIRST_NAME' => $u['first_name'],
                'LAST_NAME' => $u['last_name'],
                'EMAIL' => $u['email']
            ];
        }
    }
}

$pageTitle = 'Admin Dashboard - Cleck E-Mart';
require __DIR__ . '/components/header.php';
?>
<style>
/* Clean professional tab styles */
.admin-tab-nav {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    padding-bottom: 0;
}
.admin-tab-btn {
    background: none;
    border: none;
    padding: 1rem 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    color: var(--color-muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.admin-tab-btn:hover {
    color: var(--color-foreground);
}
.admin-tab-btn.active {
    color: var(--color-primary-dark);
    border-bottom-color: var(--color-primary-dark);
    font-weight: 600;
}
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}
.metric-card {
    background: white; /* Clean white card */
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
}
.metric-card h3 {
    font-size: 0.85rem;
    color: var(--color-muted);
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.metric-card p {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-foreground);
    margin: 0;
}
.admin-panel {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}
.admin-panel h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    color: var(--color-foreground);
}
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 900px;
    max-height: 85vh;
    overflow-y: auto;
    position: relative;
    box-shadow: var(--shadow-lg);
}
.modal-close {
    position: absolute;
    top: 1.5rem; right: 1.5rem;
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: var(--color-muted);
}
</style>
<main id="main-content" class="page-layout" style="padding-top: 2rem;">
    
    <div class="container">
        <!-- Clean Tab Navigation -->
        <nav class="admin-tab-nav" aria-label="Admin Sections">
            <button class="admin-tab-btn active" onclick="openAdminTab(event, 'dashboard')">Dashboard Overview</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'shops')">Shop Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'products')">Product Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'traders')">Trader Access</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'collection')">Collection Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'orders')">Orders</button>
        </nav>

        <?php if ($flashSuccess = get_flash('success')): ?>
            <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
        <?php endif; ?>
        <?php if ($flashError = get_flash('error')): ?>
            <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <section id="dashboard" class="tab-content active-tab" style="display: block;">
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <h3>Total Revenue</h3>
                    <p>£<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Total Orders</h3>
                    <p><?php echo number_format($totalOrders); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Active Traders</h3>
                    <p><?php echo number_format($activeTraders); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Total Customers</h3>
                    <p><?php echo number_format($totalCustomers); ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="admin-panel">
                    <h3>Platform Revenue (Last 7 Days)</h3>
                    <div style="height: 200px; display: flex; align-items: flex-end; justify-content: space-between; padding-top: 1rem;">
                        <?php if (empty($chartData)): ?>
                            <div style="width: 100%; text-align: center; color: var(--color-muted); align-self: center;">No revenue data for the last 7 days.</div>
                        <?php else: ?>
                            <?php foreach ($chartData as $c): ?>
                                <div style="width: <?php echo max(5, 80 / count($chartData)); ?>%; background: var(--color-accent); height: <?php echo max(2, $c['percent']); ?>%; border-radius: 4px 4px 0 0; opacity: 0.8; position: relative;" title="£<?php echo number_format($c['total'], 2); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; color: var(--color-muted); font-size: 0.8rem; font-weight: 500;">
                        <?php foreach ($chartData as $c): ?>
                            <span style="flex: 1; text-align: center;"><?php echo e(ucwords(strtolower($c['day']))); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="admin-panel">
                    <h3>All Traders by Revenue</h3>
                    <?php if (empty($revenueByTrader)): ?>
                        <p style="color: var(--color-muted);">No revenue data available yet.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($revenueByTrader as $tr): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem;">
                                    <span style="font-weight: 500; color: var(--color-foreground);"><?php echo e($tr['TRADER_NAME'] ?? $tr['trader_name']); ?></span>
                                    <span style="color: var(--color-primary-dark); font-weight: 700; background: rgba(0,0,0,0.03); padding: 0.25rem 0.75rem; border-radius: 20px;">£<?php echo number_format((float) ($tr['TOTAL_REVENUE'] ?? $tr['total_revenue']), 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Recent Orders</h3>
                    <a href="javascript:void(0)" onclick="openOrdersModal()" style="color: var(--color-primary-dark); text-decoration: none; font-size: 0.9rem; font-weight: 600;">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                <th style="text-align: left; padding-bottom: 0.75rem;">Order ID</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Customer</th>
                                <th style="text-align: right; padding-bottom: 0.75rem;">Total</th>
                                <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem;">No recent orders.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $ro): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">#EM-<?php echo e($ro['ORDER_ID'] ?? $ro['order_id']); ?></td>
                                        <td style="padding: 1rem 0;"><?php echo e($ro['CUSTOMER_NAME'] ?? $ro['customer_name']); ?></td>
                                        <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format((float) ($ro['ORDER_TOTAL'] ?? $ro['order_total']), 2); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <?php 
                                            $status = strtoupper($ro['ORDER_STATUS'] ?? $ro['order_status']);
                                            $badgeClass = ($status === 'DELIVERED' || $status === 'COLLECTED' || $status === 'PAID') ? 'status-badge--delivered' : 'status-badge--pending'; 
                                            ?>
                                            <span class="status-badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(strtolower($status))); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Product Verification Content -->
        <section id="products" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <h3>Products Pending Verification</h3>
                <?php if (empty($pendingProducts)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">No products are currently pending verification.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Product Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Shop Name</th>
                                    <th style="text-align: right; padding-bottom: 0.75rem;">Price</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingProducts as $product): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e($product['PRODUCT_NAME']); ?></td>
                                        <td style="padding: 1rem 0;"><?php echo e($product['SHOP_NAME']); ?></td>
                                        <td style="text-align: right; padding: 1rem 0;">£<?php echo e(number_format((float)$product['PRICE'], 2)); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <span class="status-badge status-badge--pending">
                                                <?php echo e(ucwords(str_replace('_', ' ', strtolower($product['PRODUCT_VERIFICATION_STATUS'])))); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <form method="post" style="display:inline-flex; gap:0.5rem; justify-content: center;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['PRODUCT_ID']; ?>">
                                                <button type="submit" name="action" value="approve" class="button button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px;">Approve</button>
                                                <button type="submit" name="action" value="reject" class="button button--secondary button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border-color: #fca5a5;">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Shop Verification Content -->
        <section id="shops" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <h3>Shops Pending Verification</h3>
                <?php if (empty($pendingShops)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">No shops are currently pending verification.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Shop Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader Info</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingShops as $shop): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e($shop['SHOP_NAME']); ?></td>
                                        <td style="padding: 1rem 0;">
                                            <?php echo e($shop['FIRST_NAME'] . ' ' . $shop['LAST_NAME']); ?><br>
                                            <small style="color: var(--color-muted);"><?php echo e($shop['EMAIL']); ?></small>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <span class="status-badge status-badge--pending">
                                                <?php echo e(ucwords(str_replace('_', ' ', strtolower($shop['SHOP_STATUS'])))); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <form method="post" style="display:inline-flex; gap:0.5rem; justify-content: center;">
                                                <input type="hidden" name="shop_id" value="<?php echo $shop['SHOP_ID']; ?>">
                                                <button type="submit" name="action" value="approve_shop" class="button button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px;">Approve</button>
                                                <button type="submit" name="action" value="reject_shop" class="button button--secondary button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border-color: #fca5a5;">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="traders" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Trader Access</h3>
                    <span style="font-size: 0.9rem; color: var(--color-muted);">Global Account Impersonation</span>
                </div>
                
                <?php if (empty($allTraders)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">No traders found</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">There are no registered traders in the system yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader ID</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Contact Email</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allTraders as $t): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">#<?php echo e($t['USER_ID'] ?? $t['user_id']); ?></td>
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e(($t['FIRST_NAME'] ?? '') . ' ' . ($t['LAST_NAME'] ?? '')); ?></td>
                                        <td style="padding: 1rem 0; color: var(--color-muted);"><?php echo e($t['EMAIL'] ?? $t['email']); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <a href="admin-impersonate.php?trader_id=<?php echo e($t['USER_ID'] ?? $t['user_id']); ?>" class="button button--small" style="text-decoration: none; display: inline-block;">Access Account</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="collection" class="tab-content" style="display: none;">
            <div class="admin-panel" style="max-width: 800px; margin: 0 auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h3 style="margin: 0;">Collection Verification</h3>
                        <p style="margin: 0.25rem 0 0; color: var(--color-muted); font-size: 0.9rem;">Scan a customer's RFID card to pull up their orders.</p>
                    </div>
                    <button id="rfidConnectBtn" class="button" style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--color-primary-dark); color: white;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        Connect Scanner
                    </button>
                </div>

                <div id="rfidStatus" style="padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius-sm); background: #f3f4f6; color: #4b5563; font-size: 0.95rem; border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #9ca3af;"></div>
                    Scanner disconnected. Click 'Connect Scanner' to start.
                </div>

                <div id="rfidLoading" style="display: none; padding: 3rem; text-align: center; color: var(--color-muted);">
                    <svg class="spinner" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
                    <p style="margin-top: 1rem;">Looking up customer...</p>
                </div>

                <div id="rfidResults" style="display: none;">
                    <div style="background: white; border: 1px solid rgba(0,0,0,0.1); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; font-size: 1.1rem; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem;">Customer Details</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Name</span>
                                <div id="rfidCustName" style="font-weight: 600; font-size: 1.1rem;">-</div>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Email</span>
                                <div id="rfidCustEmail">-</div>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--color-muted); text-transform: uppercase;">Phone</span>
                                <div id="rfidCustPhone">-</div>
                            </div>
                        </div>
                    </div>

                    <h4 style="margin: 0 0 1rem 0; font-size: 1.1rem;">Ready for Collection</h4>
                    <div id="rfidOrdersList">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </section>

        <section id="orders" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="margin: 0;">All Orders</h3>
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="orderSearchInput" placeholder="Search orders (ID, Customer...)" style="padding: 0.5rem 1rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px; width: 250px; font-family: inherit;" onkeyup="filterOrders()">
                        <select id="orderSortSelect" style="padding: 0.5rem 1rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px; font-family: inherit; background-color: white;" onchange="sortOrders()">
                            <option value="date_desc">Sort by Date (Newest)</option>
                            <option value="date_asc">Sort by Date (Oldest)</option>
                            <option value="total_desc">Sort by Total (High to Low)</option>
                            <option value="total_asc">Sort by Total (Low to High)</option>
                            <option value="status">Sort by Status</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                <th style="text-align: left; padding-bottom: 0.75rem;">Order ID</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Customer</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Date</th>
                                <th style="text-align: right; padding-bottom: 0.75rem;">Total</th>
                                <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="allOrdersBody">
                            <?php if (empty($allOrders)): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 2rem;">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($allOrders as $o): 
                                    $dt = $o['ORDER_DATE'] ?? $o['order_date'] ?? null;
                                    $total = (float) ($o['ORDER_TOTAL'] ?? $o['order_total']);
                                    $status = strtoupper($o['ORDER_STATUS'] ?? $o['order_status']);
                                    $badgeClass = ($status === 'DELIVERED' || $status === 'COLLECTED' || $status === 'PAID') ? 'status-badge--delivered' : 'status-badge--pending'; 
                                ?>
                                    <tr class="order-row" 
                                        data-date="<?php echo $dt ? date('Y-m-d H:i:s', strtotime($dt)) : '1970-01-01'; ?>" 
                                        data-total="<?php echo $total; ?>" 
                                        data-status="<?php echo strtolower($status); ?>"
                                        style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">
                                            <a href="javascript:void(0)" onclick="openOrderDetailsModal(<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?>)" style="color: var(--color-primary-dark); text-decoration: underline;">#EM-<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?></a>
                                        </td>
                                        <td style="padding: 1rem 0;"><?php echo e($o['CUSTOMER_NAME'] ?? $o['customer_name']); ?></td>
                                        <td style="padding: 1rem 0; color: var(--color-muted);">
                                            <?php echo $dt ? date('M d, Y', strtotime($dt)) : 'N/A'; ?>
                                        </td>
                                        <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format($total, 2); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <span class="status-badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(strtolower($status))); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- The Orders Modal -->
<div id="ordersModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeOrdersModal()">&times;</button>
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">All Orders</h3>
        <div class="table-responsive">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                        <th style="text-align: left; padding-bottom: 0.75rem;">Order ID</th>
                        <th style="text-align: left; padding-bottom: 0.75rem;">Customer</th>
                        <th style="text-align: left; padding-bottom: 0.75rem;">Date</th>
                        <th style="text-align: right; padding-bottom: 0.75rem;">Total</th>
                        <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allOrders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allOrders as $o): ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 1rem 0; font-weight: 500;">
                                    <a href="javascript:void(0)" onclick="openOrderDetailsModal(<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?>)" style="color: var(--color-primary-dark); text-decoration: underline;">#EM-<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?></a>
                                </td>
                                <td style="padding: 1rem 0;"><?php echo e($o['CUSTOMER_NAME'] ?? $o['customer_name']); ?></td>
                                <td style="padding: 1rem 0; color: var(--color-muted);">
                                    <?php 
                                        $dt = $o['ORDER_DATE'] ?? $o['order_date'] ?? null;
                                        echo $dt ? date('M d, Y', strtotime($dt)) : 'N/A';
                                    ?>
                                </td>
                                <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format((float) ($o['ORDER_TOTAL'] ?? $o['order_total']), 2); ?></td>
                                <td style="text-align: center; padding: 1rem 0;">
                                    <?php 
                                    $status = strtoupper($o['ORDER_STATUS'] ?? $o['order_status']);
                                    $badgeClass = ($status === 'DELIVERED' || $status === 'COLLECTED' || $status === 'PAID') ? 'status-badge--delivered' : 'status-badge--pending'; 
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(strtolower($status))); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal-overlay" style="z-index: 1100;">
    <div class="modal-content" style="max-width: 600px;">
        <button class="modal-close" onclick="closeOrderDetailsModal()">&times;</button>
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">Order Details</h3>
        <div id="orderDetailsBody" class="table-responsive">
            <!-- Details rendered via JS -->
        </div>
    </div>
</div>

<script>
const itemsByOrder = <?php echo json_encode($itemsByOrder); ?>;

function openOrderDetailsModal(orderId) {
    const items = itemsByOrder[orderId] || [];
    let html = '<table class="data-table" style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="border-bottom: 2px solid rgba(0,0,0,0.05);"><th style="text-align: left; padding-bottom: 0.75rem;">Product</th><th style="text-align: left; padding-bottom: 0.75rem;">Shop</th><th style="text-align: center; padding-bottom: 0.75rem;">Qty</th><th style="text-align: right; padding-bottom: 0.75rem;">Price</th></tr></thead><tbody>';
    
    if (items.length === 0) {
        html += '<tr><td colspan="4" style="text-align: center; padding: 2rem;">No items found for this order.</td></tr>';
    } else {
        items.forEach(i => {
            const prodName = i.PRODUCT_NAME || i.product_name;
            const shopName = i.SHOP_NAME || i.shop_name;
            const qty = i.QUANTITY || i.quantity;
            const price = parseFloat(i.UNIT_PRICE || i.unit_price).toFixed(2);
            html += `<tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                <td style="padding: 1rem 0;">${prodName}</td>
                <td style="padding: 1rem 0; color: var(--color-muted);">${shopName}</td>
                <td style="text-align: center; padding: 1rem 0;">${qty}</td>
                <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£${price}</td>
            </tr>`;
        });
    }
    html += '</tbody></table>';
    
    document.getElementById('orderDetailsBody').innerHTML = html;
    document.getElementById('orderDetailsModal').classList.add('active');
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').classList.remove('active');
}

function openAdminTab(event, tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.style.display = 'none';
        el.classList.remove('active-tab');
    });
    // Remove active class from all tab buttons
    document.querySelectorAll('.admin-tab-btn').forEach(function(el) {
        el.classList.remove('active');
    });
    
    // Show the selected tab content
    var target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
        target.classList.add('active-tab');
    }
    // Add active class to the clicked button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
}

function openOrdersModal() {
    document.getElementById('ordersModal').classList.add('active');
}
function closeOrdersModal() {
    document.getElementById('ordersModal').classList.remove('active');
}

function filterOrders() {
    const input = document.getElementById('orderSearchInput').value.toLowerCase();
    const trs = document.querySelectorAll('#allOrdersBody tr.order-row');
    trs.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(input) ? '' : 'none';
    });
}

function sortOrders() {
    const sortBy = document.getElementById('orderSortSelect').value;
    const tbody = document.getElementById('allOrdersBody');
    const rows = Array.from(tbody.querySelectorAll('tr.order-row'));

    rows.sort((a, b) => {
        if (sortBy === 'date_desc' || sortBy === 'date_asc') {
            const da = new Date(a.dataset.date);
            const db = new Date(b.dataset.date);
            return sortBy === 'date_desc' ? db - da : da - db;
        } else if (sortBy === 'total_desc' || sortBy === 'total_asc') {
            const ta = parseFloat(a.dataset.total);
            const tb = parseFloat(b.dataset.total);
            return sortBy === 'total_desc' ? tb - ta : ta - tb;
        } else if (sortBy === 'status') {
            return a.dataset.status.localeCompare(b.dataset.status);
        }
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>

<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .order-card { background: white; border: 1px solid rgba(0,0,0,0.1); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1rem; }
    .order-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem; margin-bottom: 0.75rem; }
    .order-item-row { display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.5rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ---- RFID Web Serial Logic ----
    const connectBtn = document.getElementById('rfidConnectBtn');
    const statusEl = document.getElementById('rfidStatus');
    const loadingEl = document.getElementById('rfidLoading');
    const resultsEl = document.getElementById('rfidResults');
    const custNameEl = document.getElementById('rfidCustName');
    const custEmailEl = document.getElementById('rfidCustEmail');
    const custPhoneEl = document.getElementById('rfidCustPhone');
    const ordersListEl = document.getElementById('rfidOrdersList');

    if (!connectBtn) return; // If we aren't rendering the collection tab for some reason

    let port;
    let reader;
    let isConnected = false;

    async function connectSerial() {
        if (!('serial' in navigator)) {
            alert("Your browser doesn't support the Web Serial API. Please use Google Chrome or Microsoft Edge.");
            return;
        }

        try {
            port = await navigator.serial.requestPort();
            await port.open({ baudRate: 9600 });
            isConnected = true;
            connectBtn.textContent = 'Disconnect';
            connectBtn.style.background = '#ef4444';
            setStatus('Connected to Arduino. Ready to scan.', '#10b981');
            readLoop();
        } catch (err) {
            console.error("Serial connection error:", err);
            setStatus('Connection failed or cancelled.', '#ef4444');
        }
    }

    async function disconnectSerial() {
        if (reader) {
            await reader.cancel();
        }
        if (port) {
            await port.close();
        }
        isConnected = false;
        connectBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg> Connect Scanner`;
        connectBtn.style.background = 'var(--color-primary-dark)';
        setStatus('Scanner disconnected.', '#9ca3af');
        resultsEl.style.display = 'none';
    }

    connectBtn.addEventListener('click', async () => {
        if (isConnected) {
            await disconnectSerial();
        } else {
            await connectSerial();
        }
    });

    function setStatus(text, color) {
        statusEl.innerHTML = `<div style="width: 8px; height: 8px; border-radius: 50%; background: ${color};"></div> ${text}`;
    }

    async function readLoop() {
        const textDecoder = new TextDecoderStream();
        const readableStreamClosed = port.readable.pipeTo(textDecoder.writable);
        reader = textDecoder.readable.getReader();
        
        let buffer = '';
        
        try {
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                
                buffer += value;
                const lines = buffer.split('\n');
                
                // Keep the last incomplete line in the buffer
                buffer = lines.pop(); 
                
                for (const line of lines) {
                    const trimmedLine = line.trim();
                    if (trimmedLine.startsWith('Card UID:')) {
                        // Extract "F3 DA 84 1A" -> "F3DA841A"
                        let uidRaw = trimmedLine.replace('Card UID:', '').trim();
                        let uid = uidRaw.replace(/\s+/g, '');
                        
                        if (uid) {
                            handleCardScan(uid);
                        }
                    }
                }
            }
        } catch (error) {
            console.error("Read error:", error);
        } finally {
            reader.releaseLock();
        }
    }

    async function handleCardScan(uid) {
        setStatus(`Card scanned (UID: ${uid}). Fetching data...`, '#3b82f6');
        resultsEl.style.display = 'none';
        loadingEl.style.display = 'block';

        try {
            const response = await fetch(`lib/rfid_api.php?action=scan&uid=${encodeURIComponent(uid)}`);
            const data = await response.json();
            
            loadingEl.style.display = 'none';

            if (data.status === 'success') {
                setStatus('Data loaded successfully.', '#10b981');
                
                // Populate customer info
                custNameEl.textContent = data.customer.name;
                custEmailEl.textContent = data.customer.email;
                custPhoneEl.textContent = data.customer.phone || 'N/A';

                // Populate orders
                ordersListEl.innerHTML = '';
                if (data.orders.length === 0) {
                    ordersListEl.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--color-muted); border: 1px dashed rgba(0,0,0,0.2); border-radius: var(--radius-sm);">No active orders found for this customer.</div>';
                } else {
                    data.orders.forEach(order => {
                        let itemsHtml = '';
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                itemsHtml += `
                                    <div class="order-item-row">
                                        <span>${item.QUANTITY ?? item.quantity}x ${item.PRODUCT_NAME ?? item.product_name}</span>
                                        <span>£${parseFloat(item.UNIT_PRICE ?? item.unit_price).toFixed(2)}</span>
                                    </div>
                                `;
                            });
                        }

                        const orderId = order.ORDER_ID ?? order.order_id;
                        const totalAmount = parseFloat(order.TOTAL_AMOUNT ?? order.total_amount).toFixed(2);
                        const orderDate = new Date(order.ORDER_DATE ?? order.order_date).toLocaleDateString();
                        const status = order.ORDER_STATUS ?? order.order_status;

                        ordersListEl.innerHTML += `
                            <div class="order-card" id="rfid-order-${orderId}">
                                <div class="order-card-header">
                                    <div>
                                        <div style="font-weight: 700;">Order #EM-${orderId}</div>
                                        <div style="font-size: 0.8rem; color: var(--color-muted);">${orderDate} • Status: ${status}</div>
                                    </div>
                                    <button onclick="markOrderCollected(${orderId})" class="button button--small" style="background: #10b981; color: white;">Mark Collected</button>
                                </div>
                                <div>
                                    ${itemsHtml}
                                    <div style="border-top: 1px dashed rgba(0,0,0,0.1); margin-top: 0.5rem; padding-top: 0.5rem; text-align: right; font-weight: 700;">
                                        Total: £${totalAmount}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                resultsEl.style.display = 'block';

            } else {
                setStatus(`Error: ${data.message}`, '#ef4444');
            }
        } catch (err) {
            console.error(err);
            loadingEl.style.display = 'none';
            setStatus('Network error fetching customer data.', '#ef4444');
        }
    }

    window.markOrderCollected = async function(orderId) {
        if (!confirm('Mark this order as collected and complete?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'mark_collected');
            formData.append('order_id', orderId);

            const response = await fetch('lib/rfid_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                const card = document.getElementById(`rfid-order-${orderId}`);
                if (card) {
                    card.innerHTML = `<div style="padding: 1rem; text-align: center; color: #10b981; font-weight: 600;">✓ Order Marked as Collected</div>`;
                    setTimeout(() => card.remove(), 2000);
                }
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            alert('Network error updating order.');
        }
    };
});
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
