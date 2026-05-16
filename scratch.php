<?php
require_once __DIR__ . '/lib/oci_db.php';
$conn = db_connect();
$sql = "SELECT product_name, product_image FROM PRODUCT";
$products = db_fetch_all($sql);
foreach ($products as $p) {
    echo $p['PRODUCT_NAME'] . " | " . $p['PRODUCT_IMAGE'] . "\n";
}
