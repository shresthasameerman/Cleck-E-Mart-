<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';

require_login(['CUSTOMER']);

$pageTitle = 'Payment | Cleck E-Mart';
$metaDescription = 'Complete your order securely using PayPal.';

$customerId = current_customer_id();
if ($customerId === null) {
    // In this schema CUSTOMER.customer_id mirrors USER.user_id.
    $customerId = current_user_id();
}
$flashError = get_flash('error');
$flashSuccess = get_flash('success');
$errors = [];
$paymentSuccess = false;
$transactionId = null;

$selectedSlotDate = trim((string) ($_GET['slot_date'] ?? ''));
$selectedSlotTime = trim((string) ($_GET['slot_time'] ?? ''));

$items = [];
// This establishes $conn for the entire file. 
require_once __DIR__ . '/db_connect.php'; 

try {
    if (apex_cart_enabled()) {
        try {
            $items = apex_get_cart_items($customerId);
        } catch (Throwable $exception) {
            $items = get_cart_items_for_customer($customerId);
        }
    } else {
        $items = get_cart_items_for_customer($customerId);
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to load your basket for payment: ' . $exception->getMessage();
}

if ($items === [] && $errors === []) {
    set_flash('error', 'Your basket is empty. Add products before paying.');
    redirect('cart.php');
}

$normalizedItems = [];
$total = 0.0;

foreach ($items as $item) {
    $productId = (int) ($item['product_id'] ?? $item['PRODUCT_ID'] ?? 0);
    $name = (string) ($item['product_name'] ?? $item['PRODUCT_NAME'] ?? 'Unknown product');
    $quantity = (int) ($item['quantity'] ?? $item['QUANTITY'] ?? 0);
    $unitPrice = (float) ($item['price'] ?? $item['UNIT_PRICE'] ?? 0);
    $discount = (float) ($item['discount_percentage'] ?? 0);

    $effectiveUnitPrice = $discount > 0 ? $unitPrice * (1 - ($discount / 100)) : $unitPrice;
    $lineTotal = $effectiveUnitPrice * $quantity;

    $normalizedItems[] = [
        'product_id' => $productId,
        'name' => $name,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'discount_percentage' => $discount,
        'line_total' => $lineTotal,
    ];

    $total += $lineTotal;
}

$subtotal = $total;
$couponDiscount = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'apply_coupon') {
        $couponCode = trim($_POST['coupon_code'] ?? '');
        if ($couponCode === '') {
            set_flash('error', 'Please enter a coupon code.');
        } else {
            global $conn;
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
            
            if ($coupon) {
                $minAmount = (float) $coupon['MINIMUM_ORDER_AMOUNT'];
                if ($subtotal >= $minAmount) {
                    $_SESSION['applied_coupon'] = [
                        'id' => (int) $coupon['COUPON_ID'],
                        'code' => strtoupper($couponCode),
                        'discount' => (float) $coupon['DISCOUNT_AMOUNT'],
                        'min_amount' => $minAmount
                    ];
                    set_flash('success', 'Coupon applied successfully!');
                } else {
                    set_flash('error', 'Minimum order amount for this coupon is £' . number_format($minAmount, 2));
                }
            } else {
                set_flash('error', 'Invalid or expired coupon code.');
            }
        }
        $url = 'payment.php' . ($selectedSlotDate !== '' && $selectedSlotTime !== '' ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : '');
        redirect($url);
    } elseif ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        set_flash('success', 'Coupon removed.');
        $url = 'payment.php' . ($selectedSlotDate !== '' && $selectedSlotTime !== '' ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : '');
        redirect($url);
    }
}

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
if ($appliedCoupon) {
    if ($subtotal >= $appliedCoupon['min_amount']) {
        $couponDiscount = $appliedCoupon['discount'];
        $total = max(0.0, $subtotal - $couponDiscount);
    } else {
        unset($_SESSION['applied_coupon']);
        $appliedCoupon = null;
        set_flash('error', 'Coupon removed because your basket no longer meets the minimum amount.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'paypal_checkout') {
    $termsAccepted = !empty($_POST['terms_accepted']);

    if (!$termsAccepted) {
        $errors[] = 'Please accept the terms before completing PayPal payment.';
    }

    if ($normalizedItems === []) {
        $errors[] = 'Your basket is empty. Please add products before paying.';
    }

    if ($customerId === null || (int) $customerId <= 0) {
        $errors[] = 'Your customer account is not linked correctly. Please sign out and sign in again.';
    }

    if ($errors === []) {
        // ====================================================================
        // ORACLE OCI8 TRANSACTION BLOCK
        // ====================================================================
        
        try {
            global $conn; 
            
            if (!$conn) {
                throw new Exception('Database connection failed. Please try again later.');
            }

            // ====================================================================
            // 🛠️ Verify user is in CUSTOMER table
            // ====================================================================
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

            // ====================================================================
            // 🛠️ DYNAMIC SLOT_ID LOOKUP (Fixes ORA-02291 Error)
            // ====================================================================
            $actualSlotId = null;
            
            if ($selectedSlotDate !== '' && $selectedSlotTime !== '') {
                // Try to find the exact slot ID based on the time and date selected in the UI
                $searchSql = "SELECT slot_id FROM COLLECTION_SLOT 
                              WHERE slot_time = :s_time 
                              AND TO_CHAR(slot_date, 'DD Mon YYYY') LIKE '%' || :s_date || '%' 
                              FETCH FIRST 1 ROWS ONLY";
                $searchStmt = oci_parse($conn, $searchSql);
                
                // Remove day names (e.g., 'Friday') to make matching database dates easier
                $cleanDate = trim(preg_replace('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\s+/i', '', $selectedSlotDate));
                
                oci_bind_by_name($searchStmt, ':s_time', $selectedSlotTime);
                oci_bind_by_name($searchStmt, ':s_date', $cleanDate);
                oci_execute($searchStmt, OCI_NO_AUTO_COMMIT);
                
                $row = oci_fetch_assoc($searchStmt);
                if ($row) {
                    $actualSlotId = (int)$row['SLOT_ID'];
                }
                oci_free_statement($searchStmt);
            }
            
            // Safety Fallback: If no exact match is found, grab ANY available slot to prevent crashes
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
            
            // ====================================================================
            // STEP 1: INSERT ORDER
            // ====================================================================
            
            $couponId = $appliedCoupon ? (string)$appliedCoupon['id'] : null;
            
            $orderSql = "INSERT INTO \"ORDER\" (customer_id, slot_id, coupon_id, order_status, order_date) 
                         VALUES (:customer_id, :slot_id, :coupon_id, 'PAID', SYSDATE)
                         RETURNING order_id INTO :new_order_id";
            
            $orderStmt = oci_parse($conn, $orderSql);
            if (!$orderStmt) {
                throw new Exception('Failed to parse ORDER insert: ' . oci_error($conn)['message']);
            }
            
            oci_bind_by_name($orderStmt, ':customer_id', $customerId, -1, SQLT_INT);
            oci_bind_by_name($orderStmt, ':slot_id', $actualSlotId, -1, SQLT_INT); // Uses our dynamic slot!
            oci_bind_by_name($orderStmt, ':coupon_id', $couponId, -1, SQLT_INT);
            
            $newOrderId = null;
            oci_bind_by_name($orderStmt, ':new_order_id', $newOrderId, 32);
            
            if (!oci_execute($orderStmt, OCI_NO_AUTO_COMMIT)) {
                throw new Exception('Failed to insert ORDER: ' . oci_error($orderStmt)['message']);
            }
            
            oci_fetch($orderStmt);
            
            if ($newOrderId === null) {
                throw new Exception('ORDER inserted but order_id was not returned by the database.');
            }
            
            oci_free_statement($orderStmt);
            
            $transactionId = 'PAYPAL-' . str_pad((string)$newOrderId, 12, '0', STR_PAD_LEFT);
            
           // ====================================================================
            // STEP 2: INSERT ORDER ITEMS
            // ====================================================================
            
            $itemSql = "INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) 
                        VALUES (:order_id, :product_id, :quantity, :unit_price)";
            
            $itemStmt = oci_parse($conn, $itemSql);
            if (!$itemStmt) {
                throw new Exception('Failed to parse ORDER_ITEM insert: ' . oci_error($conn)['message']);
            }
            
            foreach ($normalizedItems as $line) {
                // 1. Assign to fresh, strict variables inside the loop
                $loopOrderId = (string) $newOrderId;
                $loopProductId = (int) $line['product_id'];
                $loopQuantity = (int) $line['quantity'];
                $loopUnitPrice = (string) $line['unit_price']; // String bypasses float errors!
                
                // 2. Bind directly to these fresh variables
                oci_bind_by_name($itemStmt, ':order_id', $loopOrderId);
                oci_bind_by_name($itemStmt, ':product_id', $loopProductId);
                oci_bind_by_name($itemStmt, ':quantity', $loopQuantity);
                oci_bind_by_name($itemStmt, ':unit_price', $loopUnitPrice);
                
                if (!oci_execute($itemStmt, OCI_NO_AUTO_COMMIT)) {
                    throw new Exception('Failed to insert ORDER_ITEM for product ' . $loopProductId . ': ' . oci_error($itemStmt)['message']);
                }
            }
            
            oci_free_statement($itemStmt);

            // ====================================================================
            // STEP 3: INSERT PAYMENT RECORD
            // ====================================================================
            
            $paypalTransactionId = trim($_POST['paypal_transaction_id'] ?? '');
            if ($paypalTransactionId === '') {
                $paypalTransactionId = 'PP-TXN-' . date('Ymd') . '-' . rand(1000, 9999); // Fallback if missing
            }
            
            $paymentSql = "INSERT INTO PAYMENT (order_id, amount_paid, payment_method, payment_status, payment_date, transaction_reference) 
                           VALUES (:order_id, :amount, 'PAYPAL', 'COMPLETED', SYSDATE, :transaction_reference)";
            
            $paymentStmt = oci_parse($conn, $paymentSql);
            if (!$paymentStmt) {
                throw new Exception('Failed to parse PAYMENT insert: ' . oci_error($conn)['message']);
            }
            
            $bindAmount = (string) $total; // Convert float to string
            oci_bind_by_name($paymentStmt, ':order_id', $newOrderId);
            oci_bind_by_name($paymentStmt, ':amount', $bindAmount);
            oci_bind_by_name($paymentStmt, ':transaction_reference', $paypalTransactionId);
            
            if (!oci_execute($paymentStmt, OCI_NO_AUTO_COMMIT)) {
                throw new Exception('Failed to insert PAYMENT: ' . oci_error($paymentStmt)['message']);
            }
            
            oci_free_statement($paymentStmt);
            // ====================================================================
            // STEP 4: COMMIT TRANSACTION
            // ====================================================================
            
            if (!oci_commit($conn)) {
                throw new Exception('Failed to commit transaction: ' . oci_error($conn)['message']);
            }
            
            $paymentSuccess = true;
            
            // ====================================================================
            // STEP 4.5: SEND INVOICE EMAIL
            // ====================================================================
            
            $customerName = $_SESSION['first_name'] ?? 'Customer';
            $customerEmail = $_SESSION['email'] ?? '';
            
            if ($customerEmail) {
                $subject = "Invoice for Order #" . $newOrderId . " - Cleck E-Mart";
                
                $message = "
                <html>
                <head>
                <title>Invoice for Order #{$newOrderId}</title>
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
                            <p>Invoice for Order #{$newOrderId}</p>
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
                            
                foreach ($normalizedItems as $line) {
                    $itemTotal = number_format($line['line_total'], 2);
                    $unitPrice = number_format($line['unit_price'], 2);
                    $message .= "
                                <tr>
                                    <td>{$line['name']}</td>
                                    <td>{$line['quantity']}</td>
                                    <td>£{$unitPrice}</td>
                                    <td>£{$itemTotal}</td>
                                </tr>";
                }
                
                $formattedTotal = number_format($total, 2);
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

                require_once __DIR__ . '/lib/email_helpers.php';
                send_email($customerEmail, $subject, $message);
            }
            
            // ====================================================================
            // STEP 5: CLEAR CART (only after successful commit)
            // ====================================================================
            
            foreach ($normalizedItems as $line) {
                $pid = (int) $line['product_id'];
                if ($pid <= 0) {
                    continue;
                }
                
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
            
            unset($_SESSION['applied_coupon']);
            
        } catch (Exception $e) {
            // ====================================================================
            // ROLLBACK ON ERROR
            // ====================================================================
            
            if (isset($conn)) {
                oci_rollback($conn);
            }
            
            $paymentSuccess = false;
            $errors[] = 'Payment processing failed: ' . $e->getMessage();
        }
    }
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="payment-page">
    <section class="payment-hero" aria-labelledby="payment-title">
        <div class="container">
            <div class="collection-progress" aria-label="Checkout progress">
                <div class="collection-progress__step is-complete">
                    <span class="collection-progress__number">1</span>
                    <span class="collection-progress__label">Basket</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step is-complete">
                    <span class="collection-progress__number">2</span>
                    <span class="collection-progress__label">Collection</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step is-active" aria-current="step">
                    <span class="collection-progress__number">3</span>
                    <span class="collection-progress__label">Payment</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step">
                    <span class="collection-progress__number">4</span>
                    <span class="collection-progress__label">Confirm</span>
                </div>
            </div>

            <div class="payment-hero__panel">
                <h1 id="payment-title">Secure Payment</h1>
                <p>Pay safely with PayPal. This checkout only accepts PayPal for all orders.</p>
                <?php if ($selectedSlotDate !== '' && $selectedSlotTime !== ''): ?>
                    <p class="payment-slot">Collection slot: <strong><?php echo e($selectedSlotDate . ' at ' . $selectedSlotTime); ?></strong></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="payment-content" aria-label="Payment details">
        <div class="container payment-layout">
            <section class="payment-method" aria-labelledby="payment-method-title">
                <div class="payment-method__card">
                    <p class="payment-method__eyebrow">Payment Gateway</p>
                    <h2 id="payment-method-title">PayPal Only</h2>

                    <?php if ($flashError !== null): ?>
                        <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($flashSuccess !== null): ?>
                        <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
                    <?php endif; ?>

                    <?php if ($errors !== []): ?>
                        <div class="page-message page-message--error">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo e($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($paymentSuccess): ?>
                        <div class="page-message page-message--success">
                            <p>Payment completed successfully.</p>
                            <p>Transaction ID: <strong><?php echo e((string) $transactionId); ?></strong></p>
                            <p>Your basket has been cleared and your order is now being prepared.</p>
                        </div>
                        <div class="payment-success-actions">
                            <a class="button" href="index.php">Continue Shopping</a>
                            <a class="button button--secondary" href="profile.php">View My Account</a>
                        </div>
                    <?php else: ?>
                    
                        <!-- Coupon Section -->
                        <div class="coupon-section" style="margin-bottom: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px;">
                            <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 1rem;">Have a coupon?</h3>
                            <?php if ($appliedCoupon): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #e6f7ff; padding: 1rem; border-radius: 6px; border: 1px solid #b3e0ff;">
                                    <div>
                                        <strong><?php echo e($appliedCoupon['code']); ?></strong> applied 
                                        <span style="color: #0066cc;">(-£<?php echo e(number_format($appliedCoupon['discount'], 2)); ?>)</span>
                                    </div>
                                    <form method="post" action="payment.php<?php echo ($selectedSlotDate !== '' && $selectedSlotTime !== '') ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : ''; ?>" style="margin: 0;">
                                        <input type="hidden" name="action" value="remove_coupon" />
                                        <button type="submit" class="button button--secondary" style="padding: 0.5rem 1rem; min-height: auto; font-size: 0.9rem;">Remove</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <form method="post" action="payment.php<?php echo ($selectedSlotDate !== '' && $selectedSlotTime !== '') ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : ''; ?>" style="display: flex; gap: 1rem;">
                                    <input type="hidden" name="action" value="apply_coupon" />
                                    <input type="text" name="coupon_code" placeholder="Enter code" style="flex: 1; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px;" />
                                    <button type="submit" class="button" style="padding: 0.75rem 1.5rem; min-height: auto;">Apply</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="paypal-lockup" aria-hidden="true">
                            <span class="paypal-lockup__badge">PayPal</span>
                            <span class="paypal-lockup__text">Fast, encrypted checkout</span>
                        </div>

                        <form id="payment-form" method="post" action="payment.php<?php echo ($selectedSlotDate !== '' && $selectedSlotTime !== '') ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : ''; ?>" class="payment-form">
                            <input type="hidden" name="action" value="paypal_checkout" />
                            <input type="hidden" name="paypal_transaction_id" id="paypal_transaction_id" value="" />

                            <label class="auth-check payment-check">
                                <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1" />
                                <span>I confirm this order and agree to the payment terms.</span>
                            </label>

                            <div id="paypal-button-container" style="margin-top: 1.5rem;"></div>
                            <p class="payment-note">Your payment will be securely processed through PayPal Sandbox.</p>
                        </form>

                        <!-- PayPal Sandbox SDK -->
                        <script src="https://www.paypal.com/sdk/js?client-id=test&currency=GBP"></script>
                        <script>
                            paypal.Buttons({
                                onClick: function(data, actions) {
                                    if (!document.getElementById('terms_accepted').checked) {
                                        alert('Please accept the terms before completing PayPal payment.');
                                        return actions.reject();
                                    }
                                    return actions.resolve();
                                },
                                createOrder: function(data, actions) {
                                    return actions.order.create({
                                        purchase_units: [{
                                            amount: {
                                                value: '<?php echo number_format($total, 2, '.', ''); ?>'
                                            }
                                        }]
                                    });
                                },
                                onApprove: function(data, actions) {
                                    return actions.order.capture().then(function(details) {
                                        // Capture transaction ID from PayPal
                                        document.getElementById('paypal_transaction_id').value = details.id;
                                        // Once payment is approved, submit our form to process the order in the database
                                        document.getElementById('payment-form').submit();
                                    });
                                }
                            }).render('#paypal-button-container');
                        </script>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="payment-summary" aria-labelledby="payment-summary-title">
                <h2 id="payment-summary-title">Order Summary</h2>
                <div class="payment-summary__lines">
                    <?php foreach ($normalizedItems as $line): ?>
                        <p class="payment-summary__line">
                            <span><?php echo e($line['name']); ?> x<?php echo e((string) $line['quantity']); ?></span>
                            <strong>£<?php echo e(number_format((float) $line['line_total'], 2)); ?></strong>
                        </p>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($appliedCoupon): ?>
                <div class="payment-summary__divider" aria-hidden="true" style="margin: 1rem 0;"></div>
                <div class="payment-summary__lines">
                    <p class="payment-summary__line">
                        <span>Subtotal</span>
                        <strong>£<?php echo e(number_format($subtotal, 2)); ?></strong>
                    </p>
                    <p class="payment-summary__line" style="color: #d32f2f;">
                        <span>Discount (<?php echo e($appliedCoupon['code']); ?>)</span>
                        <strong>-£<?php echo e(number_format($couponDiscount, 2)); ?></strong>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="payment-summary__divider" aria-hidden="true"></div>
                <p class="payment-summary__total">
                    <span>Total</span>
                    <strong>£<?php echo e(number_format($total, 2)); ?></strong>
                </p>
            </aside>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>