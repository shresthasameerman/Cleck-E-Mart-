<?php
// This file handles the first step of password recovery, asking the user for their email address to send an OTP.

$pageTitle = 'Forgot Password | Cleck E-Mart';
$metaDescription = 'Reset your Cleck E-Mart account password.';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/email_helpers.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));

    if ($email === '') {
        $error = 'Please enter your email address.';
    } else {
        try {
            $user = null;
            if (db_is_offline()) {
                $data = offline_load();
                foreach ($data['users'] as $u) {
                    if (strtolower((string) $u['email']) === $email) {
                        $user = $u;
                        break;
                    }
                }
            } else {
                $user = db_fetch_one('SELECT user_id, first_name FROM "USER" WHERE email = :email', ['email' => $email]);
            }

            // Always pretend we sent it to prevent email enumeration attacks
            if ($user !== null) {
                $otp = sprintf('%06d', random_int(0, 999999));
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_expires'] = time() + (15 * 60);

                $subject = 'Password Reset Request - Cleck E-Mart';
                $firstName = $user['FIRST_NAME'] ?? 'Customer';
                
                $htmlBody = "
                    <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">
                        <h2>Password Reset Request</h2>
                        <p>Hi {$firstName},</p>
                        <p>We received a request to reset the password for your Cleck E-Mart account.</p>
                        <p>Your password reset OTP is: <strong style=\"font-size: 24px; color: #22c55e;\">{$otp}</strong></p>
                        <p>This code will expire in 15 minutes.</p>
                        <p>If you did not request a password reset, please ignore this email.</p>
                    </div>
                ";
                
                $altBody = "Hi {$firstName},\n\nYour password reset OTP is: {$otp}\n\nThis code will expire in 15 minutes.";

                send_email($email, $subject, $htmlBody, $altBody);
            }
            
            // Redirect to reset password page regardless of whether the email exists
            redirect('reset-password.php');
        } catch (Throwable $e) {
            $error = 'An error occurred while processing your request. Please try again.';
            error_log('Forgot password error: ' . $e->getMessage());
        }
    }
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="auth-page">
    <div class="container auth-container">
        <section class="auth-section">
            <h1 class="auth-title">Forgot Password</h1>
            <p class="auth-description">Enter your email address and we'll send you an OTP to reset your password.</p>

            <?php if ($error !== null): ?>
                <div class="page-message page-message--error" role="alert"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="forgot-password.php" novalidate>
                <div class="form-group">
                    <label for="forgot-email">Email Address</label>
                    <input type="email" id="forgot-email" name="email" required autocomplete="email" />
                </div>

                <button class="button auth-submit" type="submit">Send Reset OTP</button>
            </form>
            
            <div class="auth-footer" style="text-align: center; margin-top: 2rem;">
                <p><a href="auth.php?mode=login" style="color: var(--primary); text-decoration: underline;">Back to Login</a></p>
            </div>
        </section>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
