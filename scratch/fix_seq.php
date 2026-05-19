<?php
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/oci_db.php';
$conn = db_connect();

$stmt = oci_parse($conn, "DROP SEQUENCE seq_payment");
oci_execute($stmt);
oci_free_statement($stmt);

$stmt = oci_parse($conn, "CREATE SEQUENCE seq_payment START WITH 13117 INCREMENT BY 1 NOCACHE");
oci_execute($stmt);
oci_free_statement($stmt);

echo "Payment sequence recreated successfully.\n";
