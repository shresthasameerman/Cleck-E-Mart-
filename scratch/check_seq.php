<?php
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/oci_db.php';
$conn = db_connect();
$stmt = oci_parse($conn, "SELECT MAX(payment_id) as m FROM PAYMENT");
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
echo "Max payment_id: " . $row['M'] . "\n";
oci_free_statement($stmt);

$stmt = oci_parse($conn, "SELECT seq_payment.NEXTVAL as v FROM dual");
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
echo "Seq payment val: " . $row['V'] . "\n";
oci_free_statement($stmt);
