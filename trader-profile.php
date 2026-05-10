<?php
require_once __DIR__ . '/lib/trader_helpers.php';

trader_role_guard();

$errors = [];
$successMessage = get_flash('success');
$userId = (int) current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['profile_action'] ?? '') === 'update_trader_profile') {
    try {
        trader_update_profile($userId, [
            'first_name' => (string) ($_POST['first_name'] ?? ''),
            'last_name' => (string) ($_POST['last_name'] ?? ''),
            'email' => (string) ($_POST['email'] ?? ''),
            'phone' => (string) ($_POST['phone'] ?? ''),
            'shop_name' => (string) ($_POST['shop_name'] ?? ''),
            'shop_description' => (string) ($_POST['shop_description'] ?? ''),
            'shop_logo' => (string) ($_POST['shop_logo'] ?? ''),
        ]);

        set_flash('success', 'Trader profile settings updated successfully.');
        redirect('trader-profile.php');
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$user = db_is_offline()
    ? offline_user_by_id($userId)
    : db_fetch_one('SELECT user_id, first_name, last_name, email, phone_number, "ROLE" AS role FROM "USER" WHERE user_id = :user_id', ['user_id' => $userId]);
$shop = trader_shop_for_user($userId);
$metrics = trader_dashboard_metrics($userId);
$categories = trader_categories();

$pageTitle = 'Trader Profile Settings | Cleck E-Mart';
$metaDescription = 'Update trader account details and shop settings.';

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="trader-page">
    <div class="container">
        <?php if ($successMessage !== null): ?>
            <p class="page-message page-message--success"><?php echo e($successMessage); ?></p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="page-message page-message--error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <section class="trader-intro" aria-labelledby="trader-profile-title">
        <div class="container trader-intro__inner">
            <div>
                <p class="trader-intro__eyebrow">Profile settings</p>
                <h1 id="trader-profile-title"><?php echo e($shop['SHOP_NAME'] ?? 'Trader account'); ?></h1>
                <p class="trader-intro__sub">Update your trader details, shop branding, and public contact information.</p>
            </div>
            <div class="trader-intro__meta">
                <span><?php echo e($metrics['sold_total']); ?> products sold</span>
            </div>
        </div>
    </section>

    <section class="trader-content">
        <div class="container trader-layout">
            <aside class="trader-sidebar" aria-label="Trader navigation">
                <a class="trader-sidebar__item" href="trader-dashboard.php">Dashboard</a>
                <a class="trader-sidebar__item is-active" href="trader-profile.php">Profile Settings</a>
                <a class="trader-sidebar__item" href="trader-add-product.php">Add Product</a>
                <a class="trader-sidebar__item" href="logout.php">Sign Out</a>
            </aside>

            <div class="trader-main">
                <section class="trader-card">
                    <div class="trader-card__header">
                        <div>
                            <p class="trader-card__eyebrow">Shop details</p>
                            <h2>Trader and shop profile</h2>
                        </div>
                        <span class="trader-card__badge"><?php echo e($user['ROLE'] ?? 'TRADER'); ?></span>
                    </div>

                    <form class="trader-form" method="post" action="trader-profile.php">
                        <input type="hidden" name="profile_action" value="update_trader_profile" />
                        <div class="trader-form__grid">
                            <label>
                                <span>First name</span>
                                <input type="text" name="first_name" value="<?php echo e($user['FIRST_NAME'] ?? ''); ?>" required />
                            </label>
                            <label>
                                <span>Last name</span>
                                <input type="text" name="last_name" value="<?php echo e($user['LAST_NAME'] ?? ''); ?>" required />
                            </label>
                            <label>
                                <span>Email</span>
                                <input type="email" name="email" value="<?php echo e($user['EMAIL'] ?? ''); ?>" required />
                            </label>
                            <label>
                                <span>Phone</span>
                                <input type="tel" name="phone" value="<?php echo e($user['PHONE_NUMBER'] ?? ''); ?>" />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop name</span>
                                <input type="text" name="shop_name" value="<?php echo e($shop['SHOP_NAME'] ?? ''); ?>" required />
                            </label>
                            <label class="trader-form__full">
                                <span>Shop description</span>
                                <textarea name="shop_description" rows="4"><?php echo e($shop['SHOP_DESCRIPTION'] ?? ''); ?></textarea>
                            </label>
                            <label>
                                <span>Shop logo filename</span>
                                <input type="text" name="shop_logo" value="<?php echo e($shop['SHOP_LOGO'] ?? ''); ?>" placeholder="logo.png" />
                            </label>
                            <label>
                                <span>Top category</span>
                                <input type="text" value="<?php echo e($categories[0]['CATEGORY_NAME'] ?? 'Market listing'); ?>" readonly />
                            </label>
                        </div>

                        <button class="trader-submit" type="submit">Save Changes</button>
                    </form>
                </section>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
