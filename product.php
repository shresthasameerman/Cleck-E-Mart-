<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';

// Allow guest access to view product details, but require login for add-to-cart
$isLoggedIn = is_logged_in();
if ($isLoggedIn) {
    require_login(['CUSTOMER']);
}

$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$errors = [];
$flashSuccess = get_flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    if (!is_logged_in()) {
        set_flash('error', 'Please login before adding items to the basket.');
        redirect('auth.php?mode=login');
    }

    if (current_role() !== 'CUSTOMER' || current_customer_id() === null) {
        $errors[] = 'Only customer accounts can place items in the basket.';
    } else {
        $postedProductId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $quantity = $quantity !== false && $quantity !== null ? max(1, $quantity) : 1;

        if ($postedProductId === false || $postedProductId === null) {
            $errors[] = 'Invalid product selected.';
        } else {
            try {
                $customerId = (int) current_customer_id();
                $addSuccess = false;
                
                // Try APEX API first if enabled
                if (apex_cart_enabled()) {
                    try {
                        $addSuccess = apex_add_to_cart($customerId, (int) $postedProductId, $quantity);
                    } catch (Throwable $e) {
                        error_log('APEX add to cart failed: ' . $e->getMessage());
                        // Fall back to local
                    }
                }
                
                // Fall back to local if APEX didn't work
                if (!$addSuccess) {
                    add_product_to_cart($customerId, (int) $postedProductId, $quantity);
                }
                
                set_flash('success', 'Product added to basket.');
                redirect('cart.php');
            } catch (Throwable $exception) {
                $errors[] = 'Unable to add product to basket: ' . $exception->getMessage();
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_wishlist') {
    if (!is_logged_in()) {
        set_flash('error', 'Please login before adding items to your wishlist.');
        redirect('auth.php?mode=login');
    }

    if (current_role() !== 'CUSTOMER' || current_customer_id() === null) {
        $errors[] = 'Only customer accounts can use the wishlist.';
    } else {
        $postedProductId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($postedProductId === false || $postedProductId === null) {
            $errors[] = 'Invalid product selected.';
        } else {
            try {
                $customerId = (int) current_customer_id();
                if (!db_is_offline()) {
                    // Check if customer has a wishlist
                    $wishlist = db_fetch_one('SELECT wishlist_id FROM WISHLIST WHERE customer_id = :customer_id', ['customer_id' => $customerId]);
                    if (!$wishlist) {
                        $wishlistId = db_next_id('WISHLIST', 'wishlist_id');
                        db_execute('INSERT INTO WISHLIST (wishlist_id, customer_id) VALUES (:wishlist_id, :customer_id)', [
                            'wishlist_id' => $wishlistId,
                            'customer_id' => $customerId
                        ]);
                    } else {
                        $wishlistId = (int) $wishlist['WISHLIST_ID'];
                    }
                    
                    // Add to wishlist item
                    try {
                        db_execute('INSERT INTO WISHLIST_ITEM (product_id, wishlist_id) VALUES (:product_id, :wishlist_id)', [
                            'product_id' => $postedProductId,
                            'wishlist_id' => $wishlistId
                        ]);
                        set_flash('success', 'Product added to wishlist.');
                    } catch (Throwable $e) {
                        // Likely a unique constraint violation
                        set_flash('success', 'Product is already in your wishlist.');
                    }
                } else {
                    set_flash('error', 'Wishlist is not supported in offline mode.');
                }
                redirect('product.php?product_id=' . $postedProductId);
            } catch (Throwable $exception) {
                $errors[] = 'Unable to add product to wishlist: ' . $exception->getMessage();
            }
        }
    }
}

$product = null;

if ($productId !== false && $productId !== null) {
    try {
        if (db_is_offline()) {
            $product = offline_get_product_detail((int) $productId);
        } else {
            $product = db_fetch_one(
                "SELECT p.product_id,
                        p.product_name,
                        p.product_description,
                        p.price,
                        p.product_image,
                        d.discount_percentage,
                        NVL(u.first_name || ' ' || u.last_name, s.shop_name) AS trader_name
                 FROM PRODUCT p
                 LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id
                 JOIN SHOP s ON s.shop_id = p.shop_id
                 JOIN TRADER t ON t.trader_id = s.trader_id
                 JOIN \"USER\" u ON u.user_id = t.trader_id
                 WHERE p.product_id = :product_id",
                ['product_id' => $productId]
            );
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to load product: ' . $exception->getMessage();
    }
}

$pageTitle = 'Product View | Cleck E-Mart';
$metaDescription = 'Product details page for viewing trader info, ratings, description, and quantity.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="product-page">
    <div class="container">
        <?php if ($flashSuccess !== null): ?>
            <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="page-message page-message--error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($product === null): ?>
        <section class="product-content" aria-labelledby="product-name-title">
            <div class="container product-layout">
                <article class="product-details" aria-label="Product information">
                    <h1 id="product-name-title" class="product-box product-name">Product not found</h1>
                    <p class="product-box product-description">Open a product from the category page to view details.</p>
                    <a class="product-add-button" href="category.php">Back to Category</a>
                </article>
            </div>
        </section>
    <?php else: ?>
    <!--
        Product layout mirrors the provided wireframe:
        left = image panel, right = product metadata and actions.
    -->
    <section class="product-content" aria-labelledby="product-name-title">
        <div class="container product-layout">
            <div class="product-media" aria-label="Product image panel">
                <?php
                $productImage = trim((string) ($product['PRODUCT_IMAGE'] ?? ''));
                if ($productImage === '') {
                    $productImage = 'assets/images/product-placeholder.svg';
                } elseif (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://') && !str_starts_with($productImage, 'assets/')) {
                    $productImage = 'assets/images/' . ltrim($productImage, '/');
                }
                if (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://')) {
                    $absoluteImage = __DIR__ . '/' . $productImage;
                    if (!file_exists($absoluteImage)) {
                        $productImage = 'assets/images/product-placeholder.svg';
                    }
                }
                ?>
                <img src="<?php echo e($productImage); ?>" alt="<?php echo e($product['PRODUCT_NAME']); ?>" />
            </div>

            <article class="product-details" aria-label="Product information">
                <p class="product-box product-trader">Trader: <?php echo e($product['TRADER_NAME']); ?></p>

                <h1 id="product-name-title" class="product-box product-name">Product Name: <?php echo e($product['PRODUCT_NAME']); ?></h1>

                <p class="product-box product-rating" aria-label="Rating 5 out of 5 from 120 reviews">
                    <span class="product-stars" aria-hidden="true">
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                    </span>
                        <span class="product-price">
                            <?php 
                            $rawPrice = (float) $product['PRICE'];
                            $discount = isset($product['DISCOUNT_PERCENTAGE']) ? (float) $product['DISCOUNT_PERCENTAGE'] : 0;
                            if ($discount > 0) {
                                $discounted = $rawPrice * (1 - $discount / 100);
                                echo '<s>$' . number_format($rawPrice, 2) . '</s> $' . number_format($discounted, 2);
                            } else {
                                echo '$' . number_format($rawPrice, 2);
                            }
                            ?>
                        </span>
                </p>

                <p class="product-box product-description">
                    Product Description: <?php echo e(is_object($product['PRODUCT_DESCRIPTION']) ? $product['PRODUCT_DESCRIPTION']->load() : (string)($product['PRODUCT_DESCRIPTION'] ?? '')); ?>
                </p>

                <form class="product-form" method="post" action="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>">
                    <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                    <div class="product-box product-quantity" aria-label="Quantity selector">
                        <p class="product-quantity__label">Quantity:</p>
                        <div class="product-qty-controls">
                            <input class="product-qty-value" type="number" min="1" name="quantity" value="1" aria-label="Quantity" />
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button class="product-add-button" type="submit" name="action" value="add_to_cart" style="flex: 1; font-size: 1.1rem; padding: 0.75rem;">
                            Add to Basket
                        </button>
                        <button class="product-add-button" type="submit" name="action" value="add_to_wishlist" style="flex: 1; font-size: 1.1rem; padding: 0.75rem; background: transparent; color: #1a1a1a; border: 1px solid #1a1a1a; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" title="Save to Wishlist">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            Add to Wishlist
                        </button>
                    </div>
                </form>
            </article>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
