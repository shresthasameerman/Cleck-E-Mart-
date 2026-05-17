<?php
$file = __DIR__ . '/../lib/trader_helpers.php';
$content = file_get_contents($file);

$newFunctions = <<< 'PHP'
function trader_get_shops(int $userId): array
{
    if (db_is_offline()) {
        return offline_get_trader_shops($userId);
    }
    return db_fetch_all(
        'SELECT shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status
         FROM SHOP
         WHERE trader_id = :user_id',
        ['user_id' => $userId]
    );
}

function trader_create_shop(int $userId, array $payload): array
{
    $shopName = trim($payload['shop_name'] ?? '');
    $shopDesc = trim($payload['shop_description'] ?? '');
    $shopLogo = trim($payload['shop_logo'] ?? '');
    
    if ($shopName === '') {
        throw new InvalidArgumentException('Shop name is required.');
    }
    
    if (db_is_offline()) {
        return offline_create_shop_for_trader($userId, $shopName, $shopDesc, $shopLogo === '' ? null : $shopLogo);
    }
    
    db_begin();
    try {
        $shopId = db_next_id('SHOP', 'shop_id');
        db_execute(
            'INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
             VALUES (:shop_id, :trader_id, :shop_name, :shop_description, :shop_logo, :shop_status)',
            [
                'shop_id' => $shopId,
                'trader_id' => $userId,
                'shop_name' => $shopName,
                'shop_description' => $shopDesc,
                'shop_logo' => $shopLogo === '' ? null : $shopLogo,
                'shop_status' => 'PENDING_APPROVAL'
            ]
        );
        db_commit();
        
        return [
            'SHOP_ID' => $shopId,
            'TRADER_ID' => $userId,
            'SHOP_NAME' => $shopName,
            'SHOP_DESCRIPTION' => $shopDesc,
            'SHOP_LOGO' => $shopLogo === '' ? null : $shopLogo,
            'SHOP_STATUS' => 'PENDING_APPROVAL'
        ];
    } catch (Throwable $e) {
        db_rollback();
        throw $e;
    }
}
PHP;

if (strpos($content, 'trader_get_shops') === false) {
    file_put_contents($file, "\n" . $newFunctions . "\n", FILE_APPEND);
}
