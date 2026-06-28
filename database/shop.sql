-- ============================================================
-- Mini-Ecommerce Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    user_id     VARCHAR(50)  NOT NULL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    gender      ENUM('Male','Female','Other') NOT NULL,
    confirm_status TINYINT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: confirm_codes
-- ============================================================
CREATE TABLE IF NOT EXISTS confirm_codes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         VARCHAR(50) NOT NULL,
    confirmation_code VARCHAR(10) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: user_tokens (Remember Me)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_tokens (
    token_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     VARCHAR(50) NOT NULL,
    token       VARCHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    product_id  INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    category    VARCHAR(50) NOT NULL,
    stock       INT NOT NULL DEFAULT 0,
    image       VARCHAR(255)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    order_id        VARCHAR(50) NOT NULL PRIMARY KEY,
    user_id         VARCHAR(50) NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(50) NOT NULL,
    payment_status  VARCHAR(50) NOT NULL DEFAULT 'Pending',
    order_date      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    item_id     INT AUTO_INCREMENT PRIMARY KEY,
    order_id    VARCHAR(50) NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED: 16 Products across 4 categories
-- ============================================================
INSERT INTO products (name, description, price, category, stock, image) VALUES
-- TOP
('Classic Oxford Shirt',    'Crisp Oxford weave button-down shirt. Perfect for office or casual wear. Available in multiple fits.',                       29.99, 'Top', 50, 'shirt.jpg'),
('Fleece Zip Hoodie',       'Warm fleece-lined zip-up hoodie with kangaroo pocket and adjustable drawcord hood.',                                         45.00, 'Top', 35, 'hoodie.jpg'),
('Leather Biker Jacket',    'Genuine faux-leather biker jacket with quilted panels and asymmetric zip. Slim-fit silhouette.',                             89.99, 'Top', 20, 'jacket.jpg'),
('Cotton Graphic T-Shirt',  'Soft 100% cotton graphic tee with modern streetwear-inspired print. Pre-shrunk and machine washable.',                       18.50, 'Top', 80, 'tshirt.jpg'),
-- BOTTOM
('Slim-Fit Stretch Jeans',  'Five-pocket slim-fit jeans made from premium stretch denim for all-day comfort. Available in dark indigo.',                  54.99, 'Bottom', 60, 'jeans.jpg'),
('Cargo Shorts',            'Relaxed-fit cargo shorts with side pockets and adjustable waistband. Ideal for outdoor activities.',                          27.00, 'Bottom', 45, 'shorts.jpg'),
('Chino Trousers',          'Smart chino trousers in a straight-leg cut. Wrinkle-resistant fabric keeps you looking sharp all day.',                      38.00, 'Bottom', 55, 'pants.jpg'),
('Flared Mini Skirt',       'Trendy flared mini skirt in floral print. Elasticated waist and fully lined for comfort.',                                   22.50, 'Bottom', 30, 'skirt.jpg'),
-- SHOE
('Pro Running Sneaker',     'Lightweight mesh upper with cushioned midsole and rubber outsole. Engineered for high-performance running.',                  75.00, 'Shoe',   40, 'sports_shoe.jpg'),
('Oxford Dress Shoe',       'Classic leather Oxford dress shoe with brogue detailing. Leather insole for premium comfort.',                               110.00, 'Shoe',  25, 'formal_shoe.jpg'),
('Urban Street Sneaker',    'Low-profile canvas sneaker with vulcanised rubber sole. Clean minimalist design for everyday wear.',                          60.00, 'Shoe',  50, 'sneaker.jpg'),
('Lace-Up Combat Boots',    'Durable combat boots with lug sole and waterproof coating. Ankle-height with metal eyelets.',                                95.00, 'Shoe',  30, 'boots.jpg'),
-- ACCESSORIES
('Stainless Steel Watch',   'Minimalist stainless steel case watch with sapphire-coated crystal glass and leather strap. Water resistant 50m.',         149.99, 'Accessories', 20, 'watch.jpg'),
('Canvas Tote Bag',         'Large canvas tote with internal zip pocket and reinforced handles. Perfect for work or grocery shopping.',                   35.00, 'Accessories', 70, 'bag.jpg'),
('Braided Leather Belt',    'Full-grain braided leather belt with nickel-free buckle. Available in sizes 30–44.',                                         24.99, 'Accessories', 90, 'belt.jpg'),
('Polarised Sunglasses',    'UV400 polarised lenses with lightweight TR-90 frames. Includes hard case and microfibre pouch.',                             42.00, 'Accessories', 55, 'sunglasses.jpg');
