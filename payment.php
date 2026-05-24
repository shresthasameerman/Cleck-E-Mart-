<?php
// This file handles the checkout process, integrating with PayPal or APEX to process payment and finalize the order.

require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';

require_login(['CUSTOMER']);

$pageTitle = 'Payment | Cleck E-Mart';
$metaDescription = 'Complete your order securely using PayPal.';

$customerId = current_customer_id();
if ($customerId === null) {
    // In this schema CUSTOMER.customer_id strictly mirrors USER.user_id.
    // If a generic user reaches this point, gracefully fallback to their user ID.
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
    // First, try loading the customer's cart via the APEX API.
    // This allows syncing carts across multiple platforms (e.g., mobile app and web).
    if (apex_cart_enabled()) {
        try {
            $items = apex_get_cart_items($customerId);
        } catch (Throwable $exception) {
            // If APEX is unreachable, fallback to the local DB implementation.
            $items = get_cart_items_for_customer($customerId);
        }
    } else {
        // Pure local fetch path.
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

require_once __DIR__ . '/lib/payment_helpers.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectUrl = 'payment.php' . ($selectedSlotDate !== '' && $selectedSlotTime !== '' ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : '');
    
    if ($action === 'apply_coupon') {
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $result = validate_coupon($couponCode, $subtotal);
        
        if ($result['success']) {
            $_SESSION['applied_coupon'] = $result['coupon'];
            set_flash('success', $result['message']);
        } else {
            set_flash('error', $result['message']);
        }
        redirect($redirectUrl);
        
    } elseif ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        set_flash('success', 'Coupon removed.');
        redirect($redirectUrl);
        
    } elseif ($action === 'paypal_checkout') {
        if (empty($_POST['terms_accepted'])) {
            $errors[] = 'Please accept the terms before completing PayPal payment.';
        }
        if ($normalizedItems === []) {
            $errors[] = 'Your basket is empty. Please add products before paying.';
        }
        if ($customerId === null || (int) $customerId <= 0) {
            $errors[] = 'Your customer account is not linked correctly. Please sign out and sign in again.';
        }

        if ($errors === []) {
            $paypalTransactionId = trim($_POST['paypal_transaction_id'] ?? '');
            $customerName = $_SESSION['first_name'] ?? 'Customer';
            $customerEmail = $_SESSION['email'] ?? '';
            
            // Call the encapsulated transaction function
            $result = process_paypal_order(
                $customerId,
                $selectedSlotDate,
                $selectedSlotTime,
                $normalizedItems,
                $total,
                $paypalTransactionId,
                $appliedCoupon,
                $customerName,
                $customerEmail
            );
            
            if ($result['success']) {
                $paymentSuccess = true;
                $transactionId = $result['transaction_id'];
                unset($_SESSION['applied_coupon']);
            } else {
                $paymentSuccess = false;
                $errors[] = 'Payment processing failed: ' . $result['error'];
            }
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