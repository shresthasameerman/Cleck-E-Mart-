<?php
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
            <div class="auth-card" data-auth-card>
                <div class="auth-tabs" role="tablist" aria-label="Choose account flow">
                    <button class="auth-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="signup-panel" id="signup-tab" data-auth-switch="signup">
                        Sign Up
                    </button>
                    <button class="auth-tab" type="button" role="tab" aria-selected="false" aria-controls="login-panel" id="login-tab" data-auth-switch="login">
                        Login
                    </button>
                </div>

                <!-- Sign up form follows your wireframe field order. -->
                <section class="auth-panel is-active" id="signup-panel" role="tabpanel" aria-labelledby="signup-tab" data-auth-panel="signup">
                    <!--
                        Sign up backend integration guide:
                        - Set action to your registration endpoint (example: signup.php)
                        - Validate/sanitize: first_name, last_name, signup_email, signup_password, terms
                        - Read "account_type" to identify the user role (customer or trader)
                        - Hash password server-side before storing (password_hash in PHP)
                    -->
                    <form class="auth-form" action="#" method="post" novalidate>
                        <!--
                            Account type selector (wireframe requirement):
                            - customer: regular buyer account
                            - trader: seller/business account
                            Backend should persist this value in the users table (example column: account_type).
                        -->
                        <fieldset class="auth-role" aria-label="Choose account type">
                            <legend class="sr-only">Account type</legend>

                            <label class="auth-role__option">
                                <input type="radio" name="account_type" value="customer" checked />
                                <span>Customer</span>
                            </label>

                            <label class="auth-role__option">
                                <input type="radio" name="account_type" value="trader" />
                                <span>Trader</span>
                            </label>
                        </fieldset>

                        <div class="auth-grid auth-grid--two">
                            <label>
                                <span>First name*</span>
                                <input type="text" name="first_name" required autocomplete="given-name" placeholder="Enter first name" />
                            </label>
                            <label>
                                <span>Last name*</span>
                                <input type="text" name="last_name" required autocomplete="family-name" placeholder="Enter last name" />
                            </label>
                        </div>

                        <label>
                            <span>Email*</span>
                            <input type="email" name="signup_email" required autocomplete="email" placeholder="name@example.com" />
                        </label>

                        <label>
                            <span>Password*</span>
                            <input type="password" name="signup_password" required autocomplete="new-password" placeholder="Create a strong password" />
                        </label>

                        <label class="auth-check">
                            <input type="checkbox" name="terms" required />
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
                <section class="auth-panel" id="login-panel" role="tabpanel" aria-labelledby="login-tab" data-auth-panel="login" hidden>
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
                        <form class="auth-form" action="#" method="post" novalidate>
                            <label>
                                <span>Email address</span>
                                <input type="email" name="login_email" required autocomplete="email" placeholder="name@example.com" />
                            </label>

                            <label>
                                <span>Password</span>
                                <input type="password" name="login_password" required autocomplete="current-password" placeholder="Enter password" />
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
