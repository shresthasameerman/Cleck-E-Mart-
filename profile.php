<?php
// Reuses site-wide header/navigation to keep profile page in the same theme.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="profile-page">

    <!-- Page intro: mirrors auth-intro / contact-intro pattern -->
    <section class="profile-intro" aria-labelledby="profile-title">
        <div class="container profile-intro__inner">
            <p class="profile-intro__eyebrow">My Account</p>
            <h1 id="profile-title">Welcome back</h1>
        </div>
    </section>

    <!-- User summary banner -->
    <section class="profile-banner" aria-label="User summary">
        <div class="container">
            <div class="profile-banner__card">
                <div class="profile-avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8.2" r="3.8"/>
                        <path d="M5 19.5c2-4 4.5-5.5 7-5.5s5 1.5 7 5.5"/>
                    </svg>
                </div>
                <div class="profile-banner__info">
                    <!--
                        Backend note: replace these placeholders with session data.
                        Example: <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?>
                    -->
                    <p class="profile-banner__name">Jane Doe</p>
                    <p class="profile-banner__email">jane@example.com</p>
                    <span class="profile-banner__badge">Customer</span>
                </div>
                <div class="profile-banner__stats">
                    <div class="profile-stat">
                        <span class="profile-stat__value">12</span>
                        <span class="profile-stat__label">Orders</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat__value">3</span>
                        <span class="profile-stat__label">Reviews</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat__value">2</span>
                        <span class="profile-stat__label">Saved</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Two-column layout: sidebar nav left, content right -->
    <section class="profile" aria-label="Profile sections">
        <div class="container">
            <div class="profile-grid">

                <!-- SIDEBAR -->
                <aside class="profile-sidebar" aria-label="Profile navigation">
                    <nav class="profile-nav" aria-label="Account sections">
                        <a class="profile-nav__item is-active" href="#orders" data-profile-tab="orders">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <path d="M8 9h8M8 13h5"/>
                                </svg>
                            </span>
                            My Orders
                        </a>
                        <a class="profile-nav__item" href="#account" data-profile-tab="account">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8.2" r="3.2"/>
                                    <path d="M6.5 19.2c1.6-3 3.8-4.5 5.5-4.5s3.9 1.5 5.5 4.5"/>
                                </svg>
                            </span>
                            Account Details
                        </a>
                        <a class="profile-nav__item" href="#history" data-profile-tab="history">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 3"/>
                                </svg>
                            </span>
                            Collection History
                        </a>
                        <a class="profile-nav__item" href="#reviews" data-profile-tab="reviews">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </span>
                            My Reviews
                        </a>
                        <a class="profile-nav__item" href="#password" data-profile-tab="password">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                                    <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                                </svg>
                            </span>
                            Password
                        </a>
                        <!--
                            Backend note: sign-out should POST to a logout endpoint to
                            destroy the session. Example: action="logout.php" method="post"
                        -->
                        <a class="profile-nav__item profile-nav__item--danger" href="logout.php">
                            <span class="profile-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                            </span>
                            Sign Out
                        </a>
                    </nav>
                </aside>

                <!-- MAIN CONTENT PANELS -->
                <div class="profile-content">

                    <!-- MY ORDERS -->
                    <section class="profile-panel is-active" id="orders" data-profile-panel="orders" aria-labelledby="orders-title">
                        <h2 id="orders-title" class="profile-panel__title">My Recent Orders</h2>

                        <!--
                            Backend note: loop over orders from DB.
                            Fields: order_id, date, status, total, items_summary.
                        -->
                        <div class="order-list">

                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Order #EM-00124</p>
                                        <p class="order-card__date">12 April 2026</p>
                                    </div>
                                    <span class="order-card__status order-card__status--delivered">Delivered</span>
                                </div>
                                <p class="order-card__summary">Everyday Essentials Pack &times; 1, Home Utility Set &times; 2</p>
                                <div class="order-card__footer">
                                    <span class="order-card__total">Total: $100.00</span>
                                    <a class="order-card__link" href="#">View details</a>
                                </div>
                            </div>

                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Order #EM-00118</p>
                                        <p class="order-card__date">3 April 2026</p>
                                    </div>
                                    <span class="order-card__status order-card__status--processing">Processing</span>
                                </div>
                                <p class="order-card__summary">Weekend Carry Bag &times; 1</p>
                                <div class="order-card__footer">
                                    <span class="order-card__total">Total: $52.00</span>
                                    <a class="order-card__link" href="#">View details</a>
                                </div>
                            </div>

                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Order #EM-00103</p>
                                        <p class="order-card__date">18 March 2026</p>
                                    </div>
                                    <span class="order-card__status order-card__status--delivered">Delivered</span>
                                </div>
                                <p class="order-card__summary">Everyday Essentials Pack &times; 3</p>
                                <div class="order-card__footer">
                                    <span class="order-card__total">Total: $72.00</span>
                                    <a class="order-card__link" href="#">View details</a>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- ACCOUNT DETAILS -->
                    <section class="profile-panel" id="account" data-profile-panel="account" aria-labelledby="account-title" hidden>
                        <h2 id="account-title" class="profile-panel__title">Account Details</h2>
                        <!--
                            Backend note: set action to your update endpoint (example: update-profile.php).
                            Pre-fill values from session or DB query.
                        -->
                        <form class="profile-form" action="#" method="post" novalidate>
                            <div class="profile-form__grid">
                                <label>
                                    <span>First Name*</span>
                                    <input type="text" name="first_name" required autocomplete="given-name" placeholder="Enter first name" value="Jane" />
                                </label>
                                <label>
                                    <span>Last Name*</span>
                                    <input type="text" name="last_name" required autocomplete="family-name" placeholder="Enter last name" value="Doe" />
                                </label>
                            </div>
                            <label>
                                <span>Email*</span>
                                <input type="email" name="email" required autocomplete="email" placeholder="name@example.com" value="jane@example.com" />
                            </label>
                            <label>
                                <span>Phone</span>
                                <input type="tel" name="phone" autocomplete="tel" placeholder="+977 98XXXXXXXX" />
                            </label>
                            <button class="profile-submit" type="submit">
                                Save Changes
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </button>
                        </form>
                    </section>

                    <!-- COLLECTION HISTORY -->
                    <section class="profile-panel" id="history" data-profile-panel="history" aria-labelledby="history-title" hidden>
                        <h2 id="history-title" class="profile-panel__title">Collection History</h2>
                        <div class="order-list">
                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Collected: 13 April 2026</p>
                                        <p class="order-card__date">Order #EM-00124</p>
                                    </div>
                                    <span class="order-card__status order-card__status--delivered">Collected</span>
                                </div>
                                <p class="order-card__summary">Picked up from 123 Market Street, Kathmandu</p>
                            </div>
                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Collected: 20 March 2026</p>
                                        <p class="order-card__date">Order #EM-00103</p>
                                    </div>
                                    <span class="order-card__status order-card__status--delivered">Collected</span>
                                </div>
                                <p class="order-card__summary">Picked up from 123 Market Street, Kathmandu</p>
                            </div>
                        </div>
                    </section>

                    <!-- MY REVIEWS -->
                    <section class="profile-panel" id="reviews" data-profile-panel="reviews" aria-labelledby="reviews-title" hidden>
                        <h2 id="reviews-title" class="profile-panel__title">My Reviews</h2>
                        <div class="order-list">
                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Everyday Essentials Pack</p>
                                        <p class="order-card__date">10 April 2026</p>
                                    </div>
                                    <span class="profile-stars" aria-label="4 out of 5 stars">★★★★☆</span>
                                </div>
                                <p class="order-card__summary">"Great quality basics, arrived well packaged. Would order again."</p>
                            </div>
                            <div class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <p class="order-card__id">Home Utility Set</p>
                                        <p class="order-card__date">2 April 2026</p>
                                    </div>
                                    <span class="profile-stars" aria-label="5 out of 5 stars">★★★★★</span>
                                </div>
                                <p class="order-card__summary">"Exactly as described. Very compact and well made."</p>
                            </div>
                        </div>
                    </section>

                    <!-- PASSWORD -->
                    <section class="profile-panel" id="password" data-profile-panel="password" aria-labelledby="password-title" hidden>
                        <h2 id="password-title" class="profile-panel__title">Change Password</h2>
                        <!--
                            Backend note: verify current_password before hashing and saving new_password.
                        -->
                        <form class="profile-form" action="#" method="post" novalidate>
                            <label>
                                <span>Current Password*</span>
                                <input type="password" name="current_password" required autocomplete="current-password" placeholder="Enter current password" />
                            </label>
                            <label>
                                <span>New Password*</span>
                                <input type="password" name="new_password" required autocomplete="new-password" placeholder="Create a strong password" />
                            </label>
                            <label>
                                <span>Confirm New Password*</span>
                                <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="Repeat new password" />
                            </label>
                            <button class="profile-submit" type="submit">
                                Update Password
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </button>
                        </form>
                    </section>

                </div>
            </div>
        </div>
    </section>

</main>
<?php
// Shared footer keeps legal/quick links unified across pages.
require __DIR__ . '/components/footer.php';
?>
