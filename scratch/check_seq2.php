<?php
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/oci_db.php';
$conn = db_connect();

$stmt = oci_parse($conn, "SELECT MAX(order_id) as m FROM \"ORDER\"");
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
echo "Max order_id: " . $row['M'] . "\n";
oci_free_statement($stmt);

$stmt = oci_parse($conn, "SELECT seq_order.NEXTVAL as v FROM dual");
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
echo "Seq order val: " . $row['V'] . "\n";
oci_free_statement($stmt);
