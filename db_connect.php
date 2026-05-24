<?php
// This is a simple script to establish a connection to the Oracle database.

// db_connect.php

// 1. Enter the Oracle Database credentials.
// Ensure these match the user credentials created during Oracle XE setup.
$db_user = "CLECK";
$db_pass = "Oracle123#Apex";

// 2. Define the connection string (Host, Port, and Pluggable Database Service Name).
// 'XEPDB1' is the default pluggable database in Oracle XE.
$db_host = "localhost:1521/XEPDB1";

// 3. Establish the connection using the native Oracle OCI8 extension.
// This creates a persistent or non-persistent connection depending on php.ini configuration.
$conn = oci_connect($db_user, $db_pass, $db_host);

// 4. Validate the connection status.
// If it fails, capture the specific OCI error and terminate the script to prevent further execution.
if (!$conn) {
    $e = oci_error();
    die("Database connection failed: " . htmlentities($e['message'], ENT_QUOTES));
} else {
    // Uncomment for debugging purposes if needed.
    // echo "🎉 Boom! PHP is successfully connected to the Cleck E-Mart Database!";
}
?>
