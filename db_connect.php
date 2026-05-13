<?php
// db_connect.php

// 1. Enter the exact same Username and Password you just used in the VSCode extension
$db_user = "ADMIN"; 
$db_pass = "Oracle123#Apex";

// 2. The host string (localhost, port 1521, and your service name)
// Change 'XEPDB1' to 'FREEPDB1' or 'XE' if you used a different service name earlier.
$db_host = "localhost:1521/XEPDB1"; 

// 3. Establish the connection using the native Oracle OCI8 function
$conn = oci_connect($db_user, $db_pass, $db_host);

// 4. Check if it worked!
if (!$conn) {
    $e = oci_error();
    die("Database connection failed: " . htmlentities($e['message'], ENT_QUOTES));
} else {
    echo "🎉 Boom! PHP is successfully connected to the Cleck E-Mart Database!";
}
?>
