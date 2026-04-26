<?php
$pageTitle = 'Browse Category | Cleck E-Mart';
$metaDescription = 'Browse product categories and discover items from local traders.';
require_once __DIR__ . '/lib/oci_db.php';

$selectedCategoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$categoryTitle = 'All Categories';
$products = [];
$dbError = null;

$buildTraderType = static function (string $value): string {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim((string) $slug, '-');

    return $slug === '' ? 'trader' : $slug;
};

$resolveImage = static function (?string $path): string {
    if ($path === null || trim($path) === '') {
        return 'assets/images/product-placeholder.svg';
    }

    $clean = trim($path);
    if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, 'assets/')) {
        if (str_starts_with($clean, 'assets/')) {
            $absolute = __DIR__ . '/' . $clean;
            return file_exists($absolute) ? $clean : 'assets/images/product-placeholder.svg';
        }
        return $clean;
    }

    $relative = 'assets/images/' . ltrim($clean, '/');
    $absolute = __DIR__ . '/' . $relative;
    return file_exists($absolute) ? $relative : 'assets/images/product-placeholder.svg';
};

try {
    if (db_is_offline()) {
        $effectiveCategoryId = $selectedCategoryId !== false ? $selectedCategoryId : null;
        $categoryTitle = offline_get_category_name($effectiveCategoryId);
        $products = offline_get_products($effectiveCategoryId);
    } else {
        if ($selectedCategoryId !== false && $selectedCategoryId !== null) {
            $category = db_fetch_one(
                'SELECT category_name FROM CATEGORY WHERE category_id = :category_id',
                ['category_id' => $selectedCategoryId]
            );
            if ($category !== null) {
                $categoryTitle = (string) $category['CATEGORY_NAME'];
            }
        }

        $sql = "SELECT p.product_id,
                       p.product_name,
                       p.price,
                       p.product_image,
                       NVL(u.first_name || ' ' || u.last_name, s.shop_name) AS trader_name,
                       s.shop_name,
                       c.category_name
                FROM PRODUCT p
                JOIN SHOP s ON s.shop_id = p.shop_id
                JOIN TRADER t ON t.trader_id = s.trader_id
                JOIN \"USER\" u ON u.user_id = t.trader_id
                JOIN CATEGORY c ON c.category_id = p.category_id";

        $binds = [];
        if ($selectedCategoryId !== false && $selectedCategoryId !== null) {
            $sql .= ' WHERE p.category_id = :category_id';
            $binds['category_id'] = $selectedCategoryId;
        }

        $sql .= ' ORDER BY p.product_name';
        $products = db_fetch_all($sql, $binds);
    }
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}

// Shared header keeps this page visually and structurally consistent with the site.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="category-page" data-category-page>
    <!-- Category title panel based on the wireframe header block. -->
    <section class="category-hero" aria-labelledby="category-title">
        <div class="container">
            <div class="category-hero__title-wrap">
                <h1 id="category-title">Category: <?php echo e($categoryTitle); ?></h1>
            </div>
        </div>
    </section>

    <!-- Search field for client-side filtering across product names and trader names. -->
    <section class="category-search" aria-label="Product search in category">
        <div class="container">
            <label class="sr-only" for="category-search-input">Search products in Fresh Produce</label>
            <input
                id="category-search-input"
                class="category-search__input"
                type="search"
                placeholder="Search products..."
                data-category-search
            />
        </div>
    </section>

    <!-- Main content region: left filter rail + right product cards grid. -->
    <section class="category-content" aria-label="Fresh produce listing">
        <div class="container category-layout">
            <aside class="filter-panel" aria-label="Product filters">
                <!-- Trader filter group controls which trader segment is visible. -->
                <div class="filter-group">
                    <h2 class="filter-group__title">Traders</h2>
                    <button class="filter-btn is-active" type="button" data-filter-type="trader" data-filter-value="all">All Traders</button>
                    <?php
                    $traders = [];
                    foreach ($products as $product) {
                        $traders[(string) $product['TRADER_NAME']] = true;
                    }
                    foreach (array_keys($traders) as $traderName):
                    ?>
                        <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="<?php echo e($buildTraderType($traderName)); ?>"><?php echo e($traderName); ?></button>
                    <?php endforeach; ?>
                </div>

                <!-- Price tier filter group follows the wireframe pricing options. -->
                <div class="filter-group">
                    <h2 class="filter-group__title">Price Range</h2>
                    <button class="filter-btn is-active" type="button" data-filter-type="price" data-filter-value="all">All Prices</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="0-10">$0 - $10</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="10-20">$10 - $20</button>
                </div>
            </aside>

            <div class="category-products" aria-live="polite">
                <?php if ($dbError !== null): ?>
                    <p class="page-message page-message--error">Unable to load products from Oracle: <?php echo e($dbError); ?></p>
                <?php endif; ?>
                <div class="category-grid" data-category-grid>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $price = (float) $product['PRICE'];
                        $priceTier = $price <= 10 ? '0-10' : '10-20';
                        $traderName = (string) $product['TRADER_NAME'];
                        ?>
                        <article class="category-card" data-product-card data-trader-type="<?php echo e($buildTraderType($traderName)); ?>" data-price-tier="<?php echo e($priceTier); ?>" data-name="<?php echo e($product['PRODUCT_NAME']); ?>" data-trader="<?php echo e($traderName); ?>">
                            <div class="category-card__media">
                                <img src="<?php echo e($resolveImage($product['PRODUCT_IMAGE'] ?? null)); ?>" alt="<?php echo e($product['PRODUCT_NAME']); ?>" />
                            </div>
                            <div class="category-card__body">
                                <p class="category-card__trader">Trader: <?php echo e($traderName); ?></p>
                                <h3><?php echo e($product['PRODUCT_NAME']); ?></h3>
                                <p class="category-card__rating" aria-label="Price for <?php echo e($product['PRODUCT_NAME']); ?>">$<?php echo e(number_format($price, 2)); ?></p>
                                <a class="category-card__button" href="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>">View Product</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Empty state appears when active filters remove all visible cards. -->
                <p class="category-empty" data-empty-state hidden>No products match your current filters.</p>
            </div>
        </div>
    </section>
</main>
<?php
// Shared footer closes document and keeps footer links consistent across pages.
require __DIR__ . '/components/footer.php';
?>