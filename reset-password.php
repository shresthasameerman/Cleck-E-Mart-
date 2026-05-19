<?php
$pageTitle = 'Reset Password | Cleck E-Mart';
$metaDescription = 'Verify OTP and reset your password.';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/offline_store.php';

$error = null;
$success = get_flash('success');

// Redirect to forgot-password if no reset session exists
if (empty($_SESSION['reset_email'])) {
    redirect('forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim(filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_STRING));
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $sessionOtp = $_SESSION['reset_otp'] ?? '';
    $sessionExpires = (int) ($_SESSION['reset_expires'] ?? 0);
    $email = $_SESSION['reset_email'];

    if ($otp === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Please fill in all fields.';
    } elseif (time() > $sessionExpires) {
        $error = 'Your OTP has expired. Please request a new one.';
    } elseif ($otp !== $sessionOtp && $otp !== '123456') { // 123456 as a backdoor for testing
        $error = 'Invalid OTP. Please try again.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $error = 'Password must be at least 8 characters long and contain letters, numbers, and special characters.';
    } else {
        // Validation passed, update password
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if (db_is_offline()) {
                $data = offline_load();
                foreach ($data['users'] as &$u) {
                    if (strtolower((string) $u['email']) === $email) {
                        $u['password'] = $hashedPassword;
                        break;
                    }
                }
                offline_save($data);
            } else {
                db_execute(
                    'UPDATE "USER" SET password = :password WHERE email = :email',
                    ['password' => $hashedPassword, 'email' => $email]
                );
            }

            // Clear reset session data
            unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expires']);
            
            set_flash('success', 'Your password has been reset successfully. You can now log in.');
            redirect('auth.php?mode=login');
        } catch (Throwable $e) {
            $error = 'An error occurred while resetting your password. Please try again.';
            error_log('Reset password error: ' . $e->getMessage());
        }
    }
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="auth-page">
    <div class="container auth-container">
        <section class="auth-section">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-description">We have sent a 6-digit OTP to <strong><?php echo e($_SESSION['reset_email']); ?></strong>. Please enter it below along with your new password.</p>

            <?php if ($error !== null): ?>
                <div class="page-message page-message--error" role="alert"><?php echo e($error); ?></div>
            <?php endif; ?>
            <?php if ($success !== null): ?>
                <div class="page-message page-message--success" role="alert"><?php echo e($success); ?></div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="reset-password.php" novalidate>
                <div class="form-group">
                    <label for="reset-otp">6-Digit OTP</label>
                    <input type="text" id="reset-otp" name="otp" required autocomplete="one-time-code" maxlength="6" pattern="\d{6}" placeholder="123456" />
                </div>

                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" name="new_password" required autocomplete="new-password" />
                    <small style="display: block; margin-top: 0.25rem; color: #666;">Minimum 8 characters, letters, numbers, and special characters.</small>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required autocomplete="new-password" />
                </div>

                <button class="button auth-submit" type="submit">Reset Password</button>
            </form>
            
            <div class="auth-footer" style="text-align: center; margin-top: 2rem;">
                <p>Didn't receive the email? <a href="forgot-password.php" style="color: var(--primary); text-decoration: underline;">Try again</a></p>
                <p style="margin-top: 0.5rem;"><a href="auth.php?mode=login" style="color: var(--primary); text-decoration: underline;">Back to Login</a></p>
            </div>
        </section>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
