<?php
// This file provides an API endpoint for processing physical RFID scans during order collection.

/**
 * RFID Collection Verification API Endpoint
 * Handles RFID scan requests and order status updates from the admin dashboard.
 */

require_once __DIR__ . '/auth_helpers.php';
require_once __DIR__ . '/oci_db.php';

header('Content-Type: application/json');

// Verify user is logged in and is an admin
if (!is_logged_in() || current_role() !== 'ADMIN') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Admin access required.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'scan':
            // Logic to process an RFID scan and identify the corresponding customer
            $uid = strtoupper(trim($_GET['uid'] ?? ''));
            if ($uid === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing UID.']);
                exit;
            }

            // Hardcode mapping 
            $customerId = null;
            if ($uid === 'F3DA841A' || $uid === 'B46C4C6') {
                $customerId = 1001; // Map these specific RFID tags to customer 1001
            } else {
                // If we had a database column `rfid_uid` on CUSTOMER, we would query it here:
                // $row = db_fetch_one("SELECT customer_id FROM CUSTOMER WHERE rfid_uid = :uid", ['uid' => $uid]);
                // $customerId = $row ? $row['CUSTOMER_ID'] : null;
                
                echo json_encode(['status' => 'error', 'message' => 'Unknown RFID tag (UID: ' . $uid . ')']);
                exit;
            }

            // Logic to fetch detailed customer information from the database
            $customer = db_fetch_one(
                "SELECT u.first_name, u.last_name, u.email, u.phone_number 
                 FROM \"USER\" u 
                 JOIN CUSTOMER c ON u.user_id = c.customer_id 
                 WHERE c.customer_id = :id",
                ['id' => $customerId]
            );

            if (!$customer) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Customer not found.']);
                exit;
            }

            // Logic to retrieve all active orders (PAID, READY, or PENDING) for the customer
            $orders = db_fetch_all(
                "SELECT o.order_id, o.order_date, o.order_status, 
                        (SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) FROM ORDER_ITEM oi WHERE oi.order_id = o.order_id) as total_amount
                 FROM \"ORDER\" o 
                 WHERE o.customer_id = :id AND o.order_status IN ('PAID', 'READY', 'PENDING')
                 ORDER BY o.order_date DESC",
                ['id' => $customerId]
            );

            // Logic to fetch and group individual order items for each active order
            $itemsByOrder = [];
            if (!empty($orders)) {
                $orderIds = array_map(function($o) { return $o['ORDER_ID'] ?? $o['order_id']; }, $orders);
                $inClauseArr = [];
                $binds = [];
                foreach ($orderIds as $i => $oid) {
                    $paramName = 'oid' . $i;
                    $inClauseArr[] = ':' . $paramName;
                    $binds[$paramName] = $oid;
                }
                $inClause = implode(',', $inClauseArr);
                
                $items = db_fetch_all(
                    "SELECT oi.order_id, p.product_name, oi.quantity, oi.unit_price
                     FROM ORDER_ITEM oi
                     JOIN PRODUCT p ON oi.product_id = p.product_id
                     WHERE oi.order_id IN ($inClause)",
                    $binds
                );
                
                foreach ($items as $item) {
                    $oid = $item['ORDER_ID'] ?? $item['order_id'];
                    $itemsByOrder[$oid][] = $item;
                }
            }

            // Logic to attach the retrieved items to their respective orders
            foreach ($orders as &$order) {
                $oid = $order['ORDER_ID'] ?? $order['order_id'];
                $order['items'] = $itemsByOrder[$oid] ?? [];
            }
            unset($order);

            echo json_encode([
                'status' => 'success',
                'customer' => [
                    'id' => $customerId,
                    'name' => ($customer['FIRST_NAME'] ?? $customer['first_name']) . ' ' . ($customer['LAST_NAME'] ?? $customer['last_name']),
                    'email' => $customer['EMAIL'] ?? $customer['email'],
                    'phone' => $customer['PHONE_NUMBER'] ?? $customer['phone_number']
                ],
                'orders' => $orders
            ]);
            break;

        case 'mark_collected':
            // Logic to update an order's status to COLLECTED after a successful pickup
            $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            if (!$orderId) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
                exit;
            }

            // Update order status to COLLECTED
            db_execute(
                "UPDATE \"ORDER\" SET order_status = 'COLLECTED' WHERE order_id = :id AND order_status IN ('PAID', 'READY', 'PENDING')",
                ['id' => $orderId]
            );

            echo json_encode(['status' => 'success', 'message' => 'Order marked as collected.']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (Throwable $exception) {
    error_log('RFID API error: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $exception->getMessage()]);
}
