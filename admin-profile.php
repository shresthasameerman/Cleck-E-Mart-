<?php
require_once __DIR__ . '/lib/auth_helpers.php';

require_login(['ADMIN']);

$errors = [];
$flashSuccess = get_flash('success');
$userId = (int) current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileAction = (string) ($_POST['profile_action'] ?? '');

    if ($profileAction === 'update_account') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($firstName === '' || $lastName === '' || $email === '') {
            $errors[] = 'First name, last name, and email are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }

        if ($errors === []) {
            try {
                $existing = db_is_offline()
                    ? (offline_email_taken_by_other($userId, $email) ? ['USER_ID' => -1] : null)
                    : db_fetch_one(
                        'SELECT user_id FROM "USER" WHERE LOWER(email) = LOWER(:email) AND user_id <> :user_id',
                        [
                            'email' => $email,
                            'user_id' => $userId,
                        ]
                    );

                if ($existing !== null) {
                    $errors[] = 'This email is already used by another account.';
                } else {
                    if (db_is_offline()) {
                        offline_update_user($userId, $firstName, $lastName, $email, $phone === '' ? null : $phone);
                    } else {
                        db_execute(
                            'UPDATE "USER"
                             SET first_name = :first_name,
                                 last_name = :last_name,
                                 email = :email,
                                 phone_number = :phone_number,
                                 updated_at = CURRENT_TIMESTAMP
                             WHERE user_id = :user_id',
                            [
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $email,
                                'phone_number' => $phone === '' ? null : $phone,
                                'user_id' => $userId,
                            ]
                        );
                    }

                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['email'] = $email;

                    set_flash('success', 'Account details updated successfully.');
                    redirect('admin-profile.php');
                }
            } catch (Throwable $exception) {
                $errors[] = 'Unable to update account: ' . $exception->getMessage();
            }
        }
    }

    if ($profileAction === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'All password fields are required.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirmation do not match.';
        }
        if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }

        if ($errors === []) {
            try {
                $dbUser = db_is_offline()
                    ? offline_user_by_id($userId)
                    : db_fetch_one('SELECT password FROM "USER" WHERE user_id = :user_id', ['user_id' => $userId]);
                if ($dbUser === null || !password_verify($currentPassword, (string) $dbUser['PASSWORD'])) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    if (db_is_offline()) {
                        offline_update_password($userId, $hashedPassword);
                    } else {
                        db_execute(
                            'UPDATE "USER" SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id',
                            [
                                'password' => $hashedPassword,
                                'user_id' => $userId,
                            ]
                        );
                    }

                    set_flash('success', 'Password updated successfully.');
                    redirect('admin-profile.php');
                }
            } catch (Throwable $exception) {
                $errors[] = 'Unable to update password: ' . $exception->getMessage();
            }
        }
    }
}

$user = db_is_offline()
    ? offline_user_by_id($userId)
    : db_fetch_one(
        'SELECT user_id, first_name, last_name, email, phone_number, "ROLE" AS role
         FROM "USER"
         WHERE user_id = :user_id',
        ['user_id' => $userId]
    );

if ($user === null) {
    set_flash('error', 'Unable to load profile details.');
    redirect('index.php');
}

$pageTitle = 'Admin Profile - Cleck E-Mart';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="page-layout">
    <div class="container" style="max-width: 800px; margin-top: 2rem; margin-bottom: 4rem;">
        
        <div class="admin-dashboard-hero" style="border-radius: var(--radius-md) var(--radius-md) 0 0; margin-bottom: 0;">
            <h1 class="page-title" style="margin: 0; color: white;">Admin Profile</h1>
            <p style="margin-top: 0.5rem; opacity: 0.9;">Manage your admin account details and security.</p>
        </div>
        
        <div style="background: var(--color-primary); border: 1px solid rgba(0,0,0,0.1); border-top: none; border-radius: 0 0 var(--radius-md) var(--radius-md); padding: 2rem;">
            
            <?php if ($flashSuccess !== null): ?>
                <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="page-message page-message--error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="admin-section is-active" style="display: block; box-shadow: none; padding: 0; border: none;">
                <div class="trader-card__header">
                    <div>
                        <h2>Account Details</h2>
                    </div>
                </div>
                
                <form class="trader-form" action="admin-profile.php" method="post" novalidate>
                    <input type="hidden" name="profile_action" value="update_account" />
                    <div class="trader-form__grid">
                        <label>
                            <span>First Name*</span>
                            <input type="text" name="first_name" required autocomplete="given-name" value="<?php echo e($user['FIRST_NAME']); ?>" />
                        </label>
                        <label>
                            <span>Last Name*</span>
                            <input type="text" name="last_name" required autocomplete="family-name" value="<?php echo e($user['LAST_NAME']); ?>" />
                        </label>
                    </div>
                    <label>
                        <span>Email*</span>
                        <input type="email" name="email" required autocomplete="email" value="<?php echo e($user['EMAIL']); ?>" />
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" name="phone" autocomplete="tel" value="<?php echo e((string) ($user['PHONE_NUMBER'] ?? '')); ?>" />
                    </label>
                    <button class="trader-submit" type="submit">Save Changes</button>
                </form>

                <hr style="margin: 2.5rem 0; border: 0; border-top: 1px solid rgba(0,0,0,0.1);" />

                <div class="trader-card__header">
                    <div>
                        <h2>Change Password</h2>
                    </div>
                </div>
                
                <form class="trader-form" action="admin-profile.php" method="post" novalidate>
                    <input type="hidden" name="profile_action" value="change_password" />
                    <label>
                        <span>Current Password*</span>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" required autocomplete="current-password" placeholder="Enter current password" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                        </div>
                    </label>
                    <label>
                        <span>New Password*</span>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" required autocomplete="new-password" placeholder="Create a strong password" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                        </div>
                    </label>
                    <label>
                        <span>Confirm New Password*</span>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" required autocomplete="new-password" placeholder="Repeat new password" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                        </div>
                    </label>
                    <button class="trader-submit" type="submit">Update Password</button>
                </form>

                <hr style="margin: 2.5rem 0; border: 0; border-top: 1px solid rgba(0,0,0,0.1);" />
                
                <div style="display: flex; justify-content: flex-end;">
                    <a href="logout.php" class="button button--secondary" style="color: var(--color-accent); border-color: var(--color-accent); font-weight: 600;">
                        Sign Out
                    </a>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
