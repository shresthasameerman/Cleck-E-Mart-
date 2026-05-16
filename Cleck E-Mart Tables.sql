-- ==============================================================================
-- 0. DROP EXISTING TABLES (Uncomment to reset your schema)
-- ==============================================================================

DROP TABLE INVOICE CASCADE CONSTRAINTS;
DROP TABLE PAYMENT CASCADE CONSTRAINTS;
DROP TABLE ORDER_ITEM CASCADE CONSTRAINTS;
DROP TABLE "ORDER" CASCADE CONSTRAINTS;
DROP TABLE COUPON CASCADE CONSTRAINTS;
DROP TABLE CART_ITEM CASCADE CONSTRAINTS;
DROP TABLE CART CASCADE CONSTRAINTS;
DROP TABLE REVIEW CASCADE CONSTRAINTS;
DROP TABLE WISHLIST_ITEM CASCADE CONSTRAINTS;
DROP TABLE WISHLIST CASCADE CONSTRAINTS;
DROP TABLE PRODUCT CASCADE CONSTRAINTS;
DROP TABLE SHOP CASCADE CONSTRAINTS;
DROP TABLE DISCOUNT CASCADE CONSTRAINTS;
DROP TABLE COLLECTION_SLOT CASCADE CONSTRAINTS;
DROP TABLE CATEGORY CASCADE CONSTRAINTS;
DROP TABLE CUSTOMER CASCADE CONSTRAINTS;
DROP TABLE TRADER CASCADE CONSTRAINTS;
DROP TABLE ADMIN CASCADE CONSTRAINTS;
DROP TABLE "USER" CASCADE CONSTRAINTS;


-- ==============================================================================
-- 1. USER AND SUBTYPE TABLES 
-- Architecture: Supertype (USER) / Subtype (ADMIN, TRADER, CUSTOMER) pattern.
-- ==============================================================================

CREATE TABLE "USER" (
    user_id NUMBER(10) PRIMARY KEY,
    first_name VARCHAR2(255) NOT NULL,
    last_name VARCHAR2(255) NOT NULL,
    gender VARCHAR2(50),
    email VARCHAR2(255) UNIQUE NOT NULL, -- Used for login authentication
    password VARCHAR2(255) NOT NULL,     -- Stores hashed passwords
    phone_number VARCHAR2(20),
    address VARCHAR2(500),
    "ROLE" VARCHAR2(50) NOT NULL,        -- Differentiates UI access (e.g., ADMIN, TRADER, CUSTOMER)
    profile_picture_path VARCHAR2(500), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- Subtype tables use the identical ID from the USER table (1:1 relationship)
-- They do NOT need identity columns because the ID comes from "USER"
CREATE TABLE ADMIN (
    admin_id NUMBER(10) PRIMARY KEY, 
    privileges VARCHAR2(255),
    CONSTRAINT fk_admin_user FOREIGN KEY (admin_id) REFERENCES "USER" (user_id) ON DELETE CASCADE
);

CREATE TABLE TRADER (
    trader_id NUMBER(10) PRIMARY KEY, 
    brand_name VARCHAR2(255) UNIQUE,     -- Traders must have unique brand names
    pan_number VARCHAR2(255),
    CONSTRAINT fk_trader_user FOREIGN KEY (trader_id) REFERENCES "USER" (user_id) ON DELETE CASCADE
);

CREATE TABLE CUSTOMER (
    customer_id NUMBER(10) PRIMARY KEY, 
    loyalty_points NUMBER(10) DEFAULT 0, -- Defaults to 0 for new sign-ups
    CONSTRAINT fk_customer_user FOREIGN KEY (customer_id) REFERENCES "USER" (user_id) ON DELETE CASCADE
);


-- ==============================================================================
-- 2. MASTER DATA TABLES
-- Reference data used to populate application dropdowns and logic.
-- ==============================================================================

CREATE TABLE CATEGORY (
    category_id NUMBER(10) PRIMARY KEY,
    category_name VARCHAR2(255) UNIQUE NOT NULL,
    category_description CLOB
);

CREATE TABLE COLLECTION_SLOT (
    slot_id NUMBER(10) PRIMARY KEY,
    slot_time VARCHAR2(50) NOT NULL, 
    slot_date DATE NOT NULL, 
    max_orders NUMBER(10) NOT NULL,      -- Used by the UI to calculate remaining capacity
    slot_status VARCHAR2(50) NOT NULL, 
    -- Ensures we don't accidentally create duplicate '10-13' slots on the same day
    CONSTRAINT unique_slot_day_time UNIQUE (slot_date, slot_time) 
);

CREATE TABLE DISCOUNT (
    discount_id NUMBER(10) PRIMARY KEY,
    discount_percentage NUMBER(5, 2), 
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    discount_status VARCHAR2(50) NOT NULL 
);


-- ==============================================================================
-- 3. SHOP AND PRODUCT TABLES
-- Core inventory management for the Traders.
-- ==============================================================================

CREATE TABLE SHOP (
    shop_id NUMBER(10) PRIMARY KEY,
    trader_id NUMBER(10) NOT NULL, 
    shop_name VARCHAR2(255) NOT NULL,
    shop_description CLOB,
    shop_logo VARCHAR2(500), 
    shop_status VARCHAR2(50) DEFAULT 'ACTIVE',
    CONSTRAINT fk_shop_trader FOREIGN KEY (trader_id) REFERENCES TRADER (trader_id),
    -- A single trader cannot have two shops with the exact same name
    CONSTRAINT unique_shop_trader UNIQUE (shop_name, trader_id) 
);

CREATE TABLE PRODUCT (
    product_id NUMBER(10) PRIMARY KEY,
    shop_id NUMBER(10) NOT NULL,
    category_id NUMBER(10) NOT NULL,
    discount_id NUMBER(10),              -- Nullable: Not all products will have an active discount
    product_name VARCHAR2(255) NOT NULL,
    product_description CLOB NOT NULL,
    price NUMBER(10, 2) NOT NULL, 
    stock_quantity NUMBER(10) NOT NULL,
    product_status VARCHAR2(50) NOT NULL, 
    allergy_information VARCHAR2(500),
    min_order NUMBER(10) DEFAULT 1,
    max_order NUMBER(10),
    product_image VARCHAR2(500), 
    CONSTRAINT fk_product_shop FOREIGN KEY (shop_id) REFERENCES SHOP (shop_id),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES CATEGORY (category_id),
    CONSTRAINT fk_product_discount FOREIGN KEY (discount_id) REFERENCES DISCOUNT (discount_id)
);


-- ==============================================================================
-- 4. INTERACTION TABLES (CART, WISHLIST, REVIEW)
-- Captures customer activity before and after checkout.
-- ==============================================================================

CREATE TABLE WISHLIST (
    wishlist_id NUMBER(10) PRIMARY KEY,
    customer_id NUMBER(10) UNIQUE NOT NULL, -- One master wishlist per customer
    created_at DATE DEFAULT SYSDATE,
    CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMER (customer_id)
);

CREATE TABLE WISHLIST_ITEM (
    product_id NUMBER(10) NOT NULL,
    wishlist_id NUMBER(10) NOT NULL,
    added_date DATE DEFAULT SYSDATE,
    CONSTRAINT fk_wli_product FOREIGN KEY (product_id) REFERENCES PRODUCT (product_id),
    CONSTRAINT fk_wli_wishlist FOREIGN KEY (wishlist_id) REFERENCES WISHLIST (wishlist_id),
    -- Prevents adding the exact same product to the wishlist multiple times
    CONSTRAINT unique_wishlist_product UNIQUE (wishlist_id, product_id) 
);

CREATE TABLE REVIEW (
    review_id NUMBER(10) PRIMARY KEY,
    customer_id NUMBER(10) NOT NULL,
    product_id NUMBER(10) NOT NULL,
    rating NUMBER(2, 1) NOT NULL, 
    "COMMENT" CLOB,
    review_date DATE DEFAULT SYSDATE,
    CONSTRAINT fk_review_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMER (customer_id),
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES PRODUCT (product_id),
    -- A customer can only leave one review per product
    CONSTRAINT unique_review UNIQUE (customer_id, product_id) 
);

CREATE TABLE CART (
    cart_id NUMBER(10) PRIMARY KEY,
    customer_id NUMBER(10) NOT NULL,
    cart_status VARCHAR2(50) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMER (customer_id)
);

CREATE TABLE CART_ITEM (
    cart_id NUMBER(10) NOT NULL,
    product_id NUMBER(10) NOT NULL,
    quantity NUMBER(10) NOT NULL,
    unit_price NUMBER(10, 2) NOT NULL, 
    CONSTRAINT fk_ci_cart FOREIGN KEY (cart_id) REFERENCES CART (cart_id),
    CONSTRAINT fk_ci_product FOREIGN KEY (product_id) REFERENCES PRODUCT (product_id),
    -- If a user adds an existing product again, UI should update quantity, not insert a new row
    CONSTRAINT unique_cart_item UNIQUE (cart_id, product_id) 
);


-- ==============================================================================
-- 5. ORDER AND PAYMENT TABLES
-- Transactional data representing completed purchases.
-- ==============================================================================

CREATE TABLE COUPON (
    coupon_id NUMBER(10) PRIMARY KEY,
    coupon_code VARCHAR2(255) UNIQUE NOT NULL,
    discount_amount NUMBER(10, 2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    minimum_order_amount NUMBER(10, 2),
    coupon_status VARCHAR2(50) NOT NULL 
);

CREATE TABLE "ORDER" (
    order_id NUMBER(10) PRIMARY KEY,
    customer_id NUMBER(10) NOT NULL,
    slot_id NUMBER(10) NOT NULL,
    coupon_id NUMBER(10),                -- Nullable: Orders don't require a coupon
    order_date DATE DEFAULT SYSDATE,
    order_status VARCHAR2(50) NOT NULL,  -- e.g., 'PENDING', 'PAID', 'READY', 'COLLECTED'
    CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES CUSTOMER (customer_id),
    CONSTRAINT fk_order_slot FOREIGN KEY (slot_id) REFERENCES COLLECTION_SLOT (slot_id),
    CONSTRAINT fk_order_coupon FOREIGN KEY (coupon_id) REFERENCES COUPON (coupon_id)
);

CREATE TABLE ORDER_ITEM (
    product_id NUMBER(10) NOT NULL,
    order_id NUMBER(10) NOT NULL,
    quantity NUMBER(10) NOT NULL,
    unit_price NUMBER(10, 2) NOT NULL,   -- CRITICAL: Stores the price AT TIME OF CHECKOUT, regardless of future price changes
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES PRODUCT (product_id),
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES "ORDER" (order_id),
    CONSTRAINT unique_order_item UNIQUE (order_id, product_id) 
);

CREATE TABLE PAYMENT (
    payment_id NUMBER(10) PRIMARY KEY,
    order_id NUMBER(10) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount_paid NUMBER(10, 2) NOT NULL,
    payment_method VARCHAR2(255) NOT NULL, 
    payment_status VARCHAR2(50) NOT NULL, 
    transaction_reference VARCHAR2(255), -- Stores gateway receipt/token (e.g., from Stripe/PayPal)
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES "ORDER" (order_id)
);

CREATE TABLE INVOICE (
    invoice_id NUMBER(10) PRIMARY KEY,
    order_id NUMBER(10) NOT NULL,
    amount NUMBER(10, 2) NOT NULL,
    generated_date DATE DEFAULT SYSDATE,
    invoice_status VARCHAR2(50) NOT NULL, 
    CONSTRAINT fk_invoice_order FOREIGN KEY (order_id) REFERENCES "ORDER" (order_id)
);