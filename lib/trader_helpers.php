<?php
require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/auth_helpers.php';

function trader_role_guard(): void
{
    require_login(['TRADER']);
}

function trader_shop_for_user(int $userId): ?array
{
    if (db_is_offline()) {
        return offline_get_trader_shop($userId);
    }

    return db_fetch_one(
        'SELECT s.shop_id,
                s.trader_id,
                s.shop_name,
                s.shop_description,
                s.shop_logo,
                u.first_name,
                u.last_name,
                u.email,
                u.phone_number,
                t.brand_name,
                t.pan_number
         FROM SHOP s
         JOIN TRADER t ON t.trader_id = s.trader_id
         JOIN "USER" u ON u.user_id = t.trader_id
         WHERE t.trader_id = :trader_id',
        ['trader_id' => $userId]
    );
}

function trader_categories(): array
{
    if (db_is_offline()) {
        return offline_get_categories();
    }

    return db_fetch_all('SELECT category_id, category_name FROM CATEGORY ORDER BY category_name');
}

function trader_products_for_user(int $userId): array
{
    if (db_is_offline()) {
        return offline_get_trader_products($userId);
    }

    return db_fetch_all(
        'SELECT p.product_id,
                p.product_name,
                p.product_description,
                p.price,
                p.stock_quantity,
                p.product_status,
                p.product_image,
                p.max_order,
                p.min_order,
                c.category_name,
                NVL(SUM(oi.quantity), 0) AS sold_quantity,
                NVL(SUM(oi.quantity * oi.unit_price), 0) AS revenue
         FROM PRODUCT p
         JOIN SHOP s ON s.shop_id = p.shop_id
         JOIN CATEGORY c ON c.category_id = p.category_id
         LEFT JOIN ORDER_ITEM oi ON oi.product_id = p.product_id
         LEFT JOIN "ORDER" o ON o.order_id = oi.order_id
         WHERE s.trader_id = :trader_id
         GROUP BY p.product_id,
                  p.product_name,
                  p.product_description,
                  p.price,
                  p.stock_quantity,
                  p.product_status,
                  p.product_image,
                  p.max_order,
                  p.min_order,
                  c.category_name
         ORDER BY p.product_name',
        ['trader_id' => $userId]
    );
}

function trader_dashboard_metrics(int $userId): array
{
    if (db_is_offline()) {
        return offline_get_trader_dashboard($userId);
    }

    $shop = trader_shop_for_user($userId);
    if ($shop === null) {
        return [
            'shop' => null,
            'products' => [],
            'sold_total' => 0,
            'revenue_total' => 0.0,
            'stock_total' => 0,
            'refill_count' => 0,
            'active_count' => 0,
            'low_stock_products' => [],
            'top_products' => [],
        ];
    }

    $products = trader_products_for_user($userId);
    $soldTotal = 0;
    $revenueTotal = 0.0;
    $stockTotal = 0;
    $refillProducts = [];
    $topProducts = [];

    foreach ($products as $product) {
        $soldQuantity = (int) ($product['SOLD_QUANTITY'] ?? 0);
        $stockQuantity = (int) ($product['STOCK_QUANTITY'] ?? 0);
        $revenue = (float) ($product['REVENUE'] ?? 0);

        $soldTotal += $soldQuantity;
        $revenueTotal += $revenue;
        $stockTotal += $stockQuantity;

        $needsRefill = $stockQuantity <= 10 || strtoupper((string) ($product['PRODUCT_STATUS'] ?? '')) === 'LOW_STOCK';
        if ($needsRefill) {
            $refillProducts[] = $product;
        }

        $topProducts[] = [
            'product_id' => (int) $product['PRODUCT_ID'],
            'product_name' => (string) $product['PRODUCT_NAME'],
            'sold_quantity' => $soldQuantity,
            'stock_quantity' => $stockQuantity,
            'needs_refill' => $needsRefill,
            'product_status' => (string) $product['PRODUCT_STATUS'],
            'revenue' => $revenue,
        ];
    }

    usort($topProducts, static fn(array $a, array $b): int => $b['sold_quantity'] <=> $a['sold_quantity']);

    return [
        'shop' => $shop,
        'products' => $products,
        'sold_total' => $soldTotal,
        'revenue_total' => $revenueTotal,
        'stock_total' => $stockTotal,
        'refill_count' => count($refillProducts),
        'active_count' => count(array_filter($products, static fn(array $product): bool => (int) ($product['STOCK_QUANTITY'] ?? 0) > 0)),
        'low_stock_products' => array_slice($refillProducts, 0, 5),
        'top_products' => array_slice($topProducts, 0, 5),
    ];
}

function trader_create_product(int $userId, array $payload): array
{
    $shop = trader_shop_for_user($userId);
    if ($shop === null) {
        throw new RuntimeException('Trader shop could not be found.');
    }

    $productName = trim((string) ($payload['product_name'] ?? ''));
    $productDescription = trim((string) ($payload['product_description'] ?? ''));
    $price = (float) ($payload['price'] ?? 0);
    $stockQuantity = max(0, (int) ($payload['stock_quantity'] ?? 0));
    $categoryId = (int) ($payload['category_id'] ?? 0);
    $productImage = trim((string) ($payload['product_image'] ?? ''));
    $maxOrderRaw = trim((string) ($payload['max_order'] ?? ''));
    $allergyInformation = trim((string) ($payload['allergy_information'] ?? ''));
    $visibility = strtoupper((string) ($payload['visibility'] ?? 'PUBLISH'));
    $productStatus = $stockQuantity <= 10 ? 'LOW_STOCK' : 'IN_STOCK';

    if ($productName === '' || $productDescription === '' || $categoryId <= 0) {
        throw new InvalidArgumentException('Product name, description, and category are required.');
    }

    $maxOrder = $maxOrderRaw === '' ? null : max(1, (int) $maxOrderRaw);
    if ($visibility === 'DRAFT') {
        $productStatus = 'DRAFT';
    } elseif ($stockQuantity === 0) {
        $productStatus = 'OUT_OF_STOCK';
    }

    if (db_is_offline()) {
        return offline_create_product((int) $shop['SHOP_ID'], [
            'category_id' => $categoryId,
            'product_name' => $productName,
            'product_description' => $productDescription,
            'price' => $price,
            'stock_quantity' => $stockQuantity,
            'product_status' => $productStatus,
            'allergy_information' => $allergyInformation === '' ? null : $allergyInformation,
            'min_order' => 1,
            'max_order' => $maxOrder,
            'product_image' => $productImage === '' ? null : $productImage,
        ]);
    }

    db_begin();

    try {
        $productId = db_next_id('PRODUCT', 'product_id');
        db_execute(
            'INSERT INTO PRODUCT (
                product_id,
                shop_id,
                category_id,
                discount_id,
                product_name,
                product_description,
                price,
                stock_quantity,
                product_status,
                allergy_information,
                min_order,
                max_order,
                product_image
            ) VALUES (
                :product_id,
                :shop_id,
                :category_id,
                NULL,
                :product_name,
                :product_description,
                :price,
                :stock_quantity,
                :product_status,
                :allergy_information,
                :min_order,
                :max_order,
                :product_image
            )',
            [
                'product_id' => $productId,
                'shop_id' => (int) $shop['SHOP_ID'],
                'category_id' => $categoryId,
                'product_name' => $productName,
                'product_description' => $productDescription,
                'price' => $price,
                'stock_quantity' => $stockQuantity,
                'product_status' => $productStatus,
                'allergy_information' => $allergyInformation === '' ? null : $allergyInformation,
                'min_order' => 1,
                'max_order' => $maxOrder,
                'product_image' => $productImage === '' ? null : $productImage,
            ]
        );

        db_commit();
    } catch (Throwable $exception) {
        db_rollback();
        throw $exception;
    }

    $createdProduct = db_fetch_one(
        'SELECT p.product_id,
                p.product_name,
                p.product_description,
                p.price,
                p.stock_quantity,
                p.product_status,
                p.product_image,
                c.category_name
         FROM PRODUCT p
         JOIN CATEGORY c ON c.category_id = p.category_id
         WHERE p.product_name = :product_name AND p.shop_id = :shop_id
         ORDER BY p.product_id DESC FETCH FIRST 1 ROWS ONLY',
        [
            'product_name' => $productName,
            'shop_id' => (int) $shop['SHOP_ID'],
        ]
    );

    return $createdProduct ?? [];
}

function trader_update_profile(int $userId, array $payload): array
{
    $firstName = trim((string) ($payload['first_name'] ?? ''));
    $lastName = trim((string) ($payload['last_name'] ?? ''));
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $phone = trim((string) ($payload['phone'] ?? ''));
    $shopName = trim((string) ($payload['shop_name'] ?? ''));
    $shopDescription = trim((string) ($payload['shop_description'] ?? ''));
    $shopLogo = trim((string) ($payload['shop_logo'] ?? ''));

    if ($firstName === '' || $lastName === '' || $email === '' || $shopName === '') {
        throw new InvalidArgumentException('First name, last name, email, and shop name are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    $shop = trader_shop_for_user($userId);
    if ($shop === null) {
        throw new RuntimeException('Trader shop could not be found.');
    }

    if (db_is_offline()) {
        offline_update_user($userId, $firstName, $lastName, $email, $phone === '' ? null : $phone);
        offline_update_shop((int) $shop['SHOP_ID'], $shopName, $shopDescription, $shopLogo === '' ? null : $shopLogo);

        return trader_shop_for_user($userId) ?? [];
    }

    db_begin();

    try {
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

        db_execute(
            'UPDATE SHOP
             SET shop_name = :shop_name,
                 shop_description = :shop_description,
                 shop_logo = :shop_logo
             WHERE shop_id = :shop_id',
            [
                'shop_name' => $shopName,
                'shop_description' => $shopDescription,
                'shop_logo' => $shopLogo === '' ? null : $shopLogo,
                'shop_id' => (int) $shop['SHOP_ID'],
            ]
        );

        db_commit();
    } catch (Throwable $exception) {
        db_rollback();
        throw $exception;
    }

    return trader_shop_for_user($userId) ?? [];
}
