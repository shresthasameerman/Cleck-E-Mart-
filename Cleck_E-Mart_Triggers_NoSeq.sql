-- ============================================================
--  CLECK E-MART  |  Triggers Script (No Sequences)
-- ============================================================
--  Sequences have been removed. Every ID trigger now reads
--  MAX(id) + 1 directly from its table at insert time.
--
--  Benefits over sequences:
--    - No hardcoded START WITH values to maintain
--    - Safe to re-run after any amount of seed data
--    - Adding more seed rows later never causes conflicts
--    - One fewer object to manage per table
--
--  Trade-off to be aware of:
--    - Under very high concurrent load, two sessions could
--      read the same MAX before either has committed, causing
--      a duplicate-key error on the PK.  For a single-user
--      APEX prototype this is not a concern in practice.
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
DROP TRIGGER trg_update_product_status;
DROP TRIGGER trg_deduct_stock_on_payment;
DROP TRIGGER trg_slot_capacity_check;
DROP TRIGGER trg_slot_lead_time_check;
DROP TRIGGER trg_slot_day_check;
DROP TRIGGER trg_coupon_validation;
DROP TRIGGER trg_discount_date_check;
DROP TRIGGER trg_auto_generate_invoice;
DROP TRIGGER trg_failed_payment_revert_order;
DROP TRIGGER trg_prevent_negative_stock;
DROP TRIGGER trg_loyalty_points_on_collect;
DROP TRIGGER trg_review_purchase_check;
DROP TRIGGER trg_restore_stock_on_cancel;


-- ============================================================
-- NOTE: Sequences have been removed entirely.
-- Each ID trigger below reads MAX(id) from its table at
-- insert time and assigns MAX + 1.  This means the next ID
-- is always correct regardless of how much seed data exists
-- or how many rows are added later — no hardcoded start
-- values, no stale sequence state, no unique-key conflicts.
-- ============================================================

COMMIT;

-- ============================================================
-- 1. USER
--    (CUSTOMER, TRADER, and ADMIN inherit from this)
-- ============================================================
CREATE OR REPLACE TRIGGER trg_user_bi
BEFORE INSERT ON "USER"
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.user_id IS NULL THEN
        SELECT NVL(MAX(user_id), 0) + 1 INTO v_next_id FROM "USER";
        :NEW.user_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 2. SHOP
-- ============================================================
CREATE OR REPLACE TRIGGER trg_shop_bi
BEFORE INSERT ON SHOP
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.shop_id IS NULL THEN
        SELECT NVL(MAX(shop_id), 0) + 1 INTO v_next_id FROM SHOP;
        :NEW.shop_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 3. CATEGORY
-- ============================================================
CREATE OR REPLACE TRIGGER trg_category_bi
BEFORE INSERT ON CATEGORY
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.category_id IS NULL THEN
        SELECT NVL(MAX(category_id), 0) + 1 INTO v_next_id FROM CATEGORY;
        :NEW.category_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 4. PRODUCT
-- ============================================================
CREATE OR REPLACE TRIGGER trg_product_bi
BEFORE INSERT ON PRODUCT
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.product_id IS NULL THEN
        SELECT NVL(MAX(product_id), 0) + 1 INTO v_next_id FROM PRODUCT;
        :NEW.product_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 5. DISCOUNT
-- ============================================================
CREATE OR REPLACE TRIGGER trg_discount_bi
BEFORE INSERT ON DISCOUNT
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.discount_id IS NULL THEN
        SELECT NVL(MAX(discount_id), 0) + 1 INTO v_next_id FROM DISCOUNT;
        :NEW.discount_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 6. COUPON
-- ============================================================
CREATE OR REPLACE TRIGGER trg_coupon_bi
BEFORE INSERT ON COUPON
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.coupon_id IS NULL THEN
        SELECT NVL(MAX(coupon_id), 0) + 1 INTO v_next_id FROM COUPON;
        :NEW.coupon_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 7. COLLECTION_SLOT
-- ============================================================
CREATE OR REPLACE TRIGGER trg_slot_bi
BEFORE INSERT ON COLLECTION_SLOT
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.slot_id IS NULL THEN
        SELECT NVL(MAX(slot_id), 0) + 1 INTO v_next_id FROM COLLECTION_SLOT;
        :NEW.slot_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 8. WISHLIST
-- ============================================================
CREATE OR REPLACE TRIGGER trg_wishlist_bi
BEFORE INSERT ON WISHLIST
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.wishlist_id IS NULL THEN
        SELECT NVL(MAX(wishlist_id), 0) + 1 INTO v_next_id FROM WISHLIST;
        :NEW.wishlist_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 9. CART
-- ============================================================
CREATE OR REPLACE TRIGGER trg_cart_bi
BEFORE INSERT ON CART
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.cart_id IS NULL THEN
        SELECT NVL(MAX(cart_id), 0) + 1 INTO v_next_id FROM CART;
        :NEW.cart_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 10. ORDER
-- ============================================================
CREATE OR REPLACE TRIGGER trg_order_bi
BEFORE INSERT ON "ORDER"
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.order_id IS NULL THEN
        SELECT NVL(MAX(order_id), 0) + 1 INTO v_next_id FROM "ORDER";
        :NEW.order_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 11. PAYMENT
-- ============================================================
CREATE OR REPLACE TRIGGER trg_payment_bi
BEFORE INSERT ON PAYMENT
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.payment_id IS NULL THEN
        SELECT NVL(MAX(payment_id), 0) + 1 INTO v_next_id FROM PAYMENT;
        :NEW.payment_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 12. INVOICE
-- ============================================================
CREATE OR REPLACE TRIGGER trg_invoice_bi
BEFORE INSERT ON INVOICE
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.invoice_id IS NULL THEN
        SELECT NVL(MAX(invoice_id), 0) + 1 INTO v_next_id FROM INVOICE;
        :NEW.invoice_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 13. REVIEW
-- ============================================================
CREATE OR REPLACE TRIGGER trg_review_bi
BEFORE INSERT ON REVIEW
FOR EACH ROW
DECLARE
    v_next_id NUMBER;
BEGIN
    IF :NEW.review_id IS NULL THEN
        SELECT NVL(MAX(review_id), 0) + 1 INTO v_next_id FROM REVIEW;
        :NEW.review_id := v_next_id;
    END IF;
END;
/

-- ============================================================
-- 14. UPDATE STOCK QTY
--     Updates the stock qty if it drops to certain level
-- ============================================================
CREATE OR REPLACE TRIGGER trg_update_product_status
BEFORE INSERT OR UPDATE OF stock_quantity ON PRODUCT
FOR EACH ROW
BEGIN
    -- If stock drops to 0 (or somehow below 0), it's Out of Stock
    IF :NEW.stock_quantity <= 0 THEN
        :NEW.product_status := 'OUT_OF_STOCK';
        
    -- If stock is between 1 and 19, it's Low Stock
    ELSIF :NEW.stock_quantity < 20 THEN
        :NEW.product_status := 'LOW_STOCK';
        
    -- If stock is 20 or higher, it's fully In Stock
    ELSE
        :NEW.product_status := 'IN_STOCK';
    END IF;
END;
/

-- ============================================================
-- 15. DEDUCT PRODUCT STOCK WHEN PAID
--     Deducts the product stock when a customer completes transaction
-- ============================================================
CREATE OR REPLACE TRIGGER trg_deduct_stock_on_payment
AFTER UPDATE OF order_status ON "ORDER"
FOR EACH ROW
-- This ensures the trigger ONLY fires when the status changes specifically to PAID
WHEN (NEW.order_status = 'PAID' AND (OLD.order_status IS NULL OR OLD.order_status != 'PAID'))
BEGIN
    -- Loop through every item the customer bought in this specific order
    FOR item IN (
        SELECT product_id, quantity
        FROM ORDER_ITEM
        WHERE order_id = :NEW.order_id
    ) LOOP
        -- Deduct the exact quantity purchased from the product's total stock
        UPDATE PRODUCT
        SET stock_quantity = stock_quantity - item.quantity
        WHERE product_id = item.product_id;
    END LOOP;
END;
/

-- ============================================================
-- 16. SLOT CAPACITY CHECK
--    Requirement: B5-03 / B5-04
--    Prevents a new order being placed into a slot that
--    already has 20 confirmed (non-cancelled) orders.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_slot_capacity_check
BEFORE INSERT ON "ORDER"
FOR EACH ROW
DECLARE
    v_order_count NUMBER;
    v_max_orders  NUMBER;
BEGIN
    -- Get the configured max for this slot
    SELECT max_orders
    INTO v_max_orders
    FROM COLLECTION_SLOT
    WHERE slot_id = :NEW.slot_id;
 
    -- Count only active (non-cancelled) orders already in the slot
    SELECT COUNT(*)
    INTO v_order_count
    FROM "ORDER"
    WHERE slot_id = :NEW.slot_id
      AND order_status != 'CANCELLED';
 
    IF v_order_count >= v_max_orders THEN
        RAISE_APPLICATION_ERROR(
            -20001,
            'Booking failed: This collection slot is fully booked (' ||
            v_max_orders || ' orders maximum). Please select a different slot.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 17. SLOT 24-HOUR LEAD TIME CHECK
--    Requirement: B7-03 / E5
--    The chosen collection slot must be at least 24 hours
--    after the order is placed.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_slot_lead_time_check
BEFORE INSERT ON "ORDER"
FOR EACH ROW
DECLARE
    v_slot_date       DATE;
    v_slot_time       VARCHAR2(50);
    v_slot_start_hour NUMBER;
    v_slot_datetime   TIMESTAMP;
BEGIN
    SELECT slot_date, slot_time
    INTO v_slot_date, v_slot_time
    FROM COLLECTION_SLOT
    WHERE slot_id = :NEW.slot_id;

    -- Format is '10:00-13:00', so extract just the '10' before the first colon
    v_slot_start_hour := TO_NUMBER(SUBSTR(v_slot_time, 1, INSTR(v_slot_time, ':') - 1));

    v_slot_datetime := TO_TIMESTAMP(
        TO_CHAR(v_slot_date, 'YYYY-MM-DD') || ' ' || 
        LPAD(v_slot_start_hour, 2, '0') || ':00:00',
        'YYYY-MM-DD HH24:MI:SS'
    );

    IF v_slot_datetime < CURRENT_TIMESTAMP + INTERVAL '24' HOUR THEN
        RAISE_APPLICATION_ERROR(
            -20002,
            'Booking failed: Collection slots must be booked at least 24 hours in advance. ' ||
            'Please choose a later slot.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 18. SLOT DAY-OF-WEEK CHECK
--    Requirement: B7-02
--    Orders may only be collected on Wednesday, Thursday,
--    or Friday as per the pilot scheme rules.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_slot_day_check
BEFORE INSERT ON "ORDER"
FOR EACH ROW
DECLARE
    v_slot_date DATE;
    v_day_name  VARCHAR2(20);
BEGIN
    SELECT slot_date
    INTO v_slot_date
    FROM COLLECTION_SLOT
    WHERE slot_id = :NEW.slot_id;
 
    -- TO_CHAR with 'DAY' returns the full uppercase day name (padded)
    v_day_name := TRIM(TO_CHAR(v_slot_date, 'DAY'));
 
    IF v_day_name NOT IN ('WEDNESDAY', 'THURSDAY', 'FRIDAY') THEN
        RAISE_APPLICATION_ERROR(
            -20003,
            'Booking failed: Collection is only available on Wednesday, Thursday, and Friday. ' ||
            'The selected slot falls on a ' || INITCAP(v_day_name) || '.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 19. COUPON VALIDATION ON ORDER
--    Requirement: B7-06
--    When a coupon is applied to an order, validate it is
--    active, within its date range, and that the order total
--    meets the minimum order amount.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_coupon_validation
BEFORE INSERT ON "ORDER"
FOR EACH ROW
DECLARE
    v_coupon_status       VARCHAR2(50);
    v_valid_from          DATE;
    v_valid_to            DATE;
    v_min_order_amount    NUMBER(10, 2);
    v_cart_total          NUMBER(10, 2);
BEGIN
    -- Only run this check if a coupon has actually been applied
    IF :NEW.coupon_id IS NOT NULL THEN
 
        -- Fetch coupon details
        SELECT coupon_status, valid_from, valid_to, minimum_order_amount
        INTO v_coupon_status, v_valid_from, v_valid_to, v_min_order_amount
        FROM COUPON
        WHERE coupon_id = :NEW.coupon_id;
 
        -- Check the coupon is marked as active
        IF v_coupon_status != 'ACTIVE' THEN
            RAISE_APPLICATION_ERROR(
                -20004,
                'Coupon error: This coupon is no longer active.'
            );
        END IF;
 
        -- Check the coupon is within its valid date range
        IF SYSDATE NOT BETWEEN v_valid_from AND v_valid_to THEN
            RAISE_APPLICATION_ERROR(
                -20005,
                'Coupon error: This coupon has expired or is not yet valid.'
            );
        END IF;
 
        -- Check the cart total meets the minimum spend requirement
        -- We sum from CART_ITEM for the customer's active cart
        SELECT NVL(SUM(ci.quantity * ci.unit_price), 0)
        INTO v_cart_total
        FROM CART_ITEM ci
        JOIN CART c ON ci.cart_id = c.cart_id
        WHERE c.customer_id = :NEW.customer_id
          AND c.cart_status = 'ACTIVE';
 
        IF v_min_order_amount IS NOT NULL AND v_cart_total < v_min_order_amount THEN
            RAISE_APPLICATION_ERROR(
                -20006,
                'Coupon error: Your order total (' || v_cart_total ||
                ') does not meet the minimum spend of ' || v_min_order_amount ||
                ' required for this coupon.'
            );
        END IF;
 
    END IF;
END;
/
 
 
-- ============================================================
-- 20. DISCOUNT DATE VALIDATION
--    Requirement: General data integrity
--    Prevents a discount being saved where the end date
--    is on or before the start date.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_discount_date_check
BEFORE INSERT OR UPDATE ON DISCOUNT
FOR EACH ROW
BEGIN
    IF :NEW.end_date <= :NEW.start_date THEN
        RAISE_APPLICATION_ERROR(
            -20007,
            'Discount error: The end date must be after the start date.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 21. AUTO-GENERATE INVOICE ON SUCCESSFUL PAYMENT
--    Requirement: B7-07
--    Automatically creates an INVOICE record the moment
--    a PAYMENT row is inserted with status = 'PAID'.
--    Invoice ID is derived from MAX(invoice_id) + 1 so it
--    stays consistent with the rest of the trigger approach.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_auto_generate_invoice
AFTER INSERT ON PAYMENT
FOR EACH ROW
WHEN (NEW.payment_status = 'PAID')
DECLARE
    v_next_invoice_id NUMBER;
BEGIN
    SELECT NVL(MAX(invoice_id), 0) + 1 INTO v_next_invoice_id FROM INVOICE;

    INSERT INTO INVOICE (
        invoice_id,
        order_id,
        amount,
        generated_date,
        invoice_status
    ) VALUES (
        v_next_invoice_id,
        :NEW.order_id,
        :NEW.amount_paid,
        SYSDATE,
        'ISSUED'
    );
END;
/
 
 
-- ============================================================
-- 22. REVERT ORDER TO PENDING ON FAILED PAYMENT
--    Requirement: B7-08
--    If a payment comes in with status FAILED, the order
--    is reset to PENDING so the cart is preserved and the
--    customer can retry without losing their basket.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_failed_payment_revert_order
AFTER INSERT ON PAYMENT
FOR EACH ROW
WHEN (NEW.payment_status = 'FAILED')
BEGIN
    UPDATE "ORDER"
    SET order_status = 'PENDING'
    WHERE order_id = :NEW.order_id
      AND order_status != 'PAID'; -- Never downgrade a successfully paid order
END;
/
 
 
-- ============================================================
-- 23. PREVENT NEGATIVE STOCK
--    Requirement: General data integrity / A1-04
--    A safety net that stops stock ever going below 0,
--    regardless of how the update is triggered.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_prevent_negative_stock
BEFORE UPDATE OF stock_quantity ON PRODUCT
FOR EACH ROW
BEGIN
    IF :NEW.stock_quantity < 0 THEN
        RAISE_APPLICATION_ERROR(
            -20008,
            'Stock error: Product "' || :OLD.product_name ||
            '" does not have enough stock to fulfil this request. ' ||
            'Current stock: ' || :OLD.stock_quantity || '.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 24. AWARD LOYALTY POINTS WHEN ORDER IS COLLECTED
--     Requirement: General customer incentive
--     Awards 1 loyalty point per whole £1 spent when an
--     order status is updated to COLLECTED.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_loyalty_points_on_collect
AFTER UPDATE OF order_status ON "ORDER"
FOR EACH ROW
WHEN (NEW.order_status = 'COLLECTED' AND (OLD.order_status IS NULL OR OLD.order_status != 'COLLECTED'))
DECLARE
    v_order_total NUMBER(10, 2);
    v_points_to_add NUMBER(10);
BEGIN
    -- Sum the actual order total from ORDER_ITEM (ignores coupon discount for simplicity)
    SELECT NVL(SUM(quantity * unit_price), 0)
    INTO v_order_total
    FROM ORDER_ITEM
    WHERE order_id = :NEW.order_id;
 
    -- 1 point per whole £1 spent
    v_points_to_add := FLOOR(v_order_total);
 
    UPDATE CUSTOMER
    SET loyalty_points = loyalty_points + v_points_to_add
    WHERE customer_id = :NEW.customer_id;
END;
/
 
 
-- ============================================================
-- 25. ENFORCE PURCHASE CHECK BEFORE REVIEW
--     Requirement: A4-01 / B8-01
--     A customer can only leave a review for a product they
--     have actually bought (in a PAID or COLLECTED order).
-- ============================================================
CREATE OR REPLACE TRIGGER trg_review_purchase_check
BEFORE INSERT ON REVIEW
FOR EACH ROW
DECLARE
    v_purchase_count NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_purchase_count
    FROM ORDER_ITEM oi
    JOIN "ORDER" o ON oi.order_id = o.order_id
    WHERE o.customer_id  = :NEW.customer_id
      AND oi.product_id  = :NEW.product_id
      AND o.order_status IN ('PAID', 'COLLECTED');
 
    IF v_purchase_count = 0 THEN
        RAISE_APPLICATION_ERROR(
            -20009,
            'Review error: You can only review products you have purchased and collected.'
        );
    END IF;
END;
/
 
 
-- ============================================================
-- 26. RESTORE STOCK WHEN ORDER IS CANCELLED
--     Requirement: General data integrity
--     If a PAID order is cancelled, the stock that was
--     deducted by trg_deduct_stock_on_payment is returned.
-- ============================================================
CREATE OR REPLACE TRIGGER trg_restore_stock_on_cancel
AFTER UPDATE OF order_status ON "ORDER"
FOR EACH ROW
WHEN (NEW.order_status = 'CANCELLED' AND OLD.order_status = 'PAID')
BEGIN
    FOR item IN (
        SELECT product_id, quantity
        FROM ORDER_ITEM
        WHERE order_id = :NEW.order_id
    ) LOOP
        UPDATE PRODUCT
        SET stock_quantity = stock_quantity + item.quantity
        WHERE product_id = item.product_id;
    END LOOP;
END;
/
 
COMMIT;

