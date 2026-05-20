<?php
require_once __DIR__ . '/lib/oci_db.php';
$conn = db_connect();
$stmt = oci_parse($conn, "SELECT column_name FROM user_tab_cols WHERE table_name = 'SHOP'");
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    echo $row['COLUMN_NAME'] . "\n";
}
