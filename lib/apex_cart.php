<?php
// This file handles syncing the shopping cart state with the remote APEX database.

/**
 * Oracle APEX Shopping Cart Integration
 * Handles cart operations via Oracle APEX REST endpoints
 */

/**
 * Fetch cart items for a customer from APEX API
 *
 * @param int $customerId Customer ID
 * @return array Array of cart items with keys: product_id, product_name, product_description,
 *               price, product_image, quantity, shop_name, subtotal, discount_percentage
 * @throws RuntimeException If API call fails
 */
function apex_get_cart_items(int $customerId): array
{
    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/cart/items/" . urlencode((string) $customerId);
    $timeout = 10;

    try {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
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

        $data = json_decode($response, true);
        if ($data === null) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new RuntimeException('Invalid API response format');
        }

        return normalize_apex_cart_items($data['items']);
    } catch (Throwable $e) {
        error_log('APEX cart fetch error: ' . $e->getMessage());
        throw new RuntimeException('Unable to fetch cart: ' . $e->getMessage());
    }
}

/**
 * Update cart item quantity via APEX API
 *
 * @param int $customerId Customer ID
 * @param int $productId Product ID
 * @param int $quantity New quantity (0 = remove from cart)
 * @return bool Success status
 * @throws RuntimeException If API call fails
 */
function apex_update_cart_quantity(int $customerId, int $productId, int $quantity): bool
{
    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/cart/update/";
    $timeout = 10;

    try {
        $postData = [
            "customer_id" => $customerId,
            "product_id" => $productId,
            "quantity" => max(0, $quantity),
        ];

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200) {
            error_log("APEX cart update returned HTTP {$httpCode}");
            return false;
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        return ($result['status'] ?? '') === 'success';
    } catch (Throwable $e) {
        error_log('APEX cart update error: ' . $e->getMessage());
        throw new RuntimeException('Unable to update cart: ' . $e->getMessage());
    }
}

/**
 * Add product to cart via APEX API
 *
 * @param int $customerId Customer ID
 * @param int $productId Product ID
 * @param int $quantity Quantity to add
 * @return bool Success status
 * @throws RuntimeException If API call fails
 */
function apex_add_to_cart(int $customerId, int $productId, int $quantity): bool
{
    $url = "https://apex.oracle.com/pls/apex/nikhilesh_77576121/cart/add/";
    $timeout = 10;

    try {
        $postData = [
            "customer_id" => $customerId,
            "product_id" => $productId,
            "quantity" => max(1, $quantity),
        ];

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            error_log("APEX cart add returned HTTP {$httpCode}");
            return false;
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        return ($result['status'] ?? '') === 'success';
    } catch (Throwable $e) {
        error_log('APEX cart add error: ' . $e->getMessage());
        throw new RuntimeException('Unable to add to cart: ' . $e->getMessage());
    }
}

/**
 * Get cart total from items
 *
 * @param array $items Cart items
 * @return float Total price
 */
function apex_cart_total(array $items): float
{
    $total = 0.0;

    foreach ($items as $item) {
        $price = (float) ($item['price'] ?? 0);
        $quantity = (int) ($item['quantity'] ?? 0);
        $discount = (float) ($item['discount_percentage'] ?? 0);

        if ($discount > 0) {
            $discounted = $price * (1 - $discount / 100);
            $total += $discounted * $quantity;
        } else {
            $total += $price * $quantity;
        }
    }

    return $total;
}

/**
 * Normalize cart items from APEX API response
 *
 * @param array $items Raw items from API
 * @return array Normalized items
 */
function normalize_apex_cart_items(array $items): array
{
    $normalized = [];

    foreach ($items as $item) {
        $price = (float) ($item['price'] ?? 0);
        $quantity = (int) ($item['quantity'] ?? 1);
        $discount = (float) ($item['discount_percentage'] ?? 0);

        if ($discount > 0) {
            $discounted = $price * (1 - $discount / 100);
            $subtotal = $discounted * $quantity;
        } else {
            $subtotal = $price * $quantity;
        }

        $pid = (int) ($item['product_id'] ?? 0);
        $uploadedImg = trim((string) ($item['product_image'] ?? ''));
        if ($uploadedImg === '') {
            require_once __DIR__ . '/product_images.php';
            $img = default_product_image($pid, null, 400);
        } else {
            $img = $uploadedImg;
        }

        $normalized[] = [
            'product_id' => $pid,
            'product_name' => (string) ($item['product_name'] ?? 'Unknown'),
            'product_description' => (string) ($item['product_description'] ?? ''),
            'price' => $price,
            'product_image' => $img,
            'quantity' => $quantity,
            'shop_name' => (string) ($item['shop_name'] ?? 'Unknown Shop'),
            'subtotal' => $subtotal,
            'discount_percentage' => $discount > 0 ? $discount : null,
            'original_price' => $price,
        ];
    }

    return $normalized;
}

/**
 * Check if APEX cart integration is enabled
 *
 * @return bool True if APEX cart should be used
 */
function apex_cart_enabled(): bool
{
    $enabled = strtolower((string) (getenv('APEX_CART_ENABLED') ?: 'false'));
    return $enabled === 'true' || $enabled === '1';
}
