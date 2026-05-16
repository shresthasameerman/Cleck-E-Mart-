<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';

require_login(['CUSTOMER']);

$pageTitle = 'Your Basket | Cleck E-Mart';
$metaDescription = 'Review your basket, update item quantities, and choose a collection slot';

$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$customerId = (int) current_customer_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    try {
        if ($productId === false || $productId === null) {
            throw new RuntimeException('Invalid cart item selected.');
        }

        $quantity = $quantity !== false && $quantity !== null ? (int) $quantity : 1;
        
        // Try APEX API first if enabled
        $updateSuccess = false;
        if (apex_cart_enabled()) {
            try {
                $updateSuccess = apex_update_cart_quantity($customerId, $productId, $quantity);
            } catch (Throwable $e) {
                error_log('APEX cart update failed: ' . $e->getMessage());
                // Fall back to local
            }
        }
        
        // Fall back to local if APEX didn't work
        if (!$updateSuccess) {
            update_cart_item_quantity($customerId, $productId, $quantity);
        }
        
        set_flash('success', 'Basket updated successfully.');
    } catch (Throwable $exception) {
        set_flash('error', 'Unable to update basket: ' . $exception->getMessage());
    }

    redirect('cart.php');
}

$items = [];
$apiError = null;

try {
    // Try APEX API first if enabled
    if (apex_cart_enabled()) {
        try {
            $items = apex_get_cart_items($customerId);
        } catch (Throwable $e) {
            error_log('APEX cart fetch failed: ' . $e->getMessage());
            $apiError = 'Cart data may be outdated';
            // Fall back to local
            $items = get_cart_items_for_customer($customerId);
        }
    } else {
        $items = get_cart_items_for_customer($customerId);
    }
} catch (Throwable $exception) {
    $flashError = 'Unable to load basket: ' . $exception->getMessage();
}

$total = apex_cart_enabled() ? apex_cart_total($items) : cart_total($items);

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="cart-page">
    <section class="cart-hero" aria-labelledby="cart-title">
        <div class="container">
            <div class="cart-hero__title-wrap">
                <h1 id="cart-title">Your Basket</h1>
            </div>
        </div>
    </section>

    <section class="cart-content" aria-label="Basket contents">
        <div class="container">
            <?php if ($flashSuccess !== null): ?>
                <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
            <?php endif; ?>

            <?php if ($flashError !== null): ?>
                <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
            <?php endif; ?>

            <div class="cart-layout">

            <div class="cart-items" aria-live="polite">
                <?php if ($items === []): ?>
                    <article class="cart-item">
                        <div class="cart-item__details">
                            <h2 class="cart-item__title">Your basket is empty</h2>
                            <p>Browse the category page to add products.</p>
                            <a class="cart-summary__button button" href="category.php">Browse Products</a>
                        </div>
                    </article>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    // Handle both local DB (uppercase) and APEX API (lowercase) response formats
                    $productId = (int) ($item['product_id'] ?? $item['PRODUCT_ID'] ?? 0);
                    $name = (string) ($item['product_name'] ?? $item['PRODUCT_NAME'] ?? 'Unknown');
                    $qty = (int) ($item['quantity'] ?? $item['QUANTITY'] ?? 0);
                    $unit = (float) ($item['price'] ?? $item['UNIT_PRICE'] ?? 0);
                    $shop = (string) ($item['shop_name'] ?? $item['SHOP_NAME'] ?? 'Unknown Shop');
                    
                    // Calculate line total
                    $discount = (float) ($item['discount_percentage'] ?? 0);
                    if ($discount > 0) {
                        $discounted = $unit * (1 - $discount / 100);
                        $lineTotal = $discounted * $qty;
                    } else {
                        $lineTotal = $qty * $unit;
                    }
                    
                    // Handle image
                    $image = trim((string) ($item['product_image'] ?? $item['PRODUCT_IMAGE'] ?? ''));
                    if ($image === '') {
                        $image = 'assets/images/product-placeholder.svg';
                    } elseif (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://') && !str_starts_with($image, 'assets/')) {
                        $image = 'assets/images/products/' . ltrim($image, '/');
                    }
                    if (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
                        $absoluteImage = __DIR__ . '/' . $image;
                        if (!file_exists($absoluteImage)) {
                            $image = 'assets/images/product-placeholder.svg';
                        }
                    }
                    ?>
                    <article class="cart-item" data-product-id="<?php echo e($productId); ?>">
                        <div class="cart-item__media">
                            <img src="<?php echo e($image); ?>" alt="<?php echo e($name); ?>" />
                        </div>

                        <div class="cart-item__details">
                            <p class="cart-item__trader">Trader: <?php echo e($shop); ?></p>
                            <h2 class="cart-item__title"><?php echo e($name); ?></h2>
                        </div>

                        <form class="cart-item__controls" method="post" action="cart.php" aria-label="Quantity controls for <?php echo e($name); ?>" data-cart-form>
                            <input type="hidden" name="product_id" value="<?php echo e($productId); ?>" />
                            <button class="cart-qty-button" type="submit" name="quantity" value="<?php echo e(max(0, $qty - 1)); ?>" aria-label="Decrease quantity for <?php echo e($name); ?>" data-qty-btn="decrease">-</button>
                            <span class="cart-qty-value" data-qty-display><?php echo e($qty); ?></span>
                            <button class="cart-qty-button" type="submit" name="quantity" value="<?php echo e($qty + 1); ?>" aria-label="Increase quantity for <?php echo e($name); ?>" data-qty-btn="increase">+</button>
                        </form>

                        <div class="cart-item__price" aria-label="Line total for <?php echo e($name); ?>">
                            <?php if ($discount > 0): ?>
                                <del>£<?php echo e(number_format($unit * $qty, 2)); ?></del>
                                <span>£<?php echo e(number_format($lineTotal, 2)); ?></span>
                            <?php else: ?>
                                <span>£<?php echo e(number_format($lineTotal, 2)); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary" aria-label="Order summary">
                <h2 class="cart-summary__title">Order Summary</h2>

                <div class="cart-summary__items">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $name = (string) ($item['product_name'] ?? $item['PRODUCT_NAME'] ?? 'Unknown');
                        $qty = (int) ($item['quantity'] ?? $item['QUANTITY'] ?? 0);
                        $unit = (float) ($item['price'] ?? $item['UNIT_PRICE'] ?? 0);
                        $discount = (float) ($item['discount_percentage'] ?? 0);
                        
                        if ($discount > 0) {
                            $discounted = $unit * (1 - $discount / 100);
                            $lineTotal = $discounted * $qty;
                        } else {
                            $lineTotal = $qty * $unit;
                        }
                        ?>
                        <p class="cart-summary__line"><?php echo e($name); ?> x<?php echo e($qty); ?> £<?php echo e(number_format($lineTotal, 2)); ?></p>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary__divider" aria-hidden="true"></div>

                <p class="cart-summary__total">
                    <span>Total</span>
                    <strong>£<?php echo e(number_format($total, 2)); ?></strong>
                </p>

                <?php if ($items !== []): ?>
                    <a class="cart-summary__button button" href="collection.php">Choose Your Collection Slot</a>
                <?php endif; ?>
            </aside>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
