<?php
$pageTitle = 'Browse Category | Cleck E-Mart';
$metaDescription = 'Browse product categories and discover items from local traders.';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/auth_helpers.php';

$selectedCategoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$sortOrder = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'name_asc';
$minPrice = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);

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

    $relative = 'assets/images/products/' . ltrim($clean, '/');
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
            $sql .= ' WHERE p.category_id = :category_id AND p.product_verification_status = :verification_status AND s.shop_status = \'ACTIVE\'';
            $binds['category_id'] = $selectedCategoryId;
        } else {
            $sql .= ' WHERE p.product_verification_status = :verification_status AND s.shop_status = \'ACTIVE\'';
        }
        $binds['verification_status'] = 'APPROVED';

        if ($minPrice !== false && $minPrice !== null) {
            $sql .= ' AND p.price >= :min_price';
            $binds['min_price'] = $minPrice;
        }
        
        if ($maxPrice !== false && $maxPrice !== null) {
            $sql .= ' AND p.price <= :max_price';
            $binds['max_price'] = $maxPrice;
        }

        switch ($sortOrder) {
            case 'price_asc':
                $sql .= ' ORDER BY p.price ASC';
                break;
            case 'price_desc':
                $sql .= ' ORDER BY p.price DESC';
                break;
            case 'name_desc':
                $sql .= ' ORDER BY p.product_name DESC';
                break;
            case 'name_asc':
            default:
                $sql .= ' ORDER BY p.product_name ASC';
                break;
        }
        
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
            <label class="sr-only" for="category-search-input">Search products</label>
            <input
                id="category-search-input"
                class="category-search__input"
                type="search"
                placeholder="Search products..."
                data-category-search
            />
            
            <form method="get" action="category.php" style="display: flex; gap: 0.5rem; align-items: center;">
                <?php if ($selectedCategoryId): ?>
                    <input type="hidden" name="category_id" value="<?php echo $selectedCategoryId; ?>" />
                <?php endif; ?>
                <label for="sort-dropdown" style="font-weight: 600;">Sort By:</label>
                <select id="sort-dropdown" name="sort" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 1rem;">
                    <option value="name_asc" <?php echo $sortOrder === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sortOrder === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo $sortOrder === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo $sortOrder === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                </select>
            </form>
        </div>
    </section>

    <!-- Main content region: left filter rail + right product cards grid. -->
    <section class="category-content" aria-label="Fresh produce listing">
        <div class="container category-layout">
            <aside class="filter-panel" aria-label="Product filters">
                <!-- Category filter group -->
                <div class="filter-group">
                    <h2 class="filter-group__title">Categories</h2>
                    <button class="filter-btn is-active" type="button" data-filter-type="category" data-filter-value="all">All Categories</button>
                    <?php
                    $catNames = [];
                    foreach ($products as $product) {
                        if (!empty($product['CATEGORY_NAME'])) {
                            $catNames[(string) $product['CATEGORY_NAME']] = true;
                        }
                    }
                    foreach (array_keys($catNames) as $catName):
                    ?>
                        <button class="filter-btn" type="button" data-filter-type="category" data-filter-value="<?php echo e($buildTraderType($catName)); ?>"><?php echo e($catName); ?></button>
                    <?php endforeach; ?>
                </div>

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
                        <article class="category-card" data-product-card data-category-type="<?php echo e($buildTraderType($product['CATEGORY_NAME'])); ?>" data-trader-type="<?php echo e($buildTraderType($traderName)); ?>" data-price-tier="<?php echo e($priceTier); ?>" data-name="<?php echo e($product['PRODUCT_NAME']); ?>" data-trader="<?php echo e($traderName); ?>">
                            <div class="category-card__media">
                                <img src="<?php echo e($resolveImage($product['PRODUCT_IMAGE'] ?? null)); ?>" alt="<?php echo e($product['PRODUCT_NAME']); ?>" />
                            </div>
                            <div class="category-card__body">
                                <p class="category-card__trader">Trader: <?php echo e($traderName); ?></p>
                                <h3><?php echo e($product['PRODUCT_NAME']); ?></h3>
                                <p class="category-card__rating" aria-label="Price for <?php echo e($product['PRODUCT_NAME']); ?>">$<?php echo e(number_format($price, 2)); ?></p>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
                                    <a class="category-card__button" href="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>" style="margin-top:0;">View Product</a>
                                    <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                                        <form method="post" action="wishlist_action.php" style="display:inline; margin-left: 0.5rem;">
                                            <input type="hidden" name="action" value="add" />
                                            <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                                            <input type="hidden" name="return_url" value="category.php<?php echo isset($_GET['category_id']) ? '?category_id=' . (int)$_GET['category_id'] : ''; ?>" />
                                            <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:1.5rem;" aria-label="Add to Wishlist" title="Add to Wishlist">♥</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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