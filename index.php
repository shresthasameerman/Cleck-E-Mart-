<?php
// Shared header includes <head>, navigation, and opening <body> tag.
// Keeping this in one component ensures all pages stay visually consistent.
require __DIR__ . '/components/header.php';
?>
<!--
    Homepage layout summary:
    1) Hero with search UI (currently static; backend search can be wired via form action)
    2) Featured products (currently hard-coded sample cards)
    3) CTA block for navigation focus
-->
<main id="main-content">
    <!-- Hero / search area -->
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__search-panel">
                <h1 id="hero-title" class="sr-only">Shop the latest essentials</h1>
                <!--
                    Search form backend note:
                    - Replace action="#" with your search endpoint (example: search.php)
                    - Read query from GET parameter "q"
                -->
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

    <!-- Featured products grid -->
    <section class="featured" aria-labelledby="featured-title">
        <div class="container">
            <div class="section-heading">
                <p class="section-heading__eyebrow">Featured picks</p>
                <h2 id="featured-title">Popular products for a clean, fast browse</h2>
            </div>

            <div class="card-grid">
                <!--
                    Product card template note:
                    This repeated HTML can be rendered from database rows in a loop.
                    Typical fields: image_url, title, short_description, price, product_slug/id.
                -->
                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Minimal lifestyle product illustration" />
                    </div>
                    <div class="product-card__content">
                        <h3>Everyday Essentials Pack</h3>
                        <p>Curated basics with a soft, durable finish.</p>
                        <div class="product-card__meta">
                            <span class="product-card__price">$24.00</span>
                            <a class="product-card__link" href="#">View details</a>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Minimal lifestyle product illustration" />
                    </div>
                    <div class="product-card__content">
                        <h3>Home Utility Set</h3>
                        <p>Compact, well-made pieces that keep daily life organized.</p>
                        <div class="product-card__meta">
                            <span class="product-card__price">$38.00</span>
                            <a class="product-card__link" href="#">View details</a>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Minimal lifestyle product illustration" />
                    </div>
                    <div class="product-card__content">
                        <h3>Weekend Carry Bag</h3>
                        <p>A lightweight carryall with enough structure for everyday use.</p>
                        <div class="product-card__meta">
                            <span class="product-card__price">$52.00</span>
                            <a class="product-card__link" href="#">View details</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Call to action -->
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
<?php
// Shared footer closes the page structure and contains quick links.
require __DIR__ . '/components/footer.php';
?>