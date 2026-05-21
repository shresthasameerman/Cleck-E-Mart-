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
                        p.allergy_information,
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

                $relatedProducts = db_fetch_all(
                    "SELECT p.product_id, p.product_name, p.price, p.product_image, d.discount_percentage
                     FROM PRODUCT p
                     LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
                     WHERE p.category_id = (SELECT category_id FROM PRODUCT WHERE product_id = :product_id)
                       AND p.product_id != :product_id
                       AND p.product_verification_status = 'APPROVED'
                     FETCH FIRST 4 ROWS ONLY",
                    ['product_id' => $productId]
                );
                $product['related_products'] = $relatedProducts;
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
            <div class="product-media product-card" aria-label="Product image panel" style="background: #ffffff; padding: 2rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <?php
                $productImage = trim((string) ($product['PRODUCT_IMAGE'] ?? ''));
                if ($productImage === '') {
                    $productImage = 'assets/images/icons/product-placeholder.svg';
                } elseif (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://') && !str_starts_with($productImage, 'assets/')) {
                    $productImage = 'assets/images/products/' . ltrim($productImage, '/');
                }
                if (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://')) {
                    $absoluteImage = __DIR__ . '/' . $productImage;
                    if (!file_exists($absoluteImage)) {
                        $productImage = 'assets/images/icons/product-placeholder.svg';
                    }
                }
                ?>
                <img src="<?php echo e($productImage); ?>" alt="<?php echo e($product['PRODUCT_NAME']); ?>" style="max-width: 100%; height: auto; border-radius: var(--radius-sm);" />
            </div>

            <article class="product-details-premium" aria-label="Product information">
                <div class="product-header">
                    <span class="product-trader-premium" style="text-transform: uppercase; color: var(--color-muted); font-size: 0.85rem; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">TRADER: <?php echo e($product['TRADER_NAME']); ?></span>
                    <h1 id="product-name-title" class="brand product-title-premium" style="font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--color-brand-green); margin: 0 0 0.25rem 0; line-height: 1.1;"><?php echo e($product['PRODUCT_NAME']); ?></h1>
                </div>

                <div class="product-rating-premium" style="margin-bottom: 0.25rem;" aria-label="Rating <?php echo e($product['avg_rating'] ?? 0); ?> out of 5 from <?php echo e($product['review_count'] ?? 0); ?> reviews">
                    <span class="product-stars" aria-hidden="true">
                        <?php
                        $stars = (int) round($product['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<span style="color: ' . ($i <= $stars ? '#fbbf24' : '#e5e7eb') . ';">&#9733;</span>';
                        }
                        ?>
                        <span class="review-count-premium" style="margin-left: 0.5rem; color: var(--color-muted);">(<?php echo e($product['review_count'] ?? 0); ?> reviews)</span>
                    </span>
                </div>

                <div class="product-price-premium" style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                    <?php 
                    $rawPrice = (float) $product['PRICE'];
                    $discount = isset($product['DISCOUNT_PERCENTAGE']) ? (float) $product['DISCOUNT_PERCENTAGE'] : 0;
                    if ($discount > 0) {
                        $discounted = $rawPrice * (1 - $discount / 100);
                        echo '<s style="color: var(--color-muted); font-size: 1.1rem; margin-right: 0.5rem;">$' . number_format($rawPrice, 2) . '</s> <span style="font-size: 2rem; font-weight: 700; color: var(--color-brand-green); margin-right: 1rem;">$' . number_format($discounted, 2) . '</span> <span style="background: #e8f5e9; color: var(--color-brand-green); font-size: 0.85rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 4px;">' . $discount . '% OFF</span>';
                    } else {
                        echo '<span style="font-size: 2rem; font-weight: 700; color: var(--color-brand-green); margin-right: 0.5rem;">$' . number_format($rawPrice, 2) . '</span>';
                    }
                    ?>
                </div>

                <div class="product-description-premium" style="margin-bottom: 0.75rem; color: var(--color-muted); line-height: 1.4; font-size: 0.9rem;">
                    <?php 
                    $desc = is_object($product['PRODUCT_DESCRIPTION']) ? $product['PRODUCT_DESCRIPTION']->load() : (string)($product['PRODUCT_DESCRIPTION'] ?? '');
                    // Short description: first sentence or up to first period.
                    $shortDesc = explode('.', $desc, 2)[0] . '.';
                    echo '<p style="margin: 0 0 0.25rem 0;">' . e($shortDesc) . '</p>'; 
                    ?>
                    <p style="margin: 0;">Allergens: <span style="color: var(--color-accent); font-weight: 500;"><?php echo e($product['ALLERGY_INFORMATION'] ?? 'None'); ?></span>.</p>
                </div>

                <form class="product-form-premium" method="post" action="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>">
                    <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                    
                    <div class="product-qty-premium">
                        <label for="qty">QTY:</label>
                        <input id="qty" type="number" min="1" name="quantity" value="1" aria-label="Quantity" />
                    </div>

                    <button class="button product-add-btn-premium" type="submit" name="action" value="add_to_cart">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Add to Basket
                    </button>
                </form>
                
                <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                <form method="post" action="wishlist_action.php" class="product-wishlist-premium">
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                    <input type="hidden" name="return_url" value="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>" />
                    <button class="filter-btn" type="submit" title="Save to Wishlist">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Save to Wishlist
                    </button>
                </form>
                <?php endif; ?>
            </article>
        </div>
    </section>
    
    <section class="product-features-premium">
        <div class="container features-container-premium">
            <div class="feature-item-premium">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
                <span>Verified<br/>Trader</span>
            </div>
            <div class="feature-item-premium">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <path d="M9 21V9"></path>
                </svg>
                <span>24 hr<br/>Service</span>
            </div>
            <div class="feature-item-premium">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.69l5.66 4.2c.57.43 1.34.61 2.05.51l6.8-1c.21-.03.42.15.46.36l.78 6.75c.08.7.45 1.33 1.02 1.76l5.52 4.1c.17.13.17.38 0 .5l-5.52 4.1c-.57.42-.94 1.06-1.02 1.76l-.78 6.75c-.04.21-.25.39-.46.36l-6.8-1c-.71-.1-1.48.08-2.05.51L12 21.31l-5.66-4.2c-.57-.43-1.34-.61-2.05-.51l-6.8 1c-.21.03-.42-.15-.46-.36l-.78-6.75c-.08-.7-.45-1.33-1.02-1.76l-5.52-4.1c-.17-.13-.17-.38 0-.5l5.52-4.1c.57-.42.94-1.06 1.02-1.76l.78-6.75c.04-.21.25-.39.46-.36l6.8 1c.71.1 1.48-.08 2.05-.51L12 2.69z"></path>
                </svg>
                <span>No Artificial<br/>Preservatives</span>
            </div>
            <div class="feature-item-premium">
                <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"></path>
                    <path d="M14 9h4l4 4v5c0 .6-.4 1-1 1h-2"></path>
                    <circle cx="7" cy="18" r="2"></circle>
                    <circle cx="17" cy="18" r="2"></circle>
                </svg>
                <span>Instant<br/>Order</span>
            </div>
        </div>
    </section>

    <section class="product-bottom-premium">
        <div class="container bottom-grid-premium" style="background: #ffffff; border-radius: var(--radius-lg); padding: 3rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02); display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; margin-bottom: 3rem;">
            <!-- Left Column: Tabs -->
            <div class="bottom-left-col" style="background: transparent;">
                <div class="bottom-tabs" style="display: flex; gap: 2rem; margin-bottom: 2rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <button class="bottom-tab active" style="font-size: 0.95rem; color: var(--color-brand-green); font-weight: 700; border-bottom: 2px solid var(--color-brand-green); padding-bottom: 0.75rem; text-transform: uppercase; background: none; border-top: none; border-left: none; border-right: none; margin-bottom: -1px; cursor: default;">CUSTOMER REVIEWS</button>
                </div>
                <div class="bottom-content" style="color: var(--color-text); line-height: 1.6; font-size: 0.95rem;">
                    <?php if (empty($product['reviews'])): ?>
                        <p class="no-reviews-premium" style="color: var(--color-muted);">No reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <div class="reviews-list-premium" style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php foreach ($product['reviews'] as $review): ?>
                                <article class="review-item-premium" style="padding-bottom: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                    <div class="review-header-premium" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; align-items: flex-start;">
                                        <div>
                                            <h3 style="margin: 0 0 0.25rem 0; font-size: 1rem; font-weight: 700; color: var(--color-text);"><?php echo e($review['CUSTOMER_NAME']); ?></h3>
                                            <div class="review-stars-premium" style="font-size: 0.9rem;">
                                                <?php
                                                $rStars = (int) $review['RATING'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo '<span style="color: ' . ($i <= $rStars ? '#fbbf24' : '#e5e7eb') . ';">&#9733;</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <span class="review-date-premium" style="color: var(--color-muted); font-size: 0.85rem;"><?php echo e(date('M d, Y', strtotime($review['REVIEW_DATE']))); ?></span>
                                    </div>
                                    <?php if (!empty($review['REVIEW_COMMENT'])): ?>
                                        <p class="review-body-premium" style="margin: 0; color: var(--color-text); font-size: 0.95rem; line-height: 1.6;">
                                            <?php echo e(is_object($review['REVIEW_COMMENT']) ? $review['REVIEW_COMMENT']->load() : (string)$review['REVIEW_COMMENT']); ?>
                                        </p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: You May Also Like -->
            <div class="bottom-right-col">
                <h3 class="related-title" style="font-size: 1rem; color: var(--color-brand-green); font-weight: 700; text-transform: uppercase; margin-bottom: 1.5rem; border-bottom: 2px solid transparent; padding-bottom: 0.5rem;">YOU MAY ALSO LIKE</h3>
                <div class="related-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <?php if (!empty($product['related_products'])): ?>
                        <?php foreach ($product['related_products'] as $related): ?>
                            <?php
                            $relImage = trim((string) ($related['PRODUCT_IMAGE'] ?? ''));
                            if ($relImage === '') {
                                $relImage = 'assets/images/icons/product-placeholder.svg';
                            } elseif (!str_starts_with($relImage, 'http://') && !str_starts_with($relImage, 'https://') && !str_starts_with($relImage, 'assets/')) {
                                $relImage = 'assets/images/products/' . ltrim($relImage, '/');
                            }
                            ?>
                            <a href="product.php?product_id=<?php echo e($related['PRODUCT_ID']); ?>" class="related-card" style="text-decoration: none; color: inherit; display: block;">
                                <div style="background: #ffffff; padding: 1rem; border-radius: var(--radius-md); text-align: center; margin-bottom: 1rem; height: 120px; display: flex; align-items: center; justify-content: center;">
                                    <img src="<?php echo e($relImage); ?>" alt="<?php echo e($related['PRODUCT_NAME']); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 8px;" />
                                </div>
                                <h4 style="font-size: 0.9rem; font-weight: 500; margin: 0 0 0.25rem 0; color: var(--color-text); line-height: 1.3;"><?php echo e($related['PRODUCT_NAME']); ?></h4>
                                <p style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--color-text);">$<?php echo e(number_format((float)$related['PRICE'], 2)); ?></p>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews-premium">No related products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
