# Cleck E-Mart

> **Tech Stack:**  
> ![PHP](https://img.shields.io/badge/PHP-81.3%25-blue) 
> ![CSS](https://img.shields.io/badge/CSS-12.4%25-%231572B6) 
> ![JavaScript](https://img.shields.io/badge/JavaScript-3.5%25-F7DF1E) 
> ![PLSQL](https://img.shields.io/badge/PLSQL-2.6%25-%23E60027) 
> ![C++](https://img.shields.io/badge/C++-0.2%25-%2300599C)

Cleck E-Mart is a clean, responsive storefront homepage and e-commerce platform built primarily with PHP, CSS, JavaScript, and integrated with Oracle via PL/SQL.

---

## Technologies Used

- **Backend:** PHP (81.3%), PL/SQL (2.6%), C++ (0.2%)
- **Frontend:** CSS (12.4%), JavaScript (3.5%)
- **Database:** Oracle (OCI8 integration)
- **Others:** Semantic HTML, modular CSS, vanilla JS

---

## What’s Included

- A polished homepage based on the provided wireframe
- Reusable `header.php` and `footer.php` components
- Mobile-first responsive styling
- Hamburger navigation for smaller screens
- Lightweight JavaScript menu toggle
- Oracle DB integration using PHP and PL/SQL

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

## Major Files Overview

- **index.php:** Homepage, includes shared header/footer
- **components/header.php:** Site header, logo, navigation
- **components/footer.php:** Footer markup and links
- **assets/css/styles.css:** Responsive styles and layout
- **assets/js/script.js:** Mobile menu functionality

## Backend Integration

- **db_connect.php:** PHP-OCI8 integration for Oracle
- **lib/bootstrap.php:** Session and helper utilities
- **lib/oci_db.php:** Oracle DB queries
- **lib/auth_helpers.php:** Login/session logic
- **lib/cart_helpers.php:** Cart management
- **lib/trader_helpers.php:** Trader dashboard functions

## Oracle Database Setup

- Make sure Oracle DB is running and schema is created
- Enable OCI8 PHP extension (`php -m | grep oci8`)
- Set environment variables for DB credentials:

```bash
export ORACLE_USERNAME=ADMIN
export ORACLE_PASSWORD=Oracle123#Apex
export ORACLE_CONNECTION_STRING=localhost:1521/XEPDB1
```

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
