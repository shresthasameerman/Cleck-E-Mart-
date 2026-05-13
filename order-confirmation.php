<?php
/**
 * Order Confirmation Page
 * Displays after successful checkout with order details from Oracle
 */

session_start();

require_once 'lib/bootstrap.php';
require_once 'lib/oci_db.php';

// Get order_id from URL parameter
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

if (!$order_id) {
    echo "Error: No order ID provided.";
    exit;
}

// Get Oracle connection
$conn = db_connect();
if (!$conn) {
    echo "Error: Could not connect to database. Check that Oracle is configured properly or use offline mode.";
    exit;
}

// Fetch order details
$order_sql = "SELECT order_id, customer_id, slot_id, order_status, order_date 
              FROM \"ORDER\" 
              WHERE order_id = :order_id";

$order_stmt = oci_parse($conn, $order_sql);
oci_bind_by_name($order_stmt, ':order_id', $order_id, -1, SQLT_INT);
oci_execute($order_stmt);
$order = oci_fetch_assoc($order_stmt);
oci_free_statement($order_stmt);

if (!$order) {
    echo "Error: Order not found.";
    oci_close($conn);
    exit;
}

// Fetch order items
$items_sql = "SELECT oi.product_id, p.product_name, oi.quantity, oi.unit_price,
                     (oi.quantity * oi.unit_price) as line_total
              FROM ORDER_ITEM oi
              JOIN PRODUCT p ON oi.product_id = p.product_id
              WHERE oi.order_id = :order_id";

$items_stmt = oci_parse($conn, $items_sql);
oci_bind_by_name($items_stmt, ':order_id', $order_id, -1, SQLT_INT);
oci_execute($items_stmt);

$order_items = [];
$total = 0;
while ($row = oci_fetch_assoc($items_stmt)) {
    $order_items[] = $row;
    $total += $row['LINE_TOTAL'];
}
oci_free_statement($items_stmt);

// Fetch payment details
$payment_sql = "SELECT amount_paid, payment_method, payment_status, payment_date
                FROM PAYMENT 
                WHERE order_id = :order_id";

$payment_stmt = oci_parse($conn, $payment_sql);
oci_bind_by_name($payment_stmt, ':order_id', $order_id, -1, SQLT_INT);
oci_execute($payment_stmt);
$payment = oci_fetch_assoc($payment_stmt);
oci_free_statement($payment_stmt);

oci_close($conn);

// Format dates
$order_date = $order['ORDER_DATE'] ? date('d M Y, H:i', strtotime($order['ORDER_DATE'])) : 'N/A';
$payment_date = $payment && $payment['PAYMENT_DATE'] ? date('d M Y, H:i', strtotime($payment['PAYMENT_DATE'])) : 'N/A';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Confirmation - Cleck E-Mart</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <?php require_once 'components/header.php'; ?>
    
    <main id="main-content">
        <section class="confirmation" aria-labelledby="confirmation-title">
            <div class="container">
                <div class="confirmation__success">
                    <h1 id="confirmation-title">Order Confirmed! ✓</h1>
                    <p class="confirmation__subtitle">Thank you for your order. Your purchase has been successfully processed.</p>
                </div>
                
                <!-- Order Number & Date -->
                <div class="confirmation__card">
                    <h2>Order Details</h2>
                    <div class="confirmation__detail">
                        <span class="confirmation__label">Order Number:</span>
                        <span class="confirmation__value">#<?php echo str_pad($order['ORDER_ID'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="confirmation__detail">
                        <span class="confirmation__label">Order Date:</span>
                        <span class="confirmation__value"><?php echo htmlspecialchars($order_date); ?></span>
                    </div>
                    <div class="confirmation__detail">
                        <span class="confirmation__label">Status:</span>
                        <span class="confirmation__value confirmation__status--<?php echo strtolower($order['ORDER_STATUS']); ?>">
                            <?php echo htmlspecialchars($order['ORDER_STATUS']); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="confirmation__card">
                    <h2>Items Ordered</h2>
                    <table class="confirmation__table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['PRODUCT_NAME']); ?></td>
                                    <td><?php echo (int)$item['QUANTITY']; ?></td>
                                    <td>£<?php echo number_format($item['UNIT_PRICE'], 2); ?></td>
                                    <td>£<?php echo number_format($item['LINE_TOTAL'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="confirmation__total">
                        <strong>Order Total: £<?php echo number_format($total, 2); ?></strong>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <?php if ($payment): ?>
                    <div class="confirmation__card">
                        <h2>Payment Information</h2>
                        <div class="confirmation__detail">
                            <span class="confirmation__label">Amount Paid:</span>
                            <span class="confirmation__value">£<?php echo number_format($payment['AMOUNT_PAID'], 2); ?></span>
                        </div>
                        <div class="confirmation__detail">
                            <span class="confirmation__label">Payment Method:</span>
                            <span class="confirmation__value"><?php echo htmlspecialchars($payment['PAYMENT_METHOD']); ?></span>
                        </div>
                        <div class="confirmation__detail">
                            <span class="confirmation__label">Payment Status:</span>
                            <span class="confirmation__value confirmation__status--<?php echo strtolower($payment['PAYMENT_STATUS']); ?>">
                                <?php echo htmlspecialchars($payment['PAYMENT_STATUS']); ?>
                            </span>
                        </div>
                        <div class="confirmation__detail">
                            <span class="confirmation__label">Payment Date:</span>
                            <span class="confirmation__value"><?php echo htmlspecialchars($payment_date); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Next Steps -->
                <div class="confirmation__card confirmation__next-steps">
                    <h2>What's Next?</h2>
                    <ul>
                        <li>Your order will be prepared by the trader.</li>
                        <li>You'll receive a notification when it's ready for collection.</li>
                        <li>Collect your order at the selected time slot.</li>
                    </ul>
                </div>
                
                <!-- Action Buttons -->
                <div class="confirmation__actions">
                    <a href="order-history.php" class="button button--primary">View All Orders</a>
                    <a href="category.php" class="button button--secondary">Continue Shopping</a>
                </div>
            </div>
        </section>
    </main>
    
    <?php require_once 'components/footer.php'; ?>
    
    <style>
        .confirmation {
            padding: 3rem 0;
        }
        
        .confirmation__success {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(106, 136, 97, 0.1), rgba(217, 197, 178, 0.1));
            border-radius: 1rem;
        }
        
        .confirmation__success h1 {
            color: #6a8861;
            margin: 0 0 0.5rem;
        }
        
        .confirmation__subtitle {
            margin: 0;
            color: rgba(26, 26, 26, 0.7);
        }
        
        .confirmation__card {
            background: rgba(249, 248, 243, 0.6);
            border: 1px solid rgba(26, 26, 26, 0.08);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .confirmation__card h2 {
            margin: 0 0 1rem;
            font-size: 1.25rem;
        }
        
        .confirmation__detail {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(26, 26, 26, 0.06);
        }
        
        .confirmation__detail:last-child {
            border-bottom: 0;
        }
        
        .confirmation__label {
            font-weight: 700;
            color: rgba(26, 26, 26, 0.8);
        }
        
        .confirmation__value {
            color: #1a1a1a;
        }
        
        .confirmation__status--paid,
        .confirmation__status--completed {
            color: #6a8861;
            font-weight: 700;
        }
        
        .confirmation__status--pending {
            color: #d26648;
            font-weight: 700;
        }
        
        .confirmation__table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .confirmation__table th,
        .confirmation__table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(26, 26, 26, 0.1);
        }
        
        .confirmation__table thead {
            background: rgba(26, 26, 26, 0.04);
            font-weight: 700;
        }
        
        .confirmation__total {
            text-align: right;
            padding-top: 1rem;
            font-size: 1.15rem;
        }
        
        .confirmation__next-steps ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .confirmation__next-steps li {
            margin-bottom: 0.5rem;
        }
        
        .confirmation__actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .confirmation {
                padding: 1.5rem 0;
            }
            
            .confirmation__detail {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .confirmation__actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
