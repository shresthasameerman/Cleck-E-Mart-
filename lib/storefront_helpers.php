<?php
// These helper functions fetch products, categories, and reviews to display on the public storefront.

/**
 * Storefront Helpers
 * Extracts complex Oracle queries and business logic for the main customer-facing storefront pages.
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/offline_store.php';

/**
 * Fetches featured products for the storefront.
 * 
 * @param string $searchTerm Optional search term
 * @param int|null $selectedCategoryId Optional category filter
 * @return array Array of products
 */
function get_storefront_products(string $searchTerm = '', ?int $selectedCategoryId = null): array {
    $featuredProducts = [];
    
    if (db_is_offline()) {
        $offlineProducts = offline_get_products($selectedCategoryId);
        if ($searchTerm !== '') {
            $offlineProducts = array_values(array_filter(
                $offlineProducts,
                static function (array $product) use ($searchTerm): bool {
                    return stripos((string) ($product['PRODUCT_NAME'] ?? ''), $searchTerm) !== false;
                }
            ));
        }

        foreach ($offlineProducts as $row) {
            $productId = (int) ($row['PRODUCT_ID'] ?? 0);
            $uploadedImage = isset($row['PRODUCT_IMAGE']) ? (string) $row['PRODUCT_IMAGE'] : null;

            $featuredProducts[] = [
                'product_id' => $productId,
                'product_name' => (string) ($row['PRODUCT_NAME'] ?? ''),
                'product_description' => (string) ($row['PRODUCT_DESCRIPTION'] ?? ''),
                'allergy_information' => (string) ($row['ALLERGY_INFORMATION'] ?? 'None'),
                'price' => (float) ($row['PRICE'] ?? 0),
                'product_image' => default_product_image($productId, $uploadedImage, 400),
                'discount_percentage' => null,
                'shop_name' => (string) ($row['SHOP_NAME'] ?? ''),
                'category_name' => (string) ($row['CATEGORY_NAME'] ?? ''),
                'product_status' => (string) ($row['PRODUCT_STATUS'] ?? 'ACTIVE'),
            ];
        }
    } else {
        $conn = db_connect();

        $sql = "SELECT p.product_id,
                       p.product_name,
                       p.product_description,
                       p.allergy_information,
                       p.price,
                       p.product_image,
                       p.product_status,
                       s.shop_name,
                       c.category_name,
                       d.discount_percentage
                FROM PRODUCT p
                LEFT JOIN SHOP s ON s.shop_id = p.shop_id
                LEFT JOIN CATEGORY c ON c.category_id = p.category_id
                LEFT JOIN DISCOUNT d ON d.discount_id = p.discount_id AND d.end_date >= SYSDATE";

        $conditions = [];
        $searchBind = null;
        $categoryBind = null;

        if ($searchTerm !== '') {
            $conditions[] = "LOWER(p.product_name) LIKE LOWER('%' || :search_bind || '%')";
            $searchBind = $searchTerm;
        }

        if ($selectedCategoryId !== null) {
            $conditions[] = 'p.category_id = :cat_bind';
            $categoryBind = (int) $selectedCategoryId;
        }

        $conditions[] = "p.product_verification_status = 'APPROVED'";
        $conditions[] = "s.shop_status = 'ACTIVE'";

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.product_name';

        $statement = oci_parse($conn, $sql);
        if ($statement === false) {
            $error = oci_error($conn);
            throw new RuntimeException('Failed to prepare homepage product query: ' . ($error['message'] ?? 'unknown error'));
        }

        if ($searchBind !== null && !oci_bind_by_name($statement, ':search_bind', $searchBind)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to bind search term: ' . ($error['message'] ?? 'unknown error'));
        }

        if ($categoryBind !== null && !oci_bind_by_name($statement, ':cat_bind', $categoryBind)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to bind category filter: ' . ($error['message'] ?? 'unknown error'));
        }

        if (!@oci_execute($statement)) {
            $error = oci_error($statement);
            throw new RuntimeException('Failed to fetch homepage products: ' . ($error['message'] ?? 'unknown error'));
        }

        while (($row = oci_fetch_assoc($statement)) !== false) {
            $productId = (int) ($row['PRODUCT_ID'] ?? 0);
            $uploadedImage = isset($row['PRODUCT_IMAGE']) ? (string) $row['PRODUCT_IMAGE'] : null;

            $featuredProducts[] = [
                'product_id' => $productId,
                'product_name' => (string) ($row['PRODUCT_NAME'] ?? ''),
                'product_description' => is_object($row['PRODUCT_DESCRIPTION']) ? $row['PRODUCT_DESCRIPTION']->load() : (string) ($row['PRODUCT_DESCRIPTION'] ?? ''),
                'allergy_information' => (string) ($row['ALLERGY_INFORMATION'] ?? 'None'),
                'price' => (float) ($row['PRICE'] ?? 0),
                'product_image' => default_product_image($productId, $uploadedImage, 400),
                'discount_percentage' => isset($row['DISCOUNT_PERCENTAGE']) ? (float) $row['DISCOUNT_PERCENTAGE'] : null,
                'shop_name' => (string) ($row['SHOP_NAME'] ?? ''),
                'category_name' => (string) ($row['CATEGORY_NAME'] ?? ''),
                'product_status' => (string) ($row['PRODUCT_STATUS'] ?? 'ACTIVE'),
            ];
        }

        oci_free_statement($statement);
    }
    
    return $featuredProducts;
}

/**
 * Fetches up to 5 verified, active shops for display on the storefront.
 * 
 * @return array Array of shop details
 */
function get_storefront_verified_shops(): array {
    $verifiedShops = [];
    
    if (db_is_offline()) {
        $data = offline_load();
        foreach ($data['shops'] as $s) {
            if (isset($s['shop_status']) && $s['shop_status'] === 'ACTIVE') {
                $traderName = 'Trader';
                foreach ($data['users'] as $u) {
                    if ($u['user_id'] == $s['trader_id']) {
                        $traderName = $u['first_name'] . ' ' . $u['last_name'];
                        break;
                    }
                }
                $verifiedShops[] = [
                    'shop_id' => $s['shop_id'],
                    'shop_name' => $s['shop_name'],
                    'shop_logo' => isset($s['shop_logo']) ? $s['shop_logo'] : null,
                    'trader_name' => $traderName
                ];
                if (count($verifiedShops) >= 5) break;
            }
        }
    } else {
        $conn = db_connect();
        $sql = "SELECT s.shop_id, s.shop_name, s.shop_logo, u.first_name || ' ' || u.last_name AS trader_name
                FROM SHOP s
                JOIN TRADER t ON s.trader_id = t.trader_id
                JOIN \"USER\" u ON t.trader_id = u.user_id
                WHERE s.shop_status = 'ACTIVE'
                ORDER BY s.shop_id ASC
                FETCH FIRST 5 ROWS ONLY";
        $stmt = oci_parse($conn, $sql);
        oci_execute($stmt);
        while (($row = oci_fetch_assoc($stmt)) !== false) {
            $verifiedShops[] = [
                'shop_id' => (int)$row['SHOP_ID'],
                'shop_name' => (string)$row['SHOP_NAME'],
                'shop_logo' => isset($row['SHOP_LOGO']) ? (string)$row['SHOP_LOGO'] : null,
                'trader_name' => (string)$row['TRADER_NAME']
            ];
        }
        oci_free_statement($stmt);
    }
    
    return $verifiedShops;
}

/**
 * Fetches exhaustive product details including current discounts, reviews, and related products.
 * 
 * @param int $productId The product ID
 * @return array|null Product details array or null if not found
 */
function get_storefront_product_detail(int $productId): ?array {
    $product = null;

    if (db_is_offline()) {
        $product = offline_get_product_detail($productId);
    } else {
        $product = db_fetch_one(
            "SELECT p.product_id,
                    p.product_name,
                    p.product_description,
                    p.allergy_information,
                    p.price,
                    p.product_image,
                    d.discount_percentage,
                    NVL(u.first_name || ' ' || u.last_name, s.shop_name) AS trader_name
             FROM PRODUCT p
             LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
             JOIN SHOP s ON s.shop_id = p.shop_id
             JOIN TRADER t ON t.trader_id = s.trader_id
             JOIN \"USER\" u ON u.user_id = t.trader_id
             WHERE p.product_id = :product_id AND p.product_verification_status = 'APPROVED' AND s.shop_status = 'ACTIVE'",
            ['product_id' => $productId]
        );
        
        if ($product !== null) {
            $reviews = db_fetch_all(
                "SELECT r.rating, r.\"COMMENT\" as review_comment, r.review_date, 
                        u.first_name || ' ' || u.last_name AS customer_name
                 FROM REVIEW r
                 JOIN CUSTOMER c ON r.customer_id = c.customer_id
                 JOIN \"USER\" u ON c.customer_id = u.user_id
                 WHERE r.product_id = :product_id
                 ORDER BY r.review_date DESC",
                ['product_id' => $productId]
            );
            
            // Calculate average rating
            $avgRating = 0;
            $reviewCount = count($reviews);
            if ($reviewCount > 0) {
                $sum = 0;
                foreach ($reviews as $rev) {
                    $sum += (float) $rev['RATING'];
                }
                $avgRating = round($sum / $reviewCount, 1);
            }
            $product['avg_rating'] = $avgRating;
            $product['review_count'] = $reviewCount;
            $product['reviews'] = $reviews;

            $relatedProducts = db_fetch_all(
                "SELECT p.product_id, p.product_name, p.price, p.product_image, d.discount_percentage
                 FROM PRODUCT p
                 JOIN SHOP s ON s.shop_id = p.shop_id
                 LEFT JOIN DISCOUNT d ON p.discount_id = d.discount_id AND d.end_date >= SYSDATE
                 WHERE p.category_id = (SELECT category_id FROM PRODUCT WHERE product_id = :product_id)
                   AND p.product_id != :product_id
                   AND p.product_verification_status = 'APPROVED'
                   AND s.shop_status = 'ACTIVE'
                 FETCH FIRST 4 ROWS ONLY",
                ['product_id' => $productId]
            );
            $product['related_products'] = $relatedProducts;
        }
    }
    
    return $product;
}

/**
 * Fetches products and category title for the category browse page.
 * 
 * @param int|null $selectedCategoryId
 * @param string $sortOrder
 * @param float|null $minPrice
 * @param float|null $maxPrice
 * @return array ['categoryTitle' => string, 'products' => array]
 */
function get_storefront_category_data(?int $selectedCategoryId, string $sortOrder, ?float $minPrice, ?float $maxPrice): array {
    $categoryTitle = 'All Categories';
    $products = [];

    if (db_is_offline()) {
        $categoryTitle = offline_get_category_name($selectedCategoryId);
        $products = offline_get_products($selectedCategoryId);
    } else {
        if ($selectedCategoryId !== null) {
            $category = db_fetch_one(
                'SELECT category_name FROM CATEGORY WHERE category_id = :category_id',
                ['category_id' => $selectedCategoryId]
            );
            if ($category !== null) {
                $categoryTitle = (string) $category['CATEGORY_NAME'];
            }
        }

        $sql = "SELECT p.product_id,
                       p.product_name,
                       p.price,
                       p.product_image,
                       NVL(u.first_name || ' ' || u.last_name, s.shop_name) AS trader_name,
                       s.shop_name,
                       c.category_name
                FROM PRODUCT p
                JOIN SHOP s ON s.shop_id = p.shop_id
                JOIN TRADER t ON t.trader_id = s.trader_id
                JOIN \"USER\" u ON u.user_id = t.trader_id
                JOIN CATEGORY c ON c.category_id = p.category_id";

        $binds = [];
        if ($selectedCategoryId !== null) {
            $sql .= ' WHERE p.category_id = :category_id AND p.product_verification_status = :verification_status AND s.shop_status = \'ACTIVE\'';
            $binds['category_id'] = $selectedCategoryId;
        } else {
            $sql .= ' WHERE p.product_verification_status = :verification_status AND s.shop_status = \'ACTIVE\'';
        }
        $binds['verification_status'] = 'APPROVED';

        if ($minPrice !== null) {
            $sql .= ' AND p.price >= :min_price';
            $binds['min_price'] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $sql .= ' AND p.price <= :max_price';
            $binds['max_price'] = $maxPrice;
        }

        switch ($sortOrder) {
            case 'price_asc':
                $sql .= ' ORDER BY p.price ASC';
                break;
            case 'price_desc':
                $sql .= ' ORDER BY p.price DESC';
                break;
            case 'name_desc':
                $sql .= ' ORDER BY p.product_name DESC';
                break;
            case 'name_asc':
            default:
                $sql .= ' ORDER BY p.product_name ASC';
                break;
        }
        
        $products = db_fetch_all($sql, $binds);
    }

    return [
        'categoryTitle' => $categoryTitle,
        'products' => $products
    ];
}
