<?php
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/apex_auth.php';

$errors = [];
$activeMode = 'signup';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['auth_action'] ?? '';
    $activeMode = $action === 'login' ? 'login' : 'signup';

    if ($action === 'signup') {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['signup_email'] ?? '')));
        $password = (string) ($_POST['signup_password'] ?? '');
        $accountType = strtolower(trim((string) ($_POST['account_type'] ?? 'customer')));
        $termsAccepted = !empty($_POST['terms']);

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            $errors[] = 'Please complete all required sign-up fields.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if (!$termsAccepted) {
            $errors[] = 'You must accept the terms and conditions.';
        }
        if (!in_array($accountType, ['customer', 'trader'], true)) {
            $errors[] = 'Invalid account type selected.';
        }

        if ($errors === []) {
            try {
                $existing = db_is_offline()
                    ? offline_user_by_email($email)
                    : db_fetch_one(
                        'SELECT user_id FROM "USER" WHERE LOWER(email) = LOWER(:email)',
                        ['email' => $email]
                    );

                if ($existing !== null) {
                    $errors[] = 'An account with this email already exists.';
                } else {
                    $role = strtoupper($accountType);

                    // Generate a 6-digit OTP
                    $otp = sprintf("%06d", mt_rand(1, 999999));

                    // Log the OTP to the terminal for local testing (since local mail() is restricted)
                    error_log("\n=======================================================");
                    error_log("🚨 NEW OTP GENERATED FOR: " . $email);
                    error_log("👉 OTP CODE: " . $otp);
                    error_log("=======================================================\n");

                    // Store pending signup data in session (do NOT insert to DB yet)
                    $_SESSION['pending_signup'] = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => $password,
                        'role' => $role
                    ];
                    $_SESSION['signup_otp'] = $otp;

                    // Send the OTP via email
                    $subject = "Your Cleck E-Mart Verification Code";
                    $message = "
                    <html>
                    <head>
                        <title>Verify your email</title>
                    </head>
                    <body>
                        <h2>Cleck E-Mart Registration</h2>
                        <p>Hello $firstName,</p>
                        <p>Thank you for signing up! Your OTP verification code is: <strong style='font-size: 24px; color: #2c3e50;'>$otp</strong></p>
                        <p>Please enter this code on the website to verify your email and complete your registration.</p>
                        <br>
                        <p>Best Regards,<br>The Cleck E-Mart Team</p>
                    </body>
                    </html>
                    ";
                    
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: Cleck E-Mart <noreply@cleck-e-mart.com>" . "\r\n";

                    @mail($email, $subject, $message, $headers);

                    set_flash('success', 'An OTP has been sent to your email. Please verify to continue.');
                    redirect('verify-otp.php');
                }
            } catch (Throwable $exception) {
                db_rollback();
                $errors[] = 'Signup failed: ' . $exception->getMessage();
            }
        }
    }

    if ($action === 'login') {
        $email = strtolower(trim((string) ($_POST['login_email'] ?? '')));
        $password = (string) ($_POST['login_password'] ?? '');

        if ($email === '' || $password === '') {
            $errors[] = 'Please enter your email and password.';
        }

        if ($errors === []) {
            try {
                $user = null;
                $apexError = null;

                // Try APEX authentication first if enabled
                if (apex_auth_enabled()) {
                    try {
                        $user = apex_login_user($email, $password);
                    } catch (Throwable $exception) {
                        $apexError = $exception->getMessage();
                        error_log('APEX login exception: ' . $apexError);
                        // Fall through to local auth
                    }
                }

                // Fall back to local database authentication if APEX didn't work
                if ($user === null && db_is_offline()) {
                    $user = offline_user_by_email($email);
                    if ($user !== null && !password_verify($password, (string) $user['PASSWORD'])) {
                        $user = null;
                    }
                } elseif ($user === null && !db_is_offline()) {
                    $user = db_fetch_one(
                        'SELECT user_id, first_name, last_name, email, password, "ROLE" AS role
                         FROM "USER"
                         WHERE LOWER(email) = LOWER(:email)',
                        ['email' => $email]
                    );

                    if ($user !== null) {
                        $dbPassword = (string) $user['PASSWORD'];
                        // Allow login if it's a valid bcrypt hash OR if it exactly matches the dummy seed data (e.g., 'hashed_Pass@123')
                        if (!password_verify($password, $dbPassword) && $password !== $dbPassword) {
                            $user = null;
                        }
                    }
                }

                if ($user === null) {
                    $errors[] = 'Invalid email or password.';
                } else {
                    session_regenerate_id(true);
                    login_session($user);
                    set_flash('success', 'Welcome back, ' . (string) $user['FIRST_NAME'] . '.');
                    
                    // Route based on role
                    $role = strtoupper((string) $user['ROLE']);
                    if ($role === 'TRADER') {
                        redirect('trader-shops.php');
                    } else {
                        redirect('index.php');
                    }
                }
            } catch (Throwable $exception) {
                $errors[] = 'Login failed: ' . $exception->getMessage();
            }
        }
    }
}

if (isset($_GET['mode']) && in_array($_GET['mode'], ['signup', 'login'], true)) {
    $activeMode = (string) $_GET['mode'];
}

$flashSuccess = get_flash('success');
$flashError = get_flash('error');

// Reuses site-wide header/navigation to keep auth pages in the same theme.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="auth-page">
    <!-- Intro line mirrors the homepage tone while keeping auth as the core action. -->
    <section class="auth-intro" aria-labelledby="auth-title">
        <div class="container auth-intro__inner">
            <p class="auth-intro__eyebrow">Welcome to Cleck E-Mart</p>
            <h1 id="auth-title">Get started with your account</h1>
        </div>
    </section>

    <!-- Single card contains both flows and switches with accessible tab buttons. -->
    <section class="auth" aria-label="Account access">
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

            <div class="auth-card" data-auth-card>
                <div class="auth-tabs" role="tablist" aria-label="Choose account flow">
                    <button class="auth-tab<?php echo $activeMode === 'signup' ? ' is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo $activeMode === 'signup' ? 'true' : 'false'; ?>" aria-controls="signup-panel" id="signup-tab" data-auth-switch="signup">
                        Sign Up
                    </button>
                    <button class="auth-tab<?php echo $activeMode === 'login' ? ' is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo $activeMode === 'login' ? 'true' : 'false'; ?>" aria-controls="login-panel" id="login-tab" data-auth-switch="login">
                        Login
                    </button>
                </div>

                <!-- Sign up form follows your wireframe field order. -->
                <section class="auth-panel<?php echo $activeMode === 'signup' ? ' is-active' : ''; ?>" id="signup-panel" role="tabpanel" aria-labelledby="signup-tab" data-auth-panel="signup"<?php echo $activeMode === 'signup' ? '' : ' hidden'; ?>>
                    <!--
                        Sign up backend integration guide:
                        - Set action to your registration endpoint (example: signup.php)
                        - Validate/sanitize: first_name, last_name, signup_email, signup_password, terms
                        - Read "account_type" to identify the user role (customer or trader)
                        - Hash password server-side before storing (password_hash in PHP)
                    -->
                    <form class="auth-form" action="auth.php" method="post" novalidate>
                        <input type="hidden" name="auth_action" value="signup" />
                        <!--
                            Account type selector (wireframe requirement):
                            - customer: regular buyer account
                            - trader: seller/business account
                            Backend should persist this value in the users table (example column: account_type).
                        -->
                        <fieldset class="auth-role" aria-label="Choose account type">
                            <legend class="sr-only">Account type</legend>

                            <label class="auth-role__option">
                                <input type="radio" name="account_type" value="customer" <?php echo (($_POST['account_type'] ?? 'customer') === 'customer') ? 'checked' : ''; ?> />
                                <span>Customer</span>
                            </label>

                            <label class="auth-role__option">
                                <input type="radio" name="account_type" value="trader" <?php echo (($_POST['account_type'] ?? '') === 'trader') ? 'checked' : ''; ?> />
                                <span>Trader</span>
                            </label>
                        </fieldset>

                        <div class="auth-grid auth-grid--two">
                            <label>
                                <span>First name*</span>
                                <input type="text" name="first_name" required autocomplete="given-name" placeholder="Enter first name" value="<?php echo e($_POST['first_name'] ?? ''); ?>" />
                            </label>
                            <label>
                                <span>Last name*</span>
                                <input type="text" name="last_name" required autocomplete="family-name" placeholder="Enter last name" value="<?php echo e($_POST['last_name'] ?? ''); ?>" />
                            </label>
                        </div>

                        <label>
                            <span>Email*</span>
                            <input type="email" name="signup_email" required autocomplete="email" placeholder="name@example.com" value="<?php echo e($_POST['signup_email'] ?? ''); ?>" />
                        </label>

                        <label>
                            <span>Password*</span>
                            <div class="password-wrapper">
                                <input type="password" name="signup_password" required autocomplete="new-password" placeholder="Create a strong password" />
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                            </div>
                        </label>

                        <label class="auth-check">
                            <input type="checkbox" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> />
                            <span>I agree to the terms and conditions</span>
                        </label>

                        <button class="auth-submit" type="submit">Sign up</button>
                    </form>

                    <p class="auth-switch-text">
                        Already have an account?
                        <button class="auth-inline-action" type="button" data-auth-switch="login">Sign in</button>
                    </p>
                </section>

                <!-- Login view keeps the same visual rhythm as the sign up form. -->
                <section class="auth-panel<?php echo $activeMode === 'login' ? ' is-active' : ''; ?>" id="login-panel" role="tabpanel" aria-labelledby="login-tab" data-auth-panel="login"<?php echo $activeMode === 'login' ? '' : ' hidden'; ?>>
                    <div class="auth-login-layout">
                        <!-- Decorative block from wireframe; no business logic attached. -->
                        <div class="auth-art" aria-hidden="true">
                            <span class="auth-art__circle"></span>
                            <span class="auth-art__triangle"></span>
                        </div>

                        <!--
                            Login backend integration guide:
                            - Set action to your auth endpoint (example: login.php)
                            - Verify credentials against users table
                            - Create session and redirect on success
                        -->
                        <form class="auth-form" action="auth.php" method="post" novalidate>
                            <input type="hidden" name="auth_action" value="login" />
                            <label>
                                <span>Email address</span>
                                <input type="email" name="login_email" required autocomplete="email" placeholder="name@example.com" value="<?php echo e($_POST['login_email'] ?? ''); ?>" />
                            </label>

                            <label>
                                <span>Password</span>
                                <div class="password-wrapper">
                                    <input type="password" name="login_password" required autocomplete="current-password" placeholder="Enter password" />
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">Show</button>
                                </div>
                            </label>

                            <div class="auth-row">
                                <label class="auth-check">
                                    <input type="checkbox" name="remember" />
                                    <span>Remember me</span>
                                </label>
                                <a href="#">Forgot Password?</a>
                            </div>

                            <button class="auth-submit" type="submit">Sign in</button>
                        </form>
                    </div>

                    <p class="auth-switch-text">
                        Don't have an account?
                        <button class="auth-inline-action" type="button" data-auth-switch="signup">Sign Up</button>
                    </p>
                </section>
            </div>
        </div>
    </section>
</main>
<?php
// Shared footer keeps legal/quick links unified across pages.
require __DIR__ . '/components/footer.php';
?>
