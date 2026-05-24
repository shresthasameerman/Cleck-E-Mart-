<?php
// These helper functions handle saving and retrieving a customer's favorite products in their wishlist.

require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/offline_store.php';

// Handles the core logic and operations for ensure_wishlist
function ensure_wishlist(int $customerId): int
{
    if (db_is_offline()) {
        return 1; // Dummy for offline
    }

    $existing = db_fetch_one(
        'SELECT wishlist_id FROM WISHLIST WHERE customer_id = :customer_id',
        ['customer_id' => $customerId]
    );

    if ($existing !== null) {
        return (int) $existing['WISHLIST_ID'];
    }

    $wishlistId = db_next_id('WISHLIST', 'wishlist_id');
    db_execute(
        'INSERT INTO WISHLIST (wishlist_id, customer_id, created_at) VALUES (:wishlist_id, :customer_id, SYSDATE)',
        [
            'wishlist_id' => $wishlistId,
            'customer_id' => $customerId,
        ]
    );

    return $wishlistId;
}

// Handles the core logic and operations for add_to_wishlist
function add_to_wishlist(int $customerId, int $productId): void
{
    if (db_is_offline()) {
        return;
    }

    $wishlistId = ensure_wishlist($customerId);

    $existing = db_fetch_one(
        'SELECT 1 FROM WISHLIST_ITEM WHERE wishlist_id = :wishlist_id AND product_id = :product_id',
        [
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
        ]
    );

    if ($existing === null) {
        db_execute(
            'INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (:wishlist_id, :product_id, SYSDATE)',
            [
                'wishlist_id' => $wishlistId,
                'product_id' => $productId,
            ]
        );
    }
}

// Handles the core logic and operations for remove_from_wishlist
function remove_from_wishlist(int $customerId, int $productId): void
{
    if (db_is_offline()) {
        return;
    }

    $wishlistId = ensure_wishlist($customerId);

    db_execute(
        'DELETE FROM WISHLIST_ITEM WHERE wishlist_id = :wishlist_id AND product_id = :product_id',
        [
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
        ]
    );
}

// Handles the core logic and operations for get_wishlist_items
function get_wishlist_items(int $customerId): array
{
    if (db_is_offline()) {
        return [];
    }

    $wishlistId = ensure_wishlist($customerId);

    return db_fetch_all(
        'SELECT w.product_id,
                p.product_name,
                p.price,
                p.product_image,
                p.stock_quantity,
                p.product_status,
                s.shop_name,
                d.discount_percentage
         FROM WISHLIST_ITEM w
         JOIN PRODUCT p ON p.product_id = w.product_id
         JOIN SHOP s ON s.shop_id = p.shop_id
         LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
         WHERE w.wishlist_id = :wishlist_id
         ORDER BY w.added_date DESC',
        ['wishlist_id' => $wishlistId]
    );
}

// Handles the core logic and operations for is_in_wishlist
function is_in_wishlist(int $customerId, int $productId): bool
{
    if (db_is_offline()) {
        return false;
    }

    $wishlistId = ensure_wishlist($customerId);

    $existing = db_fetch_one(
        'SELECT 1 FROM WISHLIST_ITEM WHERE wishlist_id = :wishlist_id AND product_id = :product_id',
        [
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
        ]
    );

    return $existing !== null;
}
