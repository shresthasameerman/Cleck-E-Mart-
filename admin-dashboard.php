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
        SELECT s.shop_id, s.shop_name, s.shop_location, s.shop_pan, s.shop_products_type, s.shop_status, u.first_name, u.last_name, u.email
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
        <h1 class="page-title">Admin Dashboard</h1>
        
        <?php if ($flashSuccess = get_flash('success')): ?>
            <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
        <?php endif; ?>
        <?php if ($flashError = get_flash('error')): ?>
            <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
        <?php endif; ?>

        <section class="admin-section" style="margin-top: 2rem;">
            <h2>Products Pending Verification</h2>
            <?php if (empty($pendingProducts)): ?>
                <div class="empty-state">
                    <p>No products are currently pending verification.</p>
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

        <section class="admin-section" style="margin-top: 3rem;">
            <h2>Traders Pending Verification</h2>
            <?php if (empty($pendingTraders)): ?>
                <div class="empty-state">
                    <p>No traders are currently pending verification.</p>
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

        <section class="admin-section" style="margin-top: 3rem;">
            <h2>Shops Pending Verification</h2>
            <?php if (empty($pendingShops)): ?>
                <div class="empty-state">
                    <p>No shops are currently pending verification.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Shop Name</th>
                                <th>Trader Info</th>
                                <th>Location</th>
                                <th>PAN Number</th>
                                <th>Product Types</th>
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
                                    <td><?php echo e($shop['SHOP_LOCATION'] ?? 'N/A'); ?></td>
                                    <td><?php echo e($shop['SHOP_PAN'] ?? 'N/A'); ?></td>
                                    <td><?php echo e($shop['SHOP_PRODUCTS_TYPE'] ?? 'N/A'); ?></td>
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
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
