<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$shopId = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : null;

if (!$shopId) {
    redirect('trader-shops.php');
}

$shop = trader_shop_for_user($userId, $shopId);

if ($shop === null) {
    redirect('trader-shops.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_shop') {
    try {
        trader_update_shop($userId, $shopId, [
            'shop_name' => (string) ($_POST['shop_name'] ?? ''),
            'shop_description' => (string) ($_POST['shop_description'] ?? ''),
            'shop_location' => (string) ($_POST['shop_location'] ?? ''),
            'shop_pan' => (string) ($_POST['shop_pan'] ?? ''),
            'shop_products_type' => (string) ($_POST['shop_products_type'] ?? ''),
            'shop_logo' => (string) ($_POST['shop_logo'] ?? ''),
        ]);
        set_flash('success', 'Shop details updated successfully.');
        redirect("trader-shop-profile.php?shop_id=$shopId");
    } catch (Throwable $e) {
        set_flash('error', $e->getMessage());
    }
}

$successMessage = get_flash('success');
$errorMessage = get_flash('error');

$pageTitle = 'Shop Profile | Cleck E-Mart';
$metaDescription = 'Update your shop details.';

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

        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Shop Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Manage settings for <?php echo e($shop['SHOP_NAME'] ?? 'your shop'); ?>.</p>
                </div>

                <div class="admin-tabs">
                    <a href="trader-shops.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Back to My Shops
                    </a>
                    <hr style="border-top: 1px solid rgba(0,0,0,0.1); margin: 0.5rem 0; width: 100%;">
                    <a href="trader-shop-profile.php?shop_id=<?php echo $shopId; ?>" class="tab-button active">
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
                    <a href="trader-add-product.php?shop_id=<?php echo $shopId; ?>" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add Products
                    </a>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">
                <section class="admin-section">
                    <div class="trader-card__header">
                        <div>
                            <p class="trader-card__eyebrow">Shop Information</p>
                            <h2>Update Shop Details</h2>
                        </div>
                    </div>
                    
                    <form method="post" action="trader-shop-profile.php?shop_id=<?php echo $shopId; ?>" class="trader-form">
                        <input type="hidden" name="action" value="update_shop" />
                        <div class="trader-form__grid">
                            <label class="trader-form__full">
                                <span>Shop Name</span>
                                <input type="text" name="shop_name" value="<?php echo e($shop['SHOP_NAME'] ?? ''); ?>" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Description</span>
                                <textarea name="shop_description" rows="3" required><?php echo e($shop['SHOP_DESCRIPTION'] ?? ''); ?></textarea>
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Location</span>
                                <input type="text" name="shop_location" value="<?php echo e($shop['SHOP_LOCATION'] ?? ''); ?>" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop PAN Number</span>
                                <input type="text" name="shop_pan" value="<?php echo e($shop['SHOP_PAN'] ?? ''); ?>" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Types of Products to Sell</span>
                                <input type="text" name="shop_products_type" value="<?php echo e($shop['SHOP_PRODUCTS_TYPE'] ?? ''); ?>" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop Logo Filename (Optional)</span>
                                <input type="text" name="shop_logo" value="<?php echo e($shop['SHOP_LOGO'] ?? ''); ?>" placeholder="logo.png" />
                            </label>
                        </div>
                        <div class="trader-form__actions">
                            <button type="submit" class="button">Save Changes</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
