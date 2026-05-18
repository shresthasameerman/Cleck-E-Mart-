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
    
    if (isset($_POST['trader_id'])) {
        $traderId = (int) $_POST['trader_id'];
        
        if ($action === 'approve_trader' && $traderId > 0) {
            if (!db_is_offline()) {
                // First ensure trader_status column exists, then update
                try {
                    db_execute("UPDATE TRADER SET trader_status = 'VERIFIED' WHERE trader_id = :id", ['id' => $traderId]);
                } catch(Exception $e) {
                    // Ignore if trader_status column does not exist yet
                }
            } else {
                offline_update_trader_status($traderId, 'VERIFIED');
            }
            set_flash('success', 'Trader approved successfully.');
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

// Fetch pending traders
$pendingTraders = [];
if (!db_is_offline()) {
    $pendingTraders = db_fetch_all("
        SELECT t.trader_id, t.brand_name, t.pan_number, u.first_name, u.last_name, u.email, t.trader_status
        FROM TRADER t
        JOIN \"USER\" u ON u.user_id = t.trader_id
        WHERE t.trader_status = 'PENDING_VERIFICATION'
    ");
} else {
    $pendingTraders = offline_get_pending_traders();
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

$pageTitle = 'Admin Dashboard - Cleck E-Mart';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="page-layout">
    <div class="container">
        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Admin Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Manage pending verifications for products, traders, and shops.</p>
                </div>
                <?php if ($flashSuccess = get_flash('success')): ?>
                    <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
                <?php endif; ?>
                <?php if ($flashError = get_flash('error')): ?>
                    <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
                <?php endif; ?>

                <div class="admin-tabs">
                    <button class="tab-button active" onclick="openTab('products')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        Products Pending
                    </button>
                    <button class="tab-button" onclick="openTab('traders')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Traders Pending
                    </button>
                    <button class="tab-button" onclick="openTab('shops')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        Shops Pending
                    </button>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">

        <section id="products" class="admin-section tab-content active-tab">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-accent);"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                Products Pending Verification
            </h2>
            <?php if (empty($pendingProducts)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                    <p style="margin-top: 0.25rem; font-size: 0.9rem;">No products are currently pending verification.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Shop Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingProducts as $product): ?>
                                <tr>
                                    <td><?php echo e($product['PRODUCT_NAME']); ?></td>
                                    <td><?php echo e($product['SHOP_NAME']); ?></td>
                                    <td>$<?php echo e($product['PRICE']); ?></td>
                                    <td>
                                        <span class="status-badge status-badge--pending">
                                            <?php echo e($product['PRODUCT_VERIFICATION_STATUS']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline-flex; gap:0.5rem;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['PRODUCT_ID']; ?>">
                                            <button type="submit" name="action" value="approve" class="button button--small">Approve</button>
                                            <button type="submit" name="action" value="reject" class="button button--secondary button--small">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section id="traders" class="admin-section tab-content">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-accent);"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Traders Pending Verification
            </h2>
            <?php if (empty($pendingTraders)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                    <p style="margin-top: 0.25rem; font-size: 0.9rem;">No traders are currently pending verification.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Trader Name</th>
                                <th>Brand Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingTraders as $trader): ?>
                                <tr>
                                    <td><?php echo e($trader['FIRST_NAME'] . ' ' . $trader['LAST_NAME']); ?></td>
                                    <td><?php echo e($trader['BRAND_NAME'] ?: 'N/A'); ?></td>
                                    <td><?php echo e($trader['EMAIL']); ?></td>
                                    <td>
                                        <span class="status-badge status-badge--pending">
                                            <?php echo e($trader['TRADER_STATUS']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline-flex; gap:0.5rem;">
                                            <input type="hidden" name="trader_id" value="<?php echo $trader['TRADER_ID']; ?>">
                                            <button type="submit" name="action" value="approve_trader" class="button button--small">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section id="shops" class="admin-section tab-content">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-accent);"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Shops Pending Verification
            </h2>
            <?php if (empty($pendingShops)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                    <p style="margin-top: 0.25rem; font-size: 0.9rem;">No shops are currently pending verification.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Shop Name</th>
                                <th>Trader Info</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingShops as $shop): ?>
                                <tr>
                                    <td><?php echo e($shop['SHOP_NAME']); ?></td>
                                    <td>
                                        <?php echo e($shop['FIRST_NAME'] . ' ' . $shop['LAST_NAME']); ?><br>
                                        <small style="color: #666;"><?php echo e($shop['EMAIL']); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-badge--pending">
                                            <?php echo e($shop['SHOP_STATUS']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" style="display:inline-flex; gap:0.5rem;">
                                            <input type="hidden" name="shop_id" value="<?php echo $shop['SHOP_ID']; ?>">
                                            <button type="submit" name="action" value="approve_shop" class="button button--small">Approve</button>
                                            <button type="submit" name="action" value="reject_shop" class="button button--secondary button--small">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        </div> <!-- end admin-content-grid -->
        </div> <!-- end admin-dashboard-layout -->
    </div>
</main>
<script>
function openTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.classList.remove('active-tab');
    });
    document.querySelectorAll('.tab-button').forEach(function(el) {
        el.classList.remove('active');
    });
    
    document.getElementById(tabId).classList.add('active-tab');
    event.currentTarget.classList.add('active');
}
</script>
<?php require __DIR__ . '/components/footer.php'; ?>
