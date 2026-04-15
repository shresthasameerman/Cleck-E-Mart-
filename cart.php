<?php
$pageTitle = 'Your Basket | Cleck E-Mart';
$metaDescription = 'Review your basket, update item quantities, and choose a collection slot.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="cart-page" data-cart-page>
    <section class="cart-hero" aria-labelledby="cart-title">
        <div class="container">
            <div class="cart-hero__title-wrap">
                <h1 id="cart-title">Your Basket</h1>
            </div>
        </div>
    </section>

    <section class="cart-content" aria-label="Basket contents">
        <div class="container cart-layout">
            <div class="cart-items" aria-live="polite">
                <article class="cart-item" data-cart-item data-cart-name="Farm Fresh Apples" data-cart-qty="1" data-cart-price="5">
                    <div class="cart-item__media">
                        <img src="assets/images/fresh-apples.svg" alt="Fresh apples" />
                    </div>

                    <div class="cart-item__details">
                        <p class="cart-item__trader">Trader: Farm Fresh Apples</p>
                        <h2 class="cart-item__title">Farm Fresh Apples</h2>
                    </div>

                    <div class="cart-item__controls" aria-label="Quantity controls for Farm Fresh Apples">
                        <button class="cart-qty-button" type="button" data-cart-qty-action="decrease" aria-label="Decrease quantity for Farm Fresh Apples">-</button>
                        <span class="cart-qty-value" data-cart-qty-value>1</span>
                        <button class="cart-qty-button" type="button" data-cart-qty-action="increase" aria-label="Increase quantity for Farm Fresh Apples">+</button>
                    </div>

                    <div class="cart-item__price" aria-label="Line total for Farm Fresh Apples" data-cart-line-total>$5</div>
                </article>

                <article class="cart-item" data-cart-item data-cart-name="Green Valley Carrots" data-cart-qty="2" data-cart-price="4">
                    <div class="cart-item__media">
                        <img src="assets/images/fresh-carrots.svg" alt="Fresh carrots" />
                    </div>

                    <div class="cart-item__details">
                        <p class="cart-item__trader">Trader: Green Valley Carrots</p>
                        <h2 class="cart-item__title">Green Valley Carrots</h2>
                    </div>

                    <div class="cart-item__controls" aria-label="Quantity controls for Green Valley Carrots">
                        <button class="cart-qty-button" type="button" data-cart-qty-action="decrease" aria-label="Decrease quantity for Green Valley Carrots">-</button>
                        <span class="cart-qty-value" data-cart-qty-value>2</span>
                        <button class="cart-qty-button" type="button" data-cart-qty-action="increase" aria-label="Increase quantity for Green Valley Carrots">+</button>
                    </div>

                    <div class="cart-item__price" aria-label="Line total for Green Valley Carrots" data-cart-line-total>$8</div>
                </article>
            </div>

            <aside class="cart-summary" aria-label="Order summary">
                <h2 class="cart-summary__title">Order Summary</h2>

                <div class="cart-summary__items" data-cart-summary-items>
                    <p class="cart-summary__line">Apples x1 $5</p>
                    <p class="cart-summary__line">Carrots x2 $8</p>
                </div>

                <div class="cart-summary__divider" aria-hidden="true"></div>

                <p class="cart-summary__total">
                    <span>Total</span>
                    <strong data-cart-total>$13</strong>
                </p>

                <a class="cart-summary__button button" href="collection.php">Choose Your Collection Slot</a>
            </aside>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>