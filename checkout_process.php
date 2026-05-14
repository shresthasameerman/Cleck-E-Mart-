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
        // STEP 4.5: SEND INVOICE EMAIL
        // ====================================================================
        try {
            $cust_sql = "SELECT first_name, email FROM \"USER\" WHERE user_id = :cust_id";
            $cust_stmt = oci_parse($conn, $cust_sql);
            oci_bind_by_name($cust_stmt, ':cust_id', $customer_id, -1, SQLT_INT);
            oci_execute($cust_stmt);
            $cust_row = oci_fetch_assoc($cust_stmt);
            oci_free_statement($cust_stmt);
            
            if ($cust_row && !empty($cust_row['EMAIL'])) {
                $customerEmail = $cust_row['EMAIL'];
                $customerName = $cust_row['FIRST_NAME'] ?? 'Customer';
                
                $subject = "Invoice for Order #" . $new_order_id . " - Cleck E-Mart";
                
                $message = "
                <html>
                <head>
                <title>Invoice for Order #{$new_order_id}</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
                    .header { text-align: center; margin-bottom: 20px; }
                    .table { width: 100%; border-collapse: collapse; }
                    .table th, .table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
                    .total { font-weight: bold; font-size: 1.2em; text-align: right; }
                </style>
                </head>
                <body>
                    <div class='invoice-box'>
                        <div class='header'>
                            <h2>Cleck E-Mart</h2>
                            <p>Invoice for Order #{$new_order_id}</p>
                        </div>
                        <p>Dear {$customerName},</p>
                        <p>Thank you for your purchase! Here are the details of your order:</p>
                        <table class='table'>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                </tr>
                            </thead>
                            <tbody>";
                            
                foreach ($cart_items as $item) {
                    $itemTotalNum = $item['quantity'] * $item['unit_price'];
                    $itemTotal = number_format($itemTotalNum, 2);
                    $unitPrice = number_format($item['unit_price'], 2);
                    $productName = $item['name'] ?? $item['product_name'] ?? 'Product #' . $item['product_id'];
                    $message .= "
                                <tr>
                                    <td>{$productName}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>£{$unitPrice}</td>
                                    <td>£{$itemTotal}</td>
                                </tr>";
                }
                
                $formattedTotal = number_format($total_amount, 2);
                $message .= "
                            </tbody>
                        </table>
                        <p class='total'>Total Amount: £{$formattedTotal}</p>
                        <p>We hope to see you again soon!</p>
                        <p>Regards,<br>The Cleck E-Mart Team</p>
                    </div>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Cleck E-Mart <noreply@cleck-e-mart.com>" . "\r\n";
                
                @mail($customerEmail, $subject, $message, $headers);
            }
        } catch (Exception $e) {
            // Ignore email errors
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
