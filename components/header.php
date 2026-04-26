<?php
require_once __DIR__ . '/../lib/bootstrap.php';

$pageTitle = $pageTitle ?? 'Cleck E-Mart';
$metaDescription = $metaDescription ?? 'A responsive storefront homepage built from a wireframe.';

$headerIsLoggedIn = is_logged_in();
$headerAccountHref = $headerIsLoggedIn ? 'profile.php' : 'auth.php?mode=login';
$headerAccountLabel = $headerIsLoggedIn ? 'Account profile' : 'Login / signup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>" />
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <script defer src="assets/js/script.js"></script>
</head>
<body class="site-body" id="top">
    <!-- Accessibility: allows keyboard users to skip repeated navigation quickly. -->
    <a class="skip-link" href="#main-content">Skip to content</a>

    <!-- Site header / navigation -->
    <header class="site-header">
        <div class="container site-header__bar">
            <a class="brand" href="index.php" aria-label="Cleck E-Mart home">
                <img class="brand__logo" src="assets/images/Primary_Logo.png" alt="Cleck E-Mart" />
            </a>

            <nav class="site-nav" aria-label="Primary navigation" data-nav>
                <!-- Mobile menu toggle; JavaScript updates aria-expanded and panel state. -->
                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-nav-toggle>
                    <span class="sr-only">Toggle navigation</span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                </button>

                <div class="site-nav__panel" id="primary-navigation" data-nav-panel>
                    <a href="index.php#featured-title">Featured</a>
                    <a href="category.php">Browse Category</a>
                    <a href="index.php#cta-title">Delivery</a>
                </div>
            </nav>

            <div class="site-actions" aria-label="Account and cart actions">
                <a class="icon-button" href="cart.php" aria-label="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M6 6h15l-1.5 7.5H8.5L7.7 10H5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9.5" cy="19" r="1.25" fill="currentColor"/>
                        <circle cx="17.5" cy="19" r="1.25" fill="currentColor"/>
                    </svg>
                </a>
                <!-- Account icon routes directly to the customer profile page. -->
                <a class="icon-button icon-button--account" href="<?php echo e($headerAccountHref); ?>" aria-label="<?php echo e($headerAccountLabel); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="8.2" r="3.2" fill="none" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M6.5 19.2c1.6-3 3.8-4.5 5.5-4.5s3.9 1.5 5.5 4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </a>
                <?php if ($headerIsLoggedIn): ?>
                    <a class="icon-button" href="logout.php" aria-label="Sign out">Sign out</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
