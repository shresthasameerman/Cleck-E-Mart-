<?php
// These helper functions manage user sessions, login state, and role-based access control.

require_once __DIR__ . '/oci_db.php';

/**
 * Enforces access control by requiring the user to be logged in.
 * Optionally restricts access to specific roles.
 * Redirects to login or index page if conditions are not met.
 */
function require_login(array $allowedRoles = []): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please login to continue.');
        redirect('auth.php?mode=login');
    }

    if ($allowedRoles === []) {
        return;
    }

    $role = current_role();
    $normalizedRoles = array_map(static fn($value) => strtoupper((string) $value), $allowedRoles);

    if (!in_array((string) $role, $normalizedRoles, true)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect('index.php');
    }
}

/**
 * Sets up session variables for a newly logged-in user.
 * It also initializes customer-specific session data if applicable.
 */
function login_session(array $user): void
{
    $_SESSION['user_id'] = (int) $user['USER_ID'];
    $_SESSION['first_name'] = (string) $user['FIRST_NAME'];
    $_SESSION['last_name'] = (string) $user['LAST_NAME'];
    $_SESSION['email'] = (string) $user['EMAIL'];
    $_SESSION['role'] = strtoupper((string) $user['ROLE']);

    $role = $_SESSION['role'];
    if ($role === 'CUSTOMER') {
        if (db_is_offline()) {
            $_SESSION['customer_id'] = offline_is_customer((int) $user['USER_ID']) ? (int) $user['USER_ID'] : null;
        } else {
            $customer = db_fetch_one('SELECT customer_id FROM CUSTOMER WHERE customer_id = :customer_id', [
                'customer_id' => (int) $user['USER_ID'],
            ]);
            $_SESSION['customer_id'] = $customer ? (int) $customer['CUSTOMER_ID'] : (int) $user['USER_ID'];
        }
    } else {
        unset($_SESSION['customer_id']);
    }
}
