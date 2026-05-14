<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_discount') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $percentage = (float) ($_POST['discount_percentage'] ?? 0);
    $duration = (int) ($_POST['discount_duration'] ?? 30);
    try {
        trader_update_discount($userId, $productId, $percentage, $duration);
        set_flash('success', 'Discount updated.');
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
    }
    redirect('trader-dashboard.php');
}

$metrics = trader_dashboard_metrics($userId);
$shop = $metrics['shop'];
$inventoryProducts = $metrics['inventory_products'];
$lowStockProducts = $metrics['low_stock_products'];
$pageTitle = 'Trader Dashboard | Cleck E-Mart';
$metaDescription = 'Trader dashboard for reviewing sales, stock, and refill alerts.';

$successMessage = get_flash('success');
$errorMessage = get_flash('error');

// ====================================================================
// DYNAMIC TIMEFRAME FILTERING FOR TOP PRODUCTS
// ====================================================================

$timeframe = $_GET['timeframe'] ?? 'week';
$validTimeframes = ['week', 'month', 'year'];
if (!in_array($timeframe, $validTimeframes)) {
    $timeframe = 'week';
}

// Build date filter based on timeframe
$dateFilter = '';
$timeframeLabel = 'This Week';
switch ($timeframe) {
    case 'month':
        $dateFilter = "AND o.order_date >= ADD_MONTHS(SYSDATE, -1)";
        $timeframeLabel = 'This Month';
        break;
    case 'year':
        $dateFilter = "AND o.order_date >= ADD_MONTHS(SYSDATE, -12)";
        $timeframeLabel = 'This Year';
        break;
    case 'week':
    default:
        $dateFilter = "AND o.order_date >= SYSDATE - 7";
        $timeframeLabel = 'This Week';
        break;
}

// Fetch top products dynamically from Oracle
$topProducts = [];
if ($shop && isset($shop['SHOP_ID'])) {
    try {
        require_once __DIR__ . '/lib/oci_db.php';
        $conn = db_connect();
        
        if ($conn) {
            $sql = "SELECT p.product_name, SUM(oi.quantity) as total_sold
                    FROM \"ORDER\" o
                    JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                    JOIN PRODUCT p ON oi.product_id = p.product_id
                    WHERE p.shop_id = :shop_id
                    {$dateFilter}
                    GROUP BY p.product_name
                    ORDER BY total_sold DESC
                    FETCH FIRST 5 ROWS ONLY";
            
            $stmt = oci_parse($conn, $sql);
            if ($stmt) {
                oci_bind_by_name($stmt, ':shop_id', $shop['SHOP_ID'], -1, SQLT_INT);
                if (oci_execute($stmt)) {
                    while ($row = oci_fetch_assoc($stmt)) {
                        $topProducts[] = [
                            'product_name' => $row['PRODUCT_NAME'],
                            'sold_quantity' => (int)$row['TOTAL_SOLD']
                        ];
                    }
                }
                oci_free_statement($stmt);
            }
        }
    } catch (Exception $e) {
        $topProducts = array_slice($inventoryProducts, 0, 5);
    }
} else {
    $topProducts = array_slice($inventoryProducts, 0, 5);
}

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
        <?php if ($shop === null): ?>
            <p class="page-message page-message--error">No trader shop was found for this account.</p>
        <?php endif; ?>
    </div>

    <section class="trader-intro" aria-labelledby="trader-title">
        <div class="container trader-intro__inner">
            <div>
                <p class="trader-intro__eyebrow">Trader dashboard</p>
                <h1 id="trader-title"><?php echo e($shop['SHOP_NAME'] ?? 'Your shop overview'); ?></h1>
                <p class="trader-intro__sub"><?php echo e($shop['SHOP_DESCRIPTION'] ?? 'Track stock, sales, and refill alerts from one place.'); ?></p>
            </div>
            <div class="trader-intro__meta">
                <span><?php echo e($shop['EMAIL'] ?? 'Trader account'); ?></span>
            </div>
        </div>
    </section>

    <section class="trader-content">
        <div class="container trader-layout">
            <aside class="trader-sidebar" aria-label="Trader navigation">
                <a class="trader-sidebar__item is-active" href="trader-dashboard.php">Dashboard</a>
                <a class="trader-sidebar__item" href="trader-orders.php">Orders</a>
                <a class="trader-sidebar__item" href="trader-profile.php">Profile Settings</a>
                <a class="trader-sidebar__item" href="trader-add-product.php">Add Product</a>
                <a class="trader-sidebar__item" href="logout.php">Sign Out</a>
            </aside>

            <div class="trader-main">
                <section class="trader-stats" aria-label="Trader performance summary">
                    <article class="trader-stat-card">
                        <span class="trader-stat-card__label">Products sold</span>
                        <strong class="trader-stat-card__value"><?php echo e($metrics['sold_total']); ?></strong>
                    </article>
                    <article class="trader-stat-card">
                        <span class="trader-stat-card__label">Stock available</span>
                        <strong class="trader-stat-card__value"><?php echo e($metrics['stock_total']); ?></strong>
                    </article>
                    <article class="trader-stat-card">
                        <span class="trader-stat-card__label">Need refilling</span>
                        <strong class="trader-stat-card__value"><?php echo e($metrics['refill_count']); ?></strong>
                    </article>
                    <article class="trader-stat-card">
                        <span class="trader-stat-card__label">Live listings</span>
                        <strong class="trader-stat-card__value"><?php echo e($metrics['live_listings']); ?></strong>
                    </article>
                </section>

                <section class="trader-panel trader-panel--split">
                    <article class="trader-card trader-card--chart">
                        <div class="trader-card__header">
                            <div>
                                <p class="trader-card__eyebrow">Sales snapshot</p>
                                <h2 id="chart-title">Top products <?php echo strtolower($timeframeLabel); ?></h2>
                            </div>
                            
                            <!-- Timeframe Dropdown -->
                            <select class="trader-card__timeframe-select" id="timeframe-select" aria-label="Select timeframe">
                                <option value="week" <?php echo $timeframe === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo $timeframe === 'month' ? 'selected' : ''; ?>>This Month</option>
                                <option value="year" <?php echo $timeframe === 'year' ? 'selected' : ''; ?>>This Year</option>
                            </select>
                        </div>

                        <div class="trader-chart" aria-hidden="true">
                            <?php foreach ($topProducts as $product): ?>
                                <?php $height = max(20, min(140, ((int) $product['sold_quantity'] + 1) * 18)); ?>
                                <div class="trader-chart__bar" style="height: <?php echo e($height); ?>px;" title="<?php echo e($product['product_name']); ?>"></div>
                            <?php endforeach; ?>
                        </div>

                        <div class="trader-chart__labels">
                            <?php foreach ($topProducts as $product): ?>
                                <span><?php echo e($product['product_name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <aside class="trader-card trader-card--alerts">
                        <p class="trader-card__eyebrow">Refill alerts</p>
                        <h2>Products to restock</h2>
                        <div class="trader-alert-list">
                            <?php if ($lowStockProducts === []): ?>
                                <p class="trader-empty">No products need refilling right now.</p>
                            <?php else: ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <article class="trader-alert-item">
                                        <strong><?php echo e($product['PRODUCT_NAME']); ?></strong>
                                        <span>Stock: <?php echo e($product['STOCK_QUANTITY']); ?></span>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </aside>
                </section>

                <section class="trader-card trader-card--table">
                    <div class="trader-card__header">
                        <div>
                            <p class="trader-card__eyebrow">Inventory view</p>
                            <h2>Products sold and refill status</h2>
                        </div>
                        <a class="trader-link" href="trader-add-product.php">Add new product</a>
                    </div>

                    <div class="trader-table-wrap">
                        <table class="trader-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Refill</th>
                                    <th>Discount %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventoryProducts as $product): ?>
                                    <tr>
                                        <td><?php echo e($product['product_name']); ?></td>
                                        <td><?php echo e($product['sold_quantity']); ?></td>
                                        <td><?php echo e($product['stock_quantity']); ?></td>
                                        <td><?php echo e($product['product_status']); ?></td>
                                        <td><?php echo e(((int) $product['stock_quantity'] < 10) ? 'Yes' : 'No'); ?></td>
                                        <td>
                                            <form method="post" action="trader-dashboard.php" style="display:inline-flex; gap:0.25rem;">
                                                <input type="hidden" name="action" value="update_discount" />
                                                <input type="hidden" name="product_id" value="<?php echo e($product['product_id']); ?>" />
                                                <input type="number" name="discount_percentage" style="width: 60px; padding: 0.25rem;" min="0" max="100" value="<?php echo e($product['discount_percentage'] ?? ''); ?>" placeholder="%" />
                                                <select name="discount_duration" style="width: 85px; padding: 0.25rem; font-size: 0.8rem;">
                                                    <option value="1">1 Day</option>
                                                    <option value="3">3 Days</option>
                                                    <option value="5">5 Days</option>
                                                    <option value="10">10 Days</option>
                                                    <option value="20">20 Days</option>
                                                    <option value="30" selected>1 Month</option>
                                                </select>
                                                <button type="submit" class="button button--secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Set</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>

<script>
    /**
     * Handle timeframe dropdown change
     * Reloads the page with the selected timeframe as a GET parameter
     */
    document.addEventListener('DOMContentLoaded', function() {
        const timeframeSelect = document.getElementById('timeframe-select');
        
        if (timeframeSelect) {
            timeframeSelect.addEventListener('change', function(e) {
                const selectedTimeframe = e.target.value;
                
                // Get current URL and update the timeframe parameter
                const url = new URL(window.location);
                url.searchParams.set('timeframe', selectedTimeframe);
                
                // Reload the page with the new parameter
                window.location.href = url.toString();
            });
        }
    });
</script>

<style>
    .trader-card__timeframe-select {
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(26, 26, 26, 0.2);
        border-radius: 0.5rem;
        background: white;
        color: #1a1a1a;
        font-size: 0.9rem;
        cursor: pointer;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    
    .trader-card__timeframe-select:hover,
    .trader-card__timeframe-select:focus {
        border-color: #6a8861;
        box-shadow: 0 0 0 2px rgba(106, 136, 97, 0.1);
        outline: none;
    }
</style>
