<?php
$pageTitle = 'My Profile | Cleck E-Mart';
$metaDescription = 'View your profile, recent orders, and account details on Cleck E-Mart.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="customer-page">
    <section class="customer-hero" aria-labelledby="customer-title">
        <div class="container">
            <div class="customer-hero__panel">
                <h1 id="customer-title">Customer Profile</h1>
                <p>Manage your account details and review your latest activity.</p>
            </div>
        </div>
    </section>

    <section class="customer-content" aria-label="Customer profile overview">
        <div class="container customer-layout">
            <aside class="customer-sidebar" aria-label="Customer account navigation">
                <a class="customer-sidebar__item is-active" href="#customer-orders">My Orders</a>
                <a class="customer-sidebar__item" href="#customer-account">Account Details</a>
                <a class="customer-sidebar__item" href="#customer-orders">Collection History</a>
                <a class="customer-sidebar__item" href="#customer-orders">My Reviews</a>
                <a class="customer-sidebar__item" href="auth.php?mode=login">Password</a>
                <a class="customer-sidebar__item" href="auth.php?mode=login">Sign Out</a>
            </aside>

            <div class="customer-main">
                <article class="customer-card" id="customer-orders" aria-labelledby="recent-orders-title">
                    <h2 id="recent-orders-title">My Recent Orders</h2>
                    <div class="customer-orders">
                        <div class="customer-order-row">
                            <p>Order #1092</p>
                            <span>Collected - Apr 11, 2026</span>
                        </div>
                        <div class="customer-order-row">
                            <p>Order #1084</p>
                            <span>Collected - Apr 08, 2026</span>
                        </div>
                        <div class="customer-order-row">
                            <p>Order #1079</p>
                            <span>Collected - Apr 05, 2026</span>
                        </div>
                    </div>
                </article>

                <article class="customer-card" id="customer-account" aria-labelledby="account-info-title">
                    <h2 id="account-info-title">Account Information</h2>
                    <form class="customer-form" action="#" method="post" novalidate>
                        <label>
                            <span>First Name</span>
                            <input type="text" name="first_name" value="Sameer" autocomplete="given-name" />
                        </label>
                        <label>
                            <span>Last Name</span>
                            <input type="text" name="last_name" value="Khan" autocomplete="family-name" />
                        </label>
                        <label>
                            <span>Email</span>
                            <input type="email" name="email" value="sameer@example.com" autocomplete="email" />
                        </label>
                        <label>
                            <span>Phone</span>
                            <input type="tel" name="phone" value="+44 7700 900123" autocomplete="tel" />
                        </label>
                        <button class="customer-form__button" type="submit">Save Changes</button>
                    </form>
                </article>
            </div>

            <aside class="customer-summary" aria-label="Order pricing summary">
                <h2>Pricing Summary</h2>
                <p><span>Subtotal</span><strong>$18.50</strong></p>
                <p><span>Collection Fee</span><strong>$1.50</strong></p>
                <p class="customer-summary__total"><span>Total Paid</span><strong>$20.00</strong></p>

                <h3>Additional Info</h3>
                <p><span>Collection Slot</span><strong>Sat 9:00 - 10:00</strong></p>
                <p><span>Payment Method</span><strong>VISA •••• 4281</strong></p>
            </aside>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
