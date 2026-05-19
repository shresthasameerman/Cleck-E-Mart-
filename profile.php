<?php
require_once __DIR__ . '/lib/auth_helpers.php';

require_login();

$errors = [];
$flashSuccess = get_flash('success');
$userId = (int) current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileAction = (string) ($_POST['profile_action'] ?? '');

    if ($profileAction === 'update_account') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($firstName === '' || $lastName === '' || $email === '') {
            $errors[] = 'First name, last name, and email are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }

        if ($errors === []) {
            try {
                $existing = db_is_offline()
                    ? (offline_email_taken_by_other($userId, $email) ? ['USER_ID' => -1] : null)
                    : db_fetch_one(
                        'SELECT user_id FROM "USER" WHERE LOWER(email) = LOWER(:email) AND user_id <> :user_id',
                        [
                            'email' => $email,
                            'user_id' => $userId,
                        ]
                    );

                if ($existing !== null) {
                    $errors[] = 'This email is already used by another account.';
                } else {
                    if (db_is_offline()) {
                        offline_update_user($userId, $firstName, $lastName, $email, $phone === '' ? null : $phone);
                    } else {
                        db_execute(
                            'UPDATE "USER"
                             SET first_name = :first_name,
                                 last_name = :last_name,
                                 email = :email,
                                 phone_number = :phone_number,
                                 updated_at = CURRENT_TIMESTAMP
                             WHERE user_id = :user_id',
                            [
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $email,
                                'phone_number' => $phone === '' ? null : $phone,
                                'user_id' => $userId,
                            ]
                        );
                    }

                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['email'] = $email;

                    set_flash('success', 'Account details updated successfully.');
                    redirect('profile.php?tab=account');
                }
            } catch (Throwable $exception) {
                $errors[] = 'Unable to update account: ' . $exception->getMessage();
            }
        }
    }

    if ($profileAction === 'remove_wishlist') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId !== false && $productId !== null) {
            try {
                if (!db_is_offline()) {
                    db_execute(
                        'DELETE FROM WISHLIST_ITEM 
                         WHERE product_id = :product_id 
                           AND wishlist_id IN (SELECT wishlist_id FROM WISHLIST WHERE customer_id = :customer_id)',
                        [
                            'product_id' => $productId,
                            'customer_id' => $userId
                        ]
                    );
                    set_flash('success', 'Item removed from wishlist.');
                }
                redirect('profile.php?tab=wishlist');
            } catch (Throwable $exception) {
                $errors[] = 'Unable to remove item from wishlist: ' . $exception->getMessage();
            }
        }
    }

    if ($profileAction === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'All password fields are required.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        }
        if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }

        if ($errors === []) {
            try {
                $dbUser = db_is_offline()
                    ? offline_user_by_id($userId)
                    : db_fetch_one('SELECT password FROM "USER" WHERE user_id = :user_id', ['user_id' => $userId]);
                if ($dbUser === null || !password_verify($currentPassword, (string) $dbUser['PASSWORD'])) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    if (db_is_offline()) {
                        offline_update_password($userId, $hashedPassword);
                    } else {
                        db_execute(
                            'UPDATE "USER" SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id',
                            [
                                'password' => $hashedPassword,
                                'user_id' => $userId,
                            ]
                        );
                    }

                    set_flash('success', 'Password updated successfully.');
                    redirect('profile.php?tab=password');
                }
            } catch (Throwable $exception) {
                $errors[] = 'Unable to update password: ' . $exception->getMessage();
            }
        }
    }
}

$user = db_is_offline()
    ? offline_user_by_id($userId)
    : db_fetch_one(
        'SELECT user_id, first_name, last_name, email, phone_number, "ROLE" AS role
         FROM "USER"
         WHERE user_id = :user_id',
        ['user_id' => $userId]
    );

if ($user === null) {
    set_flash('error', 'Unable to load profile details.');
    redirect('index.php');
}

$orders = [];
$historyOrders = [];
$reviews = [];
$orderCount = 0;
$reviewCount = 0;
$savedCount = 0;

$activeTab = (string) ($_GET['tab'] ?? 'orders');

// Use the logged-in user id as the customer id per requirements
$customerId = $userId;

if (current_role() === 'CUSTOMER' && $customerId > 0) {
    if (db_is_offline()) {
        $orders = offline_get_orders_for_customer($customerId, 5);
        $historyOrders = offline_get_orders_for_customer($customerId, 5);
        $reviews = offline_get_reviews_for_customer($customerId, 5);
        $orderCount = offline_count_orders($customerId);
        $reviewCount = offline_count_reviews($customerId);
        $savedCount = offline_count_saved($customerId);
    } else {
        // Orders with LISTAGG of product names
        $orders = db_fetch_all(
            "SELECT o.order_id,
                    o.order_date,
                    o.order_status,
                    NVL(SUM(oi.quantity * oi.unit_price), 0) AS ORDER_TOTAL,
                    LISTAGG(p.product_name, ', ') WITHIN GROUP (ORDER BY p.product_name) AS ITEMS
             FROM \"ORDER\" o
             JOIN ORDER_ITEM oi ON oi.order_id = o.order_id
             JOIN PRODUCT p ON p.product_id = oi.product_id
             WHERE o.customer_id = :customer_id
             GROUP BY o.order_id, o.order_date, o.order_status
             ORDER BY o.order_date DESC
             FETCH FIRST 5 ROWS ONLY",
            ['customer_id' => $customerId]
        );

        // Collection history (only COLLECTED)
        $historyOrders = db_fetch_all(
            "SELECT o.order_id,
                    o.order_date,
                    o.order_status,
                    NVL(SUM(oi.quantity * oi.unit_price), 0) AS ORDER_TOTAL,
                    LISTAGG(p.product_name, ', ') WITHIN GROUP (ORDER BY p.product_name) AS ITEMS
             FROM \"ORDER\" o
             JOIN ORDER_ITEM oi ON oi.order_id = o.order_id
             JOIN PRODUCT p ON p.product_id = oi.product_id
             WHERE o.customer_id = :customer_id AND o.order_status = 'COLLECTED'
             GROUP BY o.order_id, o.order_date, o.order_status
             ORDER BY o.order_date DESC
             FETCH FIRST 5 ROWS ONLY",
            ['customer_id' => $customerId]
        );

        $reviews = db_fetch_all(
            'SELECT r.review_date, r.rating, r."COMMENT" AS review_comment, p.product_name
             FROM REVIEW r
             JOIN PRODUCT p ON p.product_id = r.product_id
             WHERE r.customer_id = :customer_id
             ORDER BY r.review_date DESC
             FETCH FIRST 5 ROWS ONLY',
            ['customer_id' => $customerId]
        );

        $orderCountRow = db_fetch_one('SELECT COUNT(*) AS total_count FROM "ORDER" WHERE customer_id = :customer_id', ['customer_id' => $customerId]);
        $reviewCountRow = db_fetch_one('SELECT COUNT(*) AS total_count FROM REVIEW WHERE customer_id = :customer_id', ['customer_id' => $customerId]);
        $savedCountRow = db_fetch_one(
            'SELECT COUNT(*) AS total_count
             FROM WISHLIST_ITEM wi
             JOIN WISHLIST w ON w.wishlist_id = wi.wishlist_id
             WHERE w.customer_id = :customer_id',
            ['customer_id' => $customerId]
        );

        $orderCount = (int) ($orderCountRow['TOTAL_COUNT'] ?? 0);
        $reviewCount = (int) ($reviewCountRow['TOTAL_COUNT'] ?? 0);
        $savedCount = (int) ($savedCountRow['TOTAL_COUNT'] ?? 0);

        $wishlistItems = db_fetch_all(
            'SELECT p.product_id, p.product_name, p.price, p.product_image, d.discount_percentage, wi.added_date
             FROM WISHLIST_ITEM wi
             JOIN WISHLIST w ON w.wishlist_id = wi.wishlist_id
             JOIN PRODUCT p ON p.product_id = wi.product_id
             LEFT JOIN DISCOUNT d ON d.discount_id = p.discount_id AND d.end_date >= SYSDATE
             WHERE w.customer_id = :customer_id
             ORDER BY wi.added_date DESC',
            ['customer_id' => $customerId]
        );
    }
}

// Reuses site-wide header/navigation to keep profile page in the same theme.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="profile-page">

    <div class="container">
        <?php if ($flashSuccess !== null): ?>
            <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="page-message page-message--error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="admin-dashboard-layout">
            <!-- SIDEBAR -->
            <aside class="admin-sidebar" aria-label="Profile navigation">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">My Account</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Welcome back, <?php echo e($user['FIRST_NAME']); ?>!</p>
                    <p style="margin-top: 1rem; opacity: 0.8; font-size: 0.9rem;">
                        <?php echo e($orderCount); ?> Orders • <?php echo e($reviewCount); ?> Reviews • <?php echo e($savedCount); ?> Saved
                    </p>
                </div>

                <div class="admin-tabs" role="tablist" aria-label="Account sections">
                    <a class="tab-button<?php echo $activeTab === 'orders' ? ' active' : ''; ?>" href="profile.php?tab=orders" data-profile-tab="orders">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <path d="M8 9h8M8 13h5"/>
                        </svg>
                        My Orders
                    </a>
                    <a class="tab-button<?php echo $activeTab === 'account' ? ' active' : ''; ?>" href="profile.php?tab=account" data-profile-tab="account">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8.2" r="3.2"/>
                            <path d="M6.5 19.2c1.6-3 3.8-4.5 5.5-4.5s3.9 1.5 5.5 4.5"/>
                        </svg>
                        Account Details
                    </a>
                    <a class="tab-button<?php echo $activeTab === 'history' ? ' active' : ''; ?>" href="profile.php?tab=history" data-profile-tab="history">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 3"/>
                        </svg>
                        Collection History
                    </a>
                    <a class="tab-button<?php echo $activeTab === 'reviews' ? ' active' : ''; ?>" href="profile.php?tab=reviews" data-profile-tab="reviews">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        My Reviews
                    </a>
                    <a class="tab-button<?php echo $activeTab === 'wishlist' ? ' active' : ''; ?>" href="profile.php?tab=wishlist" data-profile-tab="wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Wishlist
                    </a>
                    <a class="tab-button<?php echo $activeTab === 'password' ? ' active' : ''; ?>" href="profile.php?tab=password" data-profile-tab="password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                        Password
                    </a>
                    <a class="tab-button" href="logout.php" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </aside>

            <!-- MAIN CONTENT PANELS -->
            <div class="admin-content-grid" style="display: block;">

                    <!-- MY ORDERS -->
                    <section class="admin-section<?php echo $activeTab === 'orders' ? ' is-active' : ''; ?>" id="orders" data-profile-panel="orders" aria-labelledby="orders-title" <?php echo $activeTab !== 'orders' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="orders-title">My Recent Orders</h2>
                            </div>
                        </div>

                        <!--
                            Backend note: loop over orders from DB.
                            Fields: order_id, date, status, total, items_summary.
                        -->
                        <div class="order-list">
                            <?php if ($orders === []): ?>
                                <div class="order-card">
                                    <p class="order-card__summary">No orders found yet.</p>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($orders as $order): ?>
                                <?php
                                $statusClass = strtolower((string) $order['ORDER_STATUS']) === 'processing' ? 'order-card__status--processing' : 'order-card__status--delivered';
                                ?>
                                <div class="order-card">
                                    <div class="order-card__header">
                                        <div>
                                            <p class="order-card__id">Order #EM-<?php echo e($order['ORDER_ID']); ?></p>
                                            <p class="order-card__date"><?php echo e(date('j F Y', strtotime((string) $order['ORDER_DATE']))); ?></p>
                                        </div>
                                        <span class="order-card__status <?php echo e($statusClass); ?>"><?php echo e($order['ORDER_STATUS']); ?></span>
                                    </div>
                                    <p class="order-card__summary">Order placed successfully.</p>
                                    <?php if (!empty($order['ITEMS'])): ?>
                                        <p class="order-card__items">Items: <?php echo e($order['ITEMS']); ?></p>
                                    <?php endif; ?>
                                    <div class="order-card__footer" style="display: flex; justify-content: space-between; align-items: center;">
                                        <span class="order-card__total">Total: £<?php echo e(number_format((float) $order['ORDER_TOTAL'], 2)); ?></span>
                                        <a href="download-invoice.php?order_id=<?php echo e($order['ORDER_ID']); ?>" class="button button--small button--secondary" target="_blank" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.9rem;">Download Invoice</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </section>

                    <!-- ACCOUNT DETAILS -->
                    <section class="admin-section<?php echo $activeTab === 'account' ? ' is-active' : ''; ?>" id="account" data-profile-panel="account" aria-labelledby="account-title" <?php echo $activeTab !== 'account' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="account-title">Account Details</h2>
                            </div>
                        </div>
                        <!--
                            Backend note: set action to your update endpoint (example: update-profile.php).
                            Pre-fill values from session or DB query.
                        -->
                        <form class="trader-form" action="profile.php?tab=account" method="post" novalidate>
                            <input type="hidden" name="profile_action" value="update_account" />
                            <div class="trader-form__grid">
                                <label>
                                    <span>First Name*</span>
                                    <input type="text" name="first_name" required autocomplete="given-name" placeholder="Enter first name" value="<?php echo e($user['FIRST_NAME']); ?>" />
                                </label>
                                <label>
                                    <span>Last Name*</span>
                                    <input type="text" name="last_name" required autocomplete="family-name" placeholder="Enter last name" value="<?php echo e($user['LAST_NAME']); ?>" />
                                </label>
                            </div>
                            <label>
                                <span>Email*</span>
                                <input type="email" name="email" required autocomplete="email" placeholder="name@example.com" value="<?php echo e($user['EMAIL']); ?>" />
                            </label>
                            <label>
                                <span>Phone</span>
                                <input type="tel" name="phone" autocomplete="tel" placeholder="+977 98XXXXXXXX" value="<?php echo e((string) ($user['PHONE_NUMBER'] ?? '')); ?>" />
                            </label>
                            <button class="trader-submit" type="submit">Save Changes</button>
                        </form>
                    </section>

                    <!-- COLLECTION HISTORY -->
                    <section class="admin-section<?php echo $activeTab === 'history' ? ' is-active' : ''; ?>" id="history" data-profile-panel="history" aria-labelledby="history-title" <?php echo $activeTab !== 'history' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="history-title">Collection History</h2>
                            </div>
                        </div>
                                                <div class="order-list">
                                                    <?php if ($historyOrders === []): ?>
                                                        <div class="order-card">
                                                            <p class="order-card__summary">No collection history found.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php foreach ($historyOrders as $order): ?>
                                                        <div class="order-card">
                                                            <div class="order-card__header">
                                                                <div>
                                                                    <p class="order-card__id">Order #EM-<?php echo e($order['ORDER_ID']); ?></p>
                                                                    <p class="order-card__date"><?php echo e(date('j F Y', strtotime((string) $order['ORDER_DATE']))); ?></p>
                                                                </div>
                                                                <span class="order-card__status"><?php echo e($order['ORDER_STATUS']); ?></span>
                                                            </div>
                                                            <p class="order-card__summary">Collected on <?php echo e(date('j F Y', strtotime((string) $order['ORDER_DATE']))); ?></p>
                                                            <?php if (!empty($order['ITEMS'])): ?>
                                                                <p class="order-card__items">Items: <?php echo e($order['ITEMS']); ?></p>
                                                            <?php endif; ?>
                                                            <div class="order-card__footer" style="display: flex; justify-content: space-between; align-items: center;">
                                                                <span class="order-card__total">Total: £<?php echo e(number_format((float) $order['ORDER_TOTAL'], 2)); ?></span>
                                                                <a href="download-invoice.php?order_id=<?php echo e($order['ORDER_ID']); ?>" class="button button--small button--secondary" target="_blank" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.9rem;">Download Invoice</a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                            </section>

                    <!-- MY REVIEWS -->
                    <section class="admin-section<?php echo $activeTab === 'reviews' ? ' is-active' : ''; ?>" id="reviews" data-profile-panel="reviews" aria-labelledby="reviews-title" <?php echo $activeTab !== 'reviews' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="reviews-title">My Reviews</h2>
                            </div>
                        </div>
                        <div class="order-list">
                            <?php if ($reviews === []): ?>
                                <div class="order-card">
                                    <p class="order-card__summary">No reviews submitted yet.</p>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($reviews as $review): ?>
                                <?php
                                $rating = (float) $review['RATING'];
                                $stars = str_repeat('★', (int) round($rating)) . str_repeat('☆', max(0, 5 - (int) round($rating)));
                                ?>
                                <div class="order-card">
                                    <div class="order-card__header">
                                        <div>
                                            <p class="order-card__id"><?php echo e($review['PRODUCT_NAME']); ?></p>
                                            <p class="order-card__date"><?php echo e(date('j F Y', strtotime((string) $review['REVIEW_DATE']))); ?></p>
                                        </div>
                                        <span class="profile-stars" aria-label="<?php echo e(number_format($rating, 1)); ?> out of 5 stars"><?php echo e($stars); ?></span>
                                    </div>
                                    <p class="order-card__summary"><?php echo e((string) ($review['REVIEW_COMMENT'] ?? '')); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- PASSWORD -->
                    <section class="admin-section<?php echo $activeTab === 'password' ? ' is-active' : ''; ?>" id="password" data-profile-panel="password" aria-labelledby="password-title" <?php echo $activeTab !== 'password' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="password-title">Change Password</h2>
                            </div>
                        </div>
                        <!--
                            Backend note: verify current_password before hashing and saving new_password.
                        -->
                        <form class="trader-form" action="profile.php?tab=password" method="post" novalidate>
                            <input type="hidden" name="profile_action" value="change_password" />
                            <label>
                                <span>Current Password*</span>
                                <div class="password-wrapper">
                                    <input type="password" name="current_password" required autocomplete="current-password" placeholder="Enter current password" />
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                                </div>
                            </label>
                            <label>
                                <span>New Password*</span>
                                <div class="password-wrapper">
                                    <input type="password" name="new_password" required autocomplete="new-password" placeholder="Create a strong password" />
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                                </div>
                            </label>
                            <label>
                                <span>Confirm New Password*</span>
                                <div class="password-wrapper">
                                    <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="Repeat new password" />
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                                </div>
                            </label>
                            <button class="trader-submit" type="submit">Update Password</button>
                        </form>
                    </section>

                    <!-- WISHLIST -->
                    <section class="admin-section<?php echo $activeTab === 'wishlist' ? ' is-active' : ''; ?>" id="wishlist" data-profile-panel="wishlist" aria-labelledby="wishlist-title" <?php echo $activeTab !== 'wishlist' ? 'hidden' : ''; ?>>
                        <div class="trader-card__header">
                            <div>
                                <h2 id="wishlist-title">My Wishlist</h2>
                            </div>
                        </div>
                        <div class="orders-grid">
                            <?php if (empty($wishlistItems)): ?>
                                <p class="profile-empty">Your wishlist is empty. Browse products and click "Add to Wishlist" to save them here.</p>
                            <?php else: ?>
                                <?php foreach ($wishlistItems as $item): ?>
                                    <div class="order-card" style="display:flex; flex-direction:column; gap:0.5rem;">
                                        <div class="order-card__header">
                                            <div>
                                                <p class="order-card__id"><?php echo e($item['PRODUCT_NAME']); ?></p>
                                                <p class="order-card__date">Added: <?php echo e(date('j F Y', strtotime((string) $item['ADDED_DATE']))); ?></p>
                                            </div>
                                            <div style="display:flex; gap:0.5rem; align-items:center;">
                                                <a href="product.php?product_id=<?php echo e($item['PRODUCT_ID']); ?>" class="profile-submit" style="width:auto; padding: 0.5rem 1rem;">View Product</a>
                                                <form method="post" action="profile.php?tab=wishlist" style="margin:0;">
                                                    <input type="hidden" name="profile_action" value="remove_wishlist" />
                                                    <input type="hidden" name="product_id" value="<?php echo e($item['PRODUCT_ID']); ?>" />
                                                    <button type="submit" class="button button--secondary" style="padding: 0.5rem 1rem;" title="Remove from wishlist">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <script>
                        (function () {
                            var navItems = document.querySelectorAll('[data-profile-tab]');
                            var panels = document.querySelectorAll('[data-profile-panel]');

                            function activateTab(name, push) {
                                navItems.forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-profile-tab') === name); });
                                panels.forEach(function (p) {
                                    var match = p.getAttribute('data-profile-panel') === name;
                                    p.classList.toggle('is-active', match);
                                    if (match) p.removeAttribute('hidden'); else p.setAttribute('hidden', '');
                                });
                                if (push && window.history && window.history.pushState) {
                                    window.history.pushState({}, '', 'profile.php?tab=' + encodeURIComponent(name));
                                }
                            }

                            navItems.forEach(function (a) {
                                a.addEventListener('click', function (ev) {
                                    ev.preventDefault();
                                    activateTab(a.getAttribute('data-profile-tab'), true);
                                });
                            });
                        })();
                    </script>

                </div>
            </div>
        </div>
    </section>

</main>
<?php
// Shared footer keeps legal/quick links unified across pages.
require __DIR__ . '/components/footer.php';
?>
