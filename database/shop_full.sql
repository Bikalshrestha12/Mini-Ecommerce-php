-- ============================================================
-- Mini-Ecommerce Complete Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop;

-- ============================================================
-- TABLE: roles
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (role_name) VALUES ('user'), ('super_admin');

-- ============================================================
-- TABLE: users (enhanced)
-- ============================================================
ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id INT DEFAULT 1 AFTER confirm_status;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL AFTER role_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL AFTER avatar;
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL AFTER phone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT DEFAULT 1 AFTER address;

-- Create users table if not exists (for fresh installs)
CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(50) NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL DEFAULT 'Other',
    confirm_status TINYINT DEFAULT 0,
    role_id INT DEFAULT 1,
    avatar VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: confirm_codes
-- ============================================================
CREATE TABLE IF NOT EXISTS confirm_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    confirmation_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: user_tokens (Remember Me)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: product_categories
-- ============================================================
CREATE TABLE IF NOT EXISTS product_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO product_categories (name, slug) VALUES
('Top', 'top'), ('Bottom', 'bottom'), ('Shoe', 'shoe'), ('Accessories', 'accessories');

-- ============================================================
-- TABLE: products (enhanced)
-- ============================================================
ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL AFTER category;
ALTER TABLE products ADD COLUMN IF NOT EXISTS slug VARCHAR(200) DEFAULT NULL AFTER name;
ALTER TABLE products ADD COLUMN IF NOT EXISTS brochure VARCHAR(255) DEFAULT NULL AFTER image;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured TINYINT DEFAULT 0 AFTER stock;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active TINYINT DEFAULT 1 AFTER is_featured;
ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active;
ALTER TABLE products ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(200) DEFAULT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    category_id INT DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    is_featured TINYINT DEFAULT 0,
    image VARCHAR(255),
    brochure VARCHAR(255) DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: product_images
-- ============================================================
CREATE TABLE IF NOT EXISTS product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    is_primary TINYINT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: orders (enhanced)
-- ============================================================
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_address TEXT DEFAULT NULL AFTER total_amount;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS status ENUM('Pending','Processing','Shipped','Delivered','Cancelled','Refunded') DEFAULT 'Pending' AFTER payment_status;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER order_date;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(100) DEFAULT NULL AFTER status;

CREATE TABLE IF NOT EXISTS orders (
    order_id VARCHAR(50) NOT NULL PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT DEFAULT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    status ENUM('Pending','Processing','Shipped','Delivered','Cancelled','Refunded') DEFAULT 'Pending',
    tracking_number VARCHAR(100) DEFAULT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: project_categories
-- ============================================================
CREATE TABLE IF NOT EXISTS project_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: projects
-- ============================================================
CREATE TABLE IF NOT EXISTS projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) DEFAULT NULL,
    description TEXT,
    category_id INT DEFAULT NULL,
    client_name VARCHAR(200) DEFAULT NULL,
    completion_date DATE DEFAULT NULL,
    project_url VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES project_categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: project_images
-- ============================================================
CREATE TABLE IF NOT EXISTS project_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    is_primary TINYINT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: project_videos
-- ============================================================
CREATE TABLE IF NOT EXISTS project_videos (
    video_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    video_url VARCHAR(500) NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: project_documents
-- ============================================================
CREATE TABLE IF NOT EXISTS project_documents (
    doc_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    document VARCHAR(255) NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: career_jobs
-- ============================================================
CREATE TABLE IF NOT EXISTS career_jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    location VARCHAR(200) DEFAULT NULL,
    employment_type VARCHAR(50) DEFAULT NULL,
    description TEXT,
    requirements TEXT,
    responsibilities TEXT,
    salary_range VARCHAR(100) DEFAULT NULL,
    application_deadline DATE DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: career_applications
-- ============================================================
CREATE TABLE IF NOT EXISTS career_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT DEFAULT NULL,
    applicant_name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,
    resume_file VARCHAR(255) DEFAULT NULL,
    status ENUM('Pending','Reviewed','Shortlisted','Interviewed','Accepted','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES career_jobs(job_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: admission_programs
-- ============================================================
CREATE TABLE IF NOT EXISTS admission_programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) DEFAULT NULL,
    description TEXT,
    duration VARCHAR(100) DEFAULT NULL,
    eligibility TEXT,
    fee VARCHAR(100) DEFAULT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: admission_applications
-- ============================================================
CREATE TABLE IF NOT EXISTS admission_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT DEFAULT NULL,
    applicant_name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    previous_education TEXT DEFAULT NULL,
    documents TEXT DEFAULT NULL,
    status ENUM('Pending','Reviewed','Accepted','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES admission_programs(program_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: contact_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT DEFAULT 0,
    replied_at TIMESTAMP NULL DEFAULT NULL,
    reply TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: cms_sections
-- ============================================================
CREATE TABLE IF NOT EXISTS cms_sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(100) NOT NULL,
    section_key VARCHAR(100) NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    extra_json TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section (page, section_key)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: hero_sliders
-- ============================================================
CREATE TABLE IF NOT EXISTS hero_sliders (
    slide_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    button_text VARCHAR(100) DEFAULT NULL,
    button_url VARCHAR(500) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: testimonials
-- ============================================================
CREATE TABLE IF NOT EXISTS testimonials (
    testimonial_id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(200) NOT NULL,
    designation VARCHAR(200) DEFAULT NULL,
    company VARCHAR(200) DEFAULT NULL,
    content TEXT NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    rating INT DEFAULT 5,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: gallery
-- ============================================================
CREATE TABLE IF NOT EXISTS gallery (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: partners
-- ============================================================
CREATE TABLE IF NOT EXISTS partners (
    partner_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    website VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: team_members
-- ============================================================
CREATE TABLE IF NOT EXISTS team_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    designation VARCHAR(200) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group VARCHAR(100) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'Mini-Ecommerce', 'general'),
('site_description', 'Your modern shopping destination', 'general'),
('site_logo', '', 'general'),
('site_favicon', '', 'general'),
('contact_email', 'support@mini-ecommerce.local', 'contact'),
('contact_phone', '+977-9800000000', 'contact'),
('contact_address', 'Kathmandu, Nepal', 'contact'),
('business_hours', 'Sun-Fri: 9:00 AM - 6:00 PM', 'contact'),
('google_map', '', 'contact'),
('facebook_url', '#', 'social'),
('twitter_url', '#', 'social'),
('instagram_url', '#', 'social'),
('linkedin_url', '#', 'social'),
('youtube_url', '', 'social'),
('seo_title', 'Mini-Ecommerce - Your Modern Shopping Destination', 'seo'),
('seo_description', 'Shop the best products at great prices with Mini-Ecommerce.', 'seo'),
('seo_keywords', 'ecommerce, shop, products, online shopping', 'seo'),
('footer_text', '© 2025 Mini-Ecommerce. All rights reserved.', 'footer'),
('footer_description', 'Your modern shopping destination for quality products at great prices.', 'footer');

-- ============================================================
-- Seed data for CMS sections
-- ============================================================
INSERT IGNORE INTO cms_sections (page, section_key, title, content, sort_order) VALUES
('home', 'hero', 'Welcome to Mini-Ecommerce', 'Your one-stop shop for quality products at great prices.', 1),
('home', 'about', 'About Our Company', 'We are committed to providing the best products and services to our customers worldwide.', 2),
('home', 'services', 'Our Services', 'We offer a wide range of services including fast delivery, quality assurance, and customer support.', 3),
('home', 'why_choose_us', 'Why Choose Us', 'We provide the best quality products at competitive prices with excellent customer service.', 4),
('home', 'stats', 'Our Achievements', 'We have served thousands of happy customers worldwide.', 5),
('home', 'cta', 'Ready to Get Started?', 'Join thousands of satisfied customers and start shopping today!', 6),
('home', 'contact_preview', 'Get In Touch', 'Have questions? We are here to help you.', 7),
('about', 'intro', 'About Our Company', 'We are a leading e-commerce platform dedicated to providing quality products.', 1),
('about', 'mission', 'Our Mission', 'To make quality products accessible to everyone at affordable prices.', 2),
('about', 'vision', 'Our Vision', 'To become the most trusted online shopping destination worldwide.', 3),
('about', 'core_values', 'Our Core Values', 'Integrity, Quality, Customer Focus, Innovation, Teamwork.', 4),
('about', 'history', 'Our History', 'Founded in 2020, we have grown from a small startup to a leading e-commerce platform.', 5),
('about', 'timeline', 'Our Timeline', 'Key milestones in our journey.', 6);

-- Seed super admin
INSERT IGNORE INTO users (user_id, name, email, password_hash, gender, confirm_status, role_id, is_active)
VALUES ('ADM-001', 'Super Admin', 'admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 1, 2, 1);
-- Default password: password