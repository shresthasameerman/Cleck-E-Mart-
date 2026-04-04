# Cleck E-Mart

Cleck E-Mart is a small PHP storefront prototype built around a clean, responsive shopping and authentication experience.

## What this project contains

- A homepage with a hero search area, featured product cards, and a call-to-action section
- A combined Sign Up / Login page based on the wireframe
- A shared header and footer so the layout stays consistent across pages
- A single CSS file that controls the site theme, spacing, and responsive layout
- A small JavaScript file for navigation and auth tab switching

## Project structure

```text
index.php
auth.php
components/
  header.php
  footer.php
assets/
  css/
    styles.css
  js/
    script.js
  images/
```

## Page flow

### Homepage

File: [index.php](index.php)

This is the main storefront landing page. It includes:

- a search area at the top
- featured product cards
- a delivery / browsing call-to-action

### Authentication page

File: [auth.php](auth.php)

This page contains both account flows:

- Sign Up
- Login

The account icon in the header opens this page.

The Sign Up form includes a role selector for:

- Customer
- Trader

That value is intended to be stored in the database during registration as `account_type`.

## Shared components

### Header

File: [components/header.php](components/header.php)

The header contains:

- the site logo
- mobile navigation
- cart icon placeholder
- account icon that opens the auth page

### Footer

File: [components/footer.php](components/footer.php)

The footer contains:

- the brand text
- quick links back to important sections

## Styling

File: [assets/css/styles.css](assets/css/styles.css)

This file controls:

- the main color palette
- layout spacing
- card styling
- auth page styling
- responsive behavior for mobile, tablet, and desktop

Theme notes:

- Light background
- Beige surfaces
- Dark neutral text
- Soft green accent

## JavaScript behavior

File: [assets/js/script.js](assets/js/script.js)

This file currently handles:

- opening and closing the mobile navigation menu
- closing the menu when clicking outside it
- closing the menu with Escape
- switching between Sign Up and Login tabs on the auth page

## How to run

From the project root, start PHP's built-in server:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/
```

## Backend integration points

This project is currently frontend-first, so the form actions are placeholders.

When you connect a database, these are the main places to wire in backend logic:

- Search form in [index.php](index.php)
- Sign Up form in [auth.php](auth.php)
- Login form in [auth.php](auth.php)

Suggested database fields for users:

- first_name
- last_name
- email
- password_hash
- account_type
- created_at

## Suggested next steps

1. Replace `action="#"` with real PHP endpoints.
2. Add server-side validation for signup and login.
3. Store user accounts in a database.
4. Add session handling after login.
5. Connect the product cards to real database records.

## Notes for contributors

- Keep the shared header and footer in sync across pages.
- Reuse the existing CSS variables for any new UI so the theme stays consistent.
- Keep comments focused on intent, backend integration, and page structure.
- Use the same naming pattern for classes when adding new sections.
