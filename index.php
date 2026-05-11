<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load APEX API integration
require_once __DIR__ . '/lib/apex_api.php';

// Supports multiple common login flags so this works with your current or future auth flow.
$isLoggedIn = !empty($_SESSION['user_id'])
    || !empty($_SESSION['customer_id'])
    || !empty($_SESSION['is_logged_in'])
    || !empty($_SESSION['logged_in'])
    || !empty($_SESSION['customer_logged_in']);

// Fetch featured products from APEX API
$featuredProducts = [];
$apiError = null;
try {
    $featuredProducts = fetch_apex_products(5); // Cache for 5 minutes
} catch (Throwable $e) {
    $apiError = $e->getMessage();
    error_log('Featured products API error: ' . $apiError);
}

// Shared header includes <head>, navigation, and opening <body> tag.
// Keeping this in one component ensures all pages stay visually consistent.
require __DIR__ . '/components/header.php';
?>
<?php if ($isLoggedIn): ?>
<main id="main-content" class="signed-home">
    <!-- Signed-in landing page (wireframe 1 style) -->
    <section class="welcome-hero" aria-labelledby="welcome-title">
        <div class="container">
            <div class="welcome-hero__banner">
                <h1 id="welcome-title" class="sr-only">Welcome back to Cleck E-Mart</h1>
                <img src="assets/images/product-placeholder.svg" alt="Featured shopping banner" />
            </div>
        </div>
    </section>

    <section class="category-strip" aria-labelledby="category-strip-title">
        <div class="container">
            <h2 id="category-strip-title" class="section-heading__title-sm">Categories</h2>
            <div class="category-strip__grid">
                <a class="category-pill" href="category.php">Fresh Produce</a>
                <a class="category-pill" href="category.php">Dairy & Eggs</a>
                <a class="category-pill" href="category.php">Household</a>
                <a class="category-pill" href="category.php">Personal Care</a>
            </div>
        </div>
    </section>

    <section class="featured" aria-labelledby="featured-title-logged">
        <div class="container">
            <div class="section-heading">
                <h2 id="featured-title-logged">Featured Products</h2>
            </div>

            <?php if ($apiError): ?>
                <div class="page-message page-message--error">
                    <p>Unable to load featured products. Please try again later.</p>
                </div>
            <?php elseif (empty($featuredProducts)): ?>
                <div class="page-message page-message--error">
                    <p>No products available at the moment.</p>
                </div>
            <?php else: ?>
                <div class="card-grid card-grid--four">
                    <?php foreach (array_slice($featuredProducts, 0, 4) as $product): ?>
                        <article class="product-card">
                            <div class="product-card__media">
                                <img src="<?php echo htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                            </div>
                            <div class="product-card__content">
                                <h3><?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars((string) $product['product_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="product-card__shop">Shop: <?php echo htmlspecialchars((string) $product['shop_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="product-card__category">Category: <?php echo htmlspecialchars((string) $product['category_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="product-card__price"><?php echo format_product_price($product); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="promo" aria-label="Promotions">
        <div class="container">
            <a class="promo-banner" href="category.php">Promotion / Offer Banner</a>
        </div>
    </section>
</main>
<?php else: ?>
<!--
    Guest homepage layout summary:
    1) Hero with search UI (wireframe 2)
    2) Featured products (hard-coded sample cards)
    3) CTA block for navigation focus
-->
<main id="main-content">
    <!-- Hero / search area -->
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__search-panel">
                <h1 id="hero-title" class="sr-only">Shop the latest essentials</h1>
                <form class="search" role="search" action="#" method="get">
                    <label class="sr-only" for="site-search">Search products</label>
                    <input id="site-search" class="search__input" type="search" name="q" placeholder="Search products, brands, or categories" />
                    <button class="search__button" type="submit">Search</button>
                </form>
            </div>

            <div class="hero__banner" aria-hidden="true">
                <p class="hero__eyebrow">New season collection</p>
                <p class="hero__headline">Simple essentials, refined for everyday shopping.</p>
            </div>
        </div>
    </section>

    <section class="featured" aria-labelledby="featured-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-heading__eyebrow">Featured picks</p>
                <h2 id="featured-title">Popular products for a clean, fast browse</h2>
            </div>

            <?php if ($apiError): ?>
                <div class="page-message page-message--error">
                    <p>Unable to load featured products. Please try again later.</p>
                </div>
            <?php elseif (empty($featuredProducts)): ?>
                <div class="page-message page-message--error">
                    <p>No products available at the moment.</p>
                </div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach (array_slice($featuredProducts, 0, 3) as $product): ?>
                        <article class="product-card">
                            <div class="product-card__media">
                                <img src="<?php echo htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                            </div>
                            <div class="product-card__content">
                                <h3><?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars((string) $product['product_description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="product-card__meta">
                                    <span class="product-card__price"><?php echo format_product_price($product); ?></span>
                                    <a class="product-card__link" href="product.php?product_id=<?php echo (int) $product['product_id']; ?>">View details</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta" aria-labelledby="cta-title">
        <div class="container cta__inner">
            <div>
                <p class="cta__eyebrow">Fast delivery</p>
                <h2 id="cta-title">Shop with a smoother checkout and clearer product discovery.</h2>
            </div>
            <a class="button button--secondary" href="category.php">Browse Category</a>
        </div>
    </section>
</main>
<?php endif; ?>
<?php
// Shared footer closes the page structure and contains quick links.
require __DIR__ . '/components/footer.php';
?>