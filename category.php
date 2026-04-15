<?php
// Shared header keeps this page visually and structurally consistent with the site.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="category-page" data-category-page>
    <!-- Category title panel based on the wireframe header block. -->
    <section class="category-hero" aria-labelledby="category-title">
        <div class="container">
            <div class="category-hero__title-wrap">
                <h1 id="category-title">Category: Fresh Produce</h1>
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
                    <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="butchers">Butchers</button>
                    <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="greengrocers">Greengrocers</button>
                    <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="fishmongers">Fishmongers</button>
                    <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="bakeries">Bakeries</button>
                    <button class="filter-btn" type="button" data-filter-type="trader" data-filter-value="delicatessens">Delicatessens</button>
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
                <div class="category-grid" data-category-grid>
                    <!-- Product card template can be server-rendered from DB rows in the future. -->
                    <article class="category-card" data-product-card data-trader-type="butchers" data-price-tier="10-20" data-name="Chicken Breast" data-trader="Prime Butchers">
                        <div class="category-card__media">
                            <img src="assets/images/trader-butchers.svg" alt="Fresh butcher cuts on a display board" />
                        </div>
                        <div class="category-card__body">
                            <p class="category-card__trader">Trader: Prime Butchers</p>
                            <h3>Chicken Breast</h3>
                            <p class="category-card__rating" aria-label="Rating 5 out of 5 with 42 reviews">&#9733;&#9733;&#9733;&#9733;&#9733; (42)</p>
                            <!-- Route each card to the product view page; this can later include product IDs. -->
                            <a class="category-card__button" href="product.php">View Product</a>
                        </div>
                    </article>

                    <article class="category-card" data-product-card data-trader-type="greengrocers" data-price-tier="0-10" data-name="Apples" data-trader="Fresh Greens">
                        <div class="category-card__media">
                            <img src="assets/images/fresh-apples.svg" alt="Fresh red apples in a basket" />
                        </div>
                        <div class="category-card__body">
                            <p class="category-card__trader">Trader: Fresh Greens</p>
                            <h3>Apples</h3>
                            <p class="category-card__rating" aria-label="Rating 5 out of 5 with 50 reviews">&#9733;&#9733;&#9733;&#9733;&#9733; (50)</p>
                            <a class="category-card__button" href="product.php">View Product</a>
                        </div>
                    </article>

                    <article class="category-card" data-product-card data-trader-type="fishmongers" data-price-tier="10-20" data-name="Salmon Fillet" data-trader="Ocean Catch">
                        <div class="category-card__media">
                            <img src="assets/images/trader-fishmongers.svg" alt="Fresh fish fillets arranged on ice" />
                        </div>
                        <div class="category-card__body">
                            <p class="category-card__trader">Trader: Ocean Catch</p>
                            <h3>Salmon Fillet</h3>
                            <p class="category-card__rating" aria-label="Rating 5 out of 5 with 33 reviews">&#9733;&#9733;&#9733;&#9733;&#9733; (33)</p>
                            <a class="category-card__button" href="product.php">View Product</a>
                        </div>
                    </article>

                    <article class="category-card" data-product-card data-trader-type="bakeries" data-price-tier="10-20" data-name="Sourdough Loaf" data-trader="Golden Crust Bakery">
                        <div class="category-card__media">
                            <img src="assets/images/fresh-bread.svg" alt="Freshly baked bread loaf" />
                        </div>
                        <div class="category-card__body">
                            <p class="category-card__trader">Trader: Golden Crust Bakery</p>
                            <h3>Sourdough Loaf</h3>
                            <p class="category-card__rating" aria-label="Rating 5 out of 5 with 47 reviews">&#9733;&#9733;&#9733;&#9733;&#9733; (47)</p>
                            <a class="category-card__button" href="product.php">View Product</a>
                        </div>
                    </article>

                    <article class="category-card" data-product-card data-trader-type="delicatessens" data-price-tier="10-20" data-name="Olive Antipasto" data-trader="Fine Deli House">
                        <div class="category-card__media">
                            <img src="assets/images/trader-delicatessens.svg" alt="Assorted deli selection with olives and cured slices" />
                        </div>
                        <div class="category-card__body">
                            <p class="category-card__trader">Trader: Fine Deli House</p>
                            <h3>Olive Antipasto</h3>
                            <p class="category-card__rating" aria-label="Rating 5 out of 5 with 29 reviews">&#9733;&#9733;&#9733;&#9733;&#9733; (29)</p>
                            <a class="category-card__button" href="product.php">View Product</a>
                        </div>
                    </article>
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