<?php
// These helper functions contain the business logic for admin tasks, like fetching platform stats and verifying users.

/**
 * Admin Helper Functions
 * Extracts complex logic and database queries from admin-dashboard.php.
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/email_helpers.php';

/**
 * Handles product approval/rejection.
 */
function handle_admin_product_action(string $action, int $productId): void {
    if ($action === 'approve' && $productId > 0) {
        if (!db_is_offline()) {
            db_execute("UPDATE PRODUCT SET product_verification_status = 'APPROVED' WHERE product_id = :id", ['id' => $productId]);
        } else {
            offline_update_product_status($productId, 'APPROVED');
        }
        set_flash('success', 'Product approved successfully.');
    } elseif ($action === 'reject' && $productId > 0) {
        if (!db_is_offline()) {
            db_execute("UPDATE PRODUCT SET product_verification_status = 'REJECTED' WHERE product_id = :id", ['id' => $productId]);
        } else {
            offline_update_product_status($productId, 'REJECTED');
        }
        set_flash('success', 'Product rejected.');
    }
}

/**
 * Handles shop approval/rejection and sends email to trader.
 */
function handle_admin_shop_action(string $action, int $shopId): void {
    if ($action === 'approve_shop' && $shopId > 0) {
        if (!db_is_offline()) {
            db_execute("UPDATE SHOP SET shop_status = 'ACTIVE' WHERE shop_id = :id", ['id' => $shopId]);
            
            $shopInfo = db_fetch_one("
                SELECT u.first_name, u.email, u.password, s.shop_name 
                FROM SHOP s 
                JOIN TRADER t ON s.trader_id = t.trader_id 
                JOIN \"USER\" u ON t.trader_id = u.user_id 
                WHERE s.shop_id = :id
            ", ['id' => $shopId]);
            
            if ($shopInfo) {
                $traderName = $shopInfo['FIRST_NAME'] ?? $shopInfo['first_name'] ?? 'Trader';
                $traderEmail = $shopInfo['EMAIL'] ?? $shopInfo['email'] ?? '';
                $traderPassword = $shopInfo['PASSWORD'] ?? $shopInfo['password'] ?? '';
                $shopName = $shopInfo['SHOP_NAME'] ?? $shopInfo['shop_name'] ?? 'Your Shop';
                
                if ($traderEmail) {
                    $subject = "Your Shop '{$shopName}' has been Approved!";
                    $message = "
                    <html>
                    <head>
                    <style>
                        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px; }
                        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
                        .header { background-color: #1a3018; color: #ffffff; padding: 30px; text-align: center; }
                        .header h2 { margin: 0; font-size: 24px; letter-spacing: 1px; }
                        .content { padding: 40px 30px; }
                        .greeting { font-size: 18px; margin-top: 0; color: #1a3018; font-weight: 600; }
                        .credentials-box { background: #f4f6f4; border: 1px solid #e0e4e0; border-radius: 8px; padding: 25px; margin: 25px 0; }
                        .credentials-box h3 { margin-top: 0; color: #1a3018; font-size: 18px; text-align: center; margin-bottom: 20px; }
                        .credential-row { margin-bottom: 12px; font-size: 15px; }
                        .credential-label { font-weight: 600; color: #555; display: inline-block; width: 90px; }
                        .btn-primary { display: inline-block; background-color: #1a3018; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; margin-top: 10px; font-size: 15px; }
                        .footer { text-align: center; padding: 30px; color: #888; font-size: 14px; background: #fafafa; border-top: 1px solid #eee; }
                    </style>
                    </head>
                    <body>
                        <div class='email-container'>
                            <div class='header'>
                                <h2>CLECK E-MART</h2>
                            </div>
                            <div class='content'>
                                <p class='greeting'>Hello {$traderName},</p>
                                <p>Great news! Your shop <strong>{$shopName}</strong> has been officially approved and is now active on Cleck E-Mart.</p>
                                <p>You can now access your dedicated Oracle Dashboard to manage your advanced operations, track analytics, and handle your business data seamlessly.</p>
                                
                                <div class='credentials-box'>
                                    <h3>Oracle Dashboard Login Details</h3>
                                    <div class='credential-row'>
                                        <span class='credential-label'>Email:</span> <strong>{$traderEmail}</strong>
                                    </div>
                                    <div class='credential-row'>
                                        <span class='credential-label'>Password:</span> <strong style='word-break: break-all; font-size: 13px;'>{$traderPassword}</strong>
                                    </div>
                                    <div style='text-align: center; margin-top: 25px;'>
                                        <a href='http://localhost:8080/ords/r/cleck_e_mart/cleck-e-mart-dashboard/login' class='btn-primary'>Go to Oracle Dashboard</a>
                                    </div>
                                </div>
                                
                                <p>We are absolutely thrilled to have you as a verified trader. Let the sales begin!</p>
                            </div>
                            <div class='footer'>
                                <p>Cleck E-Mart &copy; " . date('Y') . "<br>Bringing fresh goods to your doorstep.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    send_email($traderEmail, $subject, $message);
                }
            }
        } else {
            offline_update_shop_status($shopId, 'ACTIVE');
        }
        set_flash('success', 'Shop approved successfully.');
    } elseif ($action === 'reject_shop' && $shopId > 0) {
        if (!db_is_offline()) {
            db_execute("UPDATE SHOP SET shop_status = 'REJECTED' WHERE shop_id = :id", ['id' => $shopId]);
        } else {
            offline_update_shop_status($shopId, 'REJECTED');
        }
        set_flash('success', 'Shop rejected.');
    }
}

/**
 * Fetches pending products for approval.
 */
function get_admin_pending_products(): array {
    if (!db_is_offline()) {
        return db_fetch_all("
            SELECT p.product_id, p.product_name, p.price, p.product_verification_status, s.shop_name
            FROM PRODUCT p
            JOIN SHOP s ON p.shop_id = s.shop_id
            WHERE p.product_verification_status = 'PENDING_VERIFICATION'
        ");
    } else {
        return offline_get_pending_products();
    }
}

/**
 * Fetches pending shops for approval.
 */
function get_admin_pending_shops(): array {
    if (!db_is_offline()) {
        return db_fetch_all("
            SELECT s.shop_id, s.shop_name, s.shop_status, u.first_name, u.last_name, u.email
            FROM SHOP s
            JOIN TRADER t ON s.trader_id = t.trader_id
            JOIN \"USER\" u ON t.trader_id = u.user_id
            WHERE s.shop_status = 'PENDING_APPROVAL'
        ");
    } else {
        $pendingShops = offline_get_pending_shops();
        foreach ($pendingShops as &$shop) {
            if (!isset($shop['FIRST_NAME'])) {
                $shop['FIRST_NAME'] = 'Unknown';
                $shop['LAST_NAME'] = 'Trader';
                $shop['EMAIL'] = 'N/A';
            }
        }
        return $pendingShops;
    }
}

/**
 * Fetches all data for the Admin Dashboard overview.
 */
function get_admin_overview_data(): array {
    $data = [
        'totalRevenue' => 0,
        'totalOrders' => 0,
        'activeTraders' => 0,
        'totalCustomers' => 0,
        'recentOrders' => [],
        'revenueByTrader' => [],
        'chartData' => [],
        'allOrders' => [],
        'itemsByOrder' => [],
        'allTraders' => []
    ];

    if (!db_is_offline()) {
        try {
            $row = db_fetch_one("SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) AS total FROM ORDER_ITEM oi JOIN \"ORDER\" o ON o.order_id = oi.order_id WHERE o.order_status IN ('PAID', 'COLLECTED')");
            $data['totalRevenue'] = (float) ($row['TOTAL'] ?? 0);

            $row = db_fetch_one('SELECT COUNT(*) AS total FROM "ORDER"');
            $data['totalOrders'] = (int) ($row['TOTAL'] ?? 0);

            $row = db_fetch_one("SELECT COUNT(*) AS total FROM \"USER\" WHERE \"ROLE\" = 'TRADER'");
            $data['activeTraders'] = (int) ($row['TOTAL'] ?? 0);

            $row = db_fetch_one("SELECT COUNT(*) AS total FROM \"USER\" WHERE \"ROLE\" = 'CUSTOMER'");
            $data['totalCustomers'] = (int) ($row['TOTAL'] ?? 0);

            $data['recentOrders'] = db_fetch_all("
                SELECT o.order_id, u.first_name || ' ' || u.last_name AS customer_name,
                       (SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) FROM ORDER_ITEM oi WHERE oi.order_id = o.order_id) AS order_total,
                       o.order_status
                FROM \"ORDER\" o
                JOIN CUSTOMER c ON o.customer_id = c.customer_id
                JOIN \"USER\" u ON c.customer_id = u.user_id
                ORDER BY o.order_date DESC
                FETCH FIRST 5 ROWS ONLY
            ");

            $data['revenueByTrader'] = db_fetch_all("
                SELECT u.first_name || ' ' || u.last_name AS trader_name,
                       NVL(SUM(CASE WHEN o.order_status IN ('COLLECTED', 'PAID', 'READY') THEN (oi.quantity * oi.unit_price) ELSE 0 END), 0) AS total_revenue
                FROM \"USER\" u
                JOIN TRADER t ON u.user_id = t.trader_id
                LEFT JOIN SHOP s ON t.trader_id = s.trader_id
                LEFT JOIN PRODUCT p ON s.shop_id = p.shop_id
                LEFT JOIN ORDER_ITEM oi ON p.product_id = oi.product_id
                LEFT JOIN \"ORDER\" o ON oi.order_id = o.order_id
                GROUP BY u.first_name, u.last_name
                ORDER BY total_revenue DESC
            ");

            $weeklyRevenueData = db_fetch_all("
                SELECT TO_CHAR(o.order_date, 'YYYY-MM-DD') as date_str,
                       SUM(oi.quantity * oi.unit_price) as daily_total
                FROM \"ORDER\" o
                JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                WHERE o.order_status IN ('PAID', 'COLLECTED', 'DELIVERED')
                  AND o.order_date >= TRUNC(SYSDATE) - 6
                GROUP BY TO_CHAR(o.order_date, 'YYYY-MM-DD')
            ");
            
            $maxTotal = 0;
            $dayMap = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = date('Y-m-d', strtotime("-$i days"));
                $dayName = date('D', strtotime("-$i days"));
                $dayMap[$dateStr] = [
                    'day' => $dayName,
                    'total' => 0,
                    'percent' => 0
                ];
            }
            
            foreach ($weeklyRevenueData as $row) {
                $dStr = $row['DATE_STR'] ?? $row['date_str'];
                $total = (float)($row['DAILY_TOTAL'] ?? $row['daily_total']);
                if (isset($dayMap[$dStr])) {
                    $dayMap[$dStr]['total'] += $total;
                }
            }
            
            foreach ($dayMap as $dayData) {
                if ($dayData['total'] > $maxTotal) {
                    $maxTotal = $dayData['total'];
                }
                $data['chartData'][] = $dayData;
            }
            
            foreach ($data['chartData'] as &$c) {
                $c['percent'] = $maxTotal > 0 ? ($c['total'] / $maxTotal) * 100 : 0;
            }
            unset($c);

            $data['allOrders'] = db_fetch_all("
                SELECT o.order_id, u.first_name || ' ' || u.last_name AS customer_name,
                       (SELECT NVL(SUM(oi.quantity * oi.unit_price), 0) FROM ORDER_ITEM oi WHERE oi.order_id = o.order_id) AS order_total,
                       o.order_status, o.order_date
                FROM \"ORDER\" o
                JOIN CUSTOMER c ON o.customer_id = c.customer_id
                JOIN \"USER\" u ON c.customer_id = u.user_id
                ORDER BY o.order_date DESC
            ");

            $allOrderItems = db_fetch_all("
                SELECT oi.order_id, p.product_name, oi.quantity, oi.unit_price, s.shop_name
                FROM ORDER_ITEM oi
                JOIN PRODUCT p ON oi.product_id = p.product_id
                JOIN SHOP s ON p.shop_id = s.shop_id
            ");
            
            foreach ($allOrderItems as $item) {
                $orderId = $item['ORDER_ID'] ?? $item['order_id'];
                $data['itemsByOrder'][$orderId][] = $item;
            }

            $data['allTraders'] = db_fetch_all("
                SELECT u.user_id, u.first_name, u.last_name, u.email, t.brand_name 
                FROM \"USER\" u
                JOIN TRADER t ON u.user_id = t.trader_id
                WHERE u.role = 'TRADER'
                ORDER BY u.created_at DESC
            ");

        } catch (Throwable $e) {
            error_log("Dashboard query error: " . $e->getMessage());
        }
    } else {
        // Offline mode mock data
        $data['totalRevenue'] = 12450.00;
        $data['totalOrders'] = 842;
        $data['activeTraders'] = 24;
        $data['totalCustomers'] = 1204;
        $data['recentOrders'] = [
            ['ORDER_ID' => 1024, 'CUSTOMER_NAME' => 'John Doe', 'ORDER_TOTAL' => 45.00, 'ORDER_STATUS' => 'PROCESSING'],
            ['ORDER_ID' => 1023, 'CUSTOMER_NAME' => 'Jane Smith', 'ORDER_TOTAL' => 12.50, 'ORDER_STATUS' => 'DELIVERED'],
        ];
        $data['revenueByTrader'] = [
            ['TRADER_NAME' => 'Green Farms', 'TOTAL_REVENUE' => 4200],
            ['TRADER_NAME' => 'Fresh Catch', 'TOTAL_REVENUE' => 3850],
            ['TRADER_NAME' => 'Daily Bread', 'TOTAL_REVENUE' => 2100],
        ];
        $data['chartData'] = [
            ['day' => 'Mon', 'percent' => 40, 'total' => 240],
            ['day' => 'Tue', 'percent' => 60, 'total' => 360],
            ['day' => 'Wed', 'percent' => 50, 'total' => 300],
            ['day' => 'Thu', 'percent' => 80, 'total' => 480],
            ['day' => 'Fri', 'percent' => 70, 'total' => 420],
            ['day' => 'Sat', 'percent' => 90, 'total' => 540],
            ['day' => 'Sun', 'percent' => 75, 'total' => 450],
        ];
        $data['allOrders'] = $data['recentOrders'];
        
        $data['itemsByOrder'][1024] = [
            ['PRODUCT_NAME' => 'Mock Product A', 'QUANTITY' => 2, 'UNIT_PRICE' => 10, 'SHOP_NAME' => 'Mock Shop'],
            ['PRODUCT_NAME' => 'Mock Product B', 'QUANTITY' => 1, 'UNIT_PRICE' => 25, 'SHOP_NAME' => 'Mock Shop']
        ];
        $data['itemsByOrder'][1023] = [
            ['PRODUCT_NAME' => 'Mock Product C', 'QUANTITY' => 1, 'UNIT_PRICE' => 12.50, 'SHOP_NAME' => 'Mock Shop']
        ];
        
        $offlineData = offline_load();
        foreach ($offlineData['users'] as $u) {
            if (strtoupper((string)$u['role']) === 'TRADER') {
                $data['allTraders'][] = [
                    'USER_ID' => $u['user_id'],
                    'FIRST_NAME' => $u['first_name'],
                    'LAST_NAME' => $u['last_name'],
                    'EMAIL' => $u['email']
                ];
            }
        }
    }
    
    return $data;
}
