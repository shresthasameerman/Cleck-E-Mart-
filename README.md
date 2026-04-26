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
components/
  header.php
  footer.php
assets/
  css/
    styles.css
  js/
    script.js
  images/
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

- Core auth, product browsing, cart, and profile flows are now connected to Oracle.
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

### Prerequisites

1. Oracle DB with your schema tables created.
2. PHP OCI8 extension enabled (`php -m | grep oci8`).
3. Oracle client/network connectivity from your PHP runtime.

If `DB_DRIVER=offline`, these Oracle prerequisites are not needed.

### Environment Variables

Set these before running PHP:

```bash
export ORACLE_USERNAME=your_username
export ORACLE_PASSWORD=your_password
export ORACLE_CONNECTION_STRING=host/service_name
```

Defaults used if variables are not set:

- `ORACLE_USERNAME=system`
- `ORACLE_PASSWORD=oracle`
- `ORACLE_CONNECTION_STRING=localhost/XEPDB1`

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
