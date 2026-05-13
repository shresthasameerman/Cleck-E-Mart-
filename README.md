# Cleck E-Mart

Cleck E-Mart is a PHP storefront prototype focused on clean UI, responsive layouts, and frontend checkout/profile interactions.

## Overview

The project currently includes:

- Storefront browsing pages (home, category, product)
- Checkout flow pages (cart, collection slot)
- Account pages (auth, customer dashboard, profile dashboard)
- A contact page with business details and message form
- Shared layout components (header and footer)
- Global styling in a single CSS file
- Interactive behavior handled in a single JavaScript file

## Project Structure

```text
index.php
category.php
product.php
cart.php
collection.php
auth.php
customer.php
profile.php
contact.php
logout.php
payment.php
trader-add-product.php
trader-dashboard.php
trader-profile.php
db_connect.php
components/
  header.php
  footer.php
assets/
  css/
    styles.css
  images/
    products/
  js/
    cart.js
    script.js
data/
  offline_db.json
lib/
  apex_api.php
  apex_auth.php
  apex_cart.php
  auth_helpers.php
  bootstrap.php
  cart_api.php
  cart_helpers.php
  oci_db.php
  offline_store.php
  product_images.php
  trader_helpers.php
README.md
```

## Pages

### Home

File: [index.php](index.php)

- Hero/search section
- Featured product cards
- Delivery and browse call-to-action area

### Category

File: [category.php](category.php)

- Search input for products
- Trader and price filter controls
- Product card grid
- Links to product details

### Product Detail

File: [product.php](product.php)

- Product image and trader details
- Product name, rating, and description
- Quantity selector
- Add-to-basket button (routes to cart)

### Basket

File: [cart.php](cart.php)

- Basket item rows with quantity controls
- Line totals and order summary
- Continue-to-collection action

### Collection Slot

File: [collection.php](collection.php)

- Calendar-based day selection
- Time slot availability and selection
- Capacity-aware reservation feedback
- Confirmation step before payment

### Authentication

File: [auth.php](auth.php)

- Sign Up and Login modes
- Frontend tab switch and optional URL mode query
- Role selection in sign-up form (Customer/Trader)

### Customer Dashboard

File: [customer.php](customer.php)

- Profile summary/sidebar
- Recent orders panel
- Account information form
- Pricing summary area

### Profile Dashboard

File: [profile.php](profile.php)

- Multi-panel account UI (orders, account, history, reviews, password)
- Client-side tab navigation
- Account update and password forms

### Trader Dashboard

File: [trader-dashboard.php](trader-dashboard.php)

- Sales summary cards
- Sold products table
- Refill alerts and stock overview

### Trader Profile Settings

File: [trader-profile.php](trader-profile.php)

- Trader account details
- Shop branding and description updates
- Public contact fields

### Add Product

File: [trader-add-product.php](trader-add-product.php)

- New product form
- Category, stock, and pricing fields
- Publish or save as draft

### Contact

File: [contact.php](contact.php)

- Contact information cards
- Collection-hour details
- Message form

## Shared Components

### Header

File: [components/header.php](components/header.php)

- Global meta/title setup
- Site logo and primary navigation
- Cart and account action icons

### Footer

File: [components/footer.php](components/footer.php)

- Brand summary
- Quick links for key sections/pages

## Styling

File: [assets/css/styles.css](assets/css/styles.css)

The stylesheet controls:

- Theme variables and visual palette
- Responsive layouts across pages
- Forms, cards, tables, and dashboard sections
- Page-specific components (category/cart/collection/profile/contact)

## JavaScript Behavior

File: [assets/js/script.js](assets/js/script.js)

Current scripts include:

- Mobile navigation toggle with outside-click and Escape handling
- Auth mode switching (signup/login)
- Profile panel switching (orders/account/history/reviews/password)
- Category filters (search, trader, price, empty state)
- Cart quantity updates and summary recalculation
- Collection calendar and slot-capacity logic (uses localStorage)
- Product page quantity +/- controls

## Run Locally

From the project root:

```bash
php -S localhost:8000
```

Open:

```text
http://localhost:8000/
```

## Notes

- Core auth, product browsing, cart, and profile flows are connected to the shared data layer (offline JSON by default, Oracle when enabled).
- Some pages (`collection.php`, `contact.php`, and parts of `customer.php`) are still mostly UI-first and can be connected to backend endpoints next.
- `logout.php` is implemented and wired from the header/profile actions.

## Oracle (OCI8) Integration

The app now runs in **offline mode by default** for local development.

- Default driver: `DB_DRIVER=offline`
- Offline data file: `data/offline_db.json` (auto-created with seed data)
- Offline seed now mirrors the provided Cleck E-Mart sample dataset (16 users, 20 products, carts, orders, payments, invoices, reviews).
- No database extension is required for offline mode.

Demo login in offline mode:

- Customer: `aarav.sharma@gmail.com`
- Trader: `robert.firth@firth-butchers.co.uk`
- Admin: `admin@cleckemart.co.uk`
- Password for all seeded users: `password123`

Quick login test:

1. Run `php -S localhost:8000`
2. Open `http://localhost:8000/auth.php?mode=login`
3. Use the customer account `aarav.sharma@gmail.com` with password `password123`
4. After sign-in, open `category.php` and `profile.php` to verify data-backed pages

To force Oracle mode, set:

```bash
export DB_DRIVER=oracle
```

This project now includes Oracle-backed flows for:

- Sign up and login (`auth.php`)
- Session logout (`logout.php`)
- Category/product listing from database (`category.php`, `product.php`)
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

To test the database connection:

```bash
php db_connect.php
```

If successful, you'll see:
```
🎉 Boom! PHP is successfully connected to the Cleck E-Mart Database!
```

**Note:** This file uses hardcoded credentials for development. For production, use environment variables or secure credential management:

```php
$db_user = getenv('ORACLE_USERNAME');
$db_pass = getenv('ORACLE_PASSWORD');
```

### Environment Variables

Set these before running PHP:

```bash
export ORACLE_USERNAME=ADMIN
export ORACLE_PASSWORD=Oracle123#Apex
export ORACLE_CONNECTION_STRING=localhost:1521/XEPDB1
```

Defaults used if variables are not set:

- `ORACLE_USERNAME=ADMIN`
- `ORACLE_PASSWORD=Oracle123#Apex`
- `ORACLE_CONNECTION_STRING=localhost:1521/XEPDB1`

### Run

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/`.

### Important Schema Notes

- Your SQL uses a supertype/subtype model where `CUSTOMER.customer_id` and `TRADER.trader_id` match `USER.user_id`. This is fully respected by the integration.
- IDs are currently generated with `MAX(id) + 1` in PHP helper code. For production, replace this with Oracle sequences + triggers (or identity strategy) to avoid race conditions under concurrent traffic.

## Suggested Next Steps

1. Add seed data for `CATEGORY`, `SHOP`, and `PRODUCT` so browsing pages have initial records.
2. Implement order placement (create rows in `ORDER`, `ORDER_ITEM`, `PAYMENT`, `INVOICE`).
3. Move ID generation to Oracle sequences for production safety.
4. Add CSRF tokens to forms and stricter authorization checks for trader/admin pages.
5. Add integration tests for auth, cart, and profile update flows.
