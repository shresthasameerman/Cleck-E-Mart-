<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'scan';
$_GET['uid'] = 'F3FAA12A';
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'ADMIN';
require 'd:\Cleck-E-Mart-\lib\rfid_api.php';
