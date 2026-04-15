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

- This is currently frontend-first. Most forms use placeholder `action="#"` targets.
- Integration with database/session/auth endpoints is still pending.
- Some links (for example logout flow) may require backend routes not yet implemented.

## Suggested Next Steps

1. Connect forms to real PHP endpoints.
2. Add server-side validation and error feedback.
3. Persist users, products, cart, and orders in a database.
4. Add authenticated session flow and role-based access control.
5. Replace static product/order data with dynamic records.
