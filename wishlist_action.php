<?php
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/wishlist_helpers.php';

require_login(['CUSTOMER']);
$customerId = (int) current_customer_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $returnUrl = $_POST['return_url'] ?? 'wishlist.php';

    if ($productId > 0) {
        try {
            if ($action === 'add') {
                add_to_wishlist($customerId, $productId);
                set_flash('success', 'Item added to your wishlist.');
            } elseif ($action === 'remove') {
                remove_from_wishlist($customerId, $productId);
                set_flash('success', 'Item removed from your wishlist.');
            }
        } catch (Throwable $e) {
            set_flash('error', $e->getMessage());
        }
    }
    redirect($returnUrl);
}
redirect('index.php');
