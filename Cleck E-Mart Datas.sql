-- ============================================================
--  CLECK E-MART  |  Oracle APEX Seed Data Script
-- ============================================================
--  This script populates the database with initial sample data.
--  It includes Users, Shops, Products, Orders, and more.
--
--  ID Conventions Used:
--    USER        1000+       SHOP        5100+       CATEGORY    6100+
--    PRODUCT     7100+       DISCOUNT    8100+       COUPON      9100+
--    SLOT        501+        WISHLIST   10100+       CART       11100+
--    ORDER      12100+       PAYMENT    13100+       INVOICE    14100+
--    REVIEW     15100+
-- ============================================================

-- ============================================================
-- 0. CLEAN UP
--    (Uncomment and run this block only if you need to reset
--    the database and clear all existing data before seeding)
-- ============================================================
/*
DELETE FROM REVIEW;
DELETE FROM INVOICE;
DELETE FROM PAYMENT;
DELETE FROM ORDER_ITEM;
DELETE FROM "ORDER";
DELETE FROM CART_ITEM;
DELETE FROM CART;
DELETE FROM WISHLIST_ITEM;
DELETE FROM WISHLIST;
DELETE FROM COUPON;
DELETE FROM COLLECTION_SLOT;
DELETE FROM PRODUCT;
DELETE FROM DISCOUNT;
DELETE FROM SHOP;
DELETE FROM CATEGORY;
DELETE FROM ADMIN;
DELETE FROM TRADER;
DELETE FROM CUSTOMER;
DELETE FROM "USER";
COMMIT;

*/

-- ============================================================
-- 1. USER TABLE
--    (10 Customers + 5 Traders + 2 Admins = 17 rows total)
-- ============================================================

-- --- Customers ---
INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1001, 'Aarav', 'Sharma', 'M', 'aarav.sharma@gmail.com', 'hashed_Pass@123', '9841123456', 'Lazimpat, Kathmandu, Nepal', 'CUSTOMER', NULL, SYSDATE - 90, SYSDATE - 10);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1002, 'Priya', 'Thapa', 'F', 'priya.thapa@gmail.com', 'hashed_Pass@456', '9851234567', 'Patan, Lalitpur, Nepal', 'CUSTOMER', NULL, SYSDATE - 85, SYSDATE - 5);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1003, 'Suman', 'Gurung', 'M', 'suman.gurung@yahoo.com', 'hashed_Pass@789', '9860987654', 'Bhaktapur, Nepal', 'CUSTOMER', NULL, SYSDATE - 80, SYSDATE - 20);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1004, 'Anita', 'Rai', 'F', 'anita.rai@hotmail.com', 'hashed_Pass@321', '9875432100', 'Pokhara, Gandaki, Nepal', 'CUSTOMER', NULL, SYSDATE - 75, SYSDATE - 8);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1005, 'Dipesh', 'Magar', 'M', 'dipesh.magar@gmail.com', 'hashed_Pass@654', '9812000111', 'Thamel, Kathmandu, Nepal', 'CUSTOMER', NULL, SYSDATE - 70, SYSDATE - 3);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1006, 'Oliver', 'Thompson', 'M', 'oliver.thompson@gmail.com', 'hashed_Pass@abc', '07700900123', '14 Church Lane, Cleckhuddersfax, West Yorkshire, WF17 8AB', 'CUSTOMER', NULL, SYSDATE - 65, SYSDATE - 12);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1007, 'Sophie', 'Williams', 'F', 'sophie.w@outlook.com', 'hashed_Pass@def', '07700900456', '3 Westgate Road, Cleckhuddersfax, West Yorkshire, WF17 9CD', 'CUSTOMER', NULL, SYSDATE - 60, SYSDATE - 7);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1008, 'James', 'Hartley', 'M', 'james.hartley@gmail.com', 'hashed_Pass@ghi', '07700900789', '27 Market Street, Cleckhuddersfax, West Yorkshire, WF17 5EF', 'CUSTOMER', NULL, SYSDATE - 55, SYSDATE - 15);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1009, 'Emily', 'Broadbent', 'F', 'emily.broad@yahoo.co.uk', 'hashed_Pass@jkl', '07700900321', '8 Mill Close, Cleckhuddersfax, West Yorkshire, WF17 2GH', 'CUSTOMER', NULL, SYSDATE - 50, SYSDATE - 2);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1010, 'George', 'Pickles', 'M', 'george.pickles@btinternet.com', 'hashed_Pass@mno', '07700900654', '52 Towngate, Cleckhuddersfax, West Yorkshire, WF17 6IJ', 'CUSTOMER', NULL, SYSDATE - 45, SYSDATE - 6);

-- --- Traders ---
INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1011, 'Robert', 'Firth', 'M', 'robert.firth@firth-butchers.co.uk', 'hashed_Trader@1', '07800100001', '1 High Street, Cleckhuddersfax, WF17 1AA', 'TRADER', NULL, SYSDATE - 200, SYSDATE - 30);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1012, 'Margaret', 'Greenwood', 'F', 'margaret@greenwoods-veg.co.uk', 'hashed_Trader@2', '07800100002', '3 High Street, Cleckhuddersfax, WF17 1AB', 'TRADER', NULL, SYSDATE - 195, SYSDATE - 30);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1013, 'Harold', 'Fishwick', 'M', 'harold@fishwicks-fish.co.uk', 'hashed_Trader@3', '07800100003', '5 High Street, Cleckhuddersfax, WF17 1AC', 'TRADER', NULL, SYSDATE - 190, SYSDATE - 30);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1014, 'Dorothy', 'Bakers', 'F', 'dorothy@thedough.co.uk', 'hashed_Trader@4', '07800100004', '7 High Street, Cleckhuddersfax, WF17 1AD', 'TRADER', NULL, SYSDATE - 185, SYSDATE - 30);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1015, 'Luigi', 'Caruso', 'M', 'luigi@claesdelideli.co.uk', 'hashed_Trader@5', '07800100005', '9 High Street, Cleckhuddersfax, WF17 1AE', 'TRADER', NULL, SYSDATE - 180, SYSDATE - 30);

-- --- Admins ---
INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1016, 'Admin', 'Cleck', 'M', 'admin@cleckemart.co.uk', 'hashed_Admin@999', '07900999999', 'Cleck E-Mart HQ, WF17 1ZZ', 'ADMIN', NULL, SYSDATE - 365, SYSDATE);

INSERT INTO "USER" (user_id, first_name, last_name, gender, email, password, phone_number, address, role, profile_picture_path, created_at, updated_at)
VALUES (1017, 'Nikhilesh', 'Maharjan', 'M', 'mnikhilesh23@tbc.edu.np', 'Nikhilesh12@', '9800000017', 'Kathmandu, Nepal', 'ADMIN', NULL, SYSDATE - 100, SYSDATE);

-- ============================================================
-- 2. CUSTOMER DETAILS
-- ============================================================
DELETE FROM CUSTOMER;
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1001, 120);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1002, 55);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1003, 300);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1004, 0);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1005, 85);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1006, 200);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1007, 15);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1008, 450);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1009, 30);
INSERT INTO CUSTOMER (customer_id, loyalty_points) VALUES (1010, 0);

-- ============================================================
-- 3. TRADER DETAILS
-- ============================================================
INSERT INTO TRADER (trader_id, brand_name, pan_number) VALUES (1011, 'Firth''s Butchers', 'PAN-UK-11001');
INSERT INTO TRADER (trader_id, brand_name, pan_number) VALUES (1012, 'Greenwood''s Greengrocers', 'PAN-UK-11002');
INSERT INTO TRADER (trader_id, brand_name, pan_number) VALUES (1013, 'Fishwick''s Fishmonger', 'PAN-UK-11003');
INSERT INTO TRADER (trader_id, brand_name, pan_number) VALUES (1014, 'The Dough Bakery', 'PAN-UK-11004');
INSERT INTO TRADER (trader_id, brand_name, pan_number) VALUES (1015, 'Claes Deli & Delicatessen', 'PAN-UK-11005');

-- ============================================================
-- 4. ADMIN DETAILS
-- ============================================================
INSERT INTO ADMIN (admin_id, privileges) VALUES (1016, 'FULL_ACCESS');
INSERT INTO ADMIN (admin_id, privileges) VALUES (1017, 'FULL_ACCESS');

-- ============================================================
-- 5. SHOP
-- ============================================================
INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
VALUES (5101, 1011, 'Firth''s Butchers', 'Family-run butchers on Cleck High Street since 1972. Locally sourced beef, lamb, pork and poultry.', 'firth_logo.png', 'ACTIVE');

INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
VALUES (5102, 1012, 'Greenwood''s Greengrocers', 'Fresh seasonal fruit and vegetables direct from Yorkshire farms and local markets every morning.', 'greenwood_logo.png', 'ACTIVE');

INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
VALUES (5103, 1013, 'Fishwick''s Fishmonger', 'Daily catch from Grimsby and Whitby. Smoked, fresh and shellfish available.', 'fishwick_logo.png', 'ACTIVE');

INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
VALUES (5104, 1014, 'The Dough Bakery', 'Artisan bread, pastries, cakes and pies baked fresh every morning. Gluten-free options available.', 'dough_logo.png', 'ACTIVE');

INSERT INTO SHOP (shop_id, trader_id, shop_name, shop_description, shop_logo, shop_status)
VALUES (5105, 1015, 'Claes Deli & Delicatessen', 'Continental meats, cheeses, olives, antipasti and specialist condiments. Dine-in and takeaway platters.', 'claes_logo.png', 'ACTIVE');

-- ============================================================
-- 6. CATEGORY
-- ============================================================
INSERT INTO CATEGORY (category_id, category_name, category_description)
VALUES (6101, 'Meat & Poultry', 'Fresh cuts of beef, lamb, pork, chicken and game');

INSERT INTO CATEGORY (category_id, category_name, category_description)
VALUES (6102, 'Fruit & Vegetables', 'Fresh seasonal produce sourced from local farms');

INSERT INTO CATEGORY (category_id, category_name, category_description)
VALUES (6103, 'Fish & Seafood', 'Fresh and smoked fish plus shellfish and seafood');

INSERT INTO CATEGORY (category_id, category_name, category_description)
VALUES (6104, 'Bread & Bakery', 'Artisan breads, pastries, cakes and savoury bakes');

INSERT INTO CATEGORY (category_id, category_name, category_description)
VALUES (6105, 'Deli & Charcuterie', 'Continental meats, cheeses, olives and specialty foods');

-- ============================================================
-- 7. DISCOUNT
-- ============================================================
INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status)
VALUES (8101, 10, DATE '2026-01-01', DATE '2026-01-31', 'EXPIRED');

INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status)
VALUES (8102, 15, DATE '2026-02-01', DATE '2026-02-28', 'EXPIRED');

INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status)
VALUES (8103, 5,  DATE '2026-03-01', DATE '2026-03-31', 'EXPIRED');

INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status)
VALUES (8104, 20, DATE '2026-04-01', DATE '2026-04-07', 'EXPIRED');

INSERT INTO DISCOUNT (discount_id, discount_percentage, start_date, end_date, discount_status)
VALUES (8105, 10, SYSDATE, SYSDATE + 30, 'ACTIVE');

-- ============================================================
-- 8. PRODUCT  (10 per shop = 50 total)
-- ============================================================

-- ---------------------------------------------------------------
-- Firth's Butchers (Shop 5101) | Products 7101-7110
-- ---------------------------------------------------------------
INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7101, 5101, 6101, NULL, 'Beef Sirloin Steak (200g)', 'Prime 21-day dry-aged Yorkshire beef sirloin. Perfect for grilling or pan-frying.', 'beef_sirloin.jpg', 8.99, 40, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7102, 5101, 6101, 8105, 'Lamb Shoulder (per kg)', 'Locally sourced Yorkshire lamb shoulder - ideal for slow roasting.', 'lamb_shoulder.jpg', 12.50, 25, 'IN_STOCK', 'None', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7103, 5101, 6101, NULL, 'Pork Belly Slices (500g)', 'Hand-prepared pork belly, great for BBQ or slow-roasting.', 'pork_belly.jpg', 5.49, 30, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7104, 5101, 6101, NULL, 'Free-Range Whole Chicken (1.5kg)', 'Free-range Yorkshire chicken, oven-ready.', 'whole_chicken.jpg', 9.75, 20, 'IN_STOCK', 'None', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7105, 5101, 6101, 8105, 'Beef Mince (500g)', 'Lean 10% fat beef mince, freshly ground daily. Ideal for bolognese, burgers and meatballs.', 'beef_mince.jpg', 4.99, 50, 'IN_STOCK', 'None', 1, 6);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7106, 5101, 6101, NULL, 'Pork Sausages (6 pack)', 'Traditional Yorkshire pork sausages with a hint of sage. 97% pork.', 'pork_sausages.jpg', 3.75, 60, 'IN_STOCK', 'Gluten', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7107, 5101, 6101, NULL, 'Rack of Ribs (per kg)', 'Meaty pork spare ribs, perfect for slow smoking or BBQ glazing.', 'pork_ribs.jpg', 7.25, 18, 'IN_STOCK', 'None', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7108, 5101, 6101, NULL, 'Chicken Breast Fillets (2 pack)', 'Skinless free-range chicken breast fillets, great for grilling or stir-fry.', 'chicken_breast.jpg', 5.99, 45, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7109, 5101, 6101, 8105, 'Beef Ribeye Steak (250g)', 'Beautifully marbled ribeye cut, dry-aged for 28 days. Rich flavour and tenderness.', 'ribeye_steak.jpg', 11.50, 22, 'IN_STOCK', 'None', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7110, 5101, 6101, NULL, 'Bacon Back Rashers (300g)', 'Thick-cut smoked back bacon from British pigs. Perfect for a full Yorkshire breakfast.', 'bacon_rashers.jpg', 3.49, 55, 'IN_STOCK', 'None', 1, 6);

-- ---------------------------------------------------------------
-- Greenwood's Greengrocers (Shop 5102) | Products 7111-7120
-- ---------------------------------------------------------------
INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7111, 5102, 6102, NULL, 'Mixed Salad Bag (250g)', 'Baby leaves, rocket and spinach grown in Yorkshire greenhouses.', 'salad_bag.jpg', 1.89, 80, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7112, 5102, 6102, 8105, 'Heritage Tomatoes (500g)', 'Mixed heritage tomatoes - heirloom varieties with rich flavour.', 'heritage_tomatoes.jpg', 2.75, 60, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7113, 5102, 6102, NULL, 'Braeburn Apples (pack of 6)', 'Crisp sweet-sharp British apples from local orchards.', 'apples.jpg', 2.20, 100, 'IN_STOCK', 'None', 1, 6);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7114, 5102, 6102, NULL, 'Roasting Vegetable Box', 'Seasonal mix of parsnips, carrots, beetroot and red onions. Serves 4.', 'roast_veg_box.jpg', 4.50, 35, 'IN_STOCK', 'None', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7115, 5102, 6102, NULL, 'English Cucumber (each)', 'Crisp fresh cucumber, locally grown. Great for salads and sandwiches.', 'cucumber.jpg', 0.89, 90, 'IN_STOCK', 'None', 1, 10);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7116, 5102, 6102, 8105, 'Chestnut Mushrooms (250g)', 'Firm and earthy chestnut mushrooms, great for risottos and stir-fries.', 'mushrooms.jpg', 1.65, 70, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7117, 5102, 6102, NULL, 'Sweet Potatoes (per kg)', 'Naturally sweet and versatile. Ideal for roasting, mashing or making fries.', 'sweet_potatoes.jpg', 2.10, 55, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7118, 5102, 6102, NULL, 'Seasonal Berry Mix (300g)', 'Fresh mix of strawberries, raspberries and blueberries from British growers.', 'berry_mix.jpg', 3.50, 40, 'IN_STOCK', 'None', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7119, 5102, 6102, NULL, 'Spring Onion Bunch', 'Tender spring onions, freshly picked. Perfect for salads, garnishes and stir-fries.', 'spring_onions.jpg', 0.75, 120, 'IN_STOCK', 'None', 1, 10);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7120, 5102, 6102, 8105, 'Savoy Cabbage (each)', 'Large fresh savoy cabbage, great for braising, stir-frying or stuffing.', 'savoy_cabbage.jpg', 1.25, 45, 'IN_STOCK', 'None', 1, 5);

-- ---------------------------------------------------------------
-- Fishwick's Fishmonger (Shop 5103) | Products 7121-7130
-- ---------------------------------------------------------------
INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7121, 5103, 6103, NULL, 'Atlantic Salmon Fillet (180g)', 'Fresh Atlantic salmon fillet. Rich in omega-3. Allergens: Fish.', 'salmon_fillet.jpg', 6.99, 45, 'IN_STOCK', 'Fish', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7122, 5103, 6103, 8105, 'Smoked Haddock (200g)', 'Traditionally oak-smoked haddock from Whitby. Allergens: Fish.', 'smoked_haddock.jpg', 5.25, 30, 'IN_STOCK', 'Fish', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7123, 5103, 6103, NULL, 'North Sea Cod Fillet (180g)', 'Line-caught North Sea cod. Allergens: Fish.', 'cod_fillet.jpg', 7.49, 35, 'IN_STOCK', 'Fish', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7124, 5103, 6103, NULL, 'King Prawns (200g)', 'Raw king prawns, shell-on from sustainable fisheries. Allergens: Crustaceans.', 'king_prawns.jpg', 8.25, 20, 'LOW_STOCK', 'Crustaceans', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7125, 5103, 6103, 8105, 'Whole Sea Bass (approx 500g)', 'Fresh whole sea bass, scaled and gutted. Perfect for baking or grilling. Allergens: Fish.', 'sea_bass.jpg', 9.50, 15, 'IN_STOCK', 'Fish', 1, 2);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7126, 5103, 6103, NULL, 'Smoked Salmon Slices (100g)', 'Hand-sliced Scottish smoked salmon. Ideal for starters or bagels. Allergens: Fish.', 'smoked_salmon.jpg', 5.75, 40, 'IN_STOCK', 'Fish', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7127, 5103, 6103, NULL, 'Scallops (6 pieces)', 'Fresh hand-dived scallops from Scottish waters. Allergens: Molluscs.', 'scallops.jpg', 12.99, 12, 'LOW_STOCK', 'Molluscs', 1, 2);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7128, 5103, 6103, NULL, 'Kipper Fillets (pair)', 'Traditional Manx kippers, hot-smoked over oak chips. Allergens: Fish.', 'kippers.jpg', 4.50, 25, 'IN_STOCK', 'Fish', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7129, 5103, 6103, 8105, 'Tuna Loin Steak (180g)', 'Sashimi-grade yellowfin tuna loin. Great seared or eaten raw. Allergens: Fish.', 'tuna_steak.jpg', 8.99, 18, 'IN_STOCK', 'Fish', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7130, 5103, 6103, NULL, 'Dressed Crab (half shell)', 'Locally sourced dressed brown crab, ready to eat. Allergens: Crustaceans.', 'dressed_crab.jpg', 7.75, 10, 'LOW_STOCK', 'Crustaceans', 1, 2);

-- ---------------------------------------------------------------
-- The Dough Bakery (Shop 5104) | Products 7131-7140
-- ---------------------------------------------------------------
INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7131, 5104, 6104, NULL, 'Sourdough Loaf (800g)', 'Slow-fermented sourdough with a crispy crust. Allergens: Gluten.', 'sourdough_loaf.jpg', 3.50, 50, 'IN_STOCK', 'Gluten', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7132, 5104, 6104, 8105, 'All-Butter Croissant (x4)', 'Classic French-style butter croissants, baked fresh every morning. Allergens: Gluten, Dairy, Eggs.', 'croissants.jpg', 3.99, 40, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7133, 5104, 6104, NULL, 'Yorkshire Parkin (slice)', 'Traditional oat and treacle gingerbread cake. Allergens: Gluten, Dairy, Eggs.', 'parkin.jpg', 2.25, 60, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7134, 5104, 6104, NULL, 'Cheese & Onion Pasty', 'Shortcrust pastry filled with mature cheddar and caramelised onion. Allergens: Gluten, Dairy, Eggs.', 'pasty.jpg', 2.95, 45, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7135, 5104, 6104, 8105, 'Seeded Wholemeal Loaf (800g)', 'Hearty wholemeal loaf topped with sunflower and pumpkin seeds. Allergens: Gluten, Seeds.', 'wholemeal_loaf.jpg', 3.25, 35, 'IN_STOCK', 'Gluten, Seeds', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7136, 5104, 6104, NULL, 'Sticky Toffee Pudding (individual)', 'Classic British sponge pudding with warm toffee sauce. Allergens: Gluten, Dairy, Eggs.', 'sticky_toffee.jpg', 3.50, 30, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7137, 5104, 6104, NULL, 'Victoria Sponge Slice', 'Light vanilla sponge with jam and cream filling. Allergens: Gluten, Dairy, Eggs.', 'victoria_sponge.jpg', 2.75, 40, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7138, 5104, 6104, 8105, 'Gluten-Free Banana Bread (slice)', 'Moist banana bread made without gluten. Allergens: Dairy, Eggs, Nuts.', 'banana_bread.jpg', 2.50, 25, 'IN_STOCK', 'Dairy, Eggs, Nuts', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7139, 5104, 6104, NULL, 'Sausage Roll (large)', 'Flaky puff pastry filled with seasoned pork sausagemeat. Allergens: Gluten, Dairy, Eggs.', 'sausage_roll.jpg', 2.20, 50, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 6);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7140, 5104, 6104, NULL, 'Cinnamon Danish Swirl', 'Soft, buttery pastry with cinnamon sugar filling and vanilla glaze. Allergens: Gluten, Dairy, Eggs.', 'cinnamon_danish.jpg', 2.10, 35, 'IN_STOCK', 'Gluten, Dairy, Eggs', 1, 5);

-- ---------------------------------------------------------------
-- Claes Deli & Delicatessen (Shop 5105) | Products 7141-7150
-- ---------------------------------------------------------------
INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7141, 5105, 6105, NULL, 'Italian Prosciutto (100g)', 'Hand-sliced DOP Prosciutto di Parma, aged 18 months. Allergens: None.', 'prosciutto.jpg', 6.50, 30, 'IN_STOCK', 'None', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7142, 5105, 6105, 8105, 'Manchego Cheese (150g)', 'Spanish semi-cured sheep''s milk cheese with a firm texture. Allergens: Dairy.', 'manchego.jpg', 5.75, 25, 'IN_STOCK', 'Dairy', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7143, 5105, 6105, NULL, 'Mixed Marinated Olives (200g)', 'Kalamata and Sicilian green olives in herb-infused oil. Allergens: None.', 'mixed_olives.jpg', 3.25, 40, 'IN_STOCK', 'None', 1, 5);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7144, 5105, 6105, NULL, 'Sun-Dried Tomato Pesto (190g jar)', 'Artisan pesto with sun-dried tomatoes, basil and pine nuts. Allergens: Nuts, Dairy.', 'pesto_jar.jpg', 4.10, 35, 'IN_STOCK', 'Nuts, Dairy', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7145, 5105, 6105, 8105, 'Salami Milano (80g)', 'Thinly sliced Italian Milano salami with a mild, delicate flavour. Allergens: None.', 'salami_milano.jpg', 4.50, 35, 'IN_STOCK', 'None', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7146, 5105, 6105, NULL, 'Brie de Meaux (200g)', 'Authentic French brie with a creamy, bloomy rind. Allergens: Dairy.', 'brie.jpg', 6.25, 20, 'IN_STOCK', 'Dairy', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7147, 5105, 6105, NULL, 'Hummus & Flatbread Pack', 'Creamy Lebanese-style hummus with warm flatbreads. Allergens: Gluten, Sesame.', 'hummus_pack.jpg', 3.95, 30, 'IN_STOCK', 'Gluten, Sesame', 1, 4);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7148, 5105, 6105, 8105, 'Antipasti Selection Pot (250g)', 'Grilled artichokes, roasted peppers and sun-blushed tomatoes in extra virgin olive oil.', 'antipasti_pot.jpg', 5.25, 22, 'IN_STOCK', 'None', 1, 3);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7149, 5105, 6105, NULL, 'Truffle Pecorino (100g)', 'Italian sheep''s milk cheese infused with fine black truffle. Allergens: Dairy.', 'truffle_pecorino.jpg', 7.50, 15, 'LOW_STOCK', 'Dairy', 1, 2);

INSERT INTO PRODUCT (product_id, shop_id, category_id, discount_id, product_name, product_description, product_image, price, stock_quantity, product_status, allergy_information, min_order, max_order)
VALUES (7150, 5105, 6105, NULL, 'Chorizo Ring (200g)', 'Spanish smoked paprika chorizo ring, great for charcuterie boards or cooking. Allergens: None.', 'chorizo_ring.jpg', 4.75, 28, 'IN_STOCK', 'None', 1, 4);

-- ============================================================
-- 9. COLLECTION_SLOT
--    Wed/Thu/Fri only. 3 time slots per day. Max 20 orders.
--    Week 1: May 13(Wed), 14(Thu), 15(Fri)
--    Week 2: May 20(Wed), 21(Thu), 22(Fri)
-- ============================================================
DELETE FROM COLLECTION_SLOT;

INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (501, '10:00-13:00', DATE '2026-05-13', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (502, '13:00-16:00', DATE '2026-05-13', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (503, '16:00-19:00', DATE '2026-05-13', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (504, '10:00-13:00', DATE '2026-05-14', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (505, '13:00-16:00', DATE '2026-05-14', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (506, '16:00-19:00', DATE '2026-05-14', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (507, '10:00-13:00', DATE '2026-05-15', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (508, '13:00-16:00', DATE '2026-05-15', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (509, '16:00-19:00', DATE '2026-05-15', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (510, '10:00-13:00', DATE '2026-05-20', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (511, '13:00-16:00', DATE '2026-05-20', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (512, '16:00-19:00', DATE '2026-05-20', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (513, '10:00-13:00', DATE '2026-05-21', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (514, '13:00-16:00', DATE '2026-05-21', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (515, '16:00-19:00', DATE '2026-05-21', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (516, '10:00-13:00', DATE '2026-05-22', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (517, '13:00-16:00', DATE '2026-05-22', 20, 'AVAILABLE');
INSERT INTO COLLECTION_SLOT (slot_id, slot_time, slot_date, max_orders, slot_status) VALUES (518, '16:00-19:00', DATE '2026-05-22', 20, 'AVAILABLE');

-- ============================================================
-- 10. COUPON
-- ============================================================
INSERT INTO COUPON (coupon_id, coupon_code, discount_amount, valid_from, valid_to, minimum_order_amount, coupon_status)
VALUES (9101, 'WELCOME10', 10.00, DATE '2026-04-01', DATE '2026-06-30', 20.00, 'ACTIVE');

INSERT INTO COUPON (coupon_id, coupon_code, discount_amount, valid_from, valid_to, minimum_order_amount, coupon_status)
VALUES (9102, 'CLECK5', 5.00, DATE '2026-04-01', DATE '2026-04-30', 15.00, 'ACTIVE');

INSERT INTO COUPON (coupon_id, coupon_code, discount_amount, valid_from, valid_to, minimum_order_amount, coupon_status)
VALUES (9103, 'XMAS2026', 15.00, DATE '2026-12-01', DATE '2026-12-31', 30.00, 'INACTIVE');

INSERT INTO COUPON (coupon_id, coupon_code, discount_amount, valid_from, valid_to, minimum_order_amount, coupon_status)
VALUES (9104, 'FRESH20', 20.00, DATE '2026-03-01', DATE '2026-03-31', 40.00, 'EXPIRED');

-- ============================================================
-- 11. WISHLIST & WISHLIST_ITEM
-- ============================================================
INSERT INTO WISHLIST (wishlist_id, customer_id, created_at) VALUES (10101, 1001, SYSDATE - 20);
INSERT INTO WISHLIST (wishlist_id, customer_id, created_at) VALUES (10102, 1006, SYSDATE - 15);
INSERT INTO WISHLIST (wishlist_id, customer_id, created_at) VALUES (10103, 1008, SYSDATE - 5);

INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10101, 7101, SYSDATE - 18);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10101, 7131, SYSDATE - 10);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10101, 7145, SYSDATE - 8);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10102, 7121, SYSDATE - 14);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10102, 7142, SYSDATE - 12);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10102, 7127, SYSDATE - 6);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10103, 7112, SYSDATE - 4);
INSERT INTO WISHLIST_ITEM (wishlist_id, product_id, added_date) VALUES (10103, 7109, SYSDATE - 3);

-- ============================================================
-- 12. CART & CART_ITEM
-- ============================================================
INSERT INTO CART (cart_id, customer_id, cart_status) VALUES (11101, 1003, 'ACTIVE');
INSERT INTO CART (cart_id, customer_id, cart_status) VALUES (11102, 1007, 'ACTIVE');
INSERT INTO CART (cart_id, customer_id, cart_status) VALUES (11103, 1009, 'CHECKED_OUT');

INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11101, 7103, 1, 5.49);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11101, 7113, 2, 2.20);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11101, 7132, 1, 3.99);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11102, 7121, 2, 6.99);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11102, 7143, 1, 3.25);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11102, 7147, 1, 3.95);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11103, 7101, 1, 8.99);
INSERT INTO CART_ITEM (cart_id, product_id, quantity, unit_price) VALUES (11103, 7131, 1, 3.50);

-- ============================================================
-- 13. ORDER & ORDER_ITEM  (15 orders total)
--     COLLECTED = past orders already picked up (Week 1)
--     PAID      = paid, awaiting collection (Week 2 upcoming)
--     PENDING   = placed but not yet paid
-- ============================================================

-- Week 1 orders (slots 501-509, May 13-15) - all COLLECTED
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12101, 1001, 501, 9101, DATE '2026-05-11', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12102, 1006, 504, NULL,  DATE '2026-05-12', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12103, 1008, 502, 9102, DATE '2026-05-11', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12104, 1003, 507, NULL,  DATE '2026-05-13', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12105, 1007, 505, NULL,  DATE '2026-05-12', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12106, 1010, 508, NULL,  DATE '2026-05-13', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12107, 1002, 503, 9101, DATE '2026-05-11', 'COLLECTED');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12108, 1005, 506, NULL,  DATE '2026-05-12', 'COLLECTED');

-- Week 2 orders (slots 510-518, May 20-22)
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12109, 1004, 510, NULL,  DATE '2026-05-18', 'PAID');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12110, 1009, 513, NULL,  DATE '2026-05-19', 'PAID');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12111, 1001, 511, 9101, DATE '2026-05-18', 'PAID');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12112, 1006, 516, NULL,  DATE '2026-05-20', 'PENDING');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12113, 1003, 514, NULL,  DATE '2026-05-19', 'PAID');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12114, 1008, 517, NULL,  DATE '2026-05-20', 'PENDING');
INSERT INTO "ORDER" (order_id, customer_id, slot_id, coupon_id, order_date, order_status) VALUES (12115, 1010, 512, NULL,  DATE '2026-05-18', 'PAID');

-- Order 12101 | Aarav | butcher + bakery + deli
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12101, 7101, 1, 8.99);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12101, 7131, 1, 3.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12101, 7143, 1, 3.25);

-- Order 12102 | Oliver | fish + greengrocer
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12102, 7121, 2, 6.99);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12102, 7112, 1, 2.75);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12102, 7126, 1, 5.75);

-- Order 12103 | James | butcher + deli + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12103, 7102, 1, 12.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12103, 7142, 1, 5.75);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12103, 7132, 2, 3.99);

-- Order 12104 | Suman | greengrocer + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12104, 7103, 2, 5.49);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12104, 7111, 1, 1.89);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12104, 7135, 1, 3.25);

-- Order 12105 | Sophie | fish + deli + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12105, 7123, 1, 7.49);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12105, 7134, 2, 2.95);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12105, 7147, 1, 3.95);

-- Order 12106 | George | deli + greengrocer + butcher
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12106, 7141, 1, 6.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12106, 7114, 1, 4.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12106, 7106, 2, 3.75);

-- Order 12107 | Priya | bakery + greengrocer
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12107, 7133, 2, 2.25);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12107, 7118, 1, 3.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12107, 7119, 2, 0.75);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12107, 7136, 1, 3.50);

-- Order 12108 | Dipesh | fish + butcher
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12108, 7129, 1, 8.99);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12108, 7108, 2, 5.99);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12108, 7122, 1, 5.25);

-- Order 12109 | Anita | deli + greengrocer
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12109, 7145, 1, 4.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12109, 7144, 1, 4.10);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12109, 7116, 2, 1.65);

-- Order 12110 | Emily | fish + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12110, 7125, 1, 9.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12110, 7139, 2, 2.20);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12110, 7140, 1, 2.10);

-- Order 12111 | Aarav | butcher + greengrocer + deli
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12111, 7109, 1, 11.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12111, 7117, 1, 2.10);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12111, 7148, 1, 5.25);

-- Order 12112 | Oliver | fish + deli (PENDING - no payment)
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12112, 7124, 1, 8.25);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12112, 7150, 1, 4.75);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12112, 7146, 1, 6.25);

-- Order 12113 | Suman | butcher + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12113, 7104, 1, 9.75);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12113, 7110, 1, 3.49);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12113, 7138, 2, 2.50);

-- Order 12114 | James | greengrocer + deli (PENDING - no payment)
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12114, 7115, 3, 0.89);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12114, 7120, 1, 1.25);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12114, 7149, 1, 7.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12114, 7141, 1, 6.50);

-- Order 12115 | George | fish + greengrocer + bakery
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12115, 7128, 2, 4.50);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12115, 7113, 1, 2.20);
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, unit_price) VALUES (12115, 7137, 2, 2.75);

-- ============================================================
-- 14. PAYMENT
--     COLLECTED and PAID orders have completed payments.
--     PENDING orders have no payment record.
-- ============================================================

-- 12101: 8.99 + 3.50 + 3.25 - 10.00(WELCOME10) = 5.74
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13101, 12101, DATE '2026-05-11', 5.74, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260511-00101');

-- 12102: 6.99*2 + 2.75 + 5.75 = 22.48
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13102, 12102, DATE '2026-05-12', 22.48, 'STRIPE', 'COMPLETED', 'STR-TXN-20260512-00202');

-- 12103: 12.50 + 5.75 + 3.99*2 - 5.00(CLECK5) = 21.23
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13103, 12103, DATE '2026-05-11', 21.23, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260511-00303');

-- 12104: 5.49*2 + 1.89 + 3.25 = 16.12
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13104, 12104, DATE '2026-05-13', 16.12, 'STRIPE', 'COMPLETED', 'STR-TXN-20260513-00404');

-- 12105: 7.49 + 2.95*2 + 3.95 = 17.34
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13105, 12105, DATE '2026-05-12', 17.34, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260512-00505');

-- 12106: 6.50 + 4.50 + 3.75*2 = 18.50
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13106, 12106, DATE '2026-05-13', 18.50, 'STRIPE', 'COMPLETED', 'STR-TXN-20260513-00606');

-- 12107: 2.25*2 + 3.50 + 0.75*2 + 3.50 - 10.00(WELCOME10) = 3.00
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13107, 12107, DATE '2026-05-11', 3.00, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260511-00707');

-- 12108: 8.99 + 5.99*2 + 5.25 = 26.22
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13108, 12108, DATE '2026-05-12', 26.22, 'STRIPE', 'COMPLETED', 'STR-TXN-20260512-00808');

-- 12109: 4.50 + 4.10 + 1.65*2 = 11.90
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13109, 12109, DATE '2026-05-18', 11.90, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260518-00909');

-- 12110: 9.50 + 2.20*2 + 2.10 = 16.00
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13110, 12110, DATE '2026-05-19', 16.00, 'STRIPE', 'COMPLETED', 'STR-TXN-20260519-01010');

-- 12111: 11.50 + 2.10 + 5.25 - 10.00(WELCOME10) = 8.85
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13111, 12111, DATE '2026-05-18', 8.85, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260518-01111');

-- 12113: 9.75 + 3.49 + 2.50*2 = 18.24
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13112, 12113, DATE '2026-05-19', 18.24, 'STRIPE', 'COMPLETED', 'STR-TXN-20260519-01313');

-- 12115: 4.50*2 + 2.20 + 2.75*2 = 16.70
INSERT INTO PAYMENT (payment_id, order_id, payment_date, amount_paid, payment_method, payment_status, transaction_reference)
VALUES (13113, 12115, DATE '2026-05-18', 16.70, 'PAYPAL', 'COMPLETED', 'PP-TXN-20260518-01515');

-- ============================================================
-- 15. INVOICE  (one per completed payment)
-- ============================================================
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14101, 12101,  5.74, DATE '2026-05-11', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14102, 12102, 22.48, DATE '2026-05-12', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14103, 12103, 21.23, DATE '2026-05-11', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14104, 12104, 16.12, DATE '2026-05-13', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14105, 12105, 17.34, DATE '2026-05-12', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14106, 12106, 18.50, DATE '2026-05-13', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14107, 12107,  3.00, DATE '2026-05-11', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14108, 12108, 26.22, DATE '2026-05-12', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14109, 12109, 11.90, DATE '2026-05-18', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14110, 12110, 16.00, DATE '2026-05-19', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14111, 12111,  8.85, DATE '2026-05-18', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14112, 12113, 18.24, DATE '2026-05-19', 'ISSUED');
INSERT INTO INVOICE (invoice_id, order_id, amount, generated_date, invoice_status) VALUES (14113, 12115, 16.70, DATE '2026-05-18', 'ISSUED');

-- ============================================================
-- 16. REVIEW
--     Only customers with COLLECTED orders can review.
--     23 reviews spread across all 5 shops.
-- ============================================================

-- Aarav (order 12101 COLLECTED - butcher/bakery/deli)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15101, 1001, 7101, 5, 'Absolutely stunning sirloin - perfectly marbled and cooked beautifully.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15102, 1001, 7131, 5, 'Best sourdough I have had outside of London. Crispy crust, chewy inside.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15103, 1001, 7143, 4, 'Lovely olives, well marinated. Will order again next week.', DATE '2026-05-13');

-- Oliver (order 12102 COLLECTED - fish/greengrocer)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15104, 1006, 7121, 4, 'Lovely fresh salmon, cooked well with some lemon. Would buy again.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15105, 1006, 7112, 5, 'Gorgeous heritage tomatoes - great variety and wonderful flavour.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15106, 1006, 7126, 5, 'Incredible smoked salmon slices, perfect on bagels. Highly recommended.', DATE '2026-05-14');

-- James (order 12103 COLLECTED - butcher/deli/bakery)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15107, 1008, 7102, 5, 'The lamb shoulder was incredible after slow roasting - incredibly tender.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15108, 1008, 7142, 3, 'Good manchego, could be a bit stronger. Still enjoyed it on the cheeseboard.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15109, 1008, 7132, 4, 'Buttery and flaky croissants, almost as good as France. Great with coffee.', DATE '2026-05-13');

-- Suman (order 12104 COLLECTED - greengrocer/bakery)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15110, 1003, 7103, 4, 'Pork belly was great on the BBQ. Nice thick cut, cooked evenly.', DATE '2026-05-15');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15111, 1003, 7111, 5, 'Freshest salad bag I have bought in a long time. Crisp and tasty.', DATE '2026-05-15');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15112, 1003, 7135, 4, 'Love the seeded wholemeal loaf - hearty and filling. Great for sandwiches.', DATE '2026-05-15');

-- Sophie (order 12105 COLLECTED - fish/deli/bakery)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15113, 1007, 7123, 5, 'Beautiful cod fillet, very fresh and flaked perfectly. Excellent quality.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15114, 1007, 7134, 4, 'Cheese and onion pasty was proper good. Pastry was lovely and flaky.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15115, 1007, 7147, 4, 'Hummus was smooth and flavourful. Flatbreads were warm and soft.', DATE '2026-05-14');

-- George (order 12106 COLLECTED - deli/greengrocer/butcher)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15116, 1010, 7141, 5, 'Prosciutto was melt-in-your-mouth quality. Worth every penny.', DATE '2026-05-15');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15117, 1010, 7106, 5, 'Best sausages I have had from a proper butcher. Lovely herb flavour.', DATE '2026-05-15');

-- Priya (order 12107 COLLECTED - bakery/greengrocer)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15118, 1002, 7133, 5, 'Yorkshire Parkin was exactly as I remember from my gran. Absolutely delicious.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15119, 1002, 7118, 4, 'Berry mix was fresh and sweet. Great value for the quantity.', DATE '2026-05-13');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15120, 1002, 7136, 5, 'Sticky toffee pudding was divine. Rich sauce, moist sponge. Five stars.', DATE '2026-05-13');

-- Dipesh (order 12108 COLLECTED - fish/butcher)
INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15121, 1005, 7129, 4, 'Tuna steak was very fresh. Seared it in a pan with soy - brilliant.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15122, 1005, 7108, 5, 'Chicken breasts were thick and juicy. Free-range quality is noticeable.', DATE '2026-05-14');

INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date)
VALUES (15123, 1005, 7122, 4, 'Smoked haddock was excellent. Made a creamy chowder - family loved it.', DATE '2026-05-14');

-- ============================================================
-- COMMIT TRANSACTIONS
-- ============================================================
COMMIT;