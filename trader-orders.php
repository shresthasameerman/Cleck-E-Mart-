<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$shop = trader_shop_for_user($userId);

if ($shop === null) {
    redirect('trader-dashboard.php');
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

// Handle Item Status Update (Ready/Cancel Item)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $productId = (int) ($_POST['product_id'] ?? 0);
    $newStatus = strtoupper(trim((string) ($_POST['new_status'] ?? '')));
    try {
        if (!in_array($newStatus, ['READY', 'CANCELLED'])) {
            throw new Exception('Please select a valid status.');
        }
        trader_update_item_status($userId, $orderId, $productId, $newStatus);
        set_flash('success', "Item status updated to $newStatus successfully.");
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

    <section class="trader-intro" aria-labelledby="orders-title">
        <div class="container trader-intro__inner">
            <div>
                <p class="trader-intro__eyebrow">Order Management</p>
                <h1 id="orders-title"><?php echo e($viewingOrderId ? 'Order Details' : 'Customer Orders'); ?></h1>
                <p class="trader-intro__sub">
                    <?php echo e($viewingOrderId ? 'View and prepare items for this specific order.' : 'View all customer orders containing your products.'); ?>
                </p>
            </div>
            <?php if ($viewingOrderId): ?>
            <div class="trader-intro__meta">
                <a href="trader-orders.php" class="button button--secondary">Back to Orders</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="trader-content">
        <div class="container trader-layout">
            <aside class="trader-sidebar" aria-label="Trader navigation">
                <a class="trader-sidebar__item" href="trader-shops.php">My Shops</a>
                <a class="trader-sidebar__item" href="trader-dashboard.php">Dashboard</a>
                <a class="trader-sidebar__item is-active" href="trader-orders.php">Orders</a>
                <a class="trader-sidebar__item" href="trader-profile.php">Profile Settings</a>
                <a class="trader-sidebar__item" href="trader-add-product.php">Add Product</a>
                <a class="trader-sidebar__item" href="logout.php">Sign Out</a>
            </aside>

            <div class="trader-main">
                <?php if ($viewingOrderId): 
                    $orderInfo = trader_get_order_details($userId, $viewingOrderId);
                    if (!$orderInfo):
                ?>
                    <p class="trader-empty">Order not found or contains no products from your shop.</p>
                <?php else: ?>
                    <?php
                        $orderStatus = strtoupper($orderInfo['ORDER_STATUS'] ?? 'PENDING');
                        $orderStatusOptions = ['PAID' => 'Paid', 'READY' => 'Ready', 'COLLECTED' => 'Collected'];
                    ?>
                    <section class="trader-card">
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
                                        <th>Item Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderInfo['items'] as $item): 
                                        $itemStatus = strtoupper($item['ITEM_STATUS'] ?? 'PENDING');
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
                                            <td>
                                                <?php
                                                    $badgeClass = 'badge--default';
                                                    if ($itemStatus === 'PAID') $badgeClass = 'badge--blue';
                                                    elseif ($itemStatus === 'READY') $badgeClass = 'badge--orange';
                                                    elseif ($itemStatus === 'SHIPPED') $badgeClass = 'badge--purple';
                                                    elseif ($itemStatus === 'DELIVERED') $badgeClass = 'badge--green';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo e($itemStatus); ?></span>
                                            </td>
                                            <td>
                                                <?php if (in_array($paymentStatus, ['PAID', 'COMPLETED']) && ($itemStatus === 'PAID' || $itemStatus === 'PENDING')): ?>
                                                    <form method="post" action="trader-orders.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                                        <input type="hidden" name="action" value="update_status" />
                                                        <input type="hidden" name="order_id" value="<?php echo e($orderInfo['ORDER_ID']); ?>" />
                                                        <input type="hidden" name="product_id" value="<?php echo e($item['PRODUCT_ID']); ?>" />
                                                        <select name="new_status" style="padding: 0.3rem; font-size: 0.8rem; border-radius: 4px; border: 1px solid #ccc; background-color: #fff;" required>
                                                            <option value="" disabled selected>Select...</option>
                                                            <option value="READY">Ready</option>
                                                            <option value="CANCELLED">Cancel</option>
                                                        </select>
                                                        <button type="submit" class="button button--secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Update</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: #888;">-</span>
                                                <?php endif; ?>
                                            </td>
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
                    <section class="trader-card">
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
    </section>
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
