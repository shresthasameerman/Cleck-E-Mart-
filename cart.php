<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';

$pageTitle = 'Your Basket | Cleck E-Mart';
$metaDescription = 'Review your basket, update item quantities, and choose a collection slot.';

if (!is_logged_in()) {
    set_flash('error', 'Please login to view your basket.');
    redirect('auth.php?mode=login');
}

if (current_role() !== 'CUSTOMER' || current_customer_id() === null) {
    set_flash('error', 'Only customer accounts can manage baskets.');
    redirect('index.php');
}

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
        update_cart_item_quantity($customerId, (int) $productId, $quantity);
        set_flash('success', 'Basket updated successfully.');
    } catch (Throwable $exception) {
        set_flash('error', 'Unable to update basket: ' . $exception->getMessage());
    }

    redirect('cart.php');
}

$items = [];

try {
    $items = get_cart_items_for_customer($customerId);
} catch (Throwable $exception) {
    $flashError = 'Unable to load basket: ' . $exception->getMessage();
}

$total = cart_total($items);

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
                    $name = (string) $item['PRODUCT_NAME'];
                    $qty = (int) $item['QUANTITY'];
                    $unit = (float) $item['UNIT_PRICE'];
                    $lineTotal = $qty * $unit;
                    $image = trim((string) ($item['PRODUCT_IMAGE'] ?? ''));
                    if ($image === '') {
                        $image = 'assets/images/product-placeholder.svg';
                    } elseif (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://') && !str_starts_with($image, 'assets/')) {
                        $image = 'assets/images/' . ltrim($image, '/');
                    }
                    if (!str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
                        $absoluteImage = __DIR__ . '/' . $image;
                        if (!file_exists($absoluteImage)) {
                            $image = 'assets/images/product-placeholder.svg';
                        }
                    }
                    ?>
                    <article class="cart-item">
                        <div class="cart-item__media">
                            <img src="<?php echo e($image); ?>" alt="<?php echo e($name); ?>" />
                        </div>

                        <div class="cart-item__details">
                            <p class="cart-item__trader">Trader: <?php echo e($item['SHOP_NAME']); ?></p>
                            <h2 class="cart-item__title"><?php echo e($name); ?></h2>
                        </div>

                        <form class="cart-item__controls" method="post" action="cart.php" aria-label="Quantity controls for <?php echo e($name); ?>">
                            <input type="hidden" name="product_id" value="<?php echo e($item['PRODUCT_ID']); ?>" />
                            <button class="cart-qty-button" type="submit" name="quantity" value="<?php echo e(max(0, $qty - 1)); ?>" aria-label="Decrease quantity for <?php echo e($name); ?>">-</button>
                            <span class="cart-qty-value"><?php echo e($qty); ?></span>
                            <button class="cart-qty-button" type="submit" name="quantity" value="<?php echo e($qty + 1); ?>" aria-label="Increase quantity for <?php echo e($name); ?>">+</button>
                        </form>

                        <div class="cart-item__price" aria-label="Line total for <?php echo e($name); ?>">$<?php echo e(number_format($lineTotal, 2)); ?></div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary" aria-label="Order summary">
                <h2 class="cart-summary__title">Order Summary</h2>

                <div class="cart-summary__items">
                    <?php foreach ($items as $item): ?>
                        <?php $lineTotal = ((int) $item['QUANTITY']) * ((float) $item['UNIT_PRICE']); ?>
                        <p class="cart-summary__line"><?php echo e($item['PRODUCT_NAME']); ?> x<?php echo e($item['QUANTITY']); ?> $<?php echo e(number_format($lineTotal, 2)); ?></p>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary__divider" aria-hidden="true"></div>

                <p class="cart-summary__total">
                    <span>Total</span>
                    <strong>$<?php echo e(number_format($total, 2)); ?></strong>
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
