<?php
// This file provides a fallback mock database mechanism to keep the site running if the Oracle database goes offline.

require_once __DIR__ . '/bootstrap.php';

// Handles the core logic and operations for offline_data_file
function offline_data_file(): string
{
    return __DIR__ . '/../data/offline_db.json';
}

// Handles the core logic and operations for offline_default_data
function offline_default_data(): array
{
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

    return [
        'users' => [
            ['user_id' => 1001, 'first_name' => 'Aarav', 'last_name' => 'Sharma', 'gender' => 'M', 'email' => 'aarav.sharma@gmail.com', 'password' => $passwordHash, 'phone_number' => '9841123456', 'address' => 'Lazimpat, Kathmandu, Nepal', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-01-26T00:00:00+00:00', 'updated_at' => '2026-04-16T00:00:00+00:00'],
            ['user_id' => 1002, 'first_name' => 'Priya', 'last_name' => 'Thapa', 'gender' => 'F', 'email' => 'priya.thapa@gmail.com', 'password' => $passwordHash, 'phone_number' => '9851234567', 'address' => 'Patan, Lalitpur, Nepal', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-01-31T00:00:00+00:00', 'updated_at' => '2026-04-21T00:00:00+00:00'],
            ['user_id' => 1003, 'first_name' => 'Suman', 'last_name' => 'Gurung', 'gender' => 'M', 'email' => 'suman.gurung@yahoo.com', 'password' => $passwordHash, 'phone_number' => '9860987654', 'address' => 'Bhaktapur, Nepal', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-02-05T00:00:00+00:00', 'updated_at' => '2026-04-06T00:00:00+00:00'],
            ['user_id' => 1004, 'first_name' => 'Anita', 'last_name' => 'Rai', 'gender' => 'F', 'email' => 'anita.rai@hotmail.com', 'password' => $passwordHash, 'phone_number' => '9875432100', 'address' => 'Pokhara, Gandaki, Nepal', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-02-10T00:00:00+00:00', 'updated_at' => '2026-04-18T00:00:00+00:00'],
            ['user_id' => 1005, 'first_name' => 'Dipesh', 'last_name' => 'Magar', 'gender' => 'M', 'email' => 'dipesh.magar@gmail.com', 'password' => $passwordHash, 'phone_number' => '9812000111', 'address' => 'Thamel, Kathmandu, Nepal', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-02-15T00:00:00+00:00', 'updated_at' => '2026-04-23T00:00:00+00:00'],
            ['user_id' => 1006, 'first_name' => 'Oliver', 'last_name' => 'Thompson', 'gender' => 'M', 'email' => 'oliver.thompson@gmail.com', 'password' => $passwordHash, 'phone_number' => '07700900123', 'address' => '14 Church Lane, Cleckhuddersfax, West Yorkshire, WF17 8AB', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-02-20T00:00:00+00:00', 'updated_at' => '2026-04-14T00:00:00+00:00'],
            ['user_id' => 1007, 'first_name' => 'Sophie', 'last_name' => 'Williams', 'gender' => 'F', 'email' => 'sophie.w@outlook.com', 'password' => $passwordHash, 'phone_number' => '07700900456', 'address' => '3 Westgate Road, Cleckhuddersfax, West Yorkshire, WF17 9CD', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-02-25T00:00:00+00:00', 'updated_at' => '2026-04-19T00:00:00+00:00'],
            ['user_id' => 1008, 'first_name' => 'James', 'last_name' => 'Hartley', 'gender' => 'M', 'email' => 'james.hartley@gmail.com', 'password' => $passwordHash, 'phone_number' => '07700900789', 'address' => '27 Market Street, Cleckhuddersfax, West Yorkshire, WF17 5EF', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-03-02T00:00:00+00:00', 'updated_at' => '2026-04-11T00:00:00+00:00'],
            ['user_id' => 1009, 'first_name' => 'Emily', 'last_name' => 'Broadbent', 'gender' => 'F', 'email' => 'emily.broad@yahoo.co.uk', 'password' => $passwordHash, 'phone_number' => '07700900321', 'address' => '8 Mill Close, Cleckhuddersfax, West Yorkshire, WF17 2GH', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-03-07T00:00:00+00:00', 'updated_at' => '2026-04-24T00:00:00+00:00'],
            ['user_id' => 1010, 'first_name' => 'George', 'last_name' => 'Pickles', 'gender' => 'M', 'email' => 'george.pickles@btinternet.com', 'password' => $passwordHash, 'phone_number' => '07700900654', 'address' => '52 Towngate, Cleckhuddersfax, West Yorkshire, WF17 6IJ', 'role' => 'CUSTOMER', 'profile_picture_path' => null, 'created_at' => '2026-03-12T00:00:00+00:00', 'updated_at' => '2026-04-20T00:00:00+00:00'],
            ['user_id' => 1011, 'first_name' => 'Robert', 'last_name' => 'Firth', 'gender' => 'M', 'email' => 'robert.firth@firth-butchers.co.uk', 'password' => $passwordHash, 'phone_number' => '07800100001', 'address' => '1 High Street, Cleckhuddersfax, WF17 1AA', 'role' => 'TRADER', 'profile_picture_path' => null, 'created_at' => '2025-10-08T00:00:00+00:00', 'updated_at' => '2026-03-27T00:00:00+00:00'],
            ['user_id' => 1012, 'first_name' => 'Margaret', 'last_name' => 'Greenwood', 'gender' => 'F', 'email' => 'margaret@greenwoods-veg.co.uk', 'password' => $passwordHash, 'phone_number' => '07800100002', 'address' => '3 High Street, Cleckhuddersfax, WF17 1AB', 'role' => 'TRADER', 'profile_picture_path' => null, 'created_at' => '2025-10-13T00:00:00+00:00', 'updated_at' => '2026-03-27T00:00:00+00:00'],
            ['user_id' => 1013, 'first_name' => 'Harold', 'last_name' => 'Fishwick', 'gender' => 'M', 'email' => 'harold@fishwicks-fish.co.uk', 'password' => $passwordHash, 'phone_number' => '07800100003', 'address' => '5 High Street, Cleckhuddersfax, WF17 1AC', 'role' => 'TRADER', 'profile_picture_path' => null, 'created_at' => '2025-10-18T00:00:00+00:00', 'updated_at' => '2026-03-27T00:00:00+00:00'],
            ['user_id' => 1014, 'first_name' => 'Dorothy', 'last_name' => 'Bakers', 'gender' => 'F', 'email' => 'dorothy@thedough.co.uk', 'password' => $passwordHash, 'phone_number' => '07800100004', 'address' => '7 High Street, Cleckhuddersfax, WF17 1AD', 'role' => 'TRADER', 'profile_picture_path' => null, 'created_at' => '2025-10-23T00:00:00+00:00', 'updated_at' => '2026-03-27T00:00:00+00:00'],
            ['user_id' => 1015, 'first_name' => 'Luigi', 'last_name' => 'Caruso', 'gender' => 'M', 'email' => 'luigi@claesdelideli.co.uk', 'password' => $passwordHash, 'phone_number' => '07800100005', 'address' => '9 High Street, Cleckhuddersfax, WF17 1AE', 'role' => 'TRADER', 'profile_picture_path' => null, 'created_at' => '2025-10-28T00:00:00+00:00', 'updated_at' => '2026-03-27T00:00:00+00:00'],
            ['user_id' => 1016, 'first_name' => 'Admin', 'last_name' => 'Cleck', 'gender' => 'M', 'email' => 'admin@cleckemart.co.uk', 'password' => $passwordHash, 'phone_number' => '07900999999', 'address' => 'Cleck E-Mart HQ, WF17 1ZZ', 'role' => 'ADMIN', 'profile_picture_path' => null, 'created_at' => '2025-04-26T00:00:00+00:00', 'updated_at' => '2026-04-26T00:00:00+00:00'],
        ],
        'customers' => [
            ['customer_id' => 1001, 'loyalty_points' => 120],
            ['customer_id' => 1002, 'loyalty_points' => 55],
            ['customer_id' => 1003, 'loyalty_points' => 300],
            ['customer_id' => 1004, 'loyalty_points' => 0],
            ['customer_id' => 1005, 'loyalty_points' => 85],
            ['customer_id' => 1006, 'loyalty_points' => 200],
            ['customer_id' => 1007, 'loyalty_points' => 15],
            ['customer_id' => 1008, 'loyalty_points' => 450],
            ['customer_id' => 1009, 'loyalty_points' => 30],
            ['customer_id' => 1010, 'loyalty_points' => 0],
        ],
        'traders' => [
            ['trader_id' => 1011, 'brand_name' => "Firth's Butchers", 'pan_number' => 'PAN-UK-11001', 'trader_status' => 'VERIFIED'],
            ['trader_id' => 1012, 'brand_name' => "Greenwood's Greengrocers", 'pan_number' => 'PAN-UK-11002', 'trader_status' => 'VERIFIED'],
            ['trader_id' => 1013, 'brand_name' => "Fishwick's Fishmonger", 'pan_number' => 'PAN-UK-11003', 'trader_status' => 'VERIFIED'],
            ['trader_id' => 1014, 'brand_name' => 'The Dough Bakery', 'pan_number' => 'PAN-UK-11004', 'trader_status' => 'VERIFIED'],
            ['trader_id' => 1015, 'brand_name' => 'Claes Deli & Delicatessen', 'pan_number' => 'PAN-UK-11005', 'trader_status' => 'VERIFIED'],
        ],
        'admins' => [
            ['admin_id' => 1016, 'privileges' => 'FULL_ACCESS'],
        ],
        'categories' => [
            ['category_id' => 6101, 'category_name' => 'Meat & Poultry', 'category_description' => 'Fresh cuts of beef, lamb, pork, chicken and game'],
            ['category_id' => 6102, 'category_name' => 'Fruit & Vegetables', 'category_description' => 'Fresh seasonal produce sourced from local farms'],
            ['category_id' => 6103, 'category_name' => 'Fish & Seafood', 'category_description' => 'Fresh and smoked fish plus shellfish and seafood'],
            ['category_id' => 6104, 'category_name' => 'Bread & Bakery', 'category_description' => 'Artisan breads, pastries, cakes and savoury bakes'],
            ['category_id' => 6105, 'category_name' => 'Deli & Charcuterie', 'category_description' => 'Continental meats, cheeses, olives and specialty foods'],
        ],
        'discounts' => [
            ['discount_id' => 8101, 'discount_percentage' => 10, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'discount_status' => 'EXPIRED'],
            ['discount_id' => 8102, 'discount_percentage' => 15, 'start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'discount_status' => 'EXPIRED'],
            ['discount_id' => 8103, 'discount_percentage' => 5, 'start_date' => '2026-03-01', 'end_date' => '2026-03-31', 'discount_status' => 'EXPIRED'],
            ['discount_id' => 8104, 'discount_percentage' => 20, 'start_date' => '2026-04-01', 'end_date' => '2026-04-07', 'discount_status' => 'EXPIRED'],
            ['discount_id' => 8105, 'discount_percentage' => 10, 'start_date' => '2026-04-26', 'end_date' => '2026-05-26', 'discount_status' => 'ACTIVE'],
        ],
        'shops' => [
            ['shop_id' => 5101, 'trader_id' => 1011, 'shop_name' => "Firth's Butchers", 'shop_description' => 'Family-run butchers on Cleck High Street since 1972. Locally sourced beef, lamb, pork and poultry.', 'shop_logo' => 'firth_logo.png', 'shop_status' => 'ACTIVE'],
            ['shop_id' => 5102, 'trader_id' => 1012, 'shop_name' => "Greenwood's Greengrocers", 'shop_description' => 'Fresh seasonal fruit and vegetables direct from Yorkshire farms and local markets every morning.', 'shop_logo' => 'greenwood_logo.png', 'shop_status' => 'ACTIVE'],
            ['shop_id' => 5103, 'trader_id' => 1013, 'shop_name' => "Fishwick's Fishmonger", 'shop_description' => 'Daily catch from Grimsby and Whitby. Smoked, fresh and shellfish available.', 'shop_logo' => 'fishwick_logo.png', 'shop_status' => 'ACTIVE'],
            ['shop_id' => 5104, 'trader_id' => 1014, 'shop_name' => 'The Dough Bakery', 'shop_description' => 'Artisan bread, pastries, cakes and pies baked fresh every morning. Gluten-free options available.', 'shop_logo' => 'dough_logo.png', 'shop_status' => 'ACTIVE'],
            ['shop_id' => 5105, 'trader_id' => 1015, 'shop_name' => 'Claes Deli & Delicatessen', 'shop_description' => 'Continental meats, cheeses, olives, antipasti and specialist condiments. Dine-in and takeaway platters.', 'shop_logo' => 'claes_logo.png', 'shop_status' => 'ACTIVE'],
        ],
        'products' => [
            ['product_id' => 7101, 'shop_id' => 5101, 'category_id' => 6101, 'discount_id' => null, 'product_name' => 'Beef Sirloin Steak (200g)', 'product_description' => 'Prime 21-day dry-aged Yorkshire beef sirloin. Perfect for grilling or pan-frying.', 'price' => 8.99, 'stock_quantity' => 40, 'product_status' => 'IN_STOCK', 'product_verification_status' => 'APPROVED', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'beef_sirloin.jpg'],
            ['product_id' => 7102, 'shop_id' => 5101, 'category_id' => 6101, 'discount_id' => 8105, 'product_name' => 'Lamb Shoulder (per kg)', 'product_description' => 'Locally sourced Yorkshire lamb shoulder - ideal for slow roasting.', 'price' => 12.50, 'stock_quantity' => 25, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'lamb_shoulder.jpg'],
            ['product_id' => 7103, 'shop_id' => 5101, 'category_id' => 6101, 'discount_id' => null, 'product_name' => 'Pork Belly Slices (500g)', 'product_description' => 'Hand-prepared pork belly, great for BBQ or slow-roasting. Allergens: none.', 'price' => 5.49, 'stock_quantity' => 30, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'pork_belly.jpg'],
            ['product_id' => 7104, 'shop_id' => 5101, 'category_id' => 6101, 'discount_id' => null, 'product_name' => 'Free-Range Whole Chicken (1.5kg)', 'product_description' => 'Free-range Yorkshire chicken, oven-ready. Allergens: none.', 'price' => 9.75, 'stock_quantity' => 20, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'whole_chicken.jpg'],
            ['product_id' => 7105, 'shop_id' => 5102, 'category_id' => 6102, 'discount_id' => null, 'product_name' => 'Mixed Salad Bag (250g)', 'product_description' => 'Baby leaves, rocket and spinach grown in Yorkshire greenhouses.', 'price' => 1.89, 'stock_quantity' => 80, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'salad_bag.jpg'],
            ['product_id' => 7106, 'shop_id' => 5102, 'category_id' => 6102, 'discount_id' => 8105, 'product_name' => 'Heritage Tomatoes (500g)', 'product_description' => 'Mixed heritage tomatoes - heirloom varieties with rich flavour.', 'price' => 2.75, 'stock_quantity' => 60, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'heritage_tomatoes.jpg'],
            ['product_id' => 7107, 'shop_id' => 5102, 'category_id' => 6102, 'discount_id' => null, 'product_name' => 'Braeburn Apples (pack of 6)', 'product_description' => 'Crisp sweet-sharp British apples from local orchards.', 'price' => 2.20, 'stock_quantity' => 100, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'apples.jpg'],
            ['product_id' => 7108, 'shop_id' => 5102, 'category_id' => 6102, 'discount_id' => null, 'product_name' => 'Roasting Vegetable Box', 'product_description' => 'Seasonal mix of parsnips, carrots, beetroot and red onions. Serves 4.', 'price' => 4.50, 'stock_quantity' => 35, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'roast_veg_box.jpg'],
            ['product_id' => 7109, 'shop_id' => 5103, 'category_id' => 6103, 'discount_id' => null, 'product_name' => 'Atlantic Salmon Fillet (per portion 180g)', 'product_description' => 'Fresh Atlantic salmon fillet. Allergens: Fish.', 'price' => 6.99, 'stock_quantity' => 45, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Fish', 'min_order' => 1, 'max_order' => null, 'product_image' => 'salmon_fillet.jpg'],
            ['product_id' => 7110, 'shop_id' => 5103, 'category_id' => 6103, 'discount_id' => 8105, 'product_name' => 'Smoked Haddock (200g)', 'product_description' => 'Traditionally oak-smoked haddock from Whitby. Allergens: Fish.', 'price' => 5.25, 'stock_quantity' => 30, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Fish', 'min_order' => 1, 'max_order' => null, 'product_image' => 'smoked_haddock.jpg'],
            ['product_id' => 7111, 'shop_id' => 5103, 'category_id' => 6103, 'discount_id' => null, 'product_name' => 'North Sea Cod Fillet (per portion 180g)', 'product_description' => 'Line-caught North Sea cod. Allergens: Fish.', 'price' => 7.49, 'stock_quantity' => 35, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Fish', 'min_order' => 1, 'max_order' => null, 'product_image' => 'cod_fillet.jpg'],
            ['product_id' => 7112, 'shop_id' => 5103, 'category_id' => 6103, 'discount_id' => null, 'product_name' => 'King Prawns (200g)', 'product_description' => 'Raw king prawns, shell-on from sustainable fisheries. Allergens: Crustaceans.', 'price' => 8.25, 'stock_quantity' => 20, 'product_status' => 'LOW_STOCK', 'allergy_information' => 'Crustaceans', 'min_order' => 1, 'max_order' => null, 'product_image' => 'king_prawns.jpg'],
            ['product_id' => 7113, 'shop_id' => 5104, 'category_id' => 6104, 'discount_id' => null, 'product_name' => 'Sourdough Loaf (800g)', 'product_description' => 'Slow-fermented sourdough with a crispy crust. Allergens: Gluten.', 'price' => 3.50, 'stock_quantity' => 50, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Gluten', 'min_order' => 1, 'max_order' => null, 'product_image' => 'sourdough_loaf.jpg'],
            ['product_id' => 7114, 'shop_id' => 5104, 'category_id' => 6104, 'discount_id' => 8105, 'product_name' => 'All-Butter Croissant (x4)', 'product_description' => 'Classic French-style butter croissants, baked fresh every morning. Allergens: Gluten, Dairy, Eggs.', 'price' => 3.99, 'stock_quantity' => 40, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Gluten, Dairy, Eggs', 'min_order' => 1, 'max_order' => null, 'product_image' => 'croissants.jpg'],
            ['product_id' => 7115, 'shop_id' => 5104, 'category_id' => 6104, 'discount_id' => null, 'product_name' => 'Yorkshire Parkin (slice)', 'product_description' => 'Traditional oat and treacle gingerbread cake. Allergens: Gluten, Dairy, Eggs.', 'price' => 2.25, 'stock_quantity' => 60, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Gluten, Dairy, Eggs', 'min_order' => 1, 'max_order' => null, 'product_image' => 'parkin.jpg'],
            ['product_id' => 7116, 'shop_id' => 5104, 'category_id' => 6104, 'discount_id' => null, 'product_name' => 'Cheese & Onion Pasty', 'product_description' => 'Shortcrust pastry filled with mature cheddar and caramelised onion. Allergens: Gluten, Dairy, Eggs.', 'price' => 2.95, 'stock_quantity' => 45, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Gluten, Dairy, Eggs', 'min_order' => 1, 'max_order' => null, 'product_image' => 'pasty.jpg'],
            ['product_id' => 7117, 'shop_id' => 5105, 'category_id' => 6105, 'discount_id' => null, 'product_name' => 'Italian Prosciutto (100g)', 'product_description' => 'Hand-sliced DOP Prosciutto di Parma, aged 18 months. Allergens: none.', 'price' => 6.50, 'stock_quantity' => 30, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'prosciutto.jpg'],
            ['product_id' => 7118, 'shop_id' => 5105, 'category_id' => 6105, 'discount_id' => 8105, 'product_name' => 'Manchego Cheese (150g)', 'product_description' => 'Spanish semi-cured sheep\'s milk cheese with a firm texture. Allergens: Dairy.', 'price' => 5.75, 'stock_quantity' => 25, 'product_status' => 'IN_STOCK', 'allergy_information' => 'Dairy', 'min_order' => 1, 'max_order' => null, 'product_image' => 'manchego.jpg'],
            ['product_id' => 7119, 'shop_id' => 5105, 'category_id' => 6105, 'discount_id' => null, 'product_name' => 'Mixed Marinated Olives (200g)', 'product_description' => 'Kalamata and Sicilian green olives in herb-infused oil. Allergens: none.', 'price' => 3.25, 'stock_quantity' => 40, 'product_status' => 'IN_STOCK', 'allergy_information' => null, 'min_order' => 1, 'max_order' => null, 'product_image' => 'mixed_olives.jpg'],
            ['product_id' => 7120, 'shop_id' => 5105, 'category_id' => 6105, 'discount_id' => null, 'product_name' => 'Sun-Dried Tomato Pesto (190g jar)', 'product_description' => 'Artisan pesto made with sun-dried tomatoes, basil and pine nuts. Allergens: Nuts, Dairy.', 'price' => 4.10, 'stock_quantity' => 35, 'product_status' => 'IN_STOCK', 'product_verification_status' => 'APPROVED', 'allergy_information' => 'Nuts, Dairy', 'min_order' => 1, 'max_order' => null, 'product_image' => 'pesto_jar.jpg'],
        ],
        'collection_slots' => [
            ['slot_id' => 501, 'slot_time' => '10:00-13:00', 'slot_date' => '2026-04-15', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 502, 'slot_time' => '13:00-16:00', 'slot_date' => '2026-04-15', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 503, 'slot_time' => '16:00-19:00', 'slot_date' => '2026-04-15', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 504, 'slot_time' => '10:00-13:00', 'slot_date' => '2026-04-16', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 505, 'slot_time' => '13:00-16:00', 'slot_date' => '2026-04-16', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 506, 'slot_time' => '16:00-19:00', 'slot_date' => '2026-04-16', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 507, 'slot_time' => '10:00-13:00', 'slot_date' => '2026-04-17', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 508, 'slot_time' => '13:00-16:00', 'slot_date' => '2026-04-17', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 509, 'slot_time' => '16:00-19:00', 'slot_date' => '2026-04-17', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 510, 'slot_time' => '10:00-13:00', 'slot_date' => '2026-04-22', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 511, 'slot_time' => '13:00-16:00', 'slot_date' => '2026-04-22', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
            ['slot_id' => 512, 'slot_time' => '16:00-19:00', 'slot_date' => '2026-04-22', 'max_orders' => 20, 'slot_status' => 'AVAILABLE'],
        ],
        'coupons' => [
            ['coupon_id' => 9101, 'coupon_code' => 'WELCOME10', 'discount_amount' => 10.00, 'valid_from' => '2026-04-01', 'valid_to' => '2026-06-30', 'minimum_order_amount' => 20.00, 'coupon_status' => 'ACTIVE'],
            ['coupon_id' => 9102, 'coupon_code' => 'CLECK5', 'discount_amount' => 5.00, 'valid_from' => '2026-04-01', 'valid_to' => '2026-04-30', 'minimum_order_amount' => 15.00, 'coupon_status' => 'ACTIVE'],
            ['coupon_id' => 9103, 'coupon_code' => 'XMAS2026', 'discount_amount' => 15.00, 'valid_from' => '2026-12-01', 'valid_to' => '2026-12-31', 'minimum_order_amount' => 30.00, 'coupon_status' => 'INACTIVE'],
            ['coupon_id' => 9104, 'coupon_code' => 'FRESH20', 'discount_amount' => 20.00, 'valid_from' => '2026-03-01', 'valid_to' => '2026-03-31', 'minimum_order_amount' => 40.00, 'coupon_status' => 'EXPIRED'],
        ],
        'wishlists' => [
            ['wishlist_id' => 10101, 'customer_id' => 1001, 'created_at' => '2026-04-06'],
            ['wishlist_id' => 10102, 'customer_id' => 1006, 'created_at' => '2026-04-11'],
            ['wishlist_id' => 10103, 'customer_id' => 1008, 'created_at' => '2026-04-21'],
        ],
        'wishlist_items' => [
            ['wishlist_id' => 10101, 'product_id' => 7101, 'added_date' => '2026-04-08'],
            ['wishlist_id' => 10101, 'product_id' => 7113, 'added_date' => '2026-04-16'],
            ['wishlist_id' => 10102, 'product_id' => 7109, 'added_date' => '2026-04-12'],
            ['wishlist_id' => 10102, 'product_id' => 7118, 'added_date' => '2026-04-14'],
            ['wishlist_id' => 10103, 'product_id' => 7106, 'added_date' => '2026-04-22'],
        ],
        'carts' => [
            ['cart_id' => 11101, 'customer_id' => 1003, 'cart_status' => 'ACTIVE', 'created_at' => '2026-04-20'],
            ['cart_id' => 11102, 'customer_id' => 1007, 'cart_status' => 'ACTIVE', 'created_at' => '2026-04-20'],
            ['cart_id' => 11103, 'customer_id' => 1009, 'cart_status' => 'CHECKED_OUT', 'created_at' => '2026-04-18'],
        ],
        'cart_items' => [
            ['cart_id' => 11101, 'product_id' => 7103, 'quantity' => 1, 'unit_price' => 5.49],
            ['cart_id' => 11101, 'product_id' => 7107, 'quantity' => 2, 'unit_price' => 2.20],
            ['cart_id' => 11101, 'product_id' => 7114, 'quantity' => 1, 'unit_price' => 3.99],
            ['cart_id' => 11102, 'product_id' => 7109, 'quantity' => 2, 'unit_price' => 6.99],
            ['cart_id' => 11102, 'product_id' => 7119, 'quantity' => 1, 'unit_price' => 3.25],
            ['cart_id' => 11103, 'product_id' => 7101, 'quantity' => 1, 'unit_price' => 8.99],
            ['cart_id' => 11103, 'product_id' => 7113, 'quantity' => 1, 'unit_price' => 3.50],
        ],
        'orders' => [
            ['order_id' => 12101, 'customer_id' => 1001, 'slot_id' => 501, 'coupon_id' => 9101, 'order_date' => '2026-04-13', 'order_status' => 'COLLECTED'],
            ['order_id' => 12102, 'customer_id' => 1006, 'slot_id' => 504, 'coupon_id' => null, 'order_date' => '2026-04-14', 'order_status' => 'COLLECTED'],
            ['order_id' => 12103, 'customer_id' => 1008, 'slot_id' => 502, 'coupon_id' => 9102, 'order_date' => '2026-04-14', 'order_status' => 'COLLECTED'],
            ['order_id' => 12104, 'customer_id' => 1003, 'slot_id' => 507, 'coupon_id' => null, 'order_date' => '2026-04-15', 'order_status' => 'PAID'],
            ['order_id' => 12105, 'customer_id' => 1009, 'slot_id' => 509, 'coupon_id' => null, 'order_date' => '2026-04-16', 'order_status' => 'PAID'],
            ['order_id' => 12106, 'customer_id' => 1004, 'slot_id' => 511, 'coupon_id' => null, 'order_date' => '2026-04-17', 'order_status' => 'PENDING'],
        ],
        'order_items' => [
            ['order_id' => 12101, 'product_id' => 7101, 'quantity' => 1, 'unit_price' => 8.99],
            ['order_id' => 12101, 'product_id' => 7113, 'quantity' => 1, 'unit_price' => 3.50],
            ['order_id' => 12101, 'product_id' => 7119, 'quantity' => 1, 'unit_price' => 3.25],
            ['order_id' => 12102, 'product_id' => 7109, 'quantity' => 2, 'unit_price' => 6.99],
            ['order_id' => 12102, 'product_id' => 7106, 'quantity' => 1, 'unit_price' => 2.75],
            ['order_id' => 12103, 'product_id' => 7102, 'quantity' => 1, 'unit_price' => 12.50],
            ['order_id' => 12103, 'product_id' => 7118, 'quantity' => 1, 'unit_price' => 5.75],
            ['order_id' => 12103, 'product_id' => 7114, 'quantity' => 2, 'unit_price' => 3.99],
            ['order_id' => 12104, 'product_id' => 7103, 'quantity' => 2, 'unit_price' => 5.49],
            ['order_id' => 12104, 'product_id' => 7105, 'quantity' => 1, 'unit_price' => 1.89],
            ['order_id' => 12105, 'product_id' => 7111, 'quantity' => 1, 'unit_price' => 7.49],
            ['order_id' => 12105, 'product_id' => 7116, 'quantity' => 2, 'unit_price' => 2.95],
            ['order_id' => 12106, 'product_id' => 7117, 'quantity' => 1, 'unit_price' => 6.50],
            ['order_id' => 12106, 'product_id' => 7108, 'quantity' => 1, 'unit_price' => 4.50],
        ],
        'payments' => [
            ['payment_id' => 13101, 'order_id' => 12101, 'payment_date' => '2026-04-13', 'amount_paid' => 5.74, 'payment_method' => 'PAYPAL', 'payment_status' => 'COMPLETED', 'transaction_reference' => 'PP-TXN-20260413-00101'],
            ['payment_id' => 13102, 'order_id' => 12102, 'payment_date' => '2026-04-14', 'amount_paid' => 16.73, 'payment_method' => 'STRIPE', 'payment_status' => 'COMPLETED', 'transaction_reference' => 'STR-TXN-20260414-00202'],
            ['payment_id' => 13103, 'order_id' => 12103, 'payment_date' => '2026-04-14', 'amount_paid' => 21.23, 'payment_method' => 'PAYPAL', 'payment_status' => 'COMPLETED', 'transaction_reference' => 'PP-TXN-20260414-00303'],
            ['payment_id' => 13104, 'order_id' => 12104, 'payment_date' => '2026-04-15', 'amount_paid' => 12.87, 'payment_method' => 'STRIPE', 'payment_status' => 'COMPLETED', 'transaction_reference' => 'STR-TXN-20260415-00404'],
            ['payment_id' => 13105, 'order_id' => 12105, 'payment_date' => '2026-04-16', 'amount_paid' => 13.39, 'payment_method' => 'PAYPAL', 'payment_status' => 'COMPLETED', 'transaction_reference' => 'PP-TXN-20260416-00505'],
        ],
        'invoices' => [
            ['invoice_id' => 14101, 'order_id' => 12101, 'amount' => 5.74, 'generated_date' => '2026-04-13', 'invoice_status' => 'ISSUED'],
            ['invoice_id' => 14102, 'order_id' => 12102, 'amount' => 16.73, 'generated_date' => '2026-04-14', 'invoice_status' => 'ISSUED'],
            ['invoice_id' => 14103, 'order_id' => 12103, 'amount' => 21.23, 'generated_date' => '2026-04-14', 'invoice_status' => 'ISSUED'],
            ['invoice_id' => 14104, 'order_id' => 12104, 'amount' => 12.87, 'generated_date' => '2026-04-15', 'invoice_status' => 'ISSUED'],
            ['invoice_id' => 14105, 'order_id' => 12105, 'amount' => 13.39, 'generated_date' => '2026-04-16', 'invoice_status' => 'ISSUED'],
        ],
        'reviews' => [
            ['review_id' => 15101, 'customer_id' => 1001, 'product_id' => 7101, 'rating' => 5, 'comment' => 'Absolutely stunning sirloin - perfectly marbled and cooked beautifully.', 'review_date' => '2026-04-16'],
            ['review_id' => 15102, 'customer_id' => 1001, 'product_id' => 7113, 'rating' => 5, 'comment' => 'Best sourdough I\'ve had outside of London. Crispy crust, chewy inside.', 'review_date' => '2026-04-16'],
            ['review_id' => 15103, 'customer_id' => 1006, 'product_id' => 7109, 'rating' => 4, 'comment' => 'Lovely fresh salmon, cooked well with some lemon. Would buy again.', 'review_date' => '2026-04-17'],
            ['review_id' => 15104, 'customer_id' => 1006, 'product_id' => 7106, 'rating' => 4, 'comment' => 'Gorgeous heritage tomatoes - great variety and wonderful flavour.', 'review_date' => '2026-04-17'],
            ['review_id' => 15105, 'customer_id' => 1008, 'product_id' => 7102, 'rating' => 5, 'comment' => 'The lamb shoulder was incredible after slow roasting - incredibly tender.', 'review_date' => '2026-04-16'],
            ['review_id' => 15106, 'customer_id' => 1008, 'product_id' => 7118, 'rating' => 3, 'comment' => 'Good manchego, could be a bit stronger. Still enjoyed it on the cheeseboard.', 'review_date' => '2026-04-16'],
        ],
    ];
}

// Handles the core logic and operations for offline_load
function offline_load(): array
{
    $file = offline_data_file();
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    if (!file_exists($file)) {
        $data = offline_default_data();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $data;
    }

    $content = file_get_contents($file);
    $data = json_decode($content ?: '', true);

    if (!is_array($data)) {
        $data = offline_default_data();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    return $data;
}

// Handles the core logic and operations for offline_save
function offline_save(array $data): void
{
    file_put_contents(offline_data_file(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Handles the core logic and operations for offline_next_id
function offline_next_id(array $rows, string $idField): int
{
    $max = 0;
    foreach ($rows as $row) {
        $value = isset($row[$idField]) ? (int) $row[$idField] : 0;
        if ($value > $max) {
            $max = $value;
        }
    }

    return $max + 1;
}

// Handles the core logic and operations for offline_user_by_email
function offline_user_by_email(string $email): ?array
{
    $data = offline_load();
    foreach ($data['users'] as $user) {
        if (strtolower((string) $user['email']) === strtolower($email)) {
            return offline_user_to_upper($user);
        }
    }

    return null;
}

// Handles the core logic and operations for offline_user_by_id
function offline_user_by_id(int $userId): ?array
{
    $data = offline_load();
    foreach ($data['users'] as $user) {
        if ((int) $user['user_id'] === $userId) {
            return offline_user_to_upper($user);
        }
    }

    return null;
}

// Handles the core logic and operations for offline_create_account
function offline_create_account(string $firstName, string $lastName, string $email, string $passwordHash, string $role): array
{
    $data = offline_load();

    $userId = offline_next_id($data['users'], 'user_id');
    $user = [
        'user_id' => $userId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'gender' => null,
        'email' => strtolower($email),
        'password' => $passwordHash,
        'phone_number' => null,
        'address' => null,
        'role' => strtoupper($role),
        'profile_picture_path' => null,
        'created_at' => date('c'),
        'updated_at' => null,
    ];

    $data['users'][] = $user;

    if (strtoupper($role) === 'TRADER') {
        $data['traders'][] = [
            'trader_id' => $userId,
            'brand_name' => null,
            'pan_number' => null,
            'trader_status' => 'PENDING_VERIFICATION',
        ];

        // Create a default SHOP for the trader
        $shopId = offline_next_id($data['shops'], 'shop_id');
        $defaultShopName = $firstName . "'s Shop";
        $data['shops'][] = [
            'shop_id' => $shopId,
            'trader_id' => $userId,
            'shop_name' => $defaultShopName,
            'shop_description' => 'Welcome to our shop!',
            'shop_logo' => null,
            'shop_status' => 'ACTIVE',
        ];
    } else {
        $data['customers'][] = [
            'customer_id' => $userId,
            'loyalty_points' => 0,
        ];
    }

    offline_save($data);

    return offline_user_to_upper($user);
}

// Handles the core logic and operations for offline_is_customer
function offline_is_customer(int $userId): bool
{
    $data = offline_load();
    foreach ($data['customers'] as $customer) {
        if ((int) $customer['customer_id'] === $userId) {
            return true;
        }
    }

    return false;
}

// Handles the core logic and operations for offline_update_user
function offline_update_user(int $userId, string $firstName, string $lastName, string $email, ?string $phone): void
{
    $data = offline_load();

    foreach ($data['users'] as $index => $user) {
        if ((int) $user['user_id'] !== $userId) {
            continue;
        }

        $data['users'][$index]['first_name'] = $firstName;
        $data['users'][$index]['last_name'] = $lastName;
        $data['users'][$index]['email'] = strtolower($email);
        $data['users'][$index]['phone_number'] = $phone;
        $data['users'][$index]['updated_at'] = date('c');
        offline_save($data);
        return;
    }

    throw new RuntimeException('User not found in offline store.');
}

// Handles the core logic and operations for offline_email_taken_by_other
function offline_email_taken_by_other(int $userId, string $email): bool
{
    $data = offline_load();
    foreach ($data['users'] as $user) {
        if ((int) $user['user_id'] === $userId) {
            continue;
        }

        if (strtolower((string) $user['email']) === strtolower($email)) {
            return true;
        }
    }

    return false;
}

// Handles the core logic and operations for offline_update_password
function offline_update_password(int $userId, string $passwordHash): void
{
    $data = offline_load();

    foreach ($data['users'] as $index => $user) {
        if ((int) $user['user_id'] !== $userId) {
            continue;
        }

        $data['users'][$index]['password'] = $passwordHash;
        $data['users'][$index]['updated_at'] = date('c');
        offline_save($data);
        return;
    }

    throw new RuntimeException('User not found in offline store.');
}

// Handles the core logic and operations for offline_get_category_name
function offline_get_category_name(?int $categoryId): string
{
    if ($categoryId === null) {
        return 'All Categories';
    }

    $data = offline_load();
    foreach ($data['categories'] as $category) {
        if ((int) $category['category_id'] === $categoryId) {
            return (string) $category['category_name'];
        }
    }

    return 'All Categories';
}

// Handles the core logic and operations for offline_get_products
function offline_get_products(?int $categoryId = null): array
{
    $data = offline_load();
    $rows = [];

    foreach ($data['products'] as $product) {
        // Only show approved products to customers
        if (strtoupper((string) ($product['product_verification_status'] ?? 'PENDING_VERIFICATION')) !== 'APPROVED') {
            continue;
        }

        if ($categoryId !== null && (int) $product['category_id'] !== $categoryId) {
            continue;
        }

        $shop = null;
        foreach ($data['shops'] as $row) {
            if ((int) $row['shop_id'] === (int) $product['shop_id']) {
                $shop = $row;
                break;
            }
        }
        if ($shop === null) {
            continue;
        }

        $trader = null;
        foreach ($data['users'] as $row) {
            if ((int) $row['user_id'] === (int) $shop['trader_id']) {
                $trader = $row;
                break;
            }
        }

        $categoryName = '';
        foreach ($data['categories'] as $category) {
            if ((int) $category['category_id'] === (int) $product['category_id']) {
                $categoryName = (string) $category['category_name'];
                break;
            }
        }

        $traderName = $trader ? trim($trader['first_name'] . ' ' . $trader['last_name']) : (string) $shop['shop_name'];

        $rows[] = [
            'PRODUCT_ID' => (int) $product['product_id'],
            'PRODUCT_NAME' => (string) $product['product_name'],
            'PRICE' => (float) $product['price'],
            'PRODUCT_IMAGE' => $product['product_image'],
            'TRADER_NAME' => $traderName,
            'SHOP_NAME' => (string) $shop['shop_name'],
            'CATEGORY_NAME' => $categoryName,
        ];
    }

    usort($rows, static fn($a, $b) => strcmp((string) $a['PRODUCT_NAME'], (string) $b['PRODUCT_NAME']));

    return $rows;
}

// Handles the core logic and operations for offline_get_product_detail
function offline_get_product_detail(int $productId): ?array
{
    $data = offline_load();
    foreach (offline_get_products(null) as $row) {
        if ((int) $row['PRODUCT_ID'] !== $productId) {
            continue;
        }

        foreach ($data['products'] as $product) {
            if ((int) $product['product_id'] !== $productId) {
                continue;
            }

            return [
                'PRODUCT_ID' => (int) $product['product_id'],
                'PRODUCT_NAME' => (string) $product['product_name'],
                'PRODUCT_DESCRIPTION' => (string) $product['product_description'],
                'PRICE' => (float) $product['price'],
                'PRODUCT_IMAGE' => $product['product_image'],
                'TRADER_NAME' => $row['TRADER_NAME'],
            ];
        }
    }

    return null;
}

// Handles the core logic and operations for offline_ensure_active_cart
function offline_ensure_active_cart(int $customerId): int
{
    $data = offline_load();
    foreach ($data['carts'] as $cart) {
        if ((int) $cart['customer_id'] === $customerId && (string) $cart['cart_status'] === 'ACTIVE') {
            return (int) $cart['cart_id'];
        }
    }

    $cartId = offline_next_id($data['carts'], 'cart_id');
    $data['carts'][] = [
        'cart_id' => $cartId,
        'customer_id' => $customerId,
        'cart_status' => 'ACTIVE',
        'created_at' => date('c'),
    ];
    offline_save($data);

    return $cartId;
}

// Handles the core logic and operations for offline_add_to_cart
function offline_add_to_cart(int $customerId, int $productId, int $quantity): void
{
    $quantity = max(1, $quantity);
    $data = offline_load();
    $cartId = offline_ensure_active_cart($customerId);

    $product = null;
    foreach ($data['products'] as $row) {
        if ((int) $row['product_id'] === $productId) {
            $product = $row;
            break;
        }
    }

    if ($product === null) {
        throw new RuntimeException('Product not found.');
    }

    foreach ($data['cart_items'] as $index => $item) {
        if ((int) $item['cart_id'] === $cartId && (int) $item['product_id'] === $productId) {
            $data['cart_items'][$index]['quantity'] = (int) $item['quantity'] + $quantity;
            $data['cart_items'][$index]['unit_price'] = (float) $product['price'];
            offline_save($data);
            return;
        }
    }

    $data['cart_items'][] = [
        'cart_id' => $cartId,
        'product_id' => $productId,
        'quantity' => $quantity,
        'unit_price' => (float) $product['price'],
    ];

    offline_save($data);
}

// Handles the core logic and operations for offline_update_cart_quantity
function offline_update_cart_quantity(int $customerId, int $productId, int $quantity): void
{
    $data = offline_load();
    $cartId = offline_ensure_active_cart($customerId);

    $nextItems = [];
    foreach ($data['cart_items'] as $item) {
        if ((int) $item['cart_id'] !== $cartId || (int) $item['product_id'] !== $productId) {
            $nextItems[] = $item;
            continue;
        }

        if ($quantity > 0) {
            $item['quantity'] = $quantity;
            $nextItems[] = $item;
        }
    }

    $data['cart_items'] = $nextItems;
    offline_save($data);
}

// Handles the core logic and operations for offline_get_cart_items
function offline_get_cart_items(int $customerId): array
{
    $data = offline_load();
    $cartId = offline_ensure_active_cart($customerId);
    $rows = [];

    foreach ($data['cart_items'] as $item) {
        if ((int) $item['cart_id'] !== $cartId) {
            continue;
        }

        $product = null;
        foreach ($data['products'] as $row) {
            if ((int) $row['product_id'] === (int) $item['product_id']) {
                $product = $row;
                break;
            }
        }

        if ($product === null) {
            continue;
        }

        $shopName = 'Shop';
        foreach ($data['shops'] as $shop) {
            if ((int) $shop['shop_id'] === (int) $product['shop_id']) {
                $shopName = (string) $shop['shop_name'];
                break;
            }
        }

        $rows[] = [
            'PRODUCT_ID' => (int) $product['product_id'],
            'QUANTITY' => (int) $item['quantity'],
            'UNIT_PRICE' => (float) $item['unit_price'],
            'PRODUCT_NAME' => (string) $product['product_name'],
            'PRODUCT_IMAGE' => $product['product_image'],
            'SHOP_NAME' => $shopName,
        ];
    }

    usort($rows, static fn($a, $b) => strcmp((string) $a['PRODUCT_NAME'], (string) $b['PRODUCT_NAME']));

    return $rows;
}

// Handles the core logic and operations for offline_get_orders_for_customer
function offline_get_orders_for_customer(int $customerId, int $limit = 5): array
{
    $data = offline_load();
    $rows = [];

    foreach ($data['orders'] as $order) {
        if ((int) $order['customer_id'] !== $customerId) {
            continue;
        }

        $total = 0.0;
        foreach ($data['order_items'] as $item) {
            if ((int) $item['order_id'] === (int) $order['order_id']) {
                $total += ((float) $item['unit_price']) * ((int) $item['quantity']);
            }
        }

        $rows[] = [
            'ORDER_ID' => (int) $order['order_id'],
            'ORDER_DATE' => (string) $order['order_date'],
            'ORDER_STATUS' => (string) $order['order_status'],
            'ORDER_TOTAL' => $total,
        ];
    }

    usort($rows, static fn($a, $b) => strcmp((string) $b['ORDER_DATE'], (string) $a['ORDER_DATE']));

    return array_slice($rows, 0, $limit);
}

// Handles the core logic and operations for offline_get_reviews_for_customer
function offline_get_reviews_for_customer(int $customerId, int $limit = 5): array
{
    $data = offline_load();
    $rows = [];

    foreach ($data['reviews'] as $review) {
        if ((int) $review['customer_id'] !== $customerId) {
            continue;
        }

        $productName = 'Product';
        foreach ($data['products'] as $product) {
            if ((int) $product['product_id'] === (int) $review['product_id']) {
                $productName = (string) $product['product_name'];
                break;
            }
        }

        $rows[] = [
            'REVIEW_DATE' => (string) $review['review_date'],
            'RATING' => (float) $review['rating'],
            'REVIEW_COMMENT' => (string) ($review['comment'] ?? ''),
            'PRODUCT_NAME' => $productName,
        ];
    }

    usort($rows, static fn($a, $b) => strcmp((string) $b['REVIEW_DATE'], (string) $a['REVIEW_DATE']));

    return array_slice($rows, 0, $limit);
}

// Handles the core logic and operations for offline_count_orders
function offline_count_orders(int $customerId): int
{
    return count(offline_get_orders_for_customer($customerId, PHP_INT_MAX));
}

// Handles the core logic and operations for offline_count_reviews
function offline_count_reviews(int $customerId): int
{
    return count(offline_get_reviews_for_customer($customerId, PHP_INT_MAX));
}

// Handles the core logic and operations for offline_count_saved
function offline_count_saved(int $customerId): int
{
    $data = offline_load();
    $wishlistIds = [];

    foreach ($data['wishlists'] as $wishlist) {
        if ((int) $wishlist['customer_id'] === $customerId) {
            $wishlistIds[] = (int) $wishlist['wishlist_id'];
        }
    }

    $count = 0;
    foreach ($data['wishlist_items'] as $item) {
        if (in_array((int) $item['wishlist_id'], $wishlistIds, true)) {
            $count += 1;
        }
    }

    return $count;
}

// Handles the core logic and operations for offline_get_categories
function offline_get_categories(): array
{
    $data = offline_load();

    return array_map(static function (array $category): array {
        return [
            'CATEGORY_ID' => (int) $category['category_id'],
            'CATEGORY_NAME' => (string) $category['category_name'],
        ];
    }, $data['categories']);
}

// Handles the core logic and operations for offline_get_trader_shop
function offline_get_trader_shop(int $traderId): ?array
{
    $data = offline_load();

    $user = null;
    foreach ($data['users'] as $row) {
        if ((int) $row['user_id'] === $traderId) {
            $user = $row;
            break;
        }
    }

    if ($user === null) {
        return null;
    }

    foreach ($data['shops'] as $shop) {
        if ((int) $shop['trader_id'] === $traderId) {
            return [
                'SHOP_ID' => (int) $shop['shop_id'],
                'TRADER_ID' => (int) $shop['trader_id'],
                'SHOP_NAME' => (string) $shop['shop_name'],
                'SHOP_DESCRIPTION' => (string) $shop['shop_description'],
                'SHOP_LOGO' => (string) $shop['shop_logo'],
                'SHOP_STATUS' => (string) $shop['shop_status'],
                'FIRST_NAME' => (string) $user['first_name'],
                'LAST_NAME' => (string) $user['last_name'],
                'EMAIL' => (string) $user['email'],
                'PHONE_NUMBER' => $user['phone_number'],
                'BRAND_NAME' => null,
                'PAN_NUMBER' => null,
            ];
        }
    }

    return [
        'SHOP_ID' => 0,
        'TRADER_ID' => $traderId,
        'SHOP_NAME' => trim((string) $user['first_name'] . ' ' . (string) $user['last_name']),
        'SHOP_DESCRIPTION' => '',
        'SHOP_LOGO' => '',
        'SHOP_STATUS' => 'ACTIVE',
        'FIRST_NAME' => (string) $user['first_name'],
        'LAST_NAME' => (string) $user['last_name'],
        'EMAIL' => (string) $user['email'],
        'PHONE_NUMBER' => $user['phone_number'],
        'BRAND_NAME' => null,
        'PAN_NUMBER' => null,
    ];
}

// Handles the core logic and operations for offline_update_shop
function offline_update_shop(int $shopId, string $shopName, string $shopDescription, ?string $shopLogo): void
{
    $data = offline_load();

    foreach ($data['shops'] as $index => $shop) {
        if ((int) $shop['shop_id'] !== $shopId) {
            continue;
        }

        $data['shops'][$index]['shop_name'] = $shopName;
        $data['shops'][$index]['shop_description'] = $shopDescription;
        $data['shops'][$index]['shop_logo'] = $shopLogo;
        offline_save($data);
        return;
    }

    throw new RuntimeException('Shop not found in offline store.');
}

// Handles the core logic and operations for offline_get_trader_products
function offline_get_trader_products(int $traderId): array
{
    $data = offline_load();
    $shop = offline_get_trader_shop($traderId);
    if ($shop === null) {
        return [];
    }

    $productIds = [];
    foreach ($data['products'] as $product) {
        if ((int) $product['shop_id'] === (int) $shop['SHOP_ID']) {
            $productIds[] = (int) $product['product_id'];
        }
    }

    $soldMap = [];
    $revenueMap = [];
    foreach ($data['order_items'] as $item) {
        $productId = (int) $item['product_id'];
        if (!in_array($productId, $productIds, true)) {
            continue;
        }

        $soldMap[$productId] = ($soldMap[$productId] ?? 0) + (int) $item['quantity'];
        $revenueMap[$productId] = ($revenueMap[$productId] ?? 0.0) + ((float) $item['unit_price'] * (int) $item['quantity']);
    }

    $rows = [];
    foreach ($data['products'] as $product) {
        if ((int) $product['shop_id'] !== (int) $shop['SHOP_ID']) {
            continue;
        }

        $categoryName = '';
        foreach ($data['categories'] as $category) {
            if ((int) $category['category_id'] === (int) $product['category_id']) {
                $categoryName = (string) $category['category_name'];
                break;
            }
        }

        $soldQuantity = (int) ($soldMap[(int) $product['product_id']] ?? 0);
        $revenue = (float) ($revenueMap[(int) $product['product_id']] ?? 0);
        $stockQuantity = (int) $product['stock_quantity'];
        $needsRefill = $stockQuantity <= 10 || strtoupper((string) $product['product_status']) === 'LOW_STOCK';

        $rows[] = [
            'PRODUCT_ID' => (int) $product['product_id'],
            'PRODUCT_NAME' => (string) $product['product_name'],
            'PRODUCT_DESCRIPTION' => (string) $product['product_description'],
            'PRICE' => (float) $product['price'],
            'STOCK_QUANTITY' => $stockQuantity,
            'PRODUCT_STATUS' => (string) $product['product_status'],
            'PRODUCT_IMAGE' => $product['product_image'],
            'MAX_ORDER' => $product['max_order'],
            'MIN_ORDER' => $product['min_order'],
            'CATEGORY_NAME' => $categoryName,
            'SOLD_QUANTITY' => $soldQuantity,
            'REVENUE' => $revenue,
            'NEEDS_REFILL' => $needsRefill,
        ];
    }

    usort($rows, static fn(array $a, array $b): int => $b['SOLD_QUANTITY'] <=> $a['SOLD_QUANTITY']);

    return $rows;
}

// Handles the core logic and operations for offline_get_trader_dashboard
function offline_get_trader_dashboard(int $traderId): array
{
    $shop = offline_get_trader_shop($traderId);
    $products = offline_get_trader_products($traderId);

    $soldTotal = 0;
    $revenueTotal = 0.0;
    $stockTotal = 0;
    $refillProducts = [];

    foreach ($products as $product) {
        $soldTotal += (int) $product['SOLD_QUANTITY'];
        $revenueTotal += (float) $product['REVENUE'];
        $stockTotal += (int) $product['STOCK_QUANTITY'];

        if (!empty($product['NEEDS_REFILL'])) {
            $refillProducts[] = $product;
        }
    }

    return [
        'shop' => $shop,
        'products' => $products,
        'sold_total' => $soldTotal,
        'revenue_total' => $revenueTotal,
        'stock_total' => $stockTotal,
        'refill_count' => count($refillProducts),
        'active_count' => count(array_filter($products, static fn(array $product): bool => (int) $product['STOCK_QUANTITY'] > 0)),
        'low_stock_products' => array_slice($refillProducts, 0, 5),
        'top_products' => array_slice($products, 0, 5),
    ];
}

// Handles the core logic and operations for offline_create_product
function offline_create_product(int $shopId, array $payload): array
{
    $data = offline_load();

    $productId = offline_next_id($data['products'], 'product_id');
    $product = [
        'product_id' => $productId,
        'shop_id' => $shopId,
        'category_id' => (int) ($payload['category_id'] ?? 0),
        'discount_id' => null,
        'product_name' => (string) ($payload['product_name'] ?? ''),
        'product_description' => (string) ($payload['product_description'] ?? ''),
        'price' => (float) ($payload['price'] ?? 0),
        'stock_quantity' => max(0, (int) ($payload['stock_quantity'] ?? 0)),
        'product_status' => (string) ($payload['product_status'] ?? 'IN_STOCK'),
        'product_verification_status' => 'PENDING_VERIFICATION',
        'allergy_information' => $payload['allergy_information'] ?? null,
        'min_order' => (int) ($payload['min_order'] ?? 1),
        'max_order' => $payload['max_order'] === null ? null : (int) $payload['max_order'],
        'product_image' => $payload['product_image'] ?? null,
    ];

    $data['products'][] = $product;
    offline_save($data);

    return [
        'PRODUCT_ID' => $productId,
        'PRODUCT_NAME' => $product['product_name'],
        'PRODUCT_DESCRIPTION' => $product['product_description'],
        'PRICE' => $product['price'],
        'STOCK_QUANTITY' => $product['stock_quantity'],
        'PRODUCT_STATUS' => $product['product_status'],
        'PRODUCT_IMAGE' => $product['product_image'],
        'CATEGORY_NAME' => '',
    ];
}

// Handles the core logic and operations for offline_user_to_upper
function offline_user_to_upper(array $user): array
{
    return [
        'USER_ID' => (int) $user['user_id'],
        'FIRST_NAME' => (string) $user['first_name'],
        'LAST_NAME' => (string) $user['last_name'],
        'EMAIL' => (string) $user['email'],
        'PASSWORD' => (string) $user['password'],
        'PHONE_NUMBER' => $user['phone_number'],
        'ROLE' => (string) $user['role'],
    ];
}

// Handles the core logic and operations for offline_get_pending_products
function offline_get_pending_products(): array
{
    $data = offline_load();
    $rows = [];

    foreach ($data['products'] as $product) {
        if (($product['product_verification_status'] ?? 'PENDING_VERIFICATION') !== 'PENDING_VERIFICATION') {
            continue;
        }

        $shopName = '';
        foreach ($data['shops'] as $shop) {
            if ((int) $shop['shop_id'] === (int) $product['shop_id']) {
                $shopName = (string) $shop['shop_name'];
                break;
            }
        }

        $rows[] = [
            'PRODUCT_ID' => (int) $product['product_id'],
            'PRODUCT_NAME' => (string) $product['product_name'],
            'PRICE' => (float) $product['price'],
            'PRODUCT_VERIFICATION_STATUS' => $product['product_verification_status'] ?? 'PENDING_VERIFICATION',
            'SHOP_NAME' => $shopName,
        ];
    }
    return $rows;
}

// Handles the core logic and operations for offline_update_product_status
function offline_update_product_status(int $productId, string $status): void
{
    $data = offline_load();
    foreach ($data['products'] as $index => $product) {
        if ((int) $product['product_id'] === $productId) {
            $data['products'][$index]['product_verification_status'] = $status;
            offline_save($data);
            return;
    }
}

// Handles the core logic and operations for offline_get_pending_traders
function offline_get_pending_traders(): array
{
    $data = offline_load();
    $rows = [];

    foreach ($data['traders'] as $trader) {
        if (($trader['trader_status'] ?? 'PENDING_VERIFICATION') !== 'PENDING_VERIFICATION') {
            continue;
        }

        $user = null;
        foreach ($data['users'] as $u) {
            if ((int) $u['user_id'] === (int) $trader['trader_id']) {
                $user = $u;
                break;
            }
        }
        
        $shopName = '';
        foreach ($data['shops'] as $s) {
            if ((int) $s['trader_id'] === (int) $trader['trader_id']) {
                $shopName = (string) $s['shop_name'];
                break;
            }
        }

        if ($user) {
            $rows[] = [
                'TRADER_ID' => (int) $trader['trader_id'],
                'FIRST_NAME' => (string) $user['first_name'],
                'LAST_NAME' => (string) $user['last_name'],
                'EMAIL' => (string) $user['email'],
                'BRAND_NAME' => (string) $trader['brand_name'],
                'SHOP_NAME' => $shopName,
                'TRADER_STATUS' => $trader['trader_status'] ?? 'PENDING_VERIFICATION'
            ];
        }
    }
    return $rows;
}

// Handles the core logic and operations for offline_update_trader_status
function offline_update_trader_status(int $traderId, string $status): void
{
    $data = offline_load();
    foreach ($data['traders'] as $index => $trader) {
        if ((int) $trader['trader_id'] === $traderId) {
            $data['traders'][$index]['trader_status'] = $status;
            offline_save($data);
            return;
        }
    }
}
}

// Handles the core logic and operations for offline_get_trader_shops
function offline_get_trader_shops(int $traderId): array
{
    $data = offline_load();
    $user = null;
    foreach ($data['users'] as $row) {
        if ((int) $row['user_id'] === $traderId) {
            $user = $row;
            break;
        }
    }
    if ($user === null) {
        return [];
    }

    $shops = [];
    foreach ($data['shops'] as $shop) {
        if ((int) $shop['trader_id'] === $traderId) {
            $shops[] = [
                'SHOP_ID' => (int) $shop['shop_id'],
                'TRADER_ID' => (int) $shop['trader_id'],
                'SHOP_NAME' => (string) $shop['shop_name'],
                'SHOP_DESCRIPTION' => (string) $shop['shop_description'],
                'SHOP_LOGO' => (string) $shop['shop_logo'],
                'SHOP_STATUS' => (string) $shop['shop_status'],
            ];
        }
    }
    return $shops;
}

// Handles the core logic and operations for offline_create_shop_for_trader
function offline_create_shop_for_trader(int $traderId, string $shopName, string $shopDesc, ?string $shopLogo): array
{
    $data = offline_load();
    $shopId = offline_next_id($data['shops'] ?? [], 'shop_id');
    $shopStatus = 'PENDING_APPROVAL';

    $newShop = [
        'shop_id' => $shopId,
        'trader_id' => $traderId,
        'shop_name' => $shopName,
        'shop_description' => $shopDesc,
        'shop_logo' => $shopLogo,
        'shop_status' => $shopStatus,
    ];
    $data['shops'][] = $newShop;
    offline_save($data);
    
    return [
        'SHOP_ID' => $shopId,
        'TRADER_ID' => $traderId,
        'SHOP_NAME' => $shopName,
        'SHOP_DESCRIPTION' => $shopDesc,
        'SHOP_LOGO' => $shopLogo,
        'SHOP_STATUS' => $shopStatus,
    ];
}

// Handles the core logic and operations for offline_get_pending_shops
function offline_get_pending_shops(): array
{
    $data = offline_load();
    $shops = [];
    foreach ($data['shops'] as $shop) {
        if (($shop['shop_status'] ?? '') === 'PENDING_APPROVAL') {
            $shops[] = [
                'SHOP_ID' => (int) $shop['shop_id'],
                'SHOP_NAME' => (string) $shop['shop_name'],
                'SHOP_STATUS' => (string) $shop['shop_status']
            ];
        }
    }
    return $shops;
}

// Handles the core logic and operations for offline_update_shop_status
function offline_update_shop_status(int $shopId, string $status): void
{
    $data = offline_load();
    foreach ($data['shops'] as $index => $shop) {
        if ((int) $shop['shop_id'] === $shopId) {
            $data['shops'][$index]['shop_status'] = $status;
            offline_save($data);
            return;
        }
    }
}

// Handles the core logic and operations for offline_get_pending_reviews_for_customer
function offline_get_pending_reviews_for_customer(int $customerId): array
{
    $data = offline_load();
    
    // Find all products purchased by this customer in PAID or COLLECTED orders
    $purchasedProductIds = [];
    foreach ($data['orders'] as $order) {
        if ((int)$order['customer_id'] === $customerId && in_array(strtoupper((string)$order['order_status']), ['PAID', 'COLLECTED'], true)) {
            foreach ($data['order_items'] as $item) {
                if ((int)$item['order_id'] === (int)$order['order_id']) {
                    $purchasedProductIds[] = (int)$item['product_id'];
                }
            }
        }
    }
    $purchasedProductIds = array_unique($purchasedProductIds);

    // Find all products already reviewed by this customer
    $reviewedProductIds = [];
    foreach ($data['reviews'] as $review) {
        if ((int)$review['customer_id'] === $customerId) {
            $reviewedProductIds[] = (int)$review['product_id'];
        }
    }

    $pendingProductIds = array_diff($purchasedProductIds, $reviewedProductIds);

    $rows = [];
    foreach ($data['products'] as $product) {
        if (in_array((int)$product['product_id'], $pendingProductIds, true)) {
            $shopName = 'Shop';
            foreach ($data['shops'] as $shop) {
                if ((int)$shop['shop_id'] === (int)$product['shop_id']) {
                    $shopName = (string)$shop['shop_name'];
                    break;
                }
            }
            $rows[] = [
                'PRODUCT_ID' => (int)$product['product_id'],
                'PRODUCT_NAME' => (string)$product['product_name'],
                'PRODUCT_IMAGE' => $product['product_image'],
                'SHOP_NAME' => $shopName,
            ];
        }
    }

    return $rows;
}

// Handles the core logic and operations for offline_submit_review
function offline_submit_review(int $customerId, int $productId, float $rating, string $comment): void
{
    $data = offline_load();

    // Check unique constraint: a customer can only leave one review per product
    foreach ($data['reviews'] as $review) {
        if ((int)$review['customer_id'] === $customerId && (int)$review['product_id'] === $productId) {
            throw new RuntimeException('unique constraint violated: You have already reviewed this product.');
        }
    }

    // Verify they purchased and collected/paid for it
    $hasPurchased = false;
    foreach ($data['orders'] as $order) {
        if ((int)$order['customer_id'] === $customerId && in_array(strtoupper((string)$order['order_status']), ['PAID', 'COLLECTED'], true)) {
            foreach ($data['order_items'] as $item) {
                if ((int)$item['order_id'] === (int)$order['order_id'] && (int)$item['product_id'] === $productId) {
                    $hasPurchased = true;
                    break 2;
                }
            }
        }
    }

    if (!$hasPurchased) {
        throw new RuntimeException('ORA-20009: You can only review products you have purchased and collected/paid for.');
    }

    $reviewId = offline_next_id($data['reviews'], 'review_id');
    $data['reviews'][] = [
        'review_id' => $reviewId,
        'customer_id' => $customerId,
        'product_id' => $productId,
        'rating' => $rating,
        'comment' => $comment === '' ? null : $comment,
        'review_date' => date('Y-m-d'),
    ];

    offline_save($data);
}

// Handles the core logic and operations for offline_update_product
function offline_update_product(int $shopId, int $productId, array $payload): void
{
    $data = offline_load();
    foreach ($data['products'] as &$product) {
        if ((int)$product['product_id'] === $productId && (int)$product['shop_id'] === $shopId) {
            $product['product_name'] = $payload['product_name'];
            $product['product_description'] = $payload['product_description'];
            $product['price'] = $payload['price'];
            $product['stock_quantity'] = $payload['stock_quantity'];
            $product['product_status'] = $payload['product_status'];
            if ($payload['product_image'] !== null) {
                $product['product_image'] = $payload['product_image'];
            }
            offline_save($data);
            return;
        }
    }
    throw new RuntimeException('Product not found in offline store.');
}