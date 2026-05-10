<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$metrics = trader_dashboard_metrics($userId);
$shop = $metrics['shop'];
$products = $metrics['products'];
$topProducts = $metrics['top_products'];
$lowStockProducts = $metrics['low_stock_products'];
$pageTitle = 'Trader Dashboard | Cleck E-Mart';
$metaDescription = 'Trader dashboard for reviewing sales, stock, and refill alerts.';

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="trader-page">
    <div class="container">
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
                        <strong class="trader-stat-card__value"><?php echo e(count($products)); ?></strong>
                    </article>
                </section>

                <section class="trader-panel trader-panel--split">
                    <article class="trader-card trader-card--chart">
                        <div class="trader-card__header">
                            <div>
                                <p class="trader-card__eyebrow">Sales snapshot</p>
                                <h2>Top products this week</h2>
                            </div>
                            <span class="trader-card__badge">This week</span>
                        </div>

                        <div class="trader-chart" aria-hidden="true">
                            <?php foreach ($topProducts as $product): ?>
                                <?php $height = max(20, min(140, ((int) $product['sold_quantity'] + 1) * 18)); ?>
                                <div class="trader-chart__bar" style="height: <?php echo e($height); ?>px;"></div>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo e($product['product_name']); ?></td>
                                        <td><?php echo e($product['sold_quantity']); ?></td>
                                        <td><?php echo e($product['stock_quantity']); ?></td>
                                        <td><?php echo e($product['product_status']); ?></td>
                                        <td><?php echo e($product['needs_refill'] ? 'Yes' : 'No'); ?></td>
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
