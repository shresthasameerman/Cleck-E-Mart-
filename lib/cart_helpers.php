<?php
require_once __DIR__ . '/oci_db.php';

function ensure_active_cart(int $customerId): int
{
    if (db_is_offline()) {
        return offline_ensure_active_cart($customerId);
    }

    $existing = db_fetch_one(
        'SELECT cart_id FROM CART WHERE customer_id = :customer_id AND cart_status = :cart_status ORDER BY created_at DESC FETCH FIRST 1 ROWS ONLY',
        [
            'customer_id' => $customerId,
            'cart_status' => 'ACTIVE',
        ]
    );

    if ($existing !== null) {
        return (int) $existing['CART_ID'];
    }

    $cartId = db_next_id('CART', 'cart_id');
    db_execute(
        'INSERT INTO CART (cart_id, customer_id, cart_status, created_at) VALUES (:cart_id, :customer_id, :cart_status, CURRENT_TIMESTAMP)',
        [
            'cart_id' => $cartId,
            'customer_id' => $customerId,
            'cart_status' => 'ACTIVE',
        ]
    );

    return $cartId;
}

function add_product_to_cart(int $customerId, int $productId, int $quantity): void
{
    if (db_is_offline()) {
        offline_add_to_cart($customerId, $productId, $quantity);
        return;
    }

    $quantity = max(1, $quantity);
    $cartId = ensure_active_cart($customerId);

    $product = db_fetch_one(
        'SELECT product_id, price, stock_quantity, min_order, max_order FROM PRODUCT WHERE product_id = :product_id',
        ['product_id' => $productId]
    );

    if ($product === null) {
        throw new RuntimeException('Product not found.');
    }

    $unitPrice = (float) $product['PRICE'];
    $stockQuantity = (int) $product['STOCK_QUANTITY'];
    $minOrder = (int) ($product['MIN_ORDER'] ?? 1);
    $maxOrder = isset($product['MAX_ORDER']) ? (int) $product['MAX_ORDER'] : null;

    $existing = db_fetch_one(
        'SELECT quantity FROM CART_ITEM WHERE cart_id = :cart_id AND product_id = :product_id',
        [
            'cart_id' => $cartId,
            'product_id' => $productId,
        ]
    );

    $currentQty = $existing ? (int) $existing['QUANTITY'] : 0;
    $nextQty = $currentQty + $quantity;

    if ($nextQty < $minOrder) {
        $nextQty = $minOrder;
    }
    
    if ($maxOrder !== null && $nextQty > $maxOrder) {
        throw new RuntimeException('You cannot order more than ' . $maxOrder . ' of this item.');
    }
    
    if ($nextQty > $stockQuantity) {
        throw new RuntimeException('Not enough stock available. Only ' . $stockQuantity . ' left.');
    }

    $cartTotals = db_fetch_one(
        'SELECT NVL(SUM(quantity), 0) as total_qty FROM CART_ITEM WHERE cart_id = :cart_id',
        ['cart_id' => $cartId]
    );
    $totalQty = (int) $cartTotals['TOTAL_QTY'];
    
    if ($totalQty - $currentQty + $nextQty > 20) {
        throw new RuntimeException('You can only have a maximum of 20 items in your basket.');
    }

    if ($existing !== null) {
        db_execute(
            'UPDATE CART_ITEM SET quantity = :quantity, unit_price = :unit_price WHERE cart_id = :cart_id AND product_id = :product_id',
            [
                'quantity' => $nextQty,
                'unit_price' => $unitPrice,
                'cart_id' => $cartId,
                'product_id' => $productId,
            ]
        );
        return;
    }

    db_execute(
        'INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (:cart_id, :product_id, :quantity, :unit_price)',
        [
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $nextQty,
            'unit_price' => $unitPrice,
        ]
    );
}

function get_cart_items_for_customer(int $customerId): array
{
    if (db_is_offline()) {
        return offline_get_cart_items($customerId);
    }

    $cartId = ensure_active_cart($customerId);

    return db_fetch_all(
        'SELECT ci.product_id,
                ci.quantity,
                ci.unit_price,
                p.product_name,
                p.product_image,
                s.shop_name
         FROM CART_ITEM ci
         JOIN PRODUCT p ON p.product_id = ci.product_id
         JOIN SHOP s ON s.shop_id = p.shop_id
         WHERE ci.cart_id = :cart_id
         ORDER BY p.product_name',
        ['cart_id' => $cartId]
    );
}

function update_cart_item_quantity(int $customerId, int $productId, int $quantity): void
{
    if (db_is_offline()) {
        offline_update_cart_quantity($customerId, $productId, $quantity);
        return;
    }

    $cartId = ensure_active_cart($customerId);

    if ($quantity <= 0) {
        db_execute(
            'DELETE FROM CART_ITEM WHERE cart_id = :cart_id AND product_id = :product_id',
            [
                'cart_id' => $cartId,
                'product_id' => $productId,
            ]
        );
        return;
    }

    $product = db_fetch_one(
        'SELECT stock_quantity, min_order, max_order FROM PRODUCT WHERE product_id = :product_id',
        ['product_id' => $productId]
    );

    if ($product === null) {
        throw new RuntimeException('Product not found.');
    }

    $stockQuantity = (int) $product['STOCK_QUANTITY'];
    $minOrder = (int) ($product['MIN_ORDER'] ?? 1);
    $maxOrder = isset($product['MAX_ORDER']) ? (int) $product['MAX_ORDER'] : null;

    if ($quantity < $minOrder) {
        $quantity = $minOrder;
    }
    
    if ($maxOrder !== null && $quantity > $maxOrder) {
        throw new RuntimeException('You cannot order more than ' . $maxOrder . ' of this item.');
    }
    
    if ($quantity > $stockQuantity) {
        throw new RuntimeException('Not enough stock available. Only ' . $stockQuantity . ' left.');
    }

    $existing = db_fetch_one(
        'SELECT quantity FROM CART_ITEM WHERE cart_id = :cart_id AND product_id = :product_id',
        ['cart_id' => $cartId, 'product_id' => $productId]
    );
    $currentQty = $existing ? (int) $existing['QUANTITY'] : 0;

    $cartTotals = db_fetch_one(
        'SELECT NVL(SUM(quantity), 0) as total_qty FROM CART_ITEM WHERE cart_id = :cart_id',
        ['cart_id' => $cartId]
    );
    $totalQty = (int) $cartTotals['TOTAL_QTY'];
    
    if ($totalQty - $currentQty + $quantity > 20) {
        throw new RuntimeException('You can only have a maximum of 20 items in your basket.');
    }

    db_execute(
        'UPDATE CART_ITEM SET quantity = :quantity WHERE cart_id = :cart_id AND product_id = :product_id',
        [
            'quantity' => $quantity,
            'cart_id' => $cartId,
            'product_id' => $productId,
        ]
    );
}

function cart_total(array $items): float
{
    $total = 0.0;

    foreach ($items as $item) {
        $total += ((float) $item['UNIT_PRICE']) * ((int) $item['QUANTITY']);
    }

    return $total;
}
