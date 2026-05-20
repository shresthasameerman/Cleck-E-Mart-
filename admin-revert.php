<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth_helpers.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
}

// Restore admin session
$_SESSION['user_id'] = $_SESSION['admin_id'];
$_SESSION['role'] = $_SESSION['admin_role'];

// Fetch admin details again to refresh session state
if (!db_is_offline()) {
    $admin = db_fetch_one("SELECT * FROM \"USER\" WHERE user_id = :id", ['id' => $_SESSION['admin_id']]);
    if ($admin) {
        $_SESSION['first_name'] = $admin['FIRST_NAME'];
        $_SESSION['last_name'] = $admin['LAST_NAME'];
        $_SESSION['email'] = $admin['EMAIL'];
    }
} else {
    $data = offline_load();
    foreach ($data['users'] as $u) {
        if ((int)$u['user_id'] === $_SESSION['admin_id']) {
            $_SESSION['first_name'] = $u['first_name'];
            $_SESSION['last_name'] = $u['last_name'];
            $_SESSION['email'] = $u['email'];
            break;
        }
    }
}

// Clear impersonation data
unset($_SESSION['admin_id']);
unset($_SESSION['admin_role']);

set_flash('success', 'Returned to Admin session.');
redirect('admin-dashboard.php');
