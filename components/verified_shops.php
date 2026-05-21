<?php
$shopLogos = [
    "Firth's Butchers" => "assets/images/icons/trader-butchers.svg",
    "Greenwood's Greengrocers" => "assets/images/icons/fresh-carrots.svg",
    "Fishwick's Fishmonger" => "assets/images/icons/trader-fishmongers.svg",
    "The Dough Bakery" => "assets/images/icons/fresh-bread.svg",
    "Claes Deli & Delicatessen" => "assets/images/icons/trader-delicatessens.svg"
];
?>
<section class="verified-shops" aria-labelledby="verified-shops-title" style="margin-top: 2.5rem; margin-bottom: 2.5rem;">
    <div class="container">
        <div class="section-heading" style="margin-bottom: 1.5rem;">
            <h2 id="verified-shops-title" class="section-heading__title-sm">Verified Shops</h2>
        </div>
        <?php if (empty($verifiedShops)): ?>
            <div class="page-message">
                <p>No verified shops at the moment.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
                <?php foreach ($verifiedShops as $shop): ?>
                    <?php 
                        $logo = !empty($shop['shop_logo']) ? 'assets/Shop Logos/' . $shop['shop_logo'] : ($shopLogos[$shop['shop_name']] ?? 'assets/images/icons/product-placeholder.svg');
                    ?>
                    <article style="background: #ffffff; border-radius: var(--radius-lg); box-shadow: 0 4px 12px rgba(0,0,0,0.04); display: flex; flex-direction: column; align-items: center; padding: 1.5rem; text-align: center; border: 1px solid rgba(0,0,0,0.03);">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: #f9f9f9; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; padding: 1rem;">
                            <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?> Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; opacity: 0.8;" />
                        </div>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--color-brand-green); margin: 0 0 0.25rem 0; font-weight: 700;"><?php echo htmlspecialchars((string) $shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p style="font-size: 0.8rem; color: var(--color-muted); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">By <?php echo htmlspecialchars((string) $shop['trader_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
