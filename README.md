# Cleck E-Mart

Cleck E-Mart is a clean, responsive storefront homepage built with PHP includes, semantic HTML, modular CSS, and vanilla JavaScript.

## What’s Included

- A polished homepage based on the provided wireframe
- Reusable `header.php` and `footer.php` components
- Mobile-first responsive styling
- Hamburger navigation for smaller screens
- A lightweight JavaScript toggle for the menu

## Project Structure

```text
project-root/
├── index.php
├── db_connect.php
├── README.md
├── components/
│   ├── header.php
│   └── footer.php
└── assets/
    ├── css/
    │   └── styles.css
    ├── js/
    │   └── script.js
    └── images/
        ├── Primary_Logo.png
        └── product-placeholder.svg
```

## How to Run

From the project root, start PHP’s built-in server:

```bash
php -S 0.0.0.0:8000
```

Open the site in your browser:  
http://localhost:8000

_(If using another device, replace `localhost` with your machine’s IP address)_

If the page does not load from another device, allow the port through your firewall:

```bash
sudo ufw allow 8000/tcp
```

## Files

### `index.php`

Loads the homepage and includes the shared header and footer.

### `components/header.php`

Contains the site header, logo, navigation, and action icons.

### `components/footer.php`

Contains the footer markup and quick links.

### `assets/css/styles.css`

Contains the full responsive design system, layout styles, and component styling.

### `assets/js/script.js`

Controls the mobile hamburger navigation.

## Customization

- Replace `assets/images/Primary_Logo.png` with your final logo if needed.
- Update homepage content in `index.php`.
- Adjust colors, spacing, and typography in `assets/css/styles.css`.
- Extend the mobile menu behavior in `assets/js/script.js` if you add more navigation items.

## Notes

- This repo is frontend-first and does not require a database for the current homepage.
- `db_connect.php` can remain in the project if you plan to add backend features later.
- Add-to-cart and cart quantity updates (`product.php`, `cart.php`)
- Profile updates and password change (`profile.php`)

### New Backend Files

- `lib/bootstrap.php` (session + shared helpers)
- `lib/oci_db.php` (Oracle connection + query helpers)
- `lib/auth_helpers.php` (login/session guards)
- `lib/cart_helpers.php` (cart operations)
- `lib/trader_helpers.php` (trader dashboard/profile/product helpers)

### Prerequisites

1. Oracle DB with your schema tables created.
2. PHP OCI8 extension enabled (`php -m | grep oci8`).
3. Oracle client/network connectivity from your PHP runtime.

If `DB_DRIVER=offline`, these Oracle prerequisites are not needed.

### Database Connection Setup

File: [db_connect.php](db_connect.php)

This file establishes a direct connection to your Oracle database using OCI8. It contains:

- **Database Username**: `ADMIN`
- **Database Password**: `Oracle123#Apex`
- **Connection String**: `localhost:1521/XEPDB1`

To test database connection:

```bash
php db_connect.php
```

If successful, you'll see:
```
🎉 Boom! PHP is successfully connected to the Cleck E-Mart Database!
```

For production, use environment variables or secure credentials.

## Important Notes

- This repo is frontend-first but includes Oracle integration for future backend features.
- Use sequences/triggers to manage Oracle IDs safely (avoid MAX(id) + 1 in production).
- See in-code comments for customization and extension.

---

## Suggested Next Steps

1. Add initial seed data for `CATEGORY`, `SHOP`, and `PRODUCT`
2. Implement end-to-end order placement and payment
3. Use Oracle sequences for all IDs
4. Add CSRF & improve security
5. Expand test coverage

---

*Cleck E-Mart — modern PHP & Oracle shopfront with a modular, clean codebase.*
