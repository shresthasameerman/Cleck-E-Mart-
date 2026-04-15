<?php
$pageTitle = 'Product View | Cleck E-Mart';
$metaDescription = 'Product details page for viewing trader info, ratings, description, and quantity.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="product-page" data-product-page data-unit-price="5">
    <!--
        Product layout mirrors the provided wireframe:
        left = image panel, right = product metadata and actions.
    -->
    <section class="product-content" aria-labelledby="product-name-title">
        <div class="container product-layout">
            <div class="product-media" aria-label="Product image panel">
                <img src="assets/images/fresh-apples.svg" alt="Organic apples" />
            </div>

            <article class="product-details" aria-label="Product information">
                <p class="product-box product-trader">Trader: Fresh Farm Traders</p>

                <h1 id="product-name-title" class="product-box product-name">Product Name: Organic Apples</h1>

                <p class="product-box product-rating" aria-label="Rating 5 out of 5 from 120 reviews">
                    <span class="product-stars" aria-hidden="true">
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                        <span class="product-star">&#9733;</span>
                    </span>
                    <span>(120 Reviews)</span>
                </p>

                <p class="product-box product-description">
                    Product Description: Fresh organic apples sourced from local farms. High quality, chemical-free, and perfect for daily consumption.
                </p>

                <div class="product-box product-quantity" aria-label="Quantity selector">
                    <p class="product-quantity__label">Quantity:</p>
                    <div class="product-qty-controls">
                        <button class="product-qty-button" type="button" data-product-qty-action="decrease" aria-label="Decrease quantity">-</button>
                        <span class="product-qty-value" data-product-qty-value>1</span>
                        <button class="product-qty-button" type="button" data-product-qty-action="increase" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <!--
                    Add to basket action currently routes to cart page.
                    You can swap this for a POST form once backend cart APIs are connected.
                -->
                <a class="product-add-button" href="cart.php" data-add-to-basket>
                    Add to Basket
                </a>
            </article>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
