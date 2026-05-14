<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load APEX API integration
require_once __DIR__ . '/lib/apex_api.php';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/auth_helpers.php';

// Supports multiple common login flags so this works with your current or future auth flow.
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

try {
    if (db_is_offline()) {
        $offlineCategoryId = $selectedCategoryId;
        $offlineProducts = offline_get_products($offlineCategoryId);

        if ($searchTerm !== '') {
            $offlineProducts = array_values(array_filter(
                $offlineProducts,
                static function (array $product) use ($searchTerm): bool {
                    return stripos((string) ($product['PRODUCT_NAME'] ?? ''), $searchTerm) !== false;
                }
            ));
        }

        foreach ($offlineProducts as $row) {
            $productId = (int) ($row['PRODUCT_ID'] ?? 0);
            $uploadedImage = isset($row['PRODUCT_IMAGE']) ? (string) $row['PRODUCT_IMAGE'] : null;

            $featuredProducts[] = [
                'product_id' => $productId,
                'product_name' => (string) ($row['PRODUCT_NAME'] ?? ''),
                'product_description' => (string) ($row['PRODUCT_DESCRIPTION'] ?? ''),
                'price' => (float) ($row['PRICE'] ?? 0),
                'product_image' => default_product_image($productId, $uploadedImage, 400),
                'discount_percentage' => null,
                'shop_name' => (string) ($row['SHOP_NAME'] ?? ''),
                'category_name' => (string) ($row['CATEGORY_NAME'] ?? ''),
                'product_status' => (string) ($row['PRODUCT_STATUS'] ?? 'ACTIVE'),
            ];
        }
    } else {
        $conn = db_connect();

        $sql = "SELECT p.product_id,
                       p.product_name,
                       p.product_description,
                       p.price,
                       p.product_image,
                       p.product_status,
                       s.shop_name,
                       c.category_name,
                       d.discount_percentage
                FROM PRODUCT p
                LEFT JOIN SHOP s ON s.shop_id = p.shop_id
                LEFT JOIN CATEGORY c ON c.category_id = p.category_id
                LEFT JOIN DISCOUNT d ON d.discount_id = p.discount_id AND d.end_date >= SYSDATE";

        $conditions = [];
        $searchBind = null;
        $categoryBind = null;

        if ($searchTerm !== '') {
            $conditions[] = "LOWER(p.product_name) LIKE LOWER('%' || :search_bind || '%')";
            $searchBind = $searchTerm;
        }

        if ($selectedCategoryId !== null) {
            $conditions[] = 'p.category_id = :cat_bind';
            $categoryBind = (int) $selectedCategoryId;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.product_name';

        $statement = oci_parse($conn, $sql);
        if ($statement === false) {
            $error = oci_error($conn);
            throw new RuntimeException('Failed to prepare homepage product query: ' . ($error['message'] ?? 'unknown error'));
        }

        if ($searchBind !== null && !oci_bind_by_name($statement, ':search_bind', $searchBind)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to bind search term: ' . ($error['message'] ?? 'unknown error'));
        }

        if ($categoryBind !== null && !oci_bind_by_name($statement, ':cat_bind', $categoryBind)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to bind category filter: ' . ($error['message'] ?? 'unknown error'));
        }

        if (!@oci_execute($statement)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to fetch homepage products: ' . ($error['message'] ?? 'unknown error'));
        }

        while (($row = oci_fetch_assoc($statement)) !== false) {
            $productId = (int) ($row['PRODUCT_ID'] ?? 0);
            $uploadedImage = isset($row['PRODUCT_IMAGE']) ? (string) $row['PRODUCT_IMAGE'] : null;

            $featuredProducts[] = [
                'product_id' => $productId,
                'product_name' => (string) ($row['PRODUCT_NAME'] ?? ''),
                'product_description' => is_object($row['PRODUCT_DESCRIPTION']) ? $row['PRODUCT_DESCRIPTION']->load() : (string) ($row['PRODUCT_DESCRIPTION'] ?? ''),
                'price' => (float) ($row['PRICE'] ?? 0),
                'product_image' => default_product_image($productId, $uploadedImage, 400),
                'discount_percentage' => isset($row['DISCOUNT_PERCENTAGE']) ? (float) $row['DISCOUNT_PERCENTAGE'] : null,
                'shop_name' => (string) ($row['SHOP_NAME'] ?? ''),
                'category_name' => (string) ($row['CATEGORY_NAME'] ?? ''),
                'product_status' => (string) ($row['PRODUCT_STATUS'] ?? 'ACTIVE'),
            ];
        }

        oci_free_statement($statement);
    }
} catch (Throwable $e) {
    $apiError = $e->getMessage();
    error_log('Featured products load error: ' . $apiError);
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
                <img src="assets/images/product-placeholder.svg" alt="Featured shopping banner" />
            </div>
        </div>
    </section>

    <section class="category-strip" aria-labelledby="category-strip-title">
        <div class="container">
            <h2 id="category-strip-title" class="section-heading__title-sm">Categories</h2>
            <div class="category-strip__grid">
                <?php foreach ($homeCategories as $category): ?>
                    <a class="category-pill" href="?category_id=<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars((string) $category['label'], ENT_QUOTES, 'UTF-8'); ?></a>
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
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
                                    <a class="product-card__link" href="product.php?product_id=<?php echo (int) $product['product_id']; ?>">View details</a>
                                    <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                                        <form method="post" action="product.php" style="display:inline;">
                                            <input type="hidden" name="action" value="add_to_wishlist" />
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>" />
                                            <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:1.5rem;" aria-label="Add to Wishlist" title="Add to Wishlist">♥</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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
                                    <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                                        <form method="post" action="product.php" style="display:inline; margin-left: 0.5rem;">
                                            <input type="hidden" name="action" value="add_to_wishlist" />
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>" />
                                            <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:1.5rem;" aria-label="Add to Wishlist" title="Add to Wishlist">♥</button>
                                        </form>
                                    <?php endif; ?>
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