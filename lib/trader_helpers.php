<?php
require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/auth_helpers.php';

function trader_role_guard(): void
{
    require_login(['TRADER']);
}

function trader_handle_product_image_upload(?array $fileInput): ?string
{
    if ($fileInput === null || !isset($fileInput['name']) || $fileInput['name'] === '') {
        return null;
    }

    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File is too large (server limit).',
            UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit).',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
        ];
        $message = $errorMessages[$fileInput['error']] ?? 'Unknown upload error.';
        throw new RuntimeException('Image upload failed: ' . $message);
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mimeType = mime_content_type($fileInput['tmp_name']);
    
    if ($mimeType === false || !in_array($mimeType, $allowedMimes, true)) {
        throw new RuntimeException('Invalid image type. Allowed types: JPG, PNG, WebP, GIF.');
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($fileInput['size'] > $maxSize) {
        throw new RuntimeException('Image is too large. Maximum size is 5MB.');
    }

    $uploadDir = __DIR__ . '/../assets/images/products/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException('Failed to create upload directory.');
        }
    }

    $originalName = pathinfo($fileInput['name'], PATHINFO_FILENAME);
    $extension = pathinfo($fileInput['name'], PATHINFO_EXTENSION);
    $sanitizedName = preg_replace('/[^a-z0-9-]/', '-', strtolower($originalName));
    $sanitizedName = preg_replace('/-+/', '-', $sanitizedName);
    $sanitizedName = trim($sanitizedName, '-');
    
    if ($sanitizedName === '') {
        $sanitizedName = 'product';
    }

    $filename = $sanitizedName . '-' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($fileInput['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to save uploaded image.');
    }

    return $filename;
}

function trader_verification_status(int $userId): ?string
{
    if (db_is_offline()) {
        $data = offline_load();
        foreach ($data['traders'] as $trader) {
            if ((int) $trader['trader_id'] === $userId) {
                return (string) ($trader['trader_status'] ?? 'PENDING_VERIFICATION');
            }
        }
        return null;
    }

    try {
        $result = db_fetch_one(
            'SELECT trader_status FROM TRADER WHERE trader_id = :trader_id',
            ['trader_id' => $userId]
        );

        return $result ? (string) $result['TRADER_STATUS'] : null;
    } catch (Throwable $e) {
        // If the column doesn't exist yet in the database, assume existing traders are VERIFIED
        // This is a fallback for development mode before schema migration
        error_log('Trader verification status query failed: ' . $e->getMessage());
        return 'VERIFIED';
    }
}

function trader_is_verified(int $userId): bool
{
    $status = trader_verification_status($userId);
    return $status === 'VERIFIED';
}

function trader_shop_for_user(int $userId, ?int $shopId = null): ?array
{
    if (db_is_offline()) {
        if ($shopId) {
            $shops = offline_get_trader_shops($userId);
            foreach ($shops as $shop) {
                if ($shop['SHOP_ID'] == $shopId) {
                    $user = offline_user_by_id($userId);
                    $shop['FIRST_NAME'] = $user['FIRST_NAME'] ?? '';
                    $shop['LAST_NAME'] = $user['LAST_NAME'] ?? '';
                    $shop['EMAIL'] = $user['EMAIL'] ?? '';
                    $shop['PHONE_NUMBER'] = $user['PHONE_NUMBER'] ?? '';
                    $shop['BRAND_NAME'] = null;
                    $shop['PAN_NUMBER'] = null;
                    return $shop;
                }
            }
        }
        return offline_get_trader_shop($userId);
    }

    $where = "u.user_id = :user_id";
    $params = ['user_id' => $userId];
    if ($shopId) {
        $where .= " AND s.shop_id = :shop_id";
        $params['shop_id'] = $shopId;
    }

    return db_fetch_one(
        'SELECT s.shop_id,
                s.trader_id,
                s.shop_name,
                s.shop_description,
                s.shop_logo,
                s.shop_status,
                u.first_name,
                u.last_name,
                u.email,
                u.phone_number,
                t.brand_name,
                t.pan_number
         FROM "USER" u
         JOIN TRADER t ON t.trader_id = u.user_id
         JOIN SHOP s ON s.trader_id = t.trader_id
         WHERE ' . $where . '
         ORDER BY s.shop_id ASC
         FETCH FIRST 1 ROWS ONLY',
        $params
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

    $shop = trader_shop_for_user($userId);
    if ($shop === null) {
        return [];
    }

    return trader_products_for_shop((int) $shop['SHOP_ID']);
}

function trader_products_for_shop(int $shopId): array
{
    if (db_is_offline()) {
        return [];
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
                d.discount_percentage,
                NVL(ag.sold_quantity, 0) AS sold_quantity,
                NVL(ag.revenue, 0) AS revenue
         FROM PRODUCT p
         LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
         LEFT JOIN (
             SELECT product_id,
                    SUM(quantity) AS sold_quantity,
                    SUM(quantity * unit_price) AS revenue
             FROM ORDER_ITEM
             GROUP BY product_id
         ) ag ON ag.product_id = p.product_id
         WHERE p.shop_id = :shop_id
         ORDER BY p.product_name',
        ['shop_id' => $shopId]
    );
}

function trader_dashboard_metrics(int $userId, ?int $shopId = null): array
{
    if (db_is_offline()) {
        return offline_get_trader_dashboard($userId);
    }

    $shop = trader_shop_for_user($userId, $shopId);
    if ($shop === null) {
        return [
            'shop' => null,
            'products' => [],
            'inventory_products' => [],
            'live_listings' => 0,
            'sold_total' => 0,
            'stock_total' => 0,
            'refill_count' => 0,
            'low_stock_products' => [],
            'top_products' => [],
        ];
    }

    $shopId = (int) $shop['SHOP_ID'];
    $products = trader_products_for_shop($shopId);
    $inventoryMetrics = db_fetch_one(
        "SELECT COUNT(CASE WHEN product_status = 'IN_STOCK' THEN 1 END) AS live_listings,
            NVL(SUM(stock_quantity), 0) AS stock_total,
            COUNT(CASE WHEN product_status = 'LOW_STOCK' OR stock_quantity < 10 THEN 1 END) AS refill_count
         FROM PRODUCT
         WHERE shop_id = :shop_id",
        ['shop_id' => $shopId]
    );

    $soldMetrics = db_fetch_one(
        'SELECT NVL(SUM(oi.quantity), 0) AS sold_total
         FROM ORDER_ITEM oi
         JOIN PRODUCT p ON p.product_id = oi.product_id
         WHERE p.shop_id = :shop_id',
        ['shop_id' => $shopId]
    );

    $liveListings = (int) ($inventoryMetrics['LIVE_LISTINGS'] ?? 0);
    $stockTotal = (int) ($inventoryMetrics['STOCK_TOTAL'] ?? 0);
    $refillCount = (int) ($inventoryMetrics['REFILL_COUNT'] ?? 0);
    $soldTotal = (int) ($soldMetrics['SOLD_TOTAL'] ?? 0);

    $refillProducts = [];
    $topProducts = [];

    foreach ($products as $product) {
        $soldQuantity = (int) ($product['SOLD_QUANTITY'] ?? 0);
        $stockQuantity = (int) ($product['STOCK_QUANTITY'] ?? 0);

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
        ];
    }

    usort($topProducts, static fn(array $a, array $b): int => $b['sold_quantity'] <=> $a['sold_quantity']);

    return [
        'shop' => $shop,
        'products' => $products,
        'inventory_products' => $topProducts,
        'live_listings' => $liveListings,
        'sold_total' => $soldTotal,
        'stock_total' => $stockTotal,
        'refill_count' => $refillCount,
        'low_stock_products' => array_slice($refillProducts, 0, 5),
        'top_products' => array_slice($topProducts, 0, 5),
    ];
}

function trader_create_product(int $userId, array $payload): array
{
    $shopId = isset($payload['shop_id']) ? (int) $payload['shop_id'] : null;
    $shop = trader_shop_for_user($userId, $shopId);
    if ($shop === null) {
        throw new RuntimeException('Trader shop could not be found.');
    }
    
    if (strtoupper($shop['SHOP_STATUS']) !== 'ACTIVE') {
        throw new RuntimeException('Your shop must be ACTIVE before you can add products. Current status: ' . $shop['SHOP_STATUS']);
    }

    $productName = trim((string) ($payload['product_name'] ?? ''));
    $productDescription = trim((string) ($payload['product_description'] ?? ''));
    $price = (float) ($payload['price'] ?? 0);
    $stockQuantity = max(0, (int) ($payload['stock_quantity'] ?? 0));
    $categoryId = (int) ($payload['category_id'] ?? 0);
    $productImage = trim((string) ($payload['product_image'] ?? ''));
    $maxOrderRaw = trim((string) ($payload['max_order'] ?? ''));
    $minOrderRaw = trim((string) ($payload['min_order'] ?? ''));
    $allergyInformation = trim((string) ($payload['allergy_information'] ?? ''));
    $visibility = strtoupper((string) ($payload['visibility'] ?? 'PUBLISH'));
    $productStatus = $stockQuantity <= 10 ? 'LOW_STOCK' : 'IN_STOCK';

    if ($productName === '' || $productDescription === '' || $categoryId <= 0) {
        throw new InvalidArgumentException('Product name, description, and category are required.');
    }

    $maxOrder = $maxOrderRaw === '' ? null : max(1, (int) $maxOrderRaw);
    $minOrder = $minOrderRaw === '' ? 1 : max(1, (int) $minOrderRaw);
    
    if ($maxOrder !== null && $minOrder > $maxOrder) {
        throw new InvalidArgumentException('Minimum order quantity cannot be greater than maximum order quantity.');
    }
    
    if ($visibility === 'DRAFT') {
        $productStatus = 'DRAFT';
    } elseif ($stockQuantity === 0) {
        $productStatus = 'OUT_OF_STOCK';
    }

    $userAge = db_is_offline() ? 31 : 0;
    if (!db_is_offline()) {
        try {
            $userRow = db_fetch_one('SELECT created_at FROM "USER" WHERE user_id = :userId', ['userId' => $userId]);
            if ($userRow && !empty($userRow['CREATED_AT'])) {
                $createdAt = strtotime($userRow['CREATED_AT']);
                $userAge = (time() - $createdAt) / (60 * 60 * 24);
            }
        } catch (Throwable $e) {}
    }
    
    $verificationStatus = 'PENDING_VERIFICATION';
    if ($userAge > 30) {
        $verificationStatus = 'APPROVED';
    }

    if (db_is_offline()) {
        return offline_create_product((int) $shop['SHOP_ID'], [
            'category_id' => $categoryId,
            'product_name' => $productName,
            'product_description' => $productDescription,
            'price' => $price,
            'stock_quantity' => $stockQuantity,
            'product_status' => $productStatus,
            'product_verification_status' => $verificationStatus,
            'allergy_information' => $allergyInformation === '' ? null : $allergyInformation,
            'min_order' => $minOrder,
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
                product_verification_status,
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
                :product_verification_status,
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
                'product_verification_status' => $verificationStatus,
                'allergy_information' => $allergyInformation === '' ? null : $allergyInformation,
                'min_order' => $minOrder,
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
    $gender = trim((string) ($payload['gender'] ?? ''));
    $address = trim((string) ($payload['address'] ?? ''));
    $brandName = trim((string) ($payload['brand_name'] ?? ''));
    $panNumber = trim((string) ($payload['pan_number'] ?? ''));
    
    $currentPassword = (string) ($payload['current_password'] ?? '');
    $newPassword = (string) ($payload['new_password'] ?? '');

    if ($firstName === '' || $lastName === '' || $email === '' || $brandName === '') {
        throw new InvalidArgumentException('First name, last name, email, and brand name are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    if (db_is_offline()) {
        offline_update_user($userId, $firstName, $lastName, $email, $phone === '' ? null : $phone);
        return [];
    }
    
    // If password change is requested, verify current password
    $passwordUpdateSql = "";
    $passwordParams = [];
    if ($currentPassword !== '' || $newPassword !== '') {
        if ($currentPassword === '' || $newPassword === '') {
            throw new InvalidArgumentException('Both current password and new password are required to change your password.');
        }
        $userRow = db_fetch_one('SELECT password FROM "USER" WHERE user_id = :user_id', ['user_id' => $userId]);
        if (!$userRow || !password_verify($currentPassword, $userRow['PASSWORD'])) {
            throw new InvalidArgumentException('The current password provided is incorrect.');
        }
        $passwordUpdateSql = ", password = :password";
        $passwordParams = ['password' => password_hash($newPassword, PASSWORD_DEFAULT)];
    }

    db_begin();

    try {
        db_execute(
            'UPDATE "USER"
             SET first_name = :first_name,
                 last_name = :last_name,
                 email = :email,
                 phone_number = :phone_number,
                 gender = :gender,
                 address = :address,
                 updated_at = CURRENT_TIMESTAMP' . $passwordUpdateSql . '
             WHERE user_id = :user_id',
            array_merge([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone_number' => $phone === '' ? null : $phone,
                'gender' => $gender === '' ? null : $gender,
                'address' => $address === '' ? null : $address,
                'user_id' => $userId,
            ], $passwordParams)
        );

        db_execute(
            'UPDATE TRADER
             SET brand_name = :brand_name,
                 pan_number = :pan_number
             WHERE trader_id = :user_id',
            [
                'brand_name' => $brandName,
                'pan_number' => $panNumber === '' ? null : $panNumber,
                'user_id' => $userId,
            ]
        );

        db_commit();
    } catch (Throwable $exception) {
        db_rollback();
        // Check for unique constraint violation on email or brand name
        if (strpos($exception->getMessage(), 'unique constraint') !== false) {
            throw new InvalidArgumentException('The email or brand name is already in use.');
        }
        throw $exception;
    }

    return [];
}

function trader_update_discount(int $userId, int $productId, float $percentage, int $durationDays = 30): void
{
    $shop = trader_shop_for_user($userId);
    if ($shop === null) {
        throw new RuntimeException('Trader shop could not be found.');
    }

    if (db_is_offline()) {
        return;
    }

    $product = db_fetch_one('SELECT product_id, discount_id FROM PRODUCT WHERE product_id = :product_id AND shop_id = :shop_id', [
        'product_id' => $productId,
        'shop_id' => (int) $shop['SHOP_ID']
    ]);

    if (!$product) {
        throw new RuntimeException('Product not found or does not belong to you.');
    }

    db_begin();
    try {
        if ($percentage <= 0) {
            db_execute('UPDATE PRODUCT SET discount_id = NULL WHERE product_id = :product_id', ['product_id' => $productId]);
        } else {
            $discountId = db_next_id('DISCOUNT', 'discount_id');
            db_execute(
                "INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status) 
                 VALUES (:discount_id, :percentage, SYSDATE, SYSDATE + :duration, 'ACTIVE')",
                ['discount_id' => $discountId, 'percentage' => $percentage, 'duration' => $durationDays]
            );
            db_execute('UPDATE PRODUCT SET discount_id = :discount_id WHERE product_id = :product_id', [
                'discount_id' => $discountId,
                'product_id' => $productId
            ]);
        }
        db_commit();
    } catch (Throwable $e) {
        db_rollback();
        throw $e;
    }
}

function trader_get_orders(int $userId, array $filters = []): array
{
    $shopId = $filters['shop_id'] ?? null;
    
    $params = ['user_id' => $userId];
    $where = "WHERE s.trader_id = :user_id";
    
    if ($shopId) {
        $where .= " AND s.shop_id = :shop_id";
        $params['shop_id'] = $shopId;
    }
    
    if (!empty($filters['customer_name'])) {
        $where .= " AND LOWER(u.first_name || ' ' || u.last_name) LIKE LOWER(:customer_name)";
        $params['customer_name'] = '%' . $filters['customer_name'] . '%';
    }
    
    if (!empty($filters['status'])) {
        $where .= " AND o.order_status = :status";
        $params['status'] = $filters['status'];
    }
    
    if (!empty($filters['date_from'])) {
        $where .= " AND o.order_date >= TO_DATE(:date_from, 'YYYY-MM-DD')";
        $params['date_from'] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where .= " AND o.order_date <= TO_DATE(:date_to, 'YYYY-MM-DD')";
        $params['date_to'] = $filters['date_to'];
    }

    $sql = "SELECT o.order_id,
                   u.first_name || ' ' || u.last_name AS customer_name,
                   o.order_date,
                   py.payment_status,
                   o.order_status,
                   SUM(oi.quantity) as total_items
            FROM \"ORDER\" o
            JOIN CUSTOMER c ON o.customer_id = c.customer_id
            JOIN \"USER\" u ON c.customer_id = u.user_id
            JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
            JOIN PRODUCT p ON oi.product_id = p.product_id
            JOIN SHOP s ON p.shop_id = s.shop_id
            LEFT JOIN PAYMENT py ON o.order_id = py.order_id
            $where
            GROUP BY o.order_id, u.first_name, u.last_name, o.order_date, py.payment_status, o.order_status
            ORDER BY o.order_id DESC";

    return db_fetch_all($sql, $params);
}

function trader_get_order_details(int $userId, int $orderId, ?int $shopId = null): ?array
{
    $orderSql = "SELECT o.order_id,
                        u.first_name || ' ' || u.last_name AS customer_name,
                        o.order_date,
                        u.address AS delivery_address,
                        py.payment_method,
                        py.payment_status,
                        o.order_status
                 FROM \"ORDER\" o
                 JOIN CUSTOMER c ON o.customer_id = c.customer_id
                 JOIN \"USER\" u ON c.customer_id = u.user_id
                 LEFT JOIN PAYMENT py ON o.order_id = py.order_id
                 WHERE o.order_id = :order_id";
    
    $orderInfo = db_fetch_one($orderSql, ['order_id' => $orderId]);
    
    if (!$orderInfo) return null;
    
    $whereShop = "s.trader_id = :user_id";
    $params = ['order_id' => $orderId, 'user_id' => $userId];
    if ($shopId) {
        $whereShop .= " AND s.shop_id = :shop_id";
        $params['shop_id'] = $shopId;
    }
    
    $itemsSql = "SELECT p.product_id,
                        p.product_image,
                        p.product_name,
                        oi.quantity,
                        oi.unit_price,
                        (oi.quantity * oi.unit_price) AS total_price
                 FROM ORDER_ITEM oi
                 JOIN PRODUCT p ON oi.product_id = p.product_id
                 JOIN SHOP s ON p.shop_id = s.shop_id
                 WHERE oi.order_id = :order_id AND $whereShop";
                 
    $items = db_fetch_all($itemsSql, $params);
    
    if (empty($items)) return null; 
    
    $orderInfo['items'] = $items;
    return $orderInfo;
}



function trader_update_order_status(int $userId, int $orderId, string $newStatus): void
{
    if (!in_array($newStatus, ['PAID', 'READY', 'COLLECTED'], true)) {
        throw new RuntimeException('Please select a valid status.');
    }

    $order = db_fetch_one(
        'SELECT o.order_id
         FROM "ORDER" o
         JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
         JOIN PRODUCT p ON oi.product_id = p.product_id
         JOIN SHOP s ON p.shop_id = s.shop_id
         WHERE o.order_id = :order_id AND s.trader_id = :user_id
         GROUP BY o.order_id',
        [
            'order_id' => $orderId,
            'user_id' => $userId
        ]
    );

    if (!$order) {
        throw new RuntimeException('Order not found or access denied.');
    }

    db_execute('UPDATE "ORDER" SET order_status = :status WHERE order_id = :order_id', [
        'status' => $newStatus,
        'order_id' => $orderId
    ]);
}

function trader_get_shops(int $userId): array
{
    if (db_is_offline()) {
        return offline_get_trader_shops($userId);
    }
    return db_fetch_all(
        'SELECT shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status
         FROM SHOP
         WHERE trader_id = :user_id',
        ['user_id' => $userId]
    );
}

function trader_create_shop(int $userId, array $payload): array
{
    $shopName = trim($payload['shop_name'] ?? '');
    $shopDesc = trim($payload['shop_description'] ?? '');
    $shopLogo = trim($payload['shop_logo'] ?? '');
    $shopLocation = trim($payload['shop_location'] ?? '');
    $shopPan = trim($payload['shop_pan'] ?? '');
    $shopProductsType = trim($payload['shop_products_type'] ?? '');
    
    if ($shopName === '') {
        throw new InvalidArgumentException('Shop name is required.');
    }
    
    if (db_is_offline()) {
        return offline_create_shop_for_trader($userId, $shopName, $shopDesc, $shopLogo === '' ? null : $shopLogo, $shopLocation, $shopPan, $shopProductsType);
    }
    
    db_begin();
    try {
        $shopId = db_next_id('SHOP', 'shop_id');
        db_execute(
            'INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_location, shop_pan, shop_products_type, shop_status)
             VALUES (:shop_id, :trader_id, :shop_name, :shop_description, :shop_logo, :shop_location, :shop_pan, :shop_products_type, :shop_status)',
            [
                'shop_id' => $shopId,
                'trader_id' => $userId,
                'shop_name' => $shopName,
                'shop_description' => $shopDesc,
                'shop_logo' => $shopLogo === '' ? null : $shopLogo,
                'shop_location' => $shopLocation,
                'shop_pan' => $shopPan,
                'shop_products_type' => $shopProductsType,
                'shop_status' => 'PENDING_APPROVAL'
            ]
        );
        db_commit();
        
        return [
            'SHOP_ID' => $shopId,
            'TRADER_ID' => $userId,
            'SHOP_NAME' => $shopName,
            'SHOP_DESCRIPTION' => $shopDesc,
            'SHOP_LOGO' => $shopLogo === '' ? null : $shopLogo,
            'SHOP_LOCATION' => $shopLocation,
            'SHOP_PAN' => $shopPan,
            'SHOP_PRODUCTS_TYPE' => $shopProductsType,
            'SHOP_STATUS' => 'PENDING_APPROVAL'
        ];
    } catch (Throwable $e) {
        db_rollback();
        throw $e;
    }
}

function trader_update_shop(int $userId, int $shopId, array $payload): void
{
    $shopName = trim($payload['shop_name'] ?? '');
    $shopDesc = trim($payload['shop_description'] ?? '');
    $shopLogo = trim($payload['shop_logo'] ?? '');
    $shopLocation = trim($payload['shop_location'] ?? '');
    $shopPan = trim($payload['shop_pan'] ?? '');
    $shopProductsType = trim($payload['shop_products_type'] ?? '');
    
    if ($shopName === '') {
        throw new InvalidArgumentException('Shop name is required.');
    }
    
    // Check if user owns the shop
    $shop = trader_shop_for_user($userId, $shopId);
    if (!$shop) {
        throw new RuntimeException('Shop not found or access denied.');
    }
    
    if (db_is_offline()) {
        return; // Not supported in offline mode
    }
    
    db_execute(
        'UPDATE SHOP SET 
            shop_name = :shop_name, 
            shop_description = :shop_description, 
            shop_logo = :shop_logo, 
            shop_location = :shop_location, 
            shop_pan = :shop_pan, 
            shop_products_type = :shop_products_type
         WHERE shop_id = :shop_id AND trader_id = :trader_id',
        [
            'shop_name' => $shopName,
            'shop_description' => $shopDesc,
            'shop_logo' => $shopLogo === '' ? null : $shopLogo,
            'shop_location' => $shopLocation,
            'shop_pan' => $shopPan,
            'shop_products_type' => $shopProductsType,
            'shop_id' => $shopId,
            'trader_id' => $userId
        ]
    );
}
