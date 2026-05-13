<?php
/**
 * Order History Page
 * Shows all orders for the logged-in customer from Oracle
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

// Get Oracle connection
$conn = db_connect();
if (!$conn) {
    $error = "Error: Could not connect to database. Check that Oracle is configured properly or use offline mode.";
} else {
    // Fetch all orders for this customer
    $sql = "SELECT o.order_id, o.order_status, o.order_date, p.amount_paid,
                   COUNT(oi.order_item_id) as item_count
            FROM \"ORDER\" o
            LEFT JOIN PAYMENT p ON o.order_id = p.order_id
            LEFT JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
            WHERE o.customer_id = :cust_id
            GROUP BY o.order_id, o.order_status, o.order_date, p.amount_paid
            ORDER BY o.order_date DESC";
    
    $stmt = oci_parse($conn, $sql);
    if (!$stmt) {
        $error = "Error: Failed to parse query.";
    } else {
        oci_bind_by_name($stmt, ':cust_id', $customer_id, -1, SQLT_INT);
        
        if (!oci_execute($stmt)) {
            $error = "Error: Failed to execute query.";
        } else {
            $orders = [];
            while ($row = oci_fetch_assoc($stmt)) {
                $orders[] = $row;
            }
        }
        
        oci_free_statement($stmt);
    }
    
    oci_close($conn);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order History - Cleck E-Mart</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
    <?php require_once 'components/header.php'; ?>
    
    <main id="main-content">
        <section class="order-history" aria-labelledby="history-title">
            <div class="container">
                <h1 id="history-title">Your Orders</h1>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (empty($orders)): ?>
                    <div class="order-history__empty">
                        <p>You haven't placed any orders yet.</p>
                        <a href="category.php" class="button button--primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="order-history__list">
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $order_date = $order['ORDER_DATE'] ? date('d M Y, H:i', strtotime($order['ORDER_DATE'])) : 'N/A';
                                $order_id_padded = str_pad($order['ORDER_ID'], 6, '0', STR_PAD_LEFT);
                            ?>
                            <article class="order-card">
                                <div class="order-card__header">
                                    <div>
                                        <h2 class="order-card__id">Order #<?php echo htmlspecialchars($order_id_padded); ?></h2>
                                        <p class="order-card__date"><?php echo htmlspecialchars($order_date); ?></p>
                                    </div>
                                    <span class="order-card__status order-card__status--<?php echo strtolower($order['ORDER_STATUS']); ?>">
                                        <?php echo htmlspecialchars($order['ORDER_STATUS']); ?>
                                    </span>
                                </div>
                                
                                <div class="order-card__body">
                                    <div class="order-card__meta">
                                        <span><?php echo (int)$order['ITEM_COUNT']; ?> item<?php echo $order['ITEM_COUNT'] != 1 ? 's' : ''; ?></span>
                                        <span>Total: £<?php echo number_format($order['AMOUNT_PAID'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="order-card__footer">
                                    <a href="order-confirmation.php?order_id=<?php echo (int)$order['ORDER_ID']; ?>" class="button button--small">
                                        View Details
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php require_once 'components/footer.php'; ?>
    
    <style>
        .order-history {
            padding: 2rem 0;
        }
        
        .order-history h1 {
            margin-bottom: 2rem;
        }
        
        .order-history__empty {
            text-align: center;
            padding: 3rem 1rem;
            background: rgba(217, 197, 178, 0.1);
            border-radius: 1rem;
        }
        
        .order-history__empty p {
            margin: 0 0 1rem;
            color: rgba(26, 26, 26, 0.7);
        }
        
        .order-history__list {
            display: grid;
            gap: 1rem;
        }
        
        .order-card {
            background: rgba(249, 248, 243, 0.6);
            border: 1px solid rgba(26, 26, 26, 0.08);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 26, 26, 0.08);
        }
        
        .order-card__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(26, 26, 26, 0.06);
        }
        
        .order-card__id {
            margin: 0 0 0.25rem;
            font-size: 1.1rem;
        }
        
        .order-card__date {
            margin: 0;
            font-size: 0.9rem;
            color: rgba(26, 26, 26, 0.6);
        }
        
        .order-card__status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .order-card__status--paid,
        .order-card__status--completed {
            background: rgba(106, 136, 97, 0.15);
            color: #6a8861;
        }
        
        .order-card__status--pending {
            background: rgba(210, 102, 72, 0.15);
            color: #d26648;
        }
        
        .order-card__body {
            margin-bottom: 1rem;
        }
        
        .order-card__meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.95rem;
            color: rgba(26, 26, 26, 0.7);
        }
        
        .order-card__footer {
            display: flex;
            gap: 0.75rem;
        }
        
        .button--small {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
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
            .order-history {
                padding: 1.5rem 0;
            }
            
            .order-card__header {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .order-card__meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>
