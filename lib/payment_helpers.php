<?php
// These helper functions handle the complex logic of processing payments and saving order details.

/**
 * Payment Helper Functions
 * Extracts complex checkout logic and transaction processing from payment.php.
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/email_helpers.php';
require_once __DIR__ . '/cart_helpers.php';
require_once __DIR__ . '/apex_cart.php';
require_once __DIR__ . '/oci_db.php';

/**
 * Validates a coupon code against the database.
 * 
 * @param string $couponCode The code entered by the user.
 * @param float $subtotal The current cart subtotal.
 * @return array ['success' => bool, 'message' => string, 'coupon' => array|null]
 */
function validate_coupon(string $couponCode, float $subtotal): array {
    global $conn;
    
    if ($couponCode === '') {
        return ['success' => false, 'message' => 'Please enter a coupon code.', 'coupon' => null];
    }
    
    $sql = "SELECT coupon_id, discount_amount, minimum_order_amount 
            FROM COUPON 
            WHERE UPPER(coupon_code) = UPPER(:code) 
            AND coupon_status = 'ACTIVE' 
            AND TRUNC(SYSDATE) >= TRUNC(valid_from) 
            AND TRUNC(SYSDATE) <= TRUNC(valid_to)";
            
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':code', $couponCode);
    oci_execute($stmt);
    $coupon = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    
    if (!$coupon) {
        return ['success' => false, 'message' => 'Invalid or expired coupon code.', 'coupon' => null];
    }
    
    $minAmount = (float) $coupon['MINIMUM_ORDER_AMOUNT'];
    if ($subtotal < $minAmount) {
        return ['success' => false, 'message' => 'Minimum order amount for this coupon is £' . number_format($minAmount, 2), 'coupon' => null];
    }
    
    return [
        'success' => true,
        'message' => 'Coupon applied successfully!',
        'coupon' => [
            'id' => (int) $coupon['COUPON_ID'],
            'code' => strtoupper($couponCode),
            'discount' => (float) $coupon['DISCOUNT_AMOUNT'],
            'min_amount' => $minAmount
        ]
    ];
}

/**
 * Executes the complete PayPal order transaction block.
 * Wraps order creation, item insertion, and payment records in a single atomic transaction.
 *
 * @param int $customerId
 * @param string $selectedSlotDate
 * @param string $selectedSlotTime
 * @param array $normalizedItems
 * @param float $total
 * @param string $paypalTransactionId
 * @param array|null $appliedCoupon
 * @param string $customerName
 * @param string $customerEmail
 * @return array ['success' => bool, 'transaction_id' => string|null, 'error' => string|null]
 */
function process_paypal_order(
    int $customerId,
    string $selectedSlotDate,
    string $selectedSlotTime,
    array $normalizedItems,
    float $total,
    string $paypalTransactionId,
    ?array $appliedCoupon,
    string $customerName,
    string $customerEmail
): array {
    global $conn;

    try {
        if (!$conn) {
            throw new Exception('Database connection failed. Please try again later.');
        }

        // 1. Verify user is in CUSTOMER table
        $checkCustSql = 'SELECT customer_id FROM CUSTOMER WHERE customer_id = :customer_id';
        $checkCustStmt = oci_parse($conn, $checkCustSql);
        oci_bind_by_name($checkCustStmt, ':customer_id', $customerId, -1, SQLT_INT);
        oci_execute($checkCustStmt, OCI_NO_AUTO_COMMIT);
        
        $customerExists = oci_fetch_assoc($checkCustStmt);
        oci_free_statement($checkCustStmt);

        if (!$customerExists) {
            $insertCustSql = 'INSERT INTO CUSTOMER (customer_id) VALUES (:customer_id)';
            $insertCustStmt = oci_parse($conn, $insertCustSql);
            oci_bind_by_name($insertCustStmt, ':customer_id', $customerId, -1, SQLT_INT);
            if (!oci_execute($insertCustStmt, OCI_NO_AUTO_COMMIT)) {
                throw new Exception('Failed to auto-register customer profile: ' . oci_error($insertCustStmt)['message']);
            }
            oci_free_statement($insertCustStmt);
        }

        // 2. Dynamic Slot ID Lookup
        $actualSlotId = null;
        if ($selectedSlotDate !== '' && $selectedSlotTime !== '') {
            $searchSql = "SELECT slot_id FROM COLLECTION_SLOT 
                          WHERE slot_time = :s_time 
                          AND TO_CHAR(slot_date, 'YYYY-MM-DD') = :s_date
                          FETCH FIRST 1 ROWS ONLY";
            $searchStmt = oci_parse($conn, $searchSql);
            $cleanDate = trim($selectedSlotDate);
            oci_bind_by_name($searchStmt, ':s_time', $selectedSlotTime);
            oci_bind_by_name($searchStmt, ':s_date', $cleanDate);
            oci_execute($searchStmt, OCI_NO_AUTO_COMMIT);
            
            $row = oci_fetch_assoc($searchStmt);
            if ($row) {
                $actualSlotId = (int)$row['SLOT_ID'];
            }
            oci_free_statement($searchStmt);
        }
        
        // Safety Fallback for Slot ID
        if ($actualSlotId === null) {
            $fallbackSql = "SELECT slot_id FROM COLLECTION_SLOT WHERE slot_status = 'AVAILABLE' FETCH FIRST 1 ROWS ONLY";
            $fallbackStmt = oci_parse($conn, $fallbackSql);
            oci_execute($fallbackStmt, OCI_NO_AUTO_COMMIT);
            $fallbackRow = oci_fetch_assoc($fallbackStmt);
            if ($fallbackRow) {
                $actualSlotId = (int)$fallbackRow['SLOT_ID'];
            } else {
                throw new Exception('No available collection slots found in the database.');
            }
            oci_free_statement($fallbackStmt);
        }
        
        // 3. Insert Parent ORDER
        $couponId = $appliedCoupon ? (string)$appliedCoupon['id'] : null;
        $newOrderId = db_next_id('"ORDER"', 'order_id');

        $orderSql = "INSERT INTO \"ORDER\" (order_id, customer_id, slot_id, coupon_id, order_status, order_date) 
                     VALUES (:order_id, :customer_id, :slot_id, :coupon_id, 'PENDING', SYSDATE)";
        $orderStmt = oci_parse($conn, $orderSql);
        oci_bind_by_name($orderStmt, ':order_id', $newOrderId, -1, SQLT_INT);
        oci_bind_by_name($orderStmt, ':customer_id', $customerId, -1, SQLT_INT);
        oci_bind_by_name($orderStmt, ':slot_id', $actualSlotId, -1, SQLT_INT);
        oci_bind_by_name($orderStmt, ':coupon_id', $couponId);
        
        if (!oci_execute($orderStmt, OCI_NO_AUTO_COMMIT)) {
            throw new Exception('Failed to insert ORDER: ' . oci_error($orderStmt)['message']);
        }
        oci_free_statement($orderStmt);
        
        $internalTransactionId = 'PAYPAL-' . str_pad((string)$newOrderId, 12, '0', STR_PAD_LEFT);
        
        // 4. Insert ORDER ITEMS
        $itemSql = "INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) 
                    VALUES (:order_id, :product_id, :quantity, :unit_price)";
        $itemStmt = oci_parse($conn, $itemSql);
        
        foreach ($normalizedItems as $line) {
            $loopOrderId = (string) $newOrderId;
            $loopProductId = (int) $line['product_id'];
            $loopQuantity = (int) $line['quantity'];
            $loopUnitPrice = (string) $line['unit_price'];
            
            oci_bind_by_name($itemStmt, ':order_id', $loopOrderId);
            oci_bind_by_name($itemStmt, ':product_id', $loopProductId);
            oci_bind_by_name($itemStmt, ':quantity', $loopQuantity);
            oci_bind_by_name($itemStmt, ':unit_price', $loopUnitPrice);
            
            if (!oci_execute($itemStmt, OCI_NO_AUTO_COMMIT)) {
                throw new Exception('Failed to insert ORDER_ITEM for product ' . $loopProductId . ': ' . oci_error($itemStmt)['message']);
            }
        }
        oci_free_statement($itemStmt);
        
        // 5. Update Order to PAID
        $updateOrderSql = "UPDATE \"ORDER\" SET order_status = 'PAID' WHERE order_id = :order_id";
        $updateOrderStmt = oci_parse($conn, $updateOrderSql);
        oci_bind_by_name($updateOrderStmt, ':order_id', $newOrderId, -1, SQLT_INT);
        if (!oci_execute($updateOrderStmt, OCI_NO_AUTO_COMMIT)) {
            throw new Exception('Failed to update ORDER to PAID: ' . oci_error($updateOrderStmt)['message']);
        }
        oci_free_statement($updateOrderStmt);

        // 6. Insert PAYMENT Record
        if ($paypalTransactionId === '') {
            $paypalTransactionId = 'PP-TXN-' . date('Ymd') . '-' . rand(1000, 9999);
        }
        $paymentSql = "INSERT INTO PAYMENT (order_id, amount_paid, payment_method, payment_status, payment_date, transaction_reference) 
                       VALUES (:order_id, :amount, 'PAYPAL', 'PAID', SYSDATE, :transaction_reference)";
        $paymentStmt = oci_parse($conn, $paymentSql);
        
        $bindAmount = (string) $total;
        oci_bind_by_name($paymentStmt, ':order_id', $newOrderId);
        oci_bind_by_name($paymentStmt, ':amount', $bindAmount);
        oci_bind_by_name($paymentStmt, ':transaction_reference', $paypalTransactionId);
        if (!oci_execute($paymentStmt, OCI_NO_AUTO_COMMIT)) {
            throw new Exception('Failed to insert PAYMENT: ' . oci_error($paymentStmt)['message']);
        }
        oci_free_statement($paymentStmt);
        
        // 7. COMMIT TRANSACTION
        if (!oci_commit($conn)) {
            throw new Exception('Failed to commit transaction: ' . oci_error($conn)['message']);
        }
        
        // 8. SEND INVOICE EMAIL
        _send_invoice_email($customerEmail, $customerName, $newOrderId, $normalizedItems, $total);
        
        // 9. CLEAR CART
        _clear_customer_cart($customerId, $normalizedItems);
        
        return [
            'success' => true,
            'transaction_id' => $internalTransactionId,
            'error' => null
        ];
        
    } catch (Exception $e) {
        if (isset($conn)) {
            oci_rollback($conn);
        }
        return [
            'success' => false,
            'transaction_id' => null,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Helper to send the invoice email.
 */
function _send_invoice_email($email, $name, $orderId, $items, $total) {
    if (!$email) return;
    
    $subject = "Invoice for Order #" . $orderId . " - Cleck E-Mart";
    $message = "
    <html>
    <head>
    <title>Invoice for Order #{$orderId}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px; }
        .invoice-box { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .header { background-color: #1a3018; color: #ffffff; padding: 40px 30px; text-align: center; }
        .header h2 { margin: 0 0 10px; font-size: 28px; letter-spacing: 1px; }
        .header p { margin: 0; opacity: 0.9; font-size: 16px; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-top: 0; color: #1a3018; font-weight: 600; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 30px 0; }
        .table th { background: #f4f6f4; color: #1a3018; padding: 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e4e0; }
        .table td { padding: 15px; border-bottom: 1px solid #eee; color: #555; }
        .table tr:last-child td { border-bottom: none; }
        .total-row { background: #1a3018; color: #fff; border-radius: 8px; }
        .total-row td { color: #fff; font-weight: bold; font-size: 18px; border: none; padding: 20px 15px; }
        .footer { text-align: center; padding: 30px; color: #888; font-size: 14px; background: #fafafa; border-top: 1px solid #eee; }
    </style>
    </head>
    <body>
        <div class='invoice-box'>
            <div class='header'>
                <h2>CLECK E-MART</h2>
                <p>Official Invoice &bull; Order #{$orderId}</p>
            </div>
            <div class='content'>
                <p class='greeting'>Dear {$name},</p>
                <p>Thank you for your purchase! Your payment has been successfully processed. Here is the invoice for your order:</p>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th style='text-align: center;'>Qty</th>
                            <th style='text-align: right;'>Amount</th>
                        </tr>
                    </thead>
                    <tbody>";
                
    foreach ($items as $line) {
        $itemTotal = number_format($line['line_total'], 2);
        $message .= "
                        <tr>
                            <td>{$line['name']}</td>
                            <td style='text-align: center;'>{$line['quantity']}</td>
                            <td style='text-align: right;'>£{$itemTotal}</td>
                        </tr>";
    }
    
    $formattedTotal = number_format($total, 2);
    $message .= "
                        <tr class='total-row'>
                            <td colspan='2' style='text-align: right; border-radius: 8px 0 0 8px;'><strong>Total Paid:</strong></td>
                            <td style='text-align: right; border-radius: 0 8px 8px 0;'><strong>£{$formattedTotal}</strong></td>
                        </tr>
                    </tbody>
                </table>
                <p>If you have any questions regarding this invoice, simply reply to this email.</p>
            </div>
            <div class='footer'>
                <p>Cleck E-Mart &copy; " . date('Y') . "<br>Bringing fresh goods to your doorstep.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    send_email($email, $subject, $message);
}

/**
 * Helper to clear customer cart after successful purchase.
 */
function _clear_customer_cart($customerId, $items) {
    foreach ($items as $line) {
        $pid = (int) $line['product_id'];
        if ($pid <= 0) continue;
        
        try {
            if (apex_cart_enabled()) {
                try {
                    apex_update_cart_quantity($customerId, $pid, 0);
                } catch (Throwable $exception) {
                    update_cart_item_quantity($customerId, $pid, 0);
                }
            } else {
                update_cart_item_quantity($customerId, $pid, 0);
            }
        } catch (Throwable $exception) {
            // Cart cleanup is best-effort; don't fail the payment
        }
    }
}
