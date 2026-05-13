<?php
/**
 * Example Checkout Page Integration
 * Shows how to use checkout_process.php in your collection/checkout flow
 * 
 * This is a practical example of how to wire everything together.
 */

session_start();

// Verify user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: /auth.php?mode=login');
    exit;
}

require_once 'lib/bootstrap.php';
require_once 'lib/oci_db.php';

$customer_id = $_SESSION['customer_id'];
$selected_slot_id = $_SESSION['selected_slot_id'] ?? null;
$cart_items = $_SESSION['cart'] ?? [];

// Validate that a slot is selected
if (!$selected_slot_id) {
    $error_message = "Please select a collection slot before checkout.";
}

// Calculate order total
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['quantity'] * $item['unit_price'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - Cleck E-Mart</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <?php require_once 'components/header.php'; ?>
    
    <main id="main-content">
        <section class="checkout" aria-labelledby="checkout-title">
            <div class="container">
                <h1 id="checkout-title">Review & Complete Your Order</h1>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Order Summary -->
                <div class="checkout__summary">
                    <h2>Order Summary</h2>
                    
                    <table class="checkout__items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>£<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>£<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="checkout__total">
                        <strong>Order Total: £<?php echo number_format($total_amount, 2); ?></strong>
                    </div>
                </div>
                
                <!-- Checkout Form -->
                <form method="POST" action="checkout_process.php" id="checkout-form" class="checkout__form">
                    
                    <!-- Hidden inputs for transaction -->
                    <input type="hidden" name="process_checkout" value="1" />
                    <input type="hidden" name="customer_id" value="<?php echo (int)$customer_id; ?>" />
                    <input type="hidden" name="slot_id" value="<?php echo (int)$selected_slot_id; ?>" />
                    
                    <!-- Payment Method Selection -->
                    <fieldset class="checkout__fieldset">
                        <legend>Payment Method</legend>
                        
                        <div class="checkout__radio-group">
                            <label class="checkout__radio-label">
                                <input type="radio" name="payment_method" value="CARD" checked />
                                <span>Debit/Credit Card</span>
                            </label>
                            
                            <label class="checkout__radio-label">
                                <input type="radio" name="payment_method" value="CASH" />
                                <span>Pay on Collection</span>
                            </label>
                        </div>
                    </fieldset>
                    
                    <!-- Terms & Conditions -->
                    <fieldset class="checkout__fieldset">
                        <label class="checkout__checkbox-label">
                            <input type="checkbox" name="agree_terms" required />
                            <span>I agree to the terms and conditions</span>
                        </label>
                    </fieldset>
                    
                    <!-- Action Buttons -->
                    <div class="checkout__actions">
                        <a href="collection.php" class="button button--secondary">Back to Slot Selection</a>
                        <button type="submit" class="button button--primary" id="submit-checkout">
                            Complete Order
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <?php require_once 'components/footer.php'; ?>
    
    <script>
        /**
         * Handle checkout form submission with transaction feedback
         */
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submit-checkout');
            const originalText = submitBtn.textContent;
            
            try {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                const formData = new FormData(e.target);
                
                // Send checkout request to transaction handler
                const response = await fetch('checkout_process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Success: Show confirmation and redirect
                    alert(`Order #${result.order_id} created successfully!\n\nYou will be redirected to the confirmation page.`);
                    
                    // Redirect to order confirmation page
                    window.location.href = `order-confirmation.php?order_id=${result.order_id}`;
                } else {
                    // Failure: Show error message
                    alert(`Checkout failed:\n\n${result.message}\n\nPlease try again or contact support.`);
                    
                    // Reset form state
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                // Network or other error
                alert(`An error occurred:\n\n${error.message}\n\nPlease try again.`);
                
                // Reset form state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    </script>
    
    <style>
        .checkout {
            padding: 2rem 0;
        }
        
        .checkout__summary {
            background: rgba(217, 197, 178, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .checkout__items {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .checkout__items th,
        .checkout__items td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(26, 26, 26, 0.1);
        }
        
        .checkout__items thead {
            background: rgba(26, 26, 26, 0.04);
            font-weight: 700;
        }
        
        .checkout__total {
            text-align: right;
            padding: 1rem 0;
            font-size: 1.25rem;
        }
        
        .checkout__form {
            max-width: 40rem;
            background: rgba(249, 248, 243, 0.5);
            border-radius: 1rem;
            padding: 2rem;
        }
        
        .checkout__fieldset {
            margin-bottom: 1.5rem;
            border: 0;
            padding: 0;
        }
        
        .checkout__fieldset legend {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .checkout__radio-group {
            display: grid;
            gap: 0.75rem;
        }
        
        .checkout__radio-label,
        .checkout__checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }
        
        .checkout__radio-label:hover,
        .checkout__checkbox-label:hover {
            background-color: rgba(26, 26, 26, 0.06);
        }
        
        .checkout__radio-label input,
        .checkout__checkbox-label input {
            cursor: pointer;
        }
        
        .checkout__actions {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        @media (max-width: 768px) {
            .checkout__actions {
                flex-direction: column;
            }
            
            .checkout__form {
                padding: 1rem;
            }
        }
    </style>
</body>
</html>
