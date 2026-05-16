-- ============================================================
--  CLECK E-MART  |  Triggers & Sequences Script (UPDATED)
-- ============================================================
--  This script ensures all auto-generated IDs start EXACTLY
--  after the latest seed data to prevent Unique Key Constraint
--  errors when adding new records through the APEX UI.
-- ============================================================

-- ============================================================
-- 1. DROP TRIGGERS
-- ============================================================
DROP TRIGGER trg_user_bi;
DROP TRIGGER trg_shop_bi;
DROP TRIGGER trg_category_bi;
DROP TRIGGER trg_product_bi;
DROP TRIGGER trg_discount_bi;
DROP TRIGGER trg_coupon_bi;
DROP TRIGGER trg_slot_bi;
DROP TRIGGER trg_wishlist_bi;
DROP TRIGGER trg_cart_bi;
DROP TRIGGER trg_order_bi;
DROP TRIGGER trg_payment_bi;
DROP TRIGGER trg_invoice_bi;
DROP TRIGGER trg_review_bi;

-- ============================================================
-- 2. DROP SEQUENCES
-- ============================================================
DROP SEQUENCE seq_user;
DROP SEQUENCE seq_shop;
DROP SEQUENCE seq_category;
DROP SEQUENCE seq_product;
DROP SEQUENCE seq_discount;
DROP SEQUENCE seq_coupon;
DROP SEQUENCE seq_slot;
DROP SEQUENCE seq_wishlist;
DROP SEQUENCE seq_cart;
DROP SEQUENCE seq_order;
DROP SEQUENCE seq_payment;
DROP SEQUENCE seq_invoice;
DROP SEQUENCE seq_review;

COMMIT;

-- ============================================================
-- 1. USER
--    Seed data ends at 1017, sequence starts at 1018.
--    (CUSTOMER, TRADER, and ADMIN inherit from this)
-- ============================================================
CREATE SEQUENCE seq_user START WITH 1018 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_user_bi
BEFORE INSERT ON "USER"
FOR EACH ROW
BEGIN
    IF :NEW.user_id IS NULL THEN
        :NEW.user_id := seq_user.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 2. SHOP
--    Seed data ends at 5105, sequence starts at 5106.
-- ============================================================
CREATE SEQUENCE seq_shop START WITH 5106 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_shop_bi
BEFORE INSERT ON SHOP
FOR EACH ROW
BEGIN
    IF :NEW.shop_id IS NULL THEN
        :NEW.shop_id := seq_shop.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 3. CATEGORY
--    Seed data ends at 6105, sequence starts at 6106.
-- ============================================================
CREATE SEQUENCE seq_category START WITH 6106 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_category_bi
BEFORE INSERT ON CATEGORY
FOR EACH ROW
BEGIN
    IF :NEW.category_id IS NULL THEN
        :NEW.category_id := seq_category.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 4. PRODUCT
--    Seed data ends at 7150 (Chorizo Ring), sequence starts at 7151.
-- ============================================================
CREATE SEQUENCE seq_product START WITH 7151 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_product_bi
BEFORE INSERT ON PRODUCT
FOR EACH ROW
BEGIN
    IF :NEW.product_id IS NULL THEN
        :NEW.product_id := seq_product.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 5. DISCOUNT
--    Seed data ends at 8105, sequence starts at 8106.
-- ============================================================
CREATE SEQUENCE seq_discount START WITH 8106 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_discount_bi
BEFORE INSERT ON DISCOUNT
FOR EACH ROW
BEGIN
    IF :NEW.discount_id IS NULL THEN
        :NEW.discount_id := seq_discount.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 6. COUPON
--    Seed data ends at 9104, sequence starts at 9105.
-- ============================================================
CREATE SEQUENCE seq_coupon START WITH 9105 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_coupon_bi
BEFORE INSERT ON COUPON
FOR EACH ROW
BEGIN
    IF :NEW.coupon_id IS NULL THEN
        :NEW.coupon_id := seq_coupon.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 7. COLLECTION_SLOT
--    Seed data ends at 518, sequence starts at 519.
-- ============================================================
CREATE SEQUENCE seq_slot START WITH 519 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_slot_bi
BEFORE INSERT ON COLLECTION_SLOT
FOR EACH ROW
BEGIN
    IF :NEW.slot_id IS NULL THEN
        :NEW.slot_id := seq_slot.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 8. WISHLIST
--    Seed data ends at 10103, sequence starts at 10104.
-- ============================================================
CREATE SEQUENCE seq_wishlist START WITH 10104 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_wishlist_bi
BEFORE INSERT ON WISHLIST
FOR EACH ROW
BEGIN
    IF :NEW.wishlist_id IS NULL THEN
        :NEW.wishlist_id := seq_wishlist.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 9. CART
--    Seed data ends at 11103, sequence starts at 11104.
-- ============================================================
CREATE SEQUENCE seq_cart START WITH 11104 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_cart_bi
BEFORE INSERT ON CART
FOR EACH ROW
BEGIN
    IF :NEW.cart_id IS NULL THEN
        :NEW.cart_id := seq_cart.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 10. ORDER
--     Seed data ends at 12115, sequence starts at 12116.
-- ============================================================
CREATE SEQUENCE seq_order START WITH 12116 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_order_bi
BEFORE INSERT ON "ORDER"
FOR EACH ROW
BEGIN
    IF :NEW.order_id IS NULL THEN
        :NEW.order_id := seq_order.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 11. PAYMENT
--     Seed data ends at 13113, sequence starts at 13114.
-- ============================================================
CREATE SEQUENCE seq_payment START WITH 13114 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_payment_bi
BEFORE INSERT ON PAYMENT
FOR EACH ROW
BEGIN
    IF :NEW.payment_id IS NULL THEN
        :NEW.payment_id := seq_payment.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 12. INVOICE
--     Seed data ends at 14113, sequence starts at 14114.
-- ============================================================
CREATE SEQUENCE seq_invoice START WITH 14114 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_invoice_bi
BEFORE INSERT ON INVOICE
FOR EACH ROW
BEGIN
    IF :NEW.invoice_id IS NULL THEN
        :NEW.invoice_id := seq_invoice.NEXTVAL;
    END IF;
END;
/

-- ============================================================
-- 13. REVIEW
--     Seed data ends at 15123, sequence starts at 15124.
-- ============================================================
CREATE SEQUENCE seq_review START WITH 15124 INCREMENT BY 1 NOCACHE;

CREATE OR REPLACE TRIGGER trg_review_bi
BEFORE INSERT ON REVIEW
FOR EACH ROW
BEGIN
    IF :NEW.review_id IS NULL THEN
        :NEW.review_id := seq_review.NEXTVAL;
    END IF;
END;
/