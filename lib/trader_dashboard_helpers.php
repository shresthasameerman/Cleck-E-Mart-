<?php
// These helper functions generate the analytics and sales reports shown on the trader dashboard.

/**
 * Trader Dashboard Helpers
 * Extracts complex Oracle queries and business logic for the Trader Portal.
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/oci_db.php';
require_once __DIR__ . '/offline_store.php';
require_once __DIR__ . '/trader_helpers.php'; // For fallback calls

/**
 * Fetches top selling products for a given shop within a timeframe.
 *
 * @param array $shop The shop data array
 * @param array $inventoryProducts Fallback inventory products
 * @param string $timeframe 'week', 'month', or 'year'
 * @return array Array of top products ['product_name', 'sold_quantity']
 */
function get_trader_top_products(?array $shop, array $inventoryProducts, string $timeframe): array {
    $topProducts = [];
    $validTimeframes = ['week', 'month', 'year'];
    
    if (!in_array($timeframe, $validTimeframes)) {
        $timeframe = 'week';
    }

    $dateFilter = '';
    switch ($timeframe) {
        case 'month':
            $dateFilter = "AND o.order_date >= ADD_MONTHS(SYSDATE, -1)";
            break;
        case 'year':
            $dateFilter = "AND o.order_date >= ADD_MONTHS(SYSDATE, -12)";
            break;
        case 'week':
        default:
            $dateFilter = "AND o.order_date >= SYSDATE - 7";
            break;
    }

    if ($shop && isset($shop['SHOP_ID']) && !db_is_offline()) {
        try {
            $conn = db_connect();
            if ($conn) {
                $sql = "SELECT p.product_name, SUM(oi.quantity) as total_sold
                        FROM \"ORDER\" o
                        JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                        JOIN PRODUCT p ON oi.product_id = p.product_id
                        WHERE p.shop_id = :shop_id
                        {$dateFilter}
                        GROUP BY p.product_name
                        ORDER BY total_sold DESC
                        FETCH FIRST 5 ROWS ONLY";
                
                $stmt = oci_parse($conn, $sql);
                if ($stmt) {
                    oci_bind_by_name($stmt, ':shop_id', $shop['SHOP_ID'], -1, SQLT_INT);
                    if (oci_execute($stmt)) {
                        while ($row = oci_fetch_assoc($stmt)) {
                            $topProducts[] = [
                                'product_name' => $row['PRODUCT_NAME'],
                                'sold_quantity' => (int)$row['TOTAL_SOLD']
                            ];
                        }
                    }
                    oci_free_statement($stmt);
                }
            }
        } catch (Exception $e) {
            $topProducts = array_slice($inventoryProducts, 0, 5);
        }
    } else {
        $topProducts = array_slice($inventoryProducts, 0, 5);
    }
    
    return $topProducts;
}

/**
 * Gets the display label for the timeframe.
 */
function get_timeframe_label(string $timeframe): string {
    switch ($timeframe) {
        case 'month': return 'This Month';
        case 'year':  return 'This Year';
        case 'week':
        default:      return 'This Week';
    }
}

/**
 * Fetches the comprehensive sales report data for a trader or specific shop.
 * 
 * @param int $userId The trader's user ID
 * @param int|null $shopId The shop ID (if filtering by shop)
 * @return array The sales data arrays and totals
 */
function get_trader_sales_report_data(int $userId, ?int $shopId): array {
    $dailySales = [];
    $detailedSales = [];
    $shopRevenue = [];
    $categorySales = [];
    $topProductsShop = [];
    $totalRevenue = 0.0;
    $totalItemsSold = 0;

    $conn = db_connect();
    if ($conn) {
        $whereClause = $shopId ? "p.shop_id = :filter_id" : "s.trader_id = :filter_id";
        $filterId = $shopId ? $shopId : $userId;

        // 1. Daily Sales Trend (Last 30 days)
        $sqlDaily = "SELECT TO_CHAR(o.order_date, 'YYYY-MM-DD') AS sale_date, SUM(oi.quantity * oi.unit_price) AS daily_revenue
                     FROM \"ORDER\" o
                     JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                     JOIN PRODUCT p ON oi.product_id = p.product_id
                     JOIN SHOP s ON p.shop_id = s.shop_id
                     WHERE $whereClause AND o.order_date >= SYSDATE - 30
                     GROUP BY TO_CHAR(o.order_date, 'YYYY-MM-DD')
                     ORDER BY sale_date ASC";
        
        $stmtDaily = oci_parse($conn, $sqlDaily);
        if ($stmtDaily) {
            oci_bind_by_name($stmtDaily, ':filter_id', $filterId, -1, SQLT_INT);
            if (oci_execute($stmtDaily)) {
                while ($row = oci_fetch_assoc($stmtDaily)) {
                    $dailySales[] = [
                        'date' => $row['SALE_DATE'],
                        'revenue' => (float) $row['DAILY_REVENUE']
                    ];
                }
            }
            oci_free_statement($stmtDaily);
        }

        // 2. Revenue by Shop (Only useful for overall report)
        if (!$shopId) {
            $sqlShop = "SELECT s.shop_name, SUM(oi.quantity * oi.unit_price) AS shop_revenue
                        FROM \"ORDER\" o
                        JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                        JOIN PRODUCT p ON oi.product_id = p.product_id
                        JOIN SHOP s ON p.shop_id = s.shop_id
                        WHERE s.trader_id = :trader_id
                        GROUP BY s.shop_name";
            $stmtShop = oci_parse($conn, $sqlShop);
            if ($stmtShop) {
                oci_bind_by_name($stmtShop, ':trader_id', $userId, -1, SQLT_INT);
                if (oci_execute($stmtShop)) {
                    while ($row = oci_fetch_assoc($stmtShop)) {
                        $shopRevenue[] = [
                            'shop_name' => $row['SHOP_NAME'],
                            'revenue' => (float) $row['SHOP_REVENUE']
                        ];
                    }
                }
                oci_free_statement($stmtShop);
            }
        }

        // 3. Sales by Category
        $sqlCat = "SELECT c.category_name, SUM(oi.quantity) AS items_sold
                    FROM \"ORDER\" o
                    JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                    JOIN PRODUCT p ON oi.product_id = p.product_id
                    JOIN SHOP s ON p.shop_id = s.shop_id
                    JOIN CATEGORY c ON p.category_id = c.category_id
                    WHERE $whereClause
                    GROUP BY c.category_name";
        $stmtCat = oci_parse($conn, $sqlCat);
        if ($stmtCat) {
            oci_bind_by_name($stmtCat, ':filter_id', $filterId, -1, SQLT_INT);
            if (oci_execute($stmtCat)) {
                while ($row = oci_fetch_assoc($stmtCat)) {
                    $categorySales[] = [
                        'category_name' => $row['CATEGORY_NAME'],
                        'items_sold' => (int) $row['ITEMS_SOLD']
                    ];
                }
            }
            oci_free_statement($stmtCat);
        }

        // 3.5 Top Products for Shop (Only for specific shop report)
        if ($shopId) {
            $sqlTop = "SELECT p.product_name, SUM(oi.quantity) AS total_sold
                       FROM \"ORDER\" o
                       JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                       JOIN PRODUCT p ON oi.product_id = p.product_id
                       WHERE p.shop_id = :shop_id
                       GROUP BY p.product_name
                       ORDER BY total_sold DESC
                       FETCH FIRST 5 ROWS ONLY";
            $stmtTop = oci_parse($conn, $sqlTop);
            if ($stmtTop) {
                oci_bind_by_name($stmtTop, ':shop_id', $shopId, -1, SQLT_INT);
                if (oci_execute($stmtTop)) {
                    while ($row = oci_fetch_assoc($stmtTop)) {
                        $topProductsShop[] = [
                            'product_name' => $row['PRODUCT_NAME'],
                            'total_sold' => (int) $row['TOTAL_SOLD']
                        ];
                    }
                }
                oci_free_statement($stmtTop);
            }
        }

        // 4. Detailed Sales List
        $sqlDetails = "SELECT o.order_id, TO_CHAR(o.order_date, 'YYYY-MM-DD HH24:MI') AS order_date,
                              p.product_name, s.shop_name, oi.quantity, oi.unit_price, (oi.quantity * oi.unit_price) AS total_price
                       FROM \"ORDER\" o
                       JOIN ORDER_ITEM oi ON o.order_id = oi.order_id
                       JOIN PRODUCT p ON oi.product_id = p.product_id
                       JOIN SHOP s ON p.shop_id = s.shop_id
                       WHERE $whereClause
                       ORDER BY o.order_date DESC";
        
        $stmtDetails = oci_parse($conn, $sqlDetails);
        if ($stmtDetails) {
            oci_bind_by_name($stmtDetails, ':filter_id', $filterId, -1, SQLT_INT);
            if (oci_execute($stmtDetails)) {
                while ($row = oci_fetch_assoc($stmtDetails)) {
                    $detailedSales[] = [
                        'order_id' => $row['ORDER_ID'],
                        'date' => $row['ORDER_DATE'],
                        'product_name' => $row['PRODUCT_NAME'],
                        'shop_name' => $row['SHOP_NAME'],
                        'quantity' => (int) $row['QUANTITY'],
                        'unit_price' => (float) $row['UNIT_PRICE'],
                        'total_price' => (float) $row['TOTAL_PRICE']
                    ];
                    $totalRevenue += (float) $row['TOTAL_PRICE'];
                    $totalItemsSold += (int) $row['QUANTITY'];
                }
            }
            oci_free_statement($stmtDetails);
        }
    }

    return [
        'dailySales' => $dailySales,
        'detailedSales' => $detailedSales,
        'shopRevenue' => $shopRevenue,
        'categorySales' => $categorySales,
        'topProductsShop' => $topProductsShop,
        'totalRevenue' => $totalRevenue,
        'totalItemsSold' => $totalItemsSold
    ];
}
