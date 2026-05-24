<?php
// These helper functions handle updating user profile details and fetching customer-specific data.

/**
 * Profile Helper Functions
 * Extracts complex logic and database queries from profile.php.
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/oci_db.php';

/**
 * Updates a customer's basic account details.
 *
 * @param int $userId
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param string $phone
 * @return array ['success' => bool, 'message' => string, 'errors' => array]
 */
function update_customer_account(int $userId, string $firstName, string $lastName, string $email, string $phone): array {
    $errors = [];
    
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

                return ['success' => true, 'message' => 'Account details updated successfully.', 'errors' => []];
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unable to update account: ' . $exception->getMessage();
        }
    }
    
    return ['success' => false, 'message' => '', 'errors' => $errors];
}

/**
 * Removes an item from the customer's wishlist.
 */
function remove_customer_wishlist_item(int $userId, int $productId): array {
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
            return ['success' => true, 'message' => 'Item removed from wishlist.', 'errors' => []];
        }
    } catch (Throwable $exception) {
        return ['success' => false, 'message' => '', 'errors' => ['Unable to remove item from wishlist: ' . $exception->getMessage()]];
    }
    return ['success' => false, 'message' => '', 'errors' => []];
}

/**
 * Updates the customer's password securely.
 */
function update_customer_password(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array {
    $errors = [];
    
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
                return ['success' => true, 'message' => 'Password updated successfully.', 'errors' => []];
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unable to update password: ' . $exception->getMessage();
        }
    }
    
    return ['success' => false, 'message' => '', 'errors' => $errors];
}

/**
 * Submits a new review for a product.
 */
function submit_customer_review(int $customerId, int $productId, float $rating, string $comment): array {
    $errors = [];
    
    if ($productId <= 0 || $rating < 1 || $rating > 5) {
        $errors[] = 'Please provide a valid rating between 1 and 5 stars.';
    } else {
        try {
            if (db_is_offline()) {
                offline_submit_review($customerId, $productId, $rating, $comment);
            } else {
                $reviewId = db_next_id('REVIEW', 'review_id');
                db_execute(
                    'INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date) 
                     VALUES (:review_id, :customer_id, :product_id, :rating, :review_comment, SYSDATE)',
                    [
                        'review_id' => $reviewId,
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'rating' => $rating,
                        'review_comment' => $comment === '' ? null : $comment
                    ]
                );
            }
            return ['success' => true, 'message' => 'Your review has been submitted successfully.', 'errors' => []];
        } catch (Throwable $exception) {
            $msg = $exception->getMessage();
            if (str_contains($msg, 'unique constraint')) {
                $errors[] = 'You have already reviewed this product.';
            } elseif (str_contains($msg, 'ORA-20009') || str_contains($msg, 'collected/paid for')) {
                $errors[] = 'You can only review products you have purchased and collected/paid for.';
            } else {
                $errors[] = 'Failed to submit review: ' . $msg;
            }
        }
    }
    
    return ['success' => false, 'message' => '', 'errors' => $errors];
}

/**
 * Fetches all necessary dashboard data for the customer's profile tabs.
 */
function get_customer_profile_data(int $customerId): array {
    $data = [
        'orders' => [],
        'historyOrders' => [],
        'reviews' => [],
        'pendingReviews' => [],
        'wishlistItems' => [],
        'orderCount' => 0,
        'reviewCount' => 0,
        'savedCount' => 0,
    ];

    if (db_is_offline()) {
        $data['orders'] = offline_get_orders_for_customer($customerId, 5);
        $data['historyOrders'] = offline_get_orders_for_customer($customerId, 5);
        $data['reviews'] = offline_get_reviews_for_customer($customerId, 5);
        $data['pendingReviews'] = offline_get_pending_reviews_for_customer($customerId);
        $data['orderCount'] = offline_count_orders($customerId);
        $data['reviewCount'] = offline_count_reviews($customerId);
        $data['savedCount'] = offline_count_saved($customerId);
        return $data;
    }

    $data['orders'] = db_fetch_all(
        "SELECT o.order_id, o.order_date, o.order_status, NVL(SUM(oi.quantity * oi.unit_price), 0) AS ORDER_TOTAL,
                LISTAGG(p.product_name, ', ') WITHIN GROUP (ORDER BY p.product_name) AS ITEMS
         FROM \"ORDER\" o
         JOIN ORDER_ITEM oi ON oi.order_id = o.order_id
         JOIN PRODUCT p ON p.product_id = oi.product_id
         WHERE o.customer_id = :customer_id
         GROUP BY o.order_id, o.order_date, o.order_status
         ORDER BY o.order_date DESC FETCH FIRST 5 ROWS ONLY",
        ['customer_id' => $customerId]
    );

    $data['historyOrders'] = db_fetch_all(
        "SELECT o.order_id, o.order_date, o.order_status, NVL(SUM(oi.quantity * oi.unit_price), 0) AS ORDER_TOTAL,
                LISTAGG(p.product_name, ', ') WITHIN GROUP (ORDER BY p.product_name) AS ITEMS
         FROM \"ORDER\" o
         JOIN ORDER_ITEM oi ON oi.order_id = o.order_id
         JOIN PRODUCT p ON p.product_id = oi.product_id
         WHERE o.customer_id = :customer_id AND o.order_status = 'COLLECTED'
         GROUP BY o.order_id, o.order_date, o.order_status
         ORDER BY o.order_date DESC FETCH FIRST 5 ROWS ONLY",
        ['customer_id' => $customerId]
    );

    $data['reviews'] = db_fetch_all(
        'SELECT r.review_date, r.rating, r."COMMENT" AS review_comment, p.product_name
         FROM REVIEW r
         JOIN PRODUCT p ON p.product_id = r.product_id
         WHERE r.customer_id = :customer_id
         ORDER BY r.review_date DESC FETCH FIRST 5 ROWS ONLY',
        ['customer_id' => $customerId]
    );

    $data['pendingReviews'] = db_fetch_all(
        "SELECT DISTINCT p.product_id, p.product_name, p.product_image, s.shop_name
         FROM PRODUCT p
         JOIN ORDER_ITEM oi ON oi.product_id = p.product_id
         JOIN \"ORDER\" o ON o.order_id = oi.order_id
         JOIN SHOP s ON s.shop_id = p.shop_id
         WHERE o.customer_id = :customer_id
           AND o.order_status IN ('PAID', 'COLLECTED')
           AND p.product_id NOT IN (SELECT r.product_id FROM REVIEW r WHERE r.customer_id = :customer_id)
         ORDER BY p.product_name ASC",
        ['customer_id' => $customerId]
    );

    $data['orderCount'] = (int) (db_fetch_one('SELECT COUNT(*) AS total_count FROM "ORDER" WHERE customer_id = :customer_id', ['customer_id' => $customerId])['TOTAL_COUNT'] ?? 0);
    $data['reviewCount'] = (int) (db_fetch_one('SELECT COUNT(*) AS total_count FROM REVIEW WHERE customer_id = :customer_id', ['customer_id' => $customerId])['TOTAL_COUNT'] ?? 0);
    $data['savedCount'] = (int) (db_fetch_one(
        'SELECT COUNT(*) AS total_count FROM WISHLIST_ITEM wi JOIN WISHLIST w ON w.wishlist_id = wi.wishlist_id WHERE w.customer_id = :customer_id',
        ['customer_id' => $customerId]
    )['TOTAL_COUNT'] ?? 0);

    $data['wishlistItems'] = db_fetch_all(
        'SELECT p.product_id, p.product_name, p.price, p.product_image, d.discount_percentage, wi.added_date
         FROM WISHLIST_ITEM wi
         JOIN WISHLIST w ON w.wishlist_id = wi.wishlist_id
         JOIN PRODUCT p ON p.product_id = wi.product_id
         LEFT JOIN DISCOUNT d ON d.discount_id = p.discount_id AND d.end_date >= SYSDATE
         WHERE w.customer_id = :customer_id
         ORDER BY wi.added_date DESC',
        ['customer_id' => $customerId]
    );

    return $data;
}
