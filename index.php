<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Supports multiple common login flags so this works with your current or future auth flow.
$isLoggedIn = !empty($_SESSION['user_id'])
    || !empty($_SESSION['customer_id'])
    || !empty($_SESSION['is_logged_in'])
    || !empty($_SESSION['logged_in'])
    || !empty($_SESSION['customer_logged_in']);

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

            <div class="card-grid card-grid--four">
                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Featured product" />
                    </div>
                    <div class="product-card__content">
                        <h3>Everyday Essentials Pack</h3>
                        <p>Curated basics with a soft, durable finish.</p>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Featured product" />
                    </div>
                    <div class="product-card__content">
                        <h3>Home Utility Set</h3>
                        <p>Practical picks for everyday use at home.</p>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Featured product" />
                    </div>
                    <div class="product-card__content">
                        <h3>Weekend Carry Bag</h3>
                        <p>Lightweight and sturdy for your daily errands.</p>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-card__media">
                        <img src="assets/images/product-placeholder.svg" alt="Featured product" />
                    </div>
                    <div class="product-card__content">
                        <h3>Family Pantry Bundle</h3>
                        <p>Well-balanced grocery set for weekly restocks.</p>
                    </div>
                </article>
            </div>
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

            <div class="card-grid">
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