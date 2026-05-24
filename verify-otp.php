<?php
// This script verifies the One-Time Password sent to a user's email during the password reset flow.

require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_auth.php';

$errors = [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');

if (!isset($_SESSION['pending_signup']) || !isset($_SESSION['signup_otp'])) {
    set_flash('error', 'No pending registration found. Please sign up again.');
    redirect('auth.php?mode=signup');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim((string) ($_POST['otp'] ?? ''));

    if ($enteredOtp === '') {
        $errors[] = 'Please enter the OTP sent to your email.';
    } elseif ($enteredOtp !== $_SESSION['signup_otp']) {
        $errors[] = 'Invalid OTP. Please try again.';
    } else {
        // OTP matches! Complete the registration process
        $signupData = $_SESSION['pending_signup'];
        $firstName = $signupData['first_name'];
        $lastName = $signupData['last_name'];
        $email = $signupData['email'];
        $password = $signupData['password'];
        $role = $signupData['role'];

        try {
            if (db_is_offline()) {
                $newUser = offline_create_account(
                    $firstName,
                    $lastName,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role
                );
            } else {
                db_begin();
                $userId = db_next_id('"USER"', 'user_id');

                db_execute(
                    'INSERT INTO "USER" (user_id, first_name, last_name, email, password, "ROLE", created_at)
                     VALUES (:user_id, :first_name, :last_name, :email, :password, :role, CURRENT_TIMESTAMP)',
                    [
                        'user_id' => $userId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => $role,
                    ]
                );

                if ($role === 'TRADER') {
                    db_execute(
                        'INSERT INTO TRADER (trader_id, brand_name) VALUES (:trader_id, :brand_name)',
                        [
                            'trader_id' => $userId,
                            'brand_name' => null,
                        ]
                    );

                    // Create a default SHOP for the trader
                    $shopId = db_next_id('SHOP', 'shop_id');
                    $defaultShopName = $firstName . "'s Shop";
                    db_execute(
                        'INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status) 
                         VALUES (:shop_id, :trader_id, :shop_name, :shop_description, :shop_logo, :shop_status)',
                        [
                            'shop_id' => $shopId,
                            'trader_id' => $userId,
                            'shop_name' => $defaultShopName,
                            'shop_description' => 'Welcome to our shop!',
                            'shop_logo' => null,
                            'shop_status' => 'PENDING',
                        ]
                    );
                } else {
                    db_execute(
                        'INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (:customer_id, :loyalty_points)',
                        [
                            'customer_id' => $userId,
                            'loyalty_points' => 0,
                        ]
                    );
                }

                db_commit();

                $newUser = db_fetch_one(
                    'SELECT user_id, first_name, last_name, email, "ROLE" AS role FROM "USER" WHERE user_id = :user_id',
                    ['user_id' => $userId]
                );
            }

            if ($newUser === null) {
                throw new RuntimeException('Unable to load newly created user session.');
            }

            // Registration successful! Clear the pending session data
            unset($_SESSION['pending_signup'], $_SESSION['signup_otp']);

            session_regenerate_id(true);
            login_session($newUser);
            set_flash('success', 'Account verified and created successfully. Welcome to Cleck E-Mart.');
            
            $newRole = strtoupper((string) $newUser['ROLE']);
            if ($newRole === 'TRADER') {
                redirect('trader-profile.php');
            } else {
                redirect('index.php');
            }

        } catch (Throwable $exception) {
            db_rollback();
            $errors[] = 'Signup failed during verification: ' . $exception->getMessage();
        }
    }
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="auth-intro" aria-labelledby="auth-title">
        <div class="container auth-intro__inner">
            <p class="auth-intro__eyebrow">Verification Required</p>
            <h1 id="auth-title">Enter your OTP</h1>
        </div>
    </section>

    <section class="auth" aria-label="OTP Verification">
        <div class="container">
            <?php if ($flashSuccess !== null): ?>
                <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
            <?php endif; ?>

            <?php if ($flashError !== null): ?>
                <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="page-message page-message--error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="auth-card">
                <section class="auth-panel is-active">
                    <p style="text-align: center; margin-bottom: 2rem; color: #555;">
                        We sent a 6-digit verification code to <strong><?php echo e($_SESSION['pending_signup']['email']); ?></strong>.<br>
                        Please enter it below to complete your registration.
                    </p>
                    <form class="auth-form" action="verify-otp.php" method="post" novalidate>
                        <label>
                            <span>6-Digit OTP*</span>
                            <input type="text" name="otp" required placeholder="123456" pattern="\d{6}" title="Please enter the 6 digit OTP" autocomplete="one-time-code" style="text-align: center; letter-spacing: 5px; font-size: 1.5rem; font-weight: bold;" maxlength="6" />
                        </label>

                        <button class="auth-submit" type="submit">Verify & Register</button>
                    </form>
                    
                    <p class="auth-switch-text" style="margin-top: 1.5rem;">
                        Didn't receive the email? <br>Check your spam folder or 
                        <a href="auth.php?mode=signup" style="color: var(--primary); text-decoration: underline;">start over</a>.
                    </p>
                </section>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
