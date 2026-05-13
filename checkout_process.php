<?php
/**
 * Checkout Transaction Handler for Oracle OCI8
 * 
 * This script processes a complete checkout with transaction support:
 * 1. Inserts a new order and captures the auto-generated order_id
 * 2. Inserts order items from the cart
 * 3. Inserts a payment record
 * 4. Commits on success or rolls back on failure
 * 5. Clears the cart after successful commit
 */

// Include your database connection
require_once 'lib/bootstrap.php';
require_once 'lib/oci_db.php';

/**
 * Process a complete checkout transaction
 * 
 * @param resource $conn OCI8 database connection
 * @param int $customer_id The customer placing the order
 * @param int $slot_id The collection slot ID
 * @param array $cart_items Array of cart items with product_id, quantity, unit_price
 * @param string $payment_method Payment method (e.g., 'CARD', 'CASH')
 * @param float $total_amount Total amount paid
 * 
 * @return array ['success' => bool, 'order_id' => int|null, 'message' => string]
 */
function processCheckoutTransaction($conn, $customer_id, $slot_id, $cart_items, $payment_method, $total_amount)
{
    try {
        // ====================================================================
        // STEP 1: INSERT ORDER & CAPTURE AUTO-GENERATED order_id
        // ====================================================================
        
        $order_sql = "INSERT INTO \"ORDER\" (customer_id, slot_id, order_status, order_date) 
                      VALUES (:cust_id, :slot_id, :status, SYSDATE)
                      RETURNING order_id INTO :new_order_id";
        
        $order_stmt = oci_parse($conn, $order_sql);
        
        if (!$order_stmt) {
            throw new Exception("Failed to parse order SQL: " . oci_error($conn)['message']);
        }
        
        // Bind input parameters
        oci_bind_by_name($order_stmt, ':cust_id', $customer_id, -1, SQLT_INT);
        oci_bind_by_name($order_stmt, ':slot_id', $slot_id, -1, SQLT_INT);
        oci_bind_by_name($order_stmt, ':status', $status = 'PAID', -1, SQLT_CHR);
        
        // CRITICAL: Bind output parameter for RETURNING clause
        $new_order_id = null;
        oci_bind_by_name($order_stmt, ':new_order_id', $new_order_id, -1, SQLT_INT);
        
        // Execute the insert
        if (!oci_execute($order_stmt)) {
            throw new Exception("Failed to insert order: " . oci_error($order_stmt)['message']);
        }
        
        // Fetch the returned order_id from the RETURNING clause
        oci_fetch($order_stmt);
        
        if ($new_order_id === null) {
            throw new Exception("Order inserted but order_id was not returned by trigger");
        }
        
        oci_free_statement($order_stmt);
        
        // ====================================================================
        // STEP 2: INSERT ORDER ITEMS (from cart)
        // ====================================================================
        
        $item_sql = "INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) 
                     VALUES (:order_id, :product_id, :qty, :price)";
        
        $item_stmt = oci_parse($conn, $item_sql);
        
        if (!$item_stmt) {
            throw new Exception("Failed to parse order item SQL: " . oci_error($conn)['message']);
        }
        
        // Loop through cart items and insert each one
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            
            // Bind parameters for this iteration
            oci_bind_by_name($item_stmt, ':order_id', $new_order_id, -1, SQLT_INT);
            oci_bind_by_name($item_stmt, ':product_id', $product_id, -1, SQLT_INT);
            oci_bind_by_name($item_stmt, ':qty', $quantity, -1, SQLT_INT);
            oci_bind_by_name($item_stmt, ':price', $unit_price, -1, SQLT_FLT);
            
            // Execute the insert for this item
            if (!oci_execute($item_stmt)) {
                throw new Exception("Failed to insert order item (product_id: $product_id): " . oci_error($item_stmt)['message']);
            }
        }
        
        oci_free_statement($item_stmt);
        
        // ====================================================================
        // STEP 3: INSERT PAYMENT RECORD
        // ====================================================================
        
        $payment_sql = "INSERT INTO PAYMENT (order_id, amount_paid, payment_method, payment_status, payment_date) 
                        VALUES (:order_id, :amount, :method, :p_status, SYSDATE)";
        
        $payment_stmt = oci_parse($conn, $payment_sql);
        
        if (!$payment_stmt) {
            throw new Exception("Failed to parse payment SQL: " . oci_error($conn)['message']);
        }
        
        $payment_status = 'COMPLETED';
        
        oci_bind_by_name($payment_stmt, ':order_id', $new_order_id, -1, SQLT_INT);
        oci_bind_by_name($payment_stmt, ':amount', $total_amount, -1, SQLT_FLT);
        oci_bind_by_name($payment_stmt, ':method', $payment_method, -1, SQLT_CHR);
        oci_bind_by_name($payment_stmt, ':p_status', $payment_status, -1, SQLT_CHR);
        
        if (!oci_execute($payment_stmt)) {
            throw new Exception("Failed to insert payment: " . oci_error($payment_stmt)['message']);
        }
        
        oci_free_statement($payment_stmt);
        
        // ====================================================================
        // STEP 4: COMMIT TRANSACTION
        // ====================================================================
        
        if (!oci_commit($conn)) {
            throw new Exception("Failed to commit transaction: " . oci_error($conn)['message']);
        }
        
        // ====================================================================
        // STEP 5: CLEAR CART (after successful commit)
        // ====================================================================
        
        clearUserCart($customer_id);
        
        return [
            'success' => true,
            'order_id' => $new_order_id,
            'message' => "Order #$new_order_id created successfully and payment recorded."
        ];
        
    } catch (Exception $e) {
        // ====================================================================
        // ROLLBACK ON ANY ERROR
        // ====================================================================
        
        oci_rollback($conn);
        
        return [
            'success' => false,
            'order_id' => null,
            'message' => "Checkout failed: " . $e->getMessage()
        ];
    }
}

/**
 * Helper function to clear a user's cart (session-based or database-based)
 * 
 * @param int $customer_id
 */
function clearUserCart($customer_id)
{
    // If using session-based cart:
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
    
    // If using database-based cart, you can also clear it here:
    // (Implement based on your cart storage method)
}

/**
 * Example usage in a checkout endpoint
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_checkout'])) {
    
    // Get the database connection (from your bootstrap)
    $conn = getOracleConnection();  // Adjust to your connection function
    
    // Sanitize and validate inputs
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $slot_id = (int)($_POST['slot_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'CARD';
    
    // Get cart items (from session or database)
    $cart_items = getCartItems($customer_id);  // Implement based on your cart system
    
    // Calculate total
    $total_amount = array_reduce($cart_items, function($sum, $item) {
        return $sum + ($item['quantity'] * $item['unit_price']);
    }, 0);
    
    // Validate cart is not empty
    if (empty($cart_items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }
    
    // Process the transaction
    $result = processCheckoutTransaction($conn, $customer_id, $slot_id, $cart_items, $payment_method, $total_amount);
    
    // Return JSON response
    header('Content-Type: application/json');
    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
    
    oci_close($conn);
    exit;
}

/**
 * Placeholder function to get cart items
 * Implement this based on your cart storage method (session, database, etc.)
 */
function getCartItems($customer_id)
{
    // Example: Retrieve from database or session
    // For now, returning empty array
    return [];
}

/**
 * Placeholder function to get Oracle connection
 * Replace with your actual connection function
 */
function getOracleConnection()
{
    return db_connect();
}
?>
