<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';
require_once __DIR__ . '/lib/wishlist_helpers.php';

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
                 LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
                 JOIN SHOP s ON s.shop_id = p.shop_id
                 JOIN TRADER t ON t.trader_id = s.trader_id
                 JOIN \"USER\" u ON u.user_id = t.trader_id
                 WHERE p.product_id = :product_id AND p.product_verification_status = 'APPROVED' AND s.shop_status = 'ACTIVE'",
                ['product_id' => $productId]
            );
            
            if ($product !== null) {
                $reviews = db_fetch_all(
                    "SELECT r.rating, r.\"COMMENT\" as review_comment, r.review_date, 
                            u.first_name || ' ' || u.last_name AS customer_name
                     FROM REVIEW r
                     JOIN CUSTOMER c ON r.customer_id = c.customer_id
                     JOIN \"USER\" u ON c.customer_id = u.user_id
                     WHERE r.product_id = :product_id
                     ORDER BY r.review_date DESC",
                    ['product_id' => $productId]
                );
                
                // Calculate average rating
                $avgRating = 0;
                $reviewCount = count($reviews);
                if ($reviewCount > 0) {
                    $sum = 0;
                    foreach ($reviews as $rev) {
                        $sum += (float) $rev['RATING'];
                    }
                    $avgRating = round($sum / $reviewCount, 1);
                }
                $product['avg_rating'] = $avgRating;
                $product['review_count'] = $reviewCount;
                $product['reviews'] = $reviews;
            }
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
                    $productImage = 'assets/images/products/' . ltrim($productImage, '/');
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

                <p class="product-box product-rating" aria-label="Rating <?php echo e($product['avg_rating'] ?? 0); ?> out of 5 from <?php echo e($product['review_count'] ?? 0); ?> reviews">
                    <span class="product-stars" aria-hidden="true">
                        <?php
                        $stars = (int) round($product['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<span class="product-star" style="color: ' . ($i <= $stars ? '#fbbf24' : '#d1d5db') . ';">&#9733;</span>';
                        }
                        ?>
                        <span style="font-size: 0.9rem; color: #666; margin-left: 0.5rem;">(<?php echo e($product['review_count'] ?? 0); ?> reviews)</span>
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
                    </div>
                </form>
                
                <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                <form class="product-form" method="post" action="wishlist_action.php" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                    <input type="hidden" name="return_url" value="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>" />
                    <button class="product-add-button" type="submit" style="width: 100%; font-size: 1.1rem; padding: 0.75rem; background: transparent; color: #1a1a1a; border: 1px solid #1a1a1a; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" title="Save to Wishlist">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Add to Wishlist
                    </button>
                </form>
                <?php endif; ?>
            </article>
        </div>
    </section>
    
    <section class="product-reviews" aria-labelledby="reviews-title" style="margin-top: 3rem;">
        <div class="container" style="max-width: 800px; margin: 0 auto; background: rgba(249, 248, 243, 0.6); padding: 2rem; border-radius: 1rem; border: 1px solid rgba(26, 26, 26, 0.08);">
            <h2 id="reviews-title" style="margin-bottom: 1.5rem;">Customer Reviews</h2>
            
            <?php if (empty($product['reviews'])): ?>
                <p>No reviews yet. Buy this product to leave a review!</p>
            <?php else: ?>
                <div class="reviews-list" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($product['reviews'] as $review): ?>
                        <article class="review-item" style="border-bottom: 1px solid rgba(26, 26, 26, 0.1); padding-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <h3 style="font-size: 1.1rem; margin: 0;"><?php echo e($review['CUSTOMER_NAME']); ?></h3>
                                    <div style="color: #fbbf24; font-size: 1.1rem;">
                                        <?php
                                        $rStars = (int) $review['RATING'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo '<span style="color: ' . ($i <= $rStars ? '#fbbf24' : '#d1d5db') . ';">&#9733;</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <span style="font-size: 0.9rem; color: #666;"><?php echo e(date('M d, Y', strtotime($review['REVIEW_DATE']))); ?></span>
                            </div>
                            <?php if (!empty($review['REVIEW_COMMENT'])): ?>
                                <p style="margin: 0.5rem 0 0; color: #333; line-height: 1.5;">
                                    <?php echo e(is_object($review['REVIEW_COMMENT']) ? $review['REVIEW_COMMENT']->load() : (string)$review['REVIEW_COMMENT']); ?>
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
