<?php
// This is the main homepage of the website, showing featured products, categories, and top shops to welcome visitors.

// Initialize session to manage user login state across requests
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load APEX API integration
require_once __DIR__ . '/lib/apex_api.php';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/auth_helpers.php';

// Determine if the user is authenticated by checking various session flags.
// This supports backward compatibility with different iterations of the login system.
$isLoggedIn = !empty($_SESSION['user_id'])
    || !empty($_SESSION['customer_id'])
    || !empty($_SESSION['is_logged_in'])
    || !empty($_SESSION['logged_in'])
    || !empty($_SESSION['customer_logged_in']);

$searchTerm = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$selectedCategoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$selectedCategoryId = $selectedCategoryId !== false ? $selectedCategoryId : null;

$homeCategories = [
    ['id' => 6101, 'label' => 'Meat & Poultry'],
    ['id' => 6102, 'label' => 'Fruit & Vegetables'],
    ['id' => 6103, 'label' => 'Fish & Seafood'],
    ['id' => 6104, 'label' => 'Bread & Bakery'],
];

$featuredProducts = [];
$apiError = null;

require_once __DIR__ . '/lib/storefront_helpers.php';

try {
    $featuredProducts = get_storefront_products($searchTerm, $selectedCategoryId);
} catch (Throwable $e) {
    $apiError = $e->getMessage();
    error_log('Featured products load error: ' . $apiError);
}

try {
    $verifiedShops = get_storefront_verified_shops();
} catch (Throwable $e) {
    error_log('Verified shops load error: ' . $e->getMessage());
}

$displayProducts = $searchTerm !== '' ? $featuredProducts : array_slice($featuredProducts, 0, $isLoggedIn ? 4 : 3);

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
                <img src="assets/images/banners/banner.png" alt="Cleck E-Mart Farmers Market" style="width: 100%; height: auto; border-radius: var(--radius-lg); object-fit: cover; max-height: 400px;" />
            </div>
        </div>
    </section>

    <section class="category-strip" aria-labelledby="category-strip-title">
        <div class="container">
            <h2 id="category-strip-title" class="section-heading__title-sm">Categories</h2>
            <div class="category-strip__grid">
                <?php foreach ($homeCategories as $category): ?>
                    <a class="category-pill" href="category.php?category_id=<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars((string) $category['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
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
            <?php elseif (empty($displayProducts)): ?>
                <div class="page-message page-message--error">
                    <p><?php echo $searchTerm !== '' ? 'No products matched your search.' : 'No products available at the moment.'; ?></p>
                </div>
            <?php else: ?>
                <div class="card-grid card-grid--four">
                    <?php foreach ($displayProducts as $product): ?>
                        <article class="product-card" style="background: #ffffff; border-radius: var(--radius-lg); box-shadow: 0 4px 12px rgba(0,0,0,0.04); display: flex; flex-direction: column; overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; height: 100%;">
                            <a href="product.php?product_id=<?php echo (int) $product['product_id']; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; flex: 1;">
                                <div class="product-card__media" style="position: relative; height: 220px; background: #ffffff; display: flex; align-items: center; justify-content: center; padding: 2rem; border-bottom: 1px solid rgba(0,0,0,0.03);">
                                    <?php 
                                        $discount = isset($product['discount_percentage']) ? (float) $product['discount_percentage'] : 0;
                                        if ($discount > 0): 
                                    ?>
                                        <span style="position: absolute; top: 1rem; right: 1rem; background: #e8f5e9; color: var(--color-brand-green); font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 4px; z-index: 1;">-<?php echo $discount; ?>%</span>
                                    <?php endif; ?>
                                    <img src="<?php echo htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 4px;" />
                                </div>
                                <div class="product-card__content" style="padding: 1.5rem; display: flex; flex-direction: column; flex: 1;">
                                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-muted); margin-bottom: 0.5rem; display: block;"><?php echo htmlspecialchars((string) $product['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--color-brand-green); margin: 0 0 0.5rem 0; font-weight: 700; line-height: 1.3;"><?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <span style="font-size: 0.85rem; color: var(--color-muted); margin-bottom: 1.5rem; display: block;">By <?php echo htmlspecialchars((string) $product['shop_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    
                                    <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: flex-end;">
                                        <div>
                                            <?php 
                                                $rawPrice = (float) $product['price'];
                                                if ($discount > 0) {
                                                    $discounted = $rawPrice * (1 - $discount / 100);
                                                    echo '<span style="color: var(--color-muted); font-size: 0.95rem; text-decoration: line-through; margin-right: 0.5rem;">&pound;' . number_format($rawPrice, 2) . '</span>';
                                                    echo '<span style="color: var(--color-brand-green); font-size: 1.3rem; font-weight: 700;">&pound;' . number_format($discounted, 2) . '</span>';
                                                } else {
                                                    echo '<span style="color: var(--color-brand-green); font-size: 1.3rem; font-weight: 700;">&pound;' . number_format($rawPrice, 2) . '</span>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php require __DIR__ . '/components/verified_shops.php'; ?>

    <section class="promo" aria-label="Promotions" style="margin-top: 2rem;">
        <div class="container">
            <h2 class="section-heading__title-sm" style="margin-bottom: 1.5rem;">Special Offers</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <a href="category.php?category_id=6104" style="position: relative; display: block; border-radius: var(--radius-lg); overflow: hidden; text-decoration: none;">
                    <img src="assets/images/banners/promo_bakery.png" alt="Bakery Discount" style="width: 100%; height: 250px; object-fit: cover; display: block; transition: transform 0.3s ease;" />
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.1)); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem;">
                        <span style="color: white; font-weight: bold; font-size: 1.5rem; margin-bottom: 0.5rem;">15% OFF Bakery</span>
                        <span style="color: rgba(255,255,255,0.9); font-size: 1rem;">Fresh sourdough, croissants & more</span>
                    </div>
                </a>
                <a href="category.php?category_id=6102" style="position: relative; display: block; border-radius: var(--radius-lg); overflow: hidden; text-decoration: none;">
                    <img src="assets/images/banners/promo_produce.png" alt="Produce Discount" style="width: 100%; height: 250px; object-fit: cover; display: block; transition: transform 0.3s ease;" />
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.1)); display: flex; flex-direction: column; justify-content: flex-end; padding: 2rem;">
                        <span style="color: white; font-weight: bold; font-size: 1.5rem; margin-bottom: 0.5rem;">10% OFF Fresh Produce</span>
                        <span style="color: rgba(255,255,255,0.9); font-size: 1rem;">Organic vegetables straight from the farm</span>
                    </div>
                </a>
            </div>
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
                <form class="search" role="search" action="index.php" method="get">
                    <label class="sr-only" for="site-search">Search products</label>
                    <input id="site-search" class="search__input" type="search" name="q" value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search products, brands, or categories" />
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
            <?php elseif (empty($displayProducts)): ?>
                <div class="page-message page-message--error">
                    <p><?php echo $searchTerm !== '' ? 'No products matched your search.' : 'No products available at the moment.'; ?></p>
                </div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($displayProducts as $product): ?>
                        <article class="product-card" style="background: #ffffff; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column;">
                            <a href="product.php?product_id=<?php echo (int) $product['product_id']; ?>" style="text-decoration: none; color: inherit; display: block; flex: 1;">
                                <div class="product-card__media" style="text-align: center; margin-bottom: 1rem; height: 160px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; border-radius: var(--radius-md);">
                                    <img src="<?php echo htmlspecialchars((string) $product['product_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 8px;" />
                                </div>
                                <div class="product-card__content">
                                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--color-brand-green); margin-bottom: 0.75rem; font-weight: 700;"><?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <?php 
                                        $desc = htmlspecialchars((string) $product['product_description'], ENT_QUOTES, 'UTF-8');
                                        $shortDesc = explode('.', $desc, 2)[0] . '.';
                                    ?>
                                    <p style="font-size: 0.85rem; color: var(--color-muted); line-height: 1.4; margin-bottom: 0.5rem;"><?php echo $shortDesc; ?></p>
                                </div>
                            </a>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top: auto;">
                                <p class="product-card__price" style="font-weight: 700; color: var(--color-text); font-size: 1.05rem; margin: 0;"><?php echo format_product_price($product); ?></p>
                                <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                                    <form method="post" action="product.php" style="display:inline; margin: 0;">
                                        <input type="hidden" name="action" value="add_to_wishlist" />
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>" />
                                        <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:1.5rem;" aria-label="Add to Wishlist" title="Add to Wishlist">♥</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php require __DIR__ . '/components/verified_shops.php'; ?>

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