<?php
require_once __DIR__ . '/lib/oci_db.php';
$conn = db_connect();
$sql = "ALTER TABLE ORDER_ITEM ADD item_status VARCHAR2(50) DEFAULT 'PENDING'";
$stmt = oci_parse($conn, $sql);
if (oci_execute($stmt)) {
    echo "Column added successfully\n";
} else {
    $e = oci_error($stmt);
    echo "Error: " . $e['message'] . "\n";
}
$sql = "UPDATE ORDER_ITEM SET item_status = 'PAID' WHERE order_id IN (SELECT order_id FROM PAYMENT WHERE payment_status = 'PAID')";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
oci_commit($conn);
