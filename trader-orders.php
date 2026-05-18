<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$shopId = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : null;
$shop = trader_shop_for_user($userId, $shopId);

if ($shop === null && $shopId) {
    redirect('trader-shops.php');
}

$successMessage = get_flash('success');
$errorMessage = get_flash('error');

// Handle Overall Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_order_status') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = strtoupper(trim((string) ($_POST['new_status'] ?? '')));
    try {
        trader_update_order_status($userId, $orderId, $newStatus);
        set_flash('success', "Order status updated to $newStatus successfully.");
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
    }
    redirect("trader-orders.php?id=$orderId");
}



$viewingOrderId = isset($_GET['id']) ? (int) $_GET['id'] : null;

$pageTitle = 'Trader Orders | Cleck E-Mart';
$metaDescription = 'Manage your customer orders and update item statuses.';

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="trader-page">
    <div class="container">
        <?php if ($successMessage): ?>
            <p class="page-message page-message--success"><?php echo e($successMessage); ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p class="page-message page-message--error"><?php echo e($errorMessage); ?></p>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Trader Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;"><?php echo $shopId ? 'Manage orders for your shop.' : 'Manage all customer orders.'; ?></p>
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
                        <a href="trader-orders.php?shop_id=<?php echo $shopId; ?>" class="tab-button active">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            Orders
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
                        <a href="trader-orders.php" class="tab-button active">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            All Orders
                        </a>
                        <a href="logout.php" class="tab-button" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            Sign Out
                        </a>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">
                <?php if ($viewingOrderId): 
                    $orderInfo = trader_get_order_details($userId, $viewingOrderId, $shopId);
                    if (!$orderInfo):
                ?>
                    <p class="trader-empty">Order not found or contains no products from your shop.</p>
                <?php else: ?>
                    <?php
                        $orderStatus = strtoupper($orderInfo['ORDER_STATUS'] ?? 'PENDING');
                        $orderStatusOptions = ['PAID' => 'Paid', 'READY' => 'Ready', 'COLLECTED' => 'Collected'];
                    ?>
                    <section class="admin-section">
                        <div class="trader-card__header">
                            <div>
                                <p class="trader-card__eyebrow">Order #<?php echo e($orderInfo['ORDER_ID']); ?></p>
                                <h2>Customer: <?php echo e($orderInfo['CUSTOMER_NAME']); ?></h2>
                            </div>
                        </div>
                        <div class="trader-form__grid" style="margin-bottom: 2rem;">
                            <label>
                                <span>Order Date</span>
                                <input type="text" value="<?php echo e(date('F j, Y', strtotime($orderInfo['ORDER_DATE']))); ?>" readonly />
                            </label>
                            <label>
                                <span>Delivery Address</span>
                                <input type="text" value="<?php echo e($orderInfo['DELIVERY_ADDRESS'] ?? 'N/A'); ?>" readonly />
                            </label>
                            <label>
                                <span>Payment Method</span>
                                <input type="text" value="<?php echo e($orderInfo['PAYMENT_METHOD'] ?? 'N/A'); ?>" readonly />
                            </label>
                            <label>
                                <span>Payment Status</span>
                                <input type="text" value="<?php echo e($orderInfo['PAYMENT_STATUS'] ?? 'PENDING'); ?>" readonly />
                            </label>
                            <div class="trader-form__full" style="display: grid; gap: 0.35rem;">
                                <span>Overall Status</span>
                                <form method="post" action="trader-orders.php" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                    <input type="hidden" name="action" value="update_order_status" />
                                    <input type="hidden" name="order_id" value="<?php echo e($orderInfo['ORDER_ID']); ?>" />
                                    <select name="new_status" required style="padding: 0.3rem; font-size: 0.9rem; border-radius: 4px; border: 1px solid #ccc; background-color: #fff;">
                                        <?php if (isset($orderStatusOptions[$orderStatus])): ?>
                                            <option value="" disabled>Select...</option>
                                        <?php else: ?>
                                            <option value="" disabled selected><?php echo e($orderStatus); ?> (current)</option>
                                        <?php endif; ?>
                                        <?php foreach ($orderStatusOptions as $value => $label): ?>
                                            <option value="<?php echo e($value); ?>" <?php echo $orderStatus === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="button button--secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Update</button>
                                </form>
                            </div>
                        </div>

                        <h3>Your Products in this Order</h3>
                        <div class="trader-table-wrap" style="margin-top: 1rem;">
                            <table class="trader-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderInfo['items'] as $item): 
                                        $paymentStatus = strtoupper($orderInfo['PAYMENT_STATUS'] ?? 'PENDING');
                                    ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['PRODUCT_IMAGE']): ?>
                                                    <img src="assets/images/products/<?php echo e($item['PRODUCT_IMAGE']); ?>" alt="" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 4px;"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($item['PRODUCT_NAME']); ?></td>
                                            <td><?php echo e($item['QUANTITY']); ?></td>
                                            <td>£<?php echo e(number_format($item['UNIT_PRICE'], 2)); ?></td>
                                            <td>£<?php echo e(number_format($item['TOTAL_PRICE'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>

                <?php else: 
                    // List View
                    $filters = [
                        'customer_name' => $_GET['customer_name'] ?? '',
                        'status' => $_GET['status'] ?? '',
                        'date_from' => $_GET['date_from'] ?? '',
                        'date_to' => $_GET['date_to'] ?? ''
                    ];
                    $orders = trader_get_orders($userId, $filters);
                ?>
                    <section class="admin-section">
                        <div class="trader-card__header" style="flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <p class="trader-card__eyebrow">All Orders</p>
                                <h2>Your received orders</h2>
                            </div>
                            
                            <!-- Filters -->
                            <form method="get" action="trader-orders.php" class="orders-filter-form">
                                <input type="text" name="customer_name" placeholder="Customer Name" value="<?php echo e($filters['customer_name']); ?>" />
                                <select name="status">
                                    <option value="">All Statuses</option>
                                    <option value="PAID" <?php echo $filters['status'] === 'PAID' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="READY" <?php echo $filters['status'] === 'READY' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="COLLECTED" <?php echo $filters['status'] === 'COLLECTED' ? 'selected' : ''; ?>>Collected</option>
                                    <option value="SHIPPED" <?php echo $filters['status'] === 'SHIPPED' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="DELIVERED" <?php echo $filters['status'] === 'DELIVERED' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                                <input type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>" aria-label="From Date" />
                                <input type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>" aria-label="To Date" />
                                <button type="submit" class="button button--secondary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">Filter</button>
                                <?php if (array_filter($filters)): ?>
                                    <a href="trader-orders.php" class="trader-link" style="margin-left: 0.5rem; font-size: 0.9rem;">Clear</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div class="trader-table-wrap">
                            <?php if (empty($orders)): ?>
                                <p class="trader-empty">No orders found matching your criteria.</p>
                            <?php else: ?>
                                <table class="trader-table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer Name</th>
                                            <th>Order Date</th>
                                            <th>Payment Status</th>
                                            <th>Overall Status</th>
                                            <th>Total Items (Yours)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): 
                                            $orderStatus = strtoupper($order['ORDER_STATUS'] ?? 'PENDING');
                                            $badgeClass = 'badge--default';
                                            if ($orderStatus === 'PAID') $badgeClass = 'badge--blue';
                                            elseif ($orderStatus === 'READY') $badgeClass = 'badge--orange';
                                            elseif ($orderStatus === 'COLLECTED') $badgeClass = 'badge--green';
                                            elseif ($orderStatus === 'SHIPPED') $badgeClass = 'badge--purple';
                                            elseif ($orderStatus === 'DELIVERED') $badgeClass = 'badge--green';
                                        ?>
                                            <tr>
                                                <td>#<?php echo e($order['ORDER_ID']); ?></td>
                                                <td><?php echo e($order['CUSTOMER_NAME']); ?></td>
                                                <td><?php echo e(date('M j, Y', strtotime($order['ORDER_DATE']))); ?></td>
                                                <td><?php echo e($order['PAYMENT_STATUS'] ?? 'PENDING'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo e($orderStatus); ?></span>
                                                </td>
                                                <td><?php echo e($order['TOTAL_ITEMS']); ?></td>
                                                <td>
                                                    <a href="trader-orders.php?id=<?php echo e($order['ORDER_ID']); ?>" class="trader-link">View Details</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
    .orders-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .orders-filter-form input,
    .orders-filter-form select {
        padding: 0.4rem 0.5rem;
        border: 1px solid rgba(26, 26, 26, 0.2);
        border-radius: 0.25rem;
        font-size: 0.9rem;
    }
    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge--default { background: #eee; color: #333; }
    .badge--blue { background: #e0f2fe; color: #0284c7; }
    .badge--orange { background: #ffedd5; color: #c2410c; }
    .badge--purple { background: #f3e8ff; color: #7e22ce; }
    .badge--green { background: #dcfce7; color: #166534; }
</style>

<?php require __DIR__ . '/components/footer.php'; ?>
