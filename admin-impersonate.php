<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth_helpers.php';

require_login(['ADMIN']);

if (!isset($_GET['trader_id'])) {
    set_flash('error', 'No trader specified.');
    redirect('admin-dashboard.php');
}

$traderId = (int) $_GET['trader_id'];

if (db_is_offline()) {
    $trader = null;
    $data = offline_load();
    foreach ($data['users'] as $u) {
        if ((int)$u['user_id'] === $traderId && strtoupper((string)$u['role']) === 'TRADER') {
            $trader = $u;
            break;
        }
    }
} else {
    $trader = db_fetch_one("SELECT * FROM \"USER\" WHERE user_id = :id AND role = 'TRADER'", ['id' => $traderId]);
}

if (!$trader) {
    set_flash('error', 'Trader not found.');
    redirect('admin-dashboard.php');
}

// Set up impersonation
$_SESSION['admin_id'] = $_SESSION['user_id'];
$_SESSION['admin_role'] = $_SESSION['role'];

// Switch to trader
$_SESSION['user_id'] = (int) ($trader['USER_ID'] ?? $trader['user_id']);
$_SESSION['first_name'] = (string) ($trader['FIRST_NAME'] ?? $trader['first_name']);
$_SESSION['last_name'] = (string) ($trader['LAST_NAME'] ?? $trader['last_name']);
$_SESSION['email'] = (string) ($trader['EMAIL'] ?? $trader['email']);
$_SESSION['role'] = 'TRADER';

set_flash('success', "You are now accessing the account of {$_SESSION['first_name']}.");
redirect('trader-profile.php');
