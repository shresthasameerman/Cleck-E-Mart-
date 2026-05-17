<?php
// PHP Script to patch offline_store.php and trader_helpers.php with new functions
$offlineFile = __DIR__ . '/../lib/offline_store.php';
$offlineContent = file_get_contents($offlineFile);

$newOfflineFunctions = <<< 'PHP'

function offline_get_trader_shops(int $traderId): array
{
    $data = offline_load();
    $user = null;
    foreach ($data['users'] as $row) {
        if ((int) $row['user_id'] === $traderId) {
            $user = $row;
            break;
        }
    }
    if ($user === null) {
        return [];
    }

    $shops = [];
    foreach ($data['shops'] as $shop) {
        if ((int) $shop['trader_id'] === $traderId) {
            $shops[] = [
                'SHOP_ID' => (int) $shop['shop_id'],
                'TRADER_ID' => (int) $shop['trader_id'],
                'SHOP_NAME' => (string) $shop['shop_name'],
                'SHOP_DESCRIPTION' => (string) $shop['shop_description'],
                'SHOP_LOGO' => (string) $shop['shop_logo'],
                'SHOP_STATUS' => (string) $shop['shop_status'],
            ];
        }
    }
    return $shops;
}

function offline_create_shop_for_trader(int $traderId, string $shopName, string $shopDesc, ?string $shopLogo): array
{
    $data = offline_load();
    $shopId = offline_next_id($data['shops'], 'shop_id');
    $shopStatus = 'PENDING_APPROVAL';
    
    $newShop = [
        'shop_id' => $shopId,
        'trader_id' => $traderId,
        'shop_name' => $shopName,
        'shop_description' => $shopDesc,
        'shop_logo' => $shopLogo,
        'shop_status' => $shopStatus,
    ];
    $data['shops'][] = $newShop;
    offline_save($data);
    
    return [
        'SHOP_ID' => $shopId,
        'TRADER_ID' => $traderId,
        'SHOP_NAME' => $shopName,
        'SHOP_DESCRIPTION' => $shopDesc,
        'SHOP_LOGO' => $shopLogo,
        'SHOP_STATUS' => $shopStatus,
    ];
}

function offline_get_pending_shops(): array
{
    $data = offline_load();
    $shops = [];
    foreach ($data['shops'] as $shop) {
        if (($shop['shop_status'] ?? '') === 'PENDING_APPROVAL') {
            $shops[] = [
                'SHOP_ID' => (int) $shop['shop_id'],
                'SHOP_NAME' => (string) $shop['shop_name'],
                'SHOP_STATUS' => (string) $shop['shop_status']
            ];
        }
    }
    return $shops;
}

function offline_update_shop_status(int $shopId, string $status): void
{
    $data = offline_load();
    foreach ($data['shops'] as $index => $shop) {
        if ((int) $shop['shop_id'] === $shopId) {
            $data['shops'][$index]['shop_status'] = $status;
            offline_save($data);
            return;
        }
    }
}
PHP;

if (strpos($offlineContent, 'offline_get_trader_shops') === false) {
    file_put_contents($offlineFile, $newOfflineFunctions, FILE_APPEND);
}
