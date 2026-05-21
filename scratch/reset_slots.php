<?php
require_once __DIR__ . '/../lib/oci_db.php';
$conn = db_connect();

$sql = 'UPDATE COLLECTION_SLOT SET max_orders = 0';
$stmt = oci_parse($conn, $sql);
if (oci_execute($stmt)) {
    echo "Successfully reset max_orders to 0.\n";
} else {
    $e = oci_error($stmt);
    echo "Error: " . $e['message'] . "\n";
}
oci_free_statement($stmt);
oci_close($conn);
?>
