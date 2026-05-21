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
        return 'assets/images/icons/product-placeholder.svg';
    }

    $clean = trim($path);
    if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, 'assets/')) {
        if (str_starts_with($clean, 'assets/')) {
            $absolute = __DIR__ . '/' . $clean;
            return file_exists($absolute) ? $clean : 'assets/images/icons/product-placeholder.svg';
        }
        return $clean;
    }

    $relative = 'assets/images/products/' . ltrim($clean, '/');
    $absolute = __DIR__ . '/' . $relative;
    return file_exists($absolute) ? $relative : 'assets/images/icons/product-placeholder.svg';
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
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center;">
                <div style="flex: 1; min-width: 250px;">
                    <label class="sr-only" for="category-search-input">Search products</label>
                    <input
                        id="category-search-input"
                        class="category-search__input"
                        type="search"
                        placeholder="Search products..."
                        data-category-search
                    />
                </div>
                
                <form method="get" action="category.php" style="display: flex; gap: 1rem; align-items: center; margin: 0;">
                    <?php if ($selectedCategoryId): ?>
                        <input type="hidden" name="category_id" value="<?php echo $selectedCategoryId; ?>" />
                    <?php endif; ?>
                    <label for="sort-dropdown" style="font-weight: 600; color: var(--color-text); font-family: 'Inter', sans-serif; white-space: nowrap;">Sort By:</label>
                    <div style="position: relative;">
                        <select id="sort-dropdown" name="sort" onchange="this.form.submit()" style="appearance: none; padding: 0.75rem 2.5rem 0.75rem 1rem; border: 1px solid rgba(0, 0, 0, 0.1); border-radius: var(--radius-sm); font-size: 0.95rem; font-family: 'Inter', sans-serif; background: #ffffff; color: var(--color-text); cursor: pointer; outline: none; box-shadow: 0 2px 4px rgba(0,0,0,0.02); min-width: 200px;">
                            <option value="name_asc" <?php echo $sortOrder === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $sortOrder === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo $sortOrder === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo $sortOrder === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        </select>
                        <svg viewBox="0 0 24 24" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); width: 1.2rem; height: 1.2rem; fill: none; stroke: var(--color-text); stroke-width: 2; pointer-events: none;"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </div>
                </form>
            </div>
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



                <!-- Price tier filter group follows the wireframe pricing options. -->
                <div class="filter-group">
                    <h2 class="filter-group__title">Price Range</h2>
                    <button class="filter-btn is-active" type="button" data-filter-type="price" data-filter-value="all">All Prices</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="0-10">$0 - $10</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="10-20">$10 - $20</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="20-50">$20 - $50</button>
                    <button class="filter-btn" type="button" data-filter-type="price" data-filter-value="50+">$50+</button>
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
                        $priceTier = '50+';
                        if ($price <= 10) $priceTier = '0-10';
                        elseif ($price <= 20) $priceTier = '10-20';
                        elseif ($price <= 50) $priceTier = '20-50';
                        
                        $traderName = (string) $product['TRADER_NAME'];
                        ?>
                        <article class="category-card" style="display: flex; flex-direction: column; height: 100%;" data-product-card data-category-type="<?php echo e($buildTraderType($product['CATEGORY_NAME'])); ?>" data-trader-type="<?php echo e($buildTraderType($traderName)); ?>" data-price-tier="<?php echo e($priceTier); ?>" data-name="<?php echo e($product['PRODUCT_NAME']); ?>" data-trader="<?php echo e($traderName); ?>">
                            <div class="category-card__media">
                                <img src="<?php echo e($resolveImage($product['PRODUCT_IMAGE'] ?? null)); ?>" alt="<?php echo e($product['PRODUCT_NAME']); ?>" />
                            </div>
                            <div class="category-card__body" style="display: flex; flex-direction: column; flex-grow: 1;">
                                <p class="category-card__trader" style="margin-bottom: 0.25rem;">Trader: <?php echo e($traderName); ?></p>
                                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; line-height: 1.4; flex-grow: 1;"><?php echo e($product['PRODUCT_NAME']); ?></h3>
                                <p class="category-card__rating" aria-label="Price for <?php echo e($product['PRODUCT_NAME']); ?>" style="margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem;">$<?php echo e(number_format($price, 2)); ?></p>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top: auto; gap: 0.5rem;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; flex: 1;">
                                        <a class="category-card__button" href="product.php?product_id=<?php echo e($product['PRODUCT_ID']); ?>" style="display: flex; align-items: center; justify-content: center; height: 100%; text-align: center; margin: 0; padding: 0.6rem 0.5rem; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; box-sizing: border-box;">View</a>
                                        
                                        <form method="post" action="product.php" style="margin: 0; display: flex; height: 100%;">
                                            <input type="hidden" name="action" value="add_to_cart" />
                                            <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                                            <input type="hidden" name="quantity" value="1" />
                                            <button type="submit" class="category-card__button" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; margin: 0; border: none; cursor: pointer; padding: 0.6rem 0.5rem; text-align: center; font-size: 0.85rem; font-family: inherit; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; box-sizing: border-box;">Add</button>
                                        </form>
                                    </div>
                                    <?php if (is_logged_in() && current_role() === 'CUSTOMER'): ?>
                                        <form method="post" action="wishlist_action.php" style="display:inline; flex-shrink: 0;">
                                            <input type="hidden" name="action" value="add" />
                                            <input type="hidden" name="product_id" value="<?php echo e($product['PRODUCT_ID']); ?>" />
                                            <input type="hidden" name="return_url" value="category.php<?php echo isset($_GET['category_id']) ? '?category_id=' . (int)$_GET['category_id'] : ''; ?>" />
                                            <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:1.5rem; padding: 0;" aria-label="Add to Wishlist" title="Add to Wishlist">♥</button>
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
