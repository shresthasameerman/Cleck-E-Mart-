<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="A responsive storefront homepage built from a wireframe." />
    <title>Cleck E-Mart</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <script defer src="assets/js/script.js"></script>
</head>
<body class="site-body" id="top">
    <a class="skip-link" href="#main-content">Skip to content</a>

    <!-- Site header / navigation -->
    <header class="site-header">
        <div class="container site-header__bar">
            <a class="brand" href="#top" aria-label="Cleck E-Mart home">
                <img class="brand__logo" src="assets/images/Primary_Logo.png" alt="Cleck E-Mart" />
            </a>

            <nav class="site-nav" aria-label="Primary navigation" data-nav>
                <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-nav-toggle>
                    <span class="sr-only">Toggle navigation</span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                    <span class="nav-toggle__bar" aria-hidden="true"></span>
                </button>

                <div class="site-nav__panel" id="primary-navigation" data-nav-panel>
                    <a href="#featured-title">Featured</a>
                    <a href="#cta-title">Delivery</a>
                    <a href="#main-content">Offers</a>
                </div>
            </nav>

            <div class="site-actions" aria-label="Account and cart actions">
                <a class="icon-button" href="#" aria-label="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M6 6h15l-1.5 7.5H8.5L7.7 10H5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9.5" cy="19" r="1.25" fill="currentColor"/>
                        <circle cx="17.5" cy="19" r="1.25" fill="currentColor"/>
                    </svg>
                </a>
                <a class="icon-button" href="#" aria-label="Account">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="8.2" r="3.2" fill="none" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M6.5 19.2c1.6-3 3.8-4.5 5.5-4.5s3.9 1.5 5.5 4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>
