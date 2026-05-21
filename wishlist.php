<?php
$pageTitle = 'Your Wishlist | Cleck E-Mart';
$metaDescription = 'View and manage your saved products.';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/wishlist_helpers.php';
require_once __DIR__ . '/lib/cart_helpers.php';

require_login(['CUSTOMER']);
$customerId = (int) current_customer_id();

$success = get_flash('success');
$error = get_flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($productId > 0) {
        try {
            if ($action === 'remove') {
                remove_from_wishlist($customerId, $productId);
                set_flash('success', 'Item removed from wishlist.');
            } elseif ($action === 'move_to_cart') {
                add_product_to_cart($customerId, $productId, 1);
                remove_from_wishlist($customerId, $productId);
                set_flash('success', 'Item moved to your basket.');
            }
        } catch (Throwable $e) {
            set_flash('error', $e->getMessage());
        }
    }
    redirect('wishlist.php');
}

$items = get_wishlist_items($customerId);

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="cart-page">
    <section class="cart-hero" aria-labelledby="wishlist-title">
        <div class="container">
            <div class="cart-hero__title-wrap">
                <h1 id="wishlist-title">Your Wishlist</h1>
            </div>
        </div>
    </section>

    <section class="cart-content" aria-label="Wishlist contents">
        <div class="container">
            <?php if ($success !== null): ?>
                <p class="page-message page-message--success"><?php echo e($success); ?></p>
            <?php endif; ?>

            <?php if ($error !== null): ?>
                <p class="page-message page-message--error"><?php echo e($error); ?></p>
            <?php endif; ?>

            <div class="cart-layout">
                <div class="cart-items" aria-live="polite" style="width: 100%;">
                    <?php if ($items === []): ?>
                        <article class="cart-item">
                            <div class="cart-item__details">
                                <h2 class="cart-item__title">Your wishlist is empty</h2>
                                <p>Save items you like by clicking the heart icon on products.</p>
                                <a class="cart-summary__button button" href="category.php" style="display: inline-block; width: auto; margin-top: 1rem;">Browse Products</a>
                            </div>
                        </article>
                    <?php endif; ?>

                    <?php foreach ($items as $item): ?>
                        <?php
                        $productId = (int) $item['PRODUCT_ID'];
                        $name = (string) $item['PRODUCT_NAME'];
                        $unitPrice = (float) $item['PRICE'];
                        $shop = (string) $item['SHOP_NAME'];
                        $stockQty = (int) $item['STOCK_QUANTITY'];
                        $discount = (float) ($item['DISCOUNT_PERCENTAGE'] ?? 0);
                        
                        $image = trim((string) $item['PRODUCT_IMAGE']);
                        if ($image === '') {
                            $image = 'assets/images/icons/product-placeholder.svg';
                        } elseif (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://') && !str_starts_with($image, 'assets/')) {
                            $image = 'assets/images/products/' . ltrim($image, '/');
                        }
                        if (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
                            $absoluteImage = __DIR__ . '/' . $image;
                            if (!file_exists($absoluteImage)) {
                                $image = 'assets/images/icons/product-placeholder.svg';
                            }
                        }
                        
                        $isAvailable = $stockQty > 0 && strtoupper((string) $item['PRODUCT_STATUS']) !== 'OUT_OF_STOCK';
                        ?>
                        <article class="cart-item" data-product-id="<?php echo e($productId); ?>">
                            <div class="cart-item__media">
                                <a href="product.php?id=<?php echo e($productId); ?>">
                                    <img src="<?php echo e($image); ?>" alt="<?php echo e($name); ?>" />
                                </a>
                            </div>

                            <div class="cart-item__details">
                                <p class="cart-item__trader">Trader: <?php echo e($shop); ?></p>
                                <h2 class="cart-item__title"><a href="product.php?id=<?php echo e($productId); ?>" style="color: inherit; text-decoration: none;"><?php echo e($name); ?></a></h2>
                                
                                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: <?php echo $isAvailable ? '#22c55e' : '#ef4444'; ?>">
                                    <?php echo $isAvailable ? 'In Stock' : 'Out of Stock'; ?>
                                </p>
                            </div>

                            <div class="cart-item__price" aria-label="Line total for <?php echo e($name); ?>" style="text-align: right;">
                                <?php if ($discount > 0): ?>
                                    <del style="display:block; color:#999; font-size:0.9rem;">£<?php echo e(number_format($unitPrice, 2)); ?></del>
                                    <span style="font-weight: 700;">£<?php echo e(number_format($unitPrice * (1 - $discount / 100), 2)); ?></span>
                                <?php else: ?>
                                    <span style="font-weight: 700;">£<?php echo e(number_format($unitPrice, 2)); ?></span>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; gap: 0.5rem; margin-left: auto;">
                                <form method="post" action="wishlist.php" style="margin: 0;">
                                    <input type="hidden" name="product_id" value="<?php echo e($productId); ?>" />
                                    <button class="button" type="submit" name="action" value="remove" style="background: white; color: #ef4444; border: 1px solid #ef4444;">Remove</button>
                                </form>
                                <?php if ($isAvailable): ?>
                                <form method="post" action="wishlist.php" style="margin: 0;">
                                    <input type="hidden" name="product_id" value="<?php echo e($productId); ?>" />
                                    <button class="button" type="submit" name="action" value="move_to_cart">Move to Basket</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
