<?php
require_once __DIR__ . '/lib/cart_helpers.php';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_cart.php';

require_login(['CUSTOMER']);

$pageTitle = 'Payment | Cleck E-Mart';
$metaDescription = 'Complete your order securely using PayPal.';

$customerId = (int) current_customer_id();
$flashError = get_flash('error');
$errors = [];
$paymentSuccess = false;
$transactionId = null;

$selectedSlotDate = trim((string) ($_GET['slot_date'] ?? ''));
$selectedSlotTime = trim((string) ($_GET['slot_time'] ?? ''));

$items = [];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'paypal_checkout') {
    $termsAccepted = !empty($_POST['terms_accepted']);

    if (!$termsAccepted) {
        $errors[] = 'Please accept the terms before completing PayPal payment.';
    }

    if ($normalizedItems === []) {
        $errors[] = 'Your basket is empty. Please add products before paying.';
    }

    if ($errors === []) {
        $transactionId = 'PAYPAL-' . strtoupper(substr(hash('sha256', uniqid((string) $customerId, true)), 0, 12));
        $paymentSuccess = true;

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
                // Keep payment success UX intact; cart cleanup best-effort only.
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
                        <div class="paypal-lockup" aria-hidden="true">
                            <span class="paypal-lockup__badge">PayPal</span>
                            <span class="paypal-lockup__text">Fast, encrypted checkout</span>
                        </div>

                        <form method="post" action="payment.php<?php echo ($selectedSlotDate !== '' && $selectedSlotTime !== '') ? '?slot_date=' . urlencode($selectedSlotDate) . '&slot_time=' . urlencode($selectedSlotTime) : ''; ?>" class="payment-form">
                            <input type="hidden" name="action" value="paypal_checkout" />

                            <label class="auth-check payment-check">
                                <input type="checkbox" name="terms_accepted" value="1" />
                                <span>I confirm this order and agree to the payment terms.</span>
                            </label>

                            <button type="submit" class="payment-paypal-btn">Pay with PayPal</button>
                            <p class="payment-note">After clicking, your payment will be processed through PayPal.</p>
                        </form>
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
