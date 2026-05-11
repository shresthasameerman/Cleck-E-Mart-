<?php
/**
 * Cart AJAX API Endpoint
 * Handles dynamic cart updates via AJAX requests
 */

require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/apex_cart.php';

header('Content-Type: application/json');

// Verify user is logged in and is a customer
if (!is_logged_in() || current_role() !== 'CUSTOMER' || current_customer_id() === null) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$customerId = (int) current_customer_id();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'fetch':
            // Fetch current cart items
            $useApex = apex_cart_enabled();
            
            $items = [];
            if ($useApex) {
                try {
                    $items = apex_get_cart_items($customerId);
                } catch (Throwable $e) {
                    // Fall back to local
                    error_log('APEX cart fetch failed, using local: ' . $e->getMessage());
                    $items = get_cart_items_for_customer($customerId);
                }
            } else {
                $items = get_cart_items_for_customer($customerId);
            }

            $total = apex_cart_total($items);

            echo json_encode([
                'status' => 'success',
                'items' => $items,
                'total' => $total,
                'item_count' => count($items),
            ]);
            break;

        case 'update':
            // Update item quantity
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

            if ($productId === false || $productId === null || $quantity === false || $quantity === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid product_id or quantity']);
                exit;
            }

            $useApex = apex_cart_enabled();
            $success = false;

            if ($useApex) {
                try {
                    $success = apex_update_cart_quantity($customerId, $productId, $quantity);
                } catch (Throwable $e) {
                    error_log('APEX cart update failed, trying local: ' . $e->getMessage());
                    update_cart_item_quantity($customerId, $productId, $quantity);
                    $success = true;
                }
            } else {
                update_cart_item_quantity($customerId, $productId, $quantity);
                $success = true;
            }

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update cart']);
            }
            break;

        case 'add':
            // Add item to cart
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

            if ($productId === false || $productId === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid product_id']);
                exit;
            }

            $quantity = $quantity !== false && $quantity !== null ? max(1, $quantity) : 1;

            $useApex = apex_cart_enabled();
            $success = false;

            if ($useApex) {
                try {
                    $success = apex_add_to_cart($customerId, $productId, $quantity);
                } catch (Throwable $e) {
                    error_log('APEX cart add failed, trying local: ' . $e->getMessage());
                    add_product_to_cart($customerId, $productId, $quantity);
                    $success = true;
                }
            } else {
                add_product_to_cart($customerId, $productId, $quantity);
                $success = true;
            }

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Throwable $exception) {
    error_log('Cart API error: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $exception->getMessage()]);
}
