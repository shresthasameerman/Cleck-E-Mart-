<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$errors = [];
$successMessage = get_flash('success');
$userId = (int) current_user_id();
$shopId = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : null;
$shop = trader_shop_for_user($userId, $shopId);
$categories = trader_categories();
$metrics = trader_dashboard_metrics($userId);

// Check trader verification status
$traderStatus = trader_verification_status($userId);
$isVerified = trader_is_verified($userId);

if (!$isVerified) {
    $errors[] = 'Your trader account is pending admin verification. You will be able to add products once your account has been verified.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['product_action'] ?? '') === 'save_product') {
    if (!$isVerified) {
        $errors[] = 'Cannot add products. Please wait for admin verification of your trader account.';
    } else {
        try {
            // Handle image upload
            $productImage = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['name'] !== '') {
                $productImage = trader_handle_product_image_upload($_FILES['product_image']);
            }

            trader_create_product($userId, [
                'product_name' => (string) ($_POST['product_name'] ?? ''),
                'product_description' => (string) ($_POST['product_description'] ?? ''),
                'category_id' => (int) ($_POST['category_id'] ?? 0),
                'price' => (float) ($_POST['price'] ?? 0),
                'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
                'max_order' => (string) ($_POST['max_order'] ?? ''),
                'allergy_information' => (string) ($_POST['allergy_information'] ?? ''),
                'product_image' => $productImage ?? '',
                'visibility' => (string) ($_POST['visibility'] ?? 'PUBLISH'),
                'shop_id' => isset($_POST['shop_id']) && $_POST['shop_id'] ? (int) $_POST['shop_id'] : null,
            ]);

            set_flash('success', 'Product saved successfully.');
            redirect('trader-add-product.php');
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$pageTitle = 'Add Product | Cleck E-Mart';
$metaDescription = 'Add a new product for your trader shop.';

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="trader-page">
    <div class="container">
        <?php if ($successMessage !== null): ?>
            <p class="page-message page-message--success"><?php echo e($successMessage); ?></p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="page-message page-message--error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <section class="trader-intro" aria-labelledby="add-product-title">
        <div class="container trader-intro__inner">
            <div>
                <p class="trader-intro__eyebrow">Add product</p>
                <h1 id="add-product-title"><?php echo e($shop['SHOP_NAME'] ?? 'Your shop'); ?></h1>
                <p class="trader-intro__sub">Create a new listing, manage stock levels, and mark items for refill before they run out.</p>
            </div>
            <div class="trader-intro__meta">
                <span><?php echo e($metrics['refill_count']); ?> refill alerts</span>
            </div>
        </div>
    </section>

    <section class="trader-content">
        <div class="container trader-layout">
            <aside class="trader-sidebar" aria-label="Trader navigation">
                <a class="trader-sidebar__item" href="trader-shops.php">My Shops</a>
                <a class="trader-sidebar__item" href="trader-dashboard.php">Dashboard</a>
                <a class="trader-sidebar__item" href="trader-orders.php">Orders</a>
                <a class="trader-sidebar__item" href="trader-profile.php">Profile Settings</a>
                <a class="trader-sidebar__item is-active" href="trader-add-product.php<?php echo isset($_GET['shop_id']) ? '?shop_id=' . (int)$_GET['shop_id'] : ''; ?>">Add Product</a>
                <a class="trader-sidebar__item" href="logout.php">Sign Out</a>
            </aside>

            <div class="trader-main">
                <section class="trader-card">
                    <div class="trader-card__header">
                        <div>
                            <p class="trader-card__eyebrow">Product information</p>
                            <h2>New product listing</h2>
                        </div>
                        <span class="trader-card__badge">Draft ready</span>
                    </div>

                    <?php if (!$isVerified): ?>
                        <div style="padding: 2rem; text-align: center;">
                            <p style="font-size: 1.1rem; margin-bottom: 1rem;">Your trader account is currently pending admin verification.</p>
                            <p style="color: #666;">Once your account has been verified by an administrator, you will be able to add products to the platform.</p>
                            <p style="margin-top: 1.5rem; color: #999; font-size: 0.9rem;">Check back soon, or contact support for more information.</p>
                        </div>
                    <?php else: ?>
                    <form class="trader-form" method="post" action="trader-add-product.php<?php echo isset($_GET['shop_id']) ? '?shop_id=' . (int)$_GET['shop_id'] : ''; ?>" enctype="multipart/form-data">
                        <input type="hidden" name="product_action" value="save_product" />
                        <input type="hidden" name="shop_id" value="<?php echo isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : ''; ?>" />
                        <div class="trader-form__grid">
                            <label class="trader-form__full">
                                <span>Product name</span>
                                <input type="text" name="product_name" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Description</span>
                                <textarea name="product_description" rows="4" required></textarea>
                            </label>
                            <label>
                                <span>Category</span>
                                <select name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo e($category['CATEGORY_ID']); ?>"><?php echo e($category['CATEGORY_NAME']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Price</span>
                                <input type="number" name="price" min="0" step="0.01" required />
                            </label>
                            <label>
                                <span>Stock available</span>
                                <input type="number" name="stock_quantity" min="0" step="1" required />
                            </label>
                            <label>
                                <span>Max per order</span>
                                <input type="number" name="max_order" min="1" step="1" />
                            </label>
                            <label class="trader-form__full">
                                <span>Dietary / allergen notes</span>
                                <input type="text" name="allergy_information" placeholder="Example: Gluten, Dairy" />
                            </label>
                            <label class="trader-form__full">
                                <span>Product image</span>
                                <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp,image/gif" />
                                <small style="display: block; margin-top: 0.5rem; color: #666;">Supported formats: JPG, PNG, WebP, GIF. Max size: 5MB</small>
                            </label>
                            <label>
                                <span>Visibility</span>
                                <select name="visibility">
                                    <option value="PUBLISH">Publish now</option>
                                    <option value="DRAFT">Save as draft</option>
                                </select>
                            </label>
                            <label>
                                <span>Refill target</span>
                                <input type="text" value="10 items or less" readonly />
                            </label>
                        </div>

                        <div class="trader-form__actions">
                            <button class="trader-submit trader-submit--secondary" type="submit" name="visibility" value="DRAFT">Save as Draft</button>
                            <button class="trader-submit" type="submit" name="visibility" value="PUBLISH">Publish Product</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
