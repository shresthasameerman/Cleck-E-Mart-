<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$userId = (int) current_user_id();
$shops = trader_get_shops($userId);
$maxShops = 2;
$shopCount = count($shops);
$categories = trader_categories();

$successMessage = get_flash('success');
$errorMessage = get_flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_shop') {
        if ($shopCount >= $maxShops) {
            set_flash('error', 'You have reached the maximum number of shops (2).');
        } else {
            try {
                $logoFilename = '';
                if (isset($_FILES['shop_logo']) && $_FILES['shop_logo']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['shop_logo']['tmp_name'];
                    $name = basename($_FILES['shop_logo']['name']);
                    $logoFilename = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $name);
                    $targetDir = __DIR__ . '/assets/Shop Logos/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    if (!move_uploaded_file($tmpName, $targetDir . $logoFilename)) {
                        throw new Exception("Failed to upload shop logo.");
                    }
                }

                trader_create_shop($userId, [
                    'shop_name' => (string) ($_POST['shop_name'] ?? ''),
                    'shop_description' => (string) ($_POST['shop_description'] ?? ''),
                    'shop_logo' => $logoFilename
                ]);
                set_flash('success', 'Shop added successfully and is pending admin approval.');
            } catch (Throwable $e) {
                set_flash('error', $e->getMessage());
            }
        }
    }
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

    <div class="container">
        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Trader Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Manage your shops or add a new one.</p>
                    <p style="margin-top: 1rem; opacity: 0.8; font-size: 0.9rem;">Shops: <?php echo $shopCount; ?>/<?php echo $maxShops; ?></p>
                </div>

                <div class="admin-tabs">
                    <a href="trader-profile.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        My Profile
                    </a>
                    <a href="trader-shops.php" class="tab-button active">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        My Shop
                    </a>
                    <a href="trader-orders.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        All Orders
                    </a>
                    <a href="trader-sales.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                        All Sales
                    </a>
                    <a href="logout.php" class="tab-button" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Sign Out
                    </a>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">
                <section class="admin-section" style="margin-bottom: 2rem;">
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
                                    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <strong>Status:</strong> 
                                        <?php if (($shop['SHOP_STATUS'] ?? '') === 'PENDING_APPROVAL' || ($shop['SHOP_STATUS'] ?? '') === 'PENDING'): ?>
                                            <span style="color: orange; font-weight: 500;">Pending Admin Approval</span>
                                        <?php elseif (($shop['SHOP_STATUS'] ?? '') === 'REJECTED'): ?>
                                            <span style="color: red; font-weight: 500;">Rejected</span>
                                        <?php elseif (($shop['SHOP_STATUS'] ?? '') === 'SUSPEND' || ($shop['SHOP_STATUS'] ?? '') === 'SUSPENDED'): ?>
                                            <span style="color: red; font-weight: 500;">Suspended</span>
                                        <?php else: ?>
                                            <span style="color: green; font-weight: 500;">Active</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="trader-dashboard.php?shop_id=<?php echo e($shop['SHOP_ID']); ?>" class="button" style="display: block; text-align: center; width: 100%; box-sizing: border-box;">View Shop Dashboard</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if ($shopCount < $maxShops): ?>
                <section class="admin-section">
                    <div class="trader-card__header">
                        <h2>Add a New Shop</h2>
                    </div>
                    <form method="post" action="trader-shops.php" class="trader-form" enctype="multipart/form-data">
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
                                <span>Shop Logo</span>
                                <div class="file-upload-wrapper">
                                    <button type="button" class="button file-upload-btn" onclick="document.getElementById('shop_logo_upload').click();">Browse files</button>
                                    <span id="shop_logo_name" class="file-upload-name">No file selected</span>
                                    <input type="file" id="shop_logo_upload" name="shop_logo" accept="image/jpeg,image/png,image/webp,image/gif" onchange="document.getElementById('shop_logo_name').textContent = this.files[0] ? this.files[0].name : 'No file selected';" />
                                </div>
                                <small style="display: block; margin-top: 0.5rem; color: #666;">Supported formats: JPG, PNG, WebP, GIF. Max size: 5MB</small>
                            </label>
                        </div>
                        <button type="submit" class="button" style="margin-top: 1rem;">Register Shop</button>
                    </form>
                </section>
                <?php else: ?>
                <section class="admin-section">
                    <p style="color: #666; text-align: center; padding: 2rem;">You have reached the maximum limit of <?php echo $maxShops; ?> shops.</p>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
