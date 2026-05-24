# Cleck E-Mart System Documentation

## Overview

This document provides a comprehensive overview of the Cleck E-Mart PHP application, detailing what each file does and the purpose of every function within those files.


### `about.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `admin-dashboard.php`
**Description:** No file description provided.

#### Functions:
- **`openOrderDetailsModal(orderId)`**
  - Handles the core logic and operations for openOrderDetailsModal
- **`closeOrderDetailsModal()`**
  - Handles the core logic and operations for closeOrderDetailsModal
- **`openAdminTab(event, tabId)`**
  - Handles the core logic and operations for openAdminTab
- **`openOrdersModal()`**
  - Handles the core logic and operations for openOrdersModal
- **`closeOrdersModal()`**
  - Handles the core logic and operations for closeOrdersModal
- **`filterOrders()`**
  - Handles the core logic and operations for filterOrders
- **`sortOrders()`**
  - Handles the core logic and operations for sortOrders
- **`connectSerial()`**
  - No description provided.
- **`disconnectSerial()`**
  - No description provided.
- **`setStatus(text, color)`**
  - Handles the core logic and operations for setStatus
- **`readLoop()`**
  - No description provided.
- **`handleCardScan(uid)`**
  - No description provided.

---

### `admin-impersonate.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `admin-profile.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `auth.php?action=revert_impersonate`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `auth.php`
**Description:** Shared footer keeps legal/quick links unified across pages.

*No functions defined in this file.*

---

### `cart.php`
**Description:** Handle both local DB (uppercase) and APEX API (lowercase) response formats

*No functions defined in this file.*

---

### `category.php`
**Description:** Shared footer closes document and keeps footer links consistent across pages.

*No functions defined in this file.*

---

### `checkout_process.php`
**Description:** Checkout Transaction Handler for Oracle OCI8 This script processes a complete checkout with transaction support: 1. Inserts a new order and captures the auto-generated order_id 2. Inserts order items from the cart 3. Inserts a payment record 4. Commits on success or rolls back on failure 5. Clears the cart after successful commit

#### Functions:
- **`processCheckoutTransaction($conn, $customer_id, $slot_id, $cart_items, $payment_method, $total_amount)`**
  - Checkout Transaction Handler for Oracle OCI8 This script processes a complete checkout with transaction support: 1. Inserts a new order and captures the auto-generated order_id 2. Inserts order items from the cart 3. Inserts a payment record 4. Commits on success or rolls back on failure 5. Clears the cart after successful commit / // Include your database connection require_once 'lib/bootstrap.php'; require_once 'lib/oci_db.php'; /** Process a complete checkout transaction
- **`clearUserCart($customer_id)`**
  - Helper function to clear a user's cart (session-based or database-based)
- **`getCartItems($customer_id)`**
  - Example usage in a checkout endpoint / if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_checkout'])) { // Get the database connection (from your bootstrap) $conn = getOracleConnection();  // Adjust to your connection function // Sanitize and validate inputs $customer_id = (int)($_POST['customer_id'] ?? 0); $slot_id = (int)($_POST['slot_id'] ?? 0); $payment_method = $_POST['payment_method'] ?? 'CARD'; // Get cart items (from session or database) $cart_items = getCartItems($customer_id);  // Implement based on your cart system // Calculate total $total_amount = array_reduce($cart_items, function($sum, $item) { return $sum + ($item['quantity'] * $item['unit_price']); }, 0); // Validate cart is not empty if (empty($cart_items)) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Cart is empty']); exit; } // Process the transaction $result = processCheckoutTransaction($conn, $customer_id, $slot_id, $cart_items, $payment_method, $total_amount); // Return JSON response header('Content-Type: application/json'); http_response_code($result['success'] ? 201 : 400); echo json_encode($result); oci_close($conn); exit; } /** Placeholder function to get cart items Implement this based on your cart storage method (session, database, etc.)
- **`getOracleConnection()`**
  - Placeholder function to get Oracle connection Replace with your actual connection function

---

### `collection.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `components/footer.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `components/header.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `components/verified_shops.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `contact.php`
**Description:** Reuses site-wide header/navigation to keep contact page in the same theme.

*No functions defined in this file.*

---

### `db_connect.php`
**Description:** db_connect.php

*No functions defined in this file.*

---

### `download-invoice.php`
**Description:** Download Invoice Script Generates a text-based invoice for a given order and forces a download.

*No functions defined in this file.*

---

### `forgot-password.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `index.php`
**Description:** Initialize session to manage user login state across requests

*No functions defined in this file.*

---

### `lib/apex_api.php`
**Description:** Oracle APEX API Integration Handles fetching product data from Oracle APEX REST endpoints

#### Functions:
- **`fetch_apex_products(int $cacheMinutes = 5)`**
  - Oracle APEX API Integration Handles fetching product data from Oracle APEX REST endpoints / require_once __DIR__ . '/offline_store.php'; require_once __DIR__ . '/product_images.php'; /** Fetch all products from Oracle APEX API with error handling and optional caching price, product_image, discount_percentage, shop_name, category_name, product_status
- **`fallback_apex_products_from_offline()`**
  - Build a featured product list from offline store when remote API is unavailable.
- **`normalize_apex_products(array $items)`**
  - Normalize product data from APEX API to consistent format
- **`format_product_price(array $product)`**
  - Format product price with discount handling
- **`get_apex_cache(string $key)`**
  - Get cached products from session
- **`set_apex_cache(string $key, array $data, int $minutes = 5)`**
  - Set cache for products
- **`clear_apex_cache()`**
  - Clear all APEX API caches

---

### `lib/apex_auth.php`
**Description:** Oracle APEX User Authentication Integration Handles login via Oracle APEX REST endpoints

#### Functions:
- **`apex_login_user(string $email, string $password)`**
  - Oracle APEX User Authentication Integration Handles login via Oracle APEX REST endpoints / /** Authenticate user via Oracle APEX API
- **`apex_auth_enabled()`**
  - Check if APEX authentication is enabled
- **`apex_register_user(string $email, string $firstName, string $lastName, string $password, string $role)`**
  - Register user via Oracle APEX API (optional - if you want to use APEX for signup too)

---

### `lib/apex_cart.php`
**Description:** Oracle APEX Shopping Cart Integration Handles cart operations via Oracle APEX REST endpoints

#### Functions:
- **`apex_get_cart_items(int $customerId)`**
  - Oracle APEX Shopping Cart Integration Handles cart operations via Oracle APEX REST endpoints / /** Fetch cart items for a customer from APEX API price, product_image, quantity, shop_name, subtotal, discount_percentage
- **`apex_update_cart_quantity(int $customerId, int $productId, int $quantity)`**
  - Update cart item quantity via APEX API
- **`apex_add_to_cart(int $customerId, int $productId, int $quantity)`**
  - Add product to cart via APEX API
- **`apex_cart_total(array $items)`**
  - Get cart total from items
- **`normalize_apex_cart_items(array $items)`**
  - Normalize cart items from APEX API response
- **`apex_cart_enabled()`**
  - Check if APEX cart integration is enabled

---

### `lib/auth_helpers.php`
**Description:** No file description provided.

#### Functions:
- **`require_login(array $allowedRoles = [])`**
  - Enforces access control by requiring the user to be logged in. Optionally restricts access to specific roles. Redirects to login or index page if conditions are not met.
- **`login_session(array $user)`**
  - Sets up session variables for a newly logged-in user. It also initializes customer-specific session data if applicable.

---

### `lib/bootstrap.php`
**Description:** No file description provided.

#### Functions:
- **`e($value)`**
  - Escapes HTML characters to prevent XSS attacks.
- **`redirect(string $path)`**
  - Redirects the user to a specific path and terminates the script.
- **`set_flash(string $key, string $message)`**
  - Sets a flash message in the session to be displayed later.
- **`get_flash(string $key)`**
  - Retrieves and unsets a flash message from the session.
- **`is_logged_in()`**
  - Checks if the current user is logged in.
- **`current_user_id()`**
  - Returns the current user's ID or null if not logged in.
- **`current_role()`**
  - Returns the role of the currently logged-in user.
- **`current_customer_id()`**
  - Returns the customer ID for the current session.

---

### `lib/cart_api.php`
**Description:** Cart AJAX API Endpoint Handles dynamic cart updates via AJAX requests

*No functions defined in this file.*

---

### `lib/cart_helpers.php`
**Description:** No file description provided.

#### Functions:
- **`ensure_active_cart(int $customerId)`**
  - Handles the core logic and operations for ensure_active_cart
- **`add_product_to_cart(int $customerId, int $productId, int $quantity)`**
  - Handles the core logic and operations for add_product_to_cart
- **`get_cart_items_for_customer(int $customerId)`**
  - Handles the core logic and operations for get_cart_items_for_customer
- **`update_cart_item_quantity(int $customerId, int $productId, int $quantity)`**
  - Handles the core logic and operations for update_cart_item_quantity
- **`cart_total(array $items)`**
  - Handles the core logic and operations for cart_total

---

### `lib/email_helpers.php`
**Description:** No file description provided.

#### Functions:
- **`send_email(string $toAddress, string $subject, string $htmlBody, string $altBody = '')`**
  - Helper function to send an email using PHPMailer and Gmail SMTP.

---

### `lib/oci_db.php`
**Description:** No file description provided.

#### Functions:
- **`db_driver()`**
  - Handles the core logic and operations for db_driver
- **`db_is_offline()`**
  - Handles the core logic and operations for db_is_offline
- **`db_connect()`**
  - Handles the core logic and operations for db_connect
- **`db_parse(string $sql)`**
  - Handles the core logic and operations for db_parse
- **`db_execute_statement($statement, array $binds = [])`**
  - Handles the core logic and operations for db_execute_statement
- **`db_fetch_all(string $sql, array $binds = [])`**
  - Handles the core logic and operations for db_fetch_all
- **`db_fetch_one(string $sql, array $binds = [])`**
  - Handles the core logic and operations for db_fetch_one
- **`db_execute(string $sql, array $binds = [])`**
  - Handles the core logic and operations for db_execute
- **`db_next_id(string $table, string $column)`**
  - Handles the core logic and operations for db_next_id
- **`db_begin()`**
  - Handles the core logic and operations for db_begin
- **`db_commit()`**
  - Handles the core logic and operations for db_commit
- **`db_rollback()`**
  - Handles the core logic and operations for db_rollback

---

### `lib/offline_store.php`
**Description:** No file description provided.

#### Functions:
- **`offline_data_file()`**
  - Handles the core logic and operations for offline_data_file
- **`offline_default_data()`**
  - Handles the core logic and operations for offline_default_data
- **`offline_load()`**
  - Handles the core logic and operations for offline_load
- **`offline_save(array $data)`**
  - Handles the core logic and operations for offline_save
- **`offline_next_id(array $rows, string $idField)`**
  - Handles the core logic and operations for offline_next_id
- **`offline_user_by_email(string $email)`**
  - Handles the core logic and operations for offline_user_by_email
- **`offline_user_by_id(int $userId)`**
  - Handles the core logic and operations for offline_user_by_id
- **`offline_create_account(string $firstName, string $lastName, string $email, string $passwordHash, string $role)`**
  - Handles the core logic and operations for offline_create_account
- **`offline_is_customer(int $userId)`**
  - Handles the core logic and operations for offline_is_customer
- **`offline_update_user(int $userId, string $firstName, string $lastName, string $email, ?string $phone)`**
  - Handles the core logic and operations for offline_update_user
- **`offline_email_taken_by_other(int $userId, string $email)`**
  - Handles the core logic and operations for offline_email_taken_by_other
- **`offline_update_password(int $userId, string $passwordHash)`**
  - Handles the core logic and operations for offline_update_password
- **`offline_get_category_name(?int $categoryId)`**
  - Handles the core logic and operations for offline_get_category_name
- **`offline_get_products(?int $categoryId = null)`**
  - Handles the core logic and operations for offline_get_products
- **`offline_get_product_detail(int $productId)`**
  - Handles the core logic and operations for offline_get_product_detail
- **`offline_ensure_active_cart(int $customerId)`**
  - Handles the core logic and operations for offline_ensure_active_cart
- **`offline_add_to_cart(int $customerId, int $productId, int $quantity)`**
  - Handles the core logic and operations for offline_add_to_cart
- **`offline_update_cart_quantity(int $customerId, int $productId, int $quantity)`**
  - Handles the core logic and operations for offline_update_cart_quantity
- **`offline_get_cart_items(int $customerId)`**
  - Handles the core logic and operations for offline_get_cart_items
- **`offline_get_orders_for_customer(int $customerId, int $limit = 5)`**
  - Handles the core logic and operations for offline_get_orders_for_customer
- **`offline_get_reviews_for_customer(int $customerId, int $limit = 5)`**
  - Handles the core logic and operations for offline_get_reviews_for_customer
- **`offline_count_orders(int $customerId)`**
  - Handles the core logic and operations for offline_count_orders
- **`offline_count_reviews(int $customerId)`**
  - Handles the core logic and operations for offline_count_reviews
- **`offline_count_saved(int $customerId)`**
  - Handles the core logic and operations for offline_count_saved
- **`offline_get_categories()`**
  - Handles the core logic and operations for offline_get_categories
- **`offline_get_trader_shop(int $traderId)`**
  - Handles the core logic and operations for offline_get_trader_shop
- **`offline_update_shop(int $shopId, string $shopName, string $shopDescription, ?string $shopLogo)`**
  - Handles the core logic and operations for offline_update_shop
- **`offline_get_trader_products(int $traderId)`**
  - Handles the core logic and operations for offline_get_trader_products
- **`offline_get_trader_dashboard(int $traderId)`**
  - Handles the core logic and operations for offline_get_trader_dashboard
- **`offline_create_product(int $shopId, array $payload)`**
  - Handles the core logic and operations for offline_create_product
- **`offline_user_to_upper(array $user)`**
  - Handles the core logic and operations for offline_user_to_upper
- **`offline_get_pending_products()`**
  - Handles the core logic and operations for offline_get_pending_products
- **`offline_update_product_status(int $productId, string $status)`**
  - Handles the core logic and operations for offline_update_product_status
- **`offline_get_pending_traders()`**
  - Handles the core logic and operations for offline_get_pending_traders
- **`offline_update_trader_status(int $traderId, string $status)`**
  - Handles the core logic and operations for offline_update_trader_status
- **`offline_get_trader_shops(int $traderId)`**
  - Handles the core logic and operations for offline_get_trader_shops
- **`offline_create_shop_for_trader(int $traderId, string $shopName, string $shopDesc, ?string $shopLogo)`**
  - Handles the core logic and operations for offline_create_shop_for_trader
- **`offline_get_pending_shops()`**
  - Handles the core logic and operations for offline_get_pending_shops
- **`offline_update_shop_status(int $shopId, string $status)`**
  - Handles the core logic and operations for offline_update_shop_status
- **`offline_get_pending_reviews_for_customer(int $customerId)`**
  - Handles the core logic and operations for offline_get_pending_reviews_for_customer
- **`offline_submit_review(int $customerId, int $productId, float $rating, string $comment)`**
  - Handles the core logic and operations for offline_submit_review
- **`offline_update_product(int $shopId, int $productId, array $payload)`**
  - Handles the core logic and operations for offline_update_product

---

### `lib/product_images.php`
**Description:** Product image helper Returns uploaded product image if available, otherwise returns a copyright-free placeholder (picsum.photos) seeded by id

#### Functions:
- **`default_product_image(int $productId = 0, ?string $uploadedFilename = null, int $size = 400)`**
  - Product image helper Returns uploaded product image if available, otherwise returns a copyright-free placeholder (picsum.photos) seeded by id

---

### `lib/rfid_api.php`
**Description:** RFID Collection Verification API Endpoint Handles RFID scan requests and order status updates from the admin dashboard.

*No functions defined in this file.*

---

### `lib/trader_helpers.php`
**Description:** No file description provided.

#### Functions:
- **`trader_role_guard()`**
  - Handles the core logic and operations for trader_role_guard
- **`trader_handle_product_image_upload(?array $fileInput)`**
  - Handles the core logic and operations for trader_handle_product_image_upload
- **`trader_verification_status(int $userId)`**
  - Handles the core logic and operations for trader_verification_status
- **`trader_is_verified(int $userId)`**
  - Handles the core logic and operations for trader_is_verified
- **`trader_shop_for_user(int $userId, ?int $shopId = null)`**
  - Handles the core logic and operations for trader_shop_for_user
- **`trader_categories()`**
  - Handles the core logic and operations for trader_categories
- **`trader_products_for_user(int $userId)`**
  - Handles the core logic and operations for trader_products_for_user
- **`trader_products_for_shop(int $shopId)`**
  - Handles the core logic and operations for trader_products_for_shop
- **`trader_dashboard_metrics(int $userId, ?int $shopId = null)`**
  - Handles the core logic and operations for trader_dashboard_metrics
- **`trader_create_product(int $userId, array $payload)`**
  - Handles the core logic and operations for trader_create_product
- **`trader_update_profile(int $userId, array $payload)`**
  - Handles the core logic and operations for trader_update_profile
- **`trader_update_discount(int $userId, int $productId, float $percentage, int $durationDays = 30)`**
  - Handles the core logic and operations for trader_update_discount
- **`trader_get_orders(int $userId, array $filters = [])`**
  - Handles the core logic and operations for trader_get_orders
- **`trader_get_order_details(int $userId, int $orderId, ?int $shopId = null)`**
  - Handles the core logic and operations for trader_get_order_details
- **`trader_update_order_status(int $userId, int $orderId, string $newStatus)`**
  - Handles the core logic and operations for trader_update_order_status
- **`trader_get_shops(int $userId)`**
  - Handles the core logic and operations for trader_get_shops
- **`trader_create_shop(int $userId, array $payload)`**
  - Handles the core logic and operations for trader_create_shop
- **`trader_update_shop(int $userId, int $shopId, array $payload)`**
  - Handles the core logic and operations for trader_update_shop
- **`trader_update_product(int $userId, int $productId, array $payload)`**
  - Handles the core logic and operations for trader_update_product

---

### `lib/wishlist_helpers.php`
**Description:** No file description provided.

#### Functions:
- **`ensure_wishlist(int $customerId)`**
  - Handles the core logic and operations for ensure_wishlist
- **`add_to_wishlist(int $customerId, int $productId)`**
  - Handles the core logic and operations for add_to_wishlist
- **`remove_from_wishlist(int $customerId, int $productId)`**
  - Handles the core logic and operations for remove_from_wishlist
- **`get_wishlist_items(int $customerId)`**
  - Handles the core logic and operations for get_wishlist_items
- **`is_in_wishlist(int $customerId, int $productId)`**
  - Handles the core logic and operations for is_in_wishlist

---

### `auth.php?action=logout`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `order-confirmation.php`
**Description:** Order Confirmation Page Displays after successful checkout with order details from Oracle

*No functions defined in this file.*

---

### `order-history.php`
**Description:** Order History Page Shows all orders for the logged-in customer from Oracle

*No functions defined in this file.*

---

### `payment.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `product-review.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `product.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `profile.php`
**Description:** Shared footer keeps legal/quick links unified across pages.

#### Functions:
- **`activateTab(name, push)`**
  - Handles the core logic and operations for activateTab

---

### `reset-password.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-add-product.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-dashboard.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-orders.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-profile.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-sales.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-shop-profile.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `trader-shops.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `verify-otp.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `wishlist.php`
**Description:** No file description provided.

*No functions defined in this file.*

---

### `wishlist_action.php`
**Description:** No file description provided.

*No functions defined in this file.*

---
