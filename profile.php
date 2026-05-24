<?php
// This is the customer dashboard where users can manage their account details, view their wishlist, and see past orders.

require_once __DIR__ . '/lib/auth_helpers.php';

require_login();

$errors = [];
$flashSuccess = get_flash('success');
$userId = (int) current_user_id();
$customerId = $userId;

require_once __DIR__ . '/lib/profile_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileAction = (string) ($_POST['profile_action'] ?? '');

    if ($profileAction === 'update_account') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        $res = update_customer_account($userId, $firstName, $lastName, $email, $phone);
        if ($res['success']) {
            set_flash('success', $res['message']);
            redirect('profile.php?tab=account');
        } else {
            $errors = array_merge($errors, $res['errors']);
        }
    }

    if ($profileAction === 'remove_wishlist') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId !== false && $productId !== null) {
            $res = remove_customer_wishlist_item($userId, $productId);
            if ($res['success']) {
                set_flash('success', $res['message']);
            } else {
                $errors = array_merge($errors, $res['errors']);
            }
            redirect('profile.php?tab=wishlist');
        }
    }

    if ($profileAction === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        $res = update_customer_password($userId, $currentPassword, $newPassword, $confirmPassword);
        if ($res['success']) {
            set_flash('success', $res['message']);
            redirect('profile.php?tab=password');
        } else {
            $errors = array_merge($errors, $res['errors']);
        }
    }

    if ($profileAction === 'submit_review') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
        $comment = trim($_POST['comment'] ?? '');

        if ($productId !== false && $productId !== null && $rating !== false && $rating !== null) {
            $res = submit_customer_review($customerId, $productId, $rating, $comment);
            if ($res['success']) {
                set_flash('success', $res['message']);
                redirect('profile.php?tab=reviews');
            } else {
                $errors = array_merge($errors, $res['errors']);
            }
        } else {
            $errors[] = 'Invalid input for review submission.';
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

$activeTab = (string) ($_GET['tab'] ?? 'orders');

$profileData = get_customer_profile_data($customerId);
$orders = $profileData['orders'];
$historyOrders = $profileData['historyOrders'];
$reviews = $profileData['reviews'];
$pendingReviews = $profileData['pendingReviews'];
$wishlistItems = $profileData['wishlistItems'];
$orderCount = $profileData['orderCount'];
$reviewCount = $profileData['reviewCount'];
$savedCount = $profileData['savedCount'];

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
                    <a class="tab-button" href="auth.php?action=logout" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
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
                            Renders a list of the customer's recent orders.
                            Dynamically sets the status class based on whether the order is processing or delivered.
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
                            Provides a form to update the user's first name, last name, email, and phone.
                            Inputs are pre-filled with the current session or database values.
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

                        <hr style="margin: 2.5rem 0 2rem; border: 0; border-top: 1px solid rgba(26,26,26,0.1);" />

                        <div class="trader-card__header" style="margin-bottom: 1.5rem;">
                            <div>
                                <h2 id="pending-reviews-title">Pending Reviews</h2>
                                <p style="margin-top: 0.25rem; font-size: 0.9rem; color: var(--color-muted);">
                                    Products you have purchased that are waiting for your review.
                                </p>
                            </div>
                        </div>

                        <div class="order-list">
                            <?php if (empty($pendingReviews)): ?>
                                <div class="order-card" style="padding: 1.5rem; text-align: center;">
                                    <p class="order-card__summary" style="margin: 0;">You have no pending reviews. Thank you for your feedback!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($pendingReviews as $prod): ?>
                                    <?php 
                                    $prodId = (int)$prod['PRODUCT_ID']; 
                                    $productImage = trim((string) ($prod['PRODUCT_IMAGE'] ?? ''));
                                    if ($productImage === '') {
                                        $productImage = 'assets/images/icons/product-placeholder.svg';
                                    } elseif (!str_starts_with($productImage, 'http://') && !str_starts_with($productImage, 'https://') && !str_starts_with($productImage, 'assets/')) {
                                        $productImage = 'assets/images/products/' . ltrim($productImage, '/');
                                    }
                                    ?>
                                    <div class="order-card" style="display: flex; flex-direction: column; gap: 1.25rem; padding: 1.25rem;">
                                        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                            <img src="<?php echo e($productImage); ?>" alt="<?php echo e($prod['PRODUCT_NAME']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid rgba(0,0,0,0.08);" onerror="this.src='assets/images/icons/product-placeholder.svg'; this.onerror=null;" />
                                            <div style="flex: 1; min-width: 200px;">
                                                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;"><?php echo e($prod['PRODUCT_NAME']); ?></h3>
                                                <p style="margin: 0.2rem 0 0; font-size: 0.85rem; color: var(--color-muted);">From <?php echo e($prod['SHOP_NAME'] ?? 'Cleck E-Mart'); ?></p>
                                            </div>
                                        </div>
                                        
                                        <form class="trader-form" action="profile.php?tab=reviews" method="post" style="gap: 1rem; border-top: 1px dashed rgba(26,26,26,0.1); padding-top: 1rem;">
                                            <input type="hidden" name="profile_action" value="submit_review" />
                                            <input type="hidden" name="product_id" value="<?php echo $prodId; ?>" />
                                            
                                            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                                <span style="font-weight: 700; font-size: 0.9rem;">Your Rating*</span>
                                                <div class="star-rating">
                                                    <input type="radio" id="star5-<?php echo $prodId; ?>" name="rating" value="5" required />
                                                    <label for="star5-<?php echo $prodId; ?>" title="5 stars">★</label>
                                                    
                                                    <input type="radio" id="star4-<?php echo $prodId; ?>" name="rating" value="4" />
                                                    <label for="star4-<?php echo $prodId; ?>" title="4 stars">★</label>
                                                    
                                                    <input type="radio" id="star3-<?php echo $prodId; ?>" name="rating" value="3" />
                                                    <label for="star3-<?php echo $prodId; ?>" title="3 stars">★</label>
                                                    
                                                    <input type="radio" id="star2-<?php echo $prodId; ?>" name="rating" value="2" />
                                                    <label for="star2-<?php echo $prodId; ?>" title="2 stars">★</label>
                                                    
                                                    <input type="radio" id="star1-<?php echo $prodId; ?>" name="rating" value="1" />
                                                    <label for="star1-<?php echo $prodId; ?>" title="1 star">★</label>
                                                </div>
                                            </div>
                                            
                                            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                                <label for="comment-<?php echo $prodId; ?>" style="font-weight: 700; font-size: 0.9rem;">Write your review (Optional)</label>
                                                <textarea id="comment-<?php echo $prodId; ?>" name="comment" rows="3" placeholder="Share your experience with this product..." style="width: 100%; border: 1px solid rgba(26,26,26,0.15); border-radius: var(--radius-sm); padding: 0.65rem 0.8rem; font-family: inherit; font-size: 0.9rem; resize: vertical; background: var(--color-primary); color: var(--color-text);"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="profile-submit" style="width: auto; align-self: flex-start; padding: 0.65rem 1.25rem; font-size: 0.9rem; border-radius: var(--radius-sm);">
                                                Submit Review
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                            Provides a form to change the user's password securely.
                            The backend verifies the current password against the hashed value before applying the new hash.
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

                            // Handles the core logic and operations for activateTab
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
