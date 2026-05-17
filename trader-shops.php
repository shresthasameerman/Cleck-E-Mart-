<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$shops = trader_get_shops($userId);
$maxShops = 2;
$shopCount = count($shops);

$successMessage = get_flash('success');
$errorMessage = get_flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_shop') {
    if ($shopCount >= $maxShops) {
        set_flash('error', 'You have reached the maximum number of shops (2).');
    } else {
        try {
            trader_create_shop($userId, [
                'shop_name' => (string) ($_POST['shop_name'] ?? ''),
                'shop_description' => (string) ($_POST['shop_description'] ?? ''),
                'shop_logo' => (string) ($_POST['shop_logo'] ?? ''),
                'shop_location' => (string) ($_POST['shop_location'] ?? ''),
                'shop_pan' => (string) ($_POST['shop_pan'] ?? ''),
                'shop_products_type' => (string) ($_POST['shop_products_type'] ?? '')
            ]);
            set_flash('success', 'Shop added successfully and is pending admin approval.');
        } catch (Throwable $e) {
            set_flash('error', $e->getMessage());
        }
    }
    redirect('trader-shops.php');
}

$pageTitle = 'My Shops | Cleck E-Mart';
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

    <section class="trader-intro">
        <div class="container trader-intro__inner">
            <div>
                <p class="trader-intro__eyebrow">Trader dashboard</p>
                <h1>My Shops</h1>
                <p class="trader-intro__sub">Manage your shops or add a new one.</p>
            </div>
            <div class="trader-intro__meta">
                <span>Shops: <?php echo $shopCount; ?>/<?php echo $maxShops; ?></span>
            </div>
        </div>
    </section>

    <section class="trader-content">
        <div class="container trader-layout">
            <aside class="trader-sidebar" aria-label="Trader navigation">
                <a class="trader-sidebar__item is-active" href="trader-shops.php">My Shops</a>
                <a class="trader-sidebar__item" href="trader-orders.php">Total Orders</a>
                <a class="trader-sidebar__item" href="trader-profile.php">Profile Settings</a>
                <a class="trader-sidebar__item" href="logout.php">Sign Out</a>
            </aside>

            <div class="trader-main">
                <section class="trader-card" style="margin-bottom: 2rem;">
                    <div class="trader-card__header">
                        <h2>Your Shops</h2>
                    </div>
                    <?php if (empty($shops)): ?>
                        <p>You don't have any shops yet.</p>
                    <?php else: ?>
                        <div class="shop-list" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                            <?php foreach ($shops as $shop): ?>
                                <article class="shop-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 8px;">
                                    <h3><?php echo e($shop['SHOP_NAME']); ?></h3>
                                    <p style="color: #666; margin-bottom: 1rem;"><?php echo e($shop['SHOP_DESCRIPTION'] ?? 'No description'); ?></p>
                                    <p style="margin-bottom: 1rem;">
                                        <strong>Status:</strong> 
                                        <?php if (($shop['SHOP_STATUS'] ?? 'ACTIVE') === 'ACTIVE'): ?>
                                            <span style="color: green;">Verified</span>
                                        <?php else: ?>
                                            <span style="color: orange;"><?php echo e($shop['SHOP_STATUS']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <a href="trader-dashboard.php?shop_id=<?php echo e($shop['SHOP_ID']); ?>" class="button">View Shop Dashboard</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if ($shopCount < $maxShops): ?>
                <section class="trader-card">
                    <div class="trader-card__header">
                        <h2>Add a New Shop</h2>
                    </div>
                    <form method="post" action="trader-shops.php" class="trader-form">
                        <input type="hidden" name="action" value="add_shop" />
                        <div class="trader-form__grid">
                            <label class="trader-form__full">
                                <span>Shop Name</span>
                                <input type="text" name="shop_name" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Description</span>
                                <textarea name="shop_description" rows="3" required></textarea>
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Location</span>
                                <input type="text" name="shop_location" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop PAN Number</span>
                                <input type="text" name="shop_pan" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Types of Products to Sell</span>
                                <input type="text" name="shop_products_type" placeholder="e.g. Electronics, Clothing" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Logo Filename (Optional)</span>
                                <input type="text" name="shop_logo" placeholder="logo.png" />
                            </label>
                        </div>
                        <button type="submit" class="button" style="margin-top: 1rem;">Register Shop</button>
                    </form>
                </section>
                <?php else: ?>
                <section class="trader-card">
                    <p style="color: #666; text-align: center; padding: 2rem;">You have reached the maximum limit of <?php echo $maxShops; ?> shops.</p>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
