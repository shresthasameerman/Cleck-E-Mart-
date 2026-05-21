<?php
/**
 * Oracle APEX API Integration
 * Handles fetching product data from Oracle APEX REST endpoints
 */

require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/product_images.php';

/**
 * Fetch all products from Oracle APEX API with error handling and optional caching
 *
 * @param int $cacheMinutes Optional cache duration in minutes. 0 = no caching (default: 5 minutes)
 * @return array Array of products with keys: product_id, product_name, product_description, 
 *               price, product_image, discount_percentage, shop_name, category_name, product_status
 * @throws RuntimeException If API call fails or response is invalid
 */
function fetch_apex_products(int $cacheMinutes = 5): array
{
    // Check cache first if enabled
    if ($cacheMinutes > 0) {
        $cached = get_apex_cache('products');
        if ($cached !== null) {
            return $cached;
        }
    }

    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/products/all/";
    $timeout = 10;

    try {
        // Initialize cURL
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Cleck-E-Mart/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("API returned HTTP {$httpCode}");
        }

        // Decode JSON response
        $data = json_decode($response, true);
        if ($data === null) {
            throw new RuntimeException('Invalid JSON response from API: ' . json_last_error_msg());
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new RuntimeException('API response missing items array or invalid format');
        }

        // Normalize product data
        $products = normalize_apex_products($data['items']);

        // Cache the results if enabled
        if ($cacheMinutes > 0) {
            set_apex_cache('products', $products, $cacheMinutes);
        }

        return $products;
    } catch (Throwable $e) {
        // Fallback to offline products so homepage remains usable if APEX is down/unreachable.
        try {
            $fallback = fallback_apex_products_from_offline();
            if ($fallback !== []) {
                return $fallback;
            }
        } catch (Throwable $fallbackException) {
            error_log('APEX fallback error: ' . $fallbackException->getMessage());
        }

        error_log('APEX API Error: ' . $e->getMessage());
        throw new RuntimeException('Unable to fetch products from APEX: ' . $e->getMessage());
    }
}

/**
 * Build a featured product list from offline store when remote API is unavailable.
 *
 * @return array Normalized product array
 */
function fallback_apex_products_from_offline(): array
{
    $offlineRows = offline_get_products(null);
    $products = [];

    foreach ($offlineRows as $row) {
        $products[] = [
            'product_id' => (int) ($row['PRODUCT_ID'] ?? 0),
            'product_name' => (string) ($row['PRODUCT_NAME'] ?? 'Unknown'),
            'product_description' => (string) ($row['PRODUCT_DESCRIPTION'] ?? ''),
            'price' => (float) ($row['PRICE'] ?? 0),
            'product_image' => (string) ($row['PRODUCT_IMAGE'] ?? 'assets/images/icons/product-placeholder.svg'),
            'discount_percentage' => null,
            'shop_name' => (string) ($row['SHOP_NAME'] ?? 'Unknown Shop'),
            'category_name' => (string) ($row['CATEGORY_NAME'] ?? 'Uncategorized'),
            'product_status' => 'ACTIVE',
        ];
    }

    return $products;
}

/**
 * Normalize product data from APEX API to consistent format
 *
 * @param array $items Raw items from API response
 * @return array Normalized product array
 */
function normalize_apex_products(array $items): array
{
    $normalized = [];

    foreach ($items as $item) {
        $pid = (int) ($item['product_id'] ?? 0);
        $uploadedImg = trim((string) ($item['product_image'] ?? ''));
        $img = $uploadedImg === '' ? default_product_image($pid, $uploadedImg ?: null, 400) : $uploadedImg;
        if ($uploadedImg === '') {
            $img = default_product_image($pid, null, 400);
        } else {
            $img = $uploadedImg;
        }

        $normalized[] = [
            'product_id' => $pid,
            'product_name' => (string) ($item['product_name'] ?? 'Unknown'),
            'product_description' => (string) ($item['product_description'] ?? ''),
            'price' => (float) ($item['price'] ?? 0),
            'product_image' => $img,
            'discount_percentage' => $item['discount_percentage'] ?? null,
            'shop_name' => (string) ($item['shop_name'] ?? 'Unknown Shop'),
            'category_name' => (string) ($item['category_name'] ?? 'Uncategorized'),
            'product_status' => (string) ($item['product_status'] ?? 'ACTIVE'),
        ];
    }

    return $normalized;
}

/**
 * Format product price with discount handling
 *
 * @param array $product Product data with price and discount_percentage keys
 * @return string Formatted price HTML string
 */
function format_product_price(array $product): string
{
    $price = (float) $product['price'];
    $discount = $product['discount_percentage'] ?? null;

    if ($discount !== null && $discount > 0) {
        $discounted = $price * (1 - $discount / 100);
        return '<s>£' . number_format($price, 2) . '</s> £' . number_format($discounted, 2);
    }

    return '£' . number_format($price, 2);
}

/**
 * Get cached products from session
 *
 * @param string $key Cache key
 * @return mixed|null Cached data or null if expired/missing
 */
function get_apex_cache(string $key): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $cacheKey = '_apex_cache_' . $key;
    $expireKey = '_apex_expire_' . $key;

    if (isset($_SESSION[$cacheKey], $_SESSION[$expireKey])) {
        if ($_SESSION[$expireKey] > time()) {
            return $_SESSION[$cacheKey];
        }
        unset($_SESSION[$cacheKey], $_SESSION[$expireKey]);
    }

    return null;
}

/**
 * Set cache for products
 *
 * @param string $key Cache key
 * @param array $data Data to cache
 * @param int $minutes Duration in minutes
 * @return void
 */
function set_apex_cache(string $key, array $data, int $minutes = 5): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $cacheKey = '_apex_cache_' . $key;
    $expireKey = '_apex_expire_' . $key;

    $_SESSION[$cacheKey] = $data;
    $_SESSION[$expireKey] = time() + ($minutes * 60);
}

/**
 * Clear all APEX API caches
 *
 * @return void
 */
function clear_apex_cache(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    foreach ($_SESSION as $key => $value) {
        if (strpos($key, '_apex_cache_') === 0 || strpos($key, '_apex_expire_') === 0) {
            unset($_SESSION[$key]);
        }
    }
}
