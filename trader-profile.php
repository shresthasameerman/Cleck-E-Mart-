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
            'gender' => (string) ($_POST['gender'] ?? ''),
            'address' => (string) ($_POST['address'] ?? ''),
            'brand_name' => (string) ($_POST['brand_name'] ?? ''),
            'pan_number' => (string) ($_POST['pan_number'] ?? ''),
            'current_password' => (string) ($_POST['current_password'] ?? ''),
            'new_password' => (string) ($_POST['new_password'] ?? ''),
        ]);

        set_flash('success', 'Trader profile settings updated successfully.');
        redirect('trader-profile.php');
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

$user = db_is_offline()
    ? offline_user_by_id($userId)
    : db_fetch_one('SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone_number, u.gender, u.address, u."ROLE" AS role, t.brand_name, t.pan_number FROM "USER" u LEFT JOIN TRADER t ON u.user_id = t.trader_id WHERE u.user_id = :user_id', ['user_id' => $userId]);

$pageTitle = 'Trader Profile Settings | Cleck E-Mart';
$metaDescription = 'Update trader account details and password.';

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

    <div class="container">
        <div class="admin-dashboard-layout">
            <aside class="admin-sidebar">
                <div class="admin-dashboard-hero">
                    <h1 class="page-title" style="margin: 0; color: white;">Trader Dashboard</h1>
                    <p style="margin-top: 0.5rem; opacity: 0.9;">Update your trader details and branding.</p>
                </div>

                <div class="admin-tabs">
                    <a href="trader-profile.php" class="tab-button active">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        My Profile
                    </a>
                    <a href="trader-shops.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        My Shop
                    </a>
                    <a href="trader-orders.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        All Orders
                    </a>
                    <a href="trader-sales.php" class="tab-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                        All Sales
                    </a>
                    <a href="logout.php" class="tab-button" style="margin-top: auto; color: var(--color-accent); border-top: 1px solid rgba(0,0,0,0.1); border-radius: 0; padding-top: 1rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Sign Out
                    </a>
                </div>
            </aside>

            <div class="admin-content-grid" style="display: block;">
                <section class="admin-section">
                    <div class="trader-card__header">
                        <div>
                            <p class="trader-card__eyebrow">Personal details</p>
                            <h2>Trader Profile</h2>
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
                            <label>
                                <span>Gender</span>
                                <select name="gender" style="width: 100%; padding: 0.8rem; border-radius: var(--radius-sm); border: 1px solid rgba(0,0,0,0.1); font-family: inherit;">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($user['GENDER'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['GENDER'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['GENDER'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </label>
                            <label>
                                <span>PAN Number</span>
                                <input type="text" name="pan_number" value="<?php echo e($user['PAN_NUMBER'] ?? ''); ?>" />
                            </label>
                            <label class="trader-form__full">
                                <span>Address</span>
                                <input type="text" name="address" value="<?php echo e($user['ADDRESS'] ?? ''); ?>" />
                            </label>
                            <label class="trader-form__full">
                                <span>Brand Name</span>
                                <input type="text" name="brand_name" value="<?php echo e($user['BRAND_NAME'] ?? ''); ?>" required />
                            </label>
                            
                            <!-- Password Change Section -->
                            <div class="trader-form__full" style="margin-top: 1.5rem;">
                                <h3 style="font-size: 1.2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 0.5rem;">Change Password</h3>
                            </div>
                            <label>
                                <span>Current Password</span>
                                <input type="password" name="current_password" placeholder="Leave blank to keep current" />
                            </label>
                            <label>
                                <span>New Password</span>
                                <input type="password" name="new_password" placeholder="Leave blank to keep current" />
                            </label>
                        </div>

                        <button class="trader-submit" type="submit">Save Changes</button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
