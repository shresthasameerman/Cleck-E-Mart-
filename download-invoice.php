<?php
/**
 * Download Invoice Script
 * Generates a text-based invoice for a given order and forces a download.
 */

session_start();

// Verify user is logged in
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['user_id'])) {
    header('Location: /auth.php?mode=login');
    exit;
}

require_once 'lib/bootstrap.php';
require_once 'lib/oci_db.php';

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$customer_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'];

if ($order_id <= 0) {
    die("Invalid Order ID.");
}

$conn = db_connect();
if (!$conn) {
    die("Database connection failed.");
}

// Check if order belongs to customer
$sql = "SELECT o.order_id, o.order_date, o.order_status, p.amount_paid, p.payment_method
        FROM \"ORDER\" o
        LEFT JOIN PAYMENT p ON o.order_id = p.order_id
        WHERE o.order_id = :order_id AND o.customer_id = :cust_id";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':order_id', $order_id, -1, SQLT_INT);
oci_bind_by_name($stmt, ':cust_id', $customer_id, -1, SQLT_INT);
oci_execute($stmt);

$order = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$order) {
    die("Order not found or you do not have permission to view it.");
}

// Fetch items
$sql_items = "SELECT oi.quantity, oi.unit_price, pr.product_name 
              FROM ORDER_ITEM oi
              JOIN PRODUCT pr ON oi.product_id = pr.product_id
              WHERE oi.order_id = :order_id";

$stmt_items = oci_parse($conn, $sql_items);
oci_bind_by_name($stmt_items, ':order_id', $order_id, -1, SQLT_INT);
oci_execute($stmt_items);

$items = [];
$total_calculated = 0;
while ($row = oci_fetch_assoc($stmt_items)) {
    $items[] = $row;
    $total_calculated += ($row['QUANTITY'] * $row['UNIT_PRICE']);
}
oci_free_statement($stmt_items);

$customerName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$orderDate = $order['ORDER_DATE'] ? date('j F Y, H:i', strtotime($order['ORDER_DATE'])) : 'N/A';
$amountPaid = $order['AMOUNT_PAID'] ? number_format($order['AMOUNT_PAID'], 2) : number_format($total_calculated, 2);
$orderStatus = $order['ORDER_STATUS'];

// Generate text content
$content = "=================================================\r\n";
$content .= "                 CLECK E-MART\r\n";
$content .= "                   INVOICE\r\n";
$content .= "=================================================\r\n\r\n";
$content .= "Order ID     : EM-" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . "\r\n";
$content .= "Order Date   : " . $orderDate . "\r\n";
$content .= "Customer     : " . $customerName . "\r\n";
$content .= "Status       : " . $orderStatus . "\r\n\r\n";
$content .= "-------------------------------------------------\r\n";
$content .= str_pad("Item", 25) . str_pad("Qty", 8) . str_pad("Price", 8) . "Total\r\n";
$content .= "-------------------------------------------------\r\n";

foreach ($items as $item) {
    $name = substr($item['PRODUCT_NAME'], 0, 23);
    $qty = $item['QUANTITY'];
    $price = number_format($item['UNIT_PRICE'], 2);
    $lineTotal = number_format($item['QUANTITY'] * $item['UNIT_PRICE'], 2);
    
    $content .= str_pad($name, 25) . str_pad($qty, 8) . str_pad("£".$price, 8) . "£".$lineTotal . "\r\n";
}

$content .= "-------------------------------------------------\r\n";
$content .= str_pad("TOTAL AMOUNT PAID:", 41) . "£" . $amountPaid . "\r\n";
$content .= "=================================================\r\n";
$content .= "      Thank you for shopping with us!\r\n";
$content .= "=================================================\r\n";

// Force Download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="invoice_EM-' . $order_id . '.txt"');
header('Content-Length: ' . strlen($content));

echo $content;
oci_close($conn);
exit;
