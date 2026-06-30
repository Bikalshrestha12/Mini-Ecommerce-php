<?php
$dbPath = __DIR__ . '/database/shop.sqlite';

if (file_exists($dbPath)) unlink($dbPath);

$db = new PDO("sqlite:$dbPath");
$db->exec('PRAGMA journal_mode=WAL');
$db->exec('PRAGMA foreign_keys=ON');
$db->exec('PRAGMA busy_timeout=5000');

$db->exec("
CREATE TABLE roles (
    role_id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name TEXT NOT NULL UNIQUE,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE users (
    user_id TEXT NOT NULL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    gender TEXT NOT NULL DEFAULT 'Other' CHECK(gender IN ('Male','Female','Other')),
    confirm_status INTEGER DEFAULT 0,
    role_id INTEGER DEFAULT 1,
    avatar TEXT DEFAULT NULL,
    phone TEXT DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL
)");

$db->exec("
CREATE TABLE confirm_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id TEXT NOT NULL,
    confirmation_code TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE user_tokens (
    token_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id TEXT NOT NULL,
    token TEXT NOT NULL,
    expires_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE product_categories (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    slug TEXT NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE products (
    product_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT DEFAULT NULL,
    description TEXT,
    price REAL NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    category_id INTEGER DEFAULT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    is_featured INTEGER DEFAULT 0,
    image TEXT,
    brochure TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (category_id) REFERENCES product_categories(category_id) ON DELETE SET NULL
)");

$db->exec("
CREATE TABLE product_images (
    image_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    image TEXT NOT NULL,
    is_primary INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE orders (
    order_id TEXT NOT NULL PRIMARY KEY,
    user_id TEXT NOT NULL,
    total_amount REAL NOT NULL,
    shipping_address TEXT DEFAULT NULL,
    payment_method TEXT NOT NULL,
    payment_status TEXT NOT NULL DEFAULT 'Pending',
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending','Processing','Shipped','Delivered','Cancelled','Refunded')),
    tracking_number TEXT DEFAULT NULL,
    order_date TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE order_items (
    item_id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id TEXT NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price REAL NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE project_categories (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    slug TEXT NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE projects (
    project_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT DEFAULT NULL,
    description TEXT,
    category_id INTEGER DEFAULT NULL,
    client_name TEXT DEFAULT NULL,
    completion_date TEXT DEFAULT NULL,
    project_url TEXT DEFAULT NULL,
    is_featured INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (category_id) REFERENCES project_categories(category_id) ON DELETE SET NULL
)");

$db->exec("
CREATE TABLE project_images (
    image_id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    image TEXT NOT NULL,
    is_primary INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE project_videos (
    video_id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    video_url TEXT NOT NULL,
    title TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE project_documents (
    doc_id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    document TEXT NOT NULL,
    title TEXT DEFAULT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
)");

$db->exec("
CREATE TABLE career_jobs (
    job_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT DEFAULT NULL,
    department TEXT DEFAULT NULL,
    location TEXT DEFAULT NULL,
    employment_type TEXT DEFAULT NULL,
    description TEXT,
    requirements TEXT,
    responsibilities TEXT,
    salary_range TEXT DEFAULT NULL,
    application_deadline TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE career_applications (
    application_id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER DEFAULT NULL,
    applicant_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,
    resume_file TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending','Reviewed','Shortlisted','Interviewed','Accepted','Rejected')),
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (job_id) REFERENCES career_jobs(job_id) ON DELETE SET NULL
)");

$db->exec("
CREATE TABLE admission_programs (
    program_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT DEFAULT NULL,
    description TEXT,
    duration TEXT DEFAULT NULL,
    eligibility TEXT,
    fee TEXT DEFAULT NULL,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE admission_applications (
    application_id INTEGER PRIMARY KEY AUTOINCREMENT,
    program_id INTEGER DEFAULT NULL,
    applicant_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT DEFAULT NULL,
    address TEXT DEFAULT NULL,
    date_of_birth TEXT DEFAULT NULL,
    gender TEXT DEFAULT NULL,
    previous_education TEXT DEFAULT NULL,
    documents TEXT DEFAULT NULL,
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending','Reviewed','Accepted','Rejected')),
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (program_id) REFERENCES admission_programs(program_id) ON DELETE SET NULL
)");

$db->exec("
CREATE TABLE contact_messages (
    message_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT DEFAULT NULL,
    subject TEXT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    replied_at TEXT DEFAULT NULL,
    reply TEXT DEFAULT NULL,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE cms_sections (
    section_id INTEGER PRIMARY KEY AUTOINCREMENT,
    page TEXT NOT NULL,
    section_key TEXT NOT NULL,
    title TEXT DEFAULT NULL,
    subtitle TEXT DEFAULT NULL,
    content TEXT DEFAULT NULL,
    image TEXT DEFAULT NULL,
    extra_json TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    UNIQUE(page, section_key)
)");

$db->exec("
CREATE TABLE hero_sliders (
    slide_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT DEFAULT NULL,
    subtitle TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    button_text TEXT DEFAULT NULL,
    button_url TEXT DEFAULT NULL,
    image TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE testimonials (
    testimonial_id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_name TEXT NOT NULL,
    designation TEXT DEFAULT NULL,
    company TEXT DEFAULT NULL,
    content TEXT NOT NULL,
    avatar TEXT DEFAULT NULL,
    rating INTEGER DEFAULT 5,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE gallery (
    image_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image TEXT NOT NULL,
    category TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE partners (
    partner_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    logo TEXT NOT NULL,
    website TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE team_members (
    member_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    designation TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    image TEXT DEFAULT NULL,
    email TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
)");

$db->exec("
CREATE TABLE settings (
    setting_id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group TEXT DEFAULT 'general',
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
)");

// Seed data
$db->exec("INSERT INTO roles (role_name) VALUES ('user'), ('super_admin')");

$db->exec("INSERT INTO product_categories (name, slug) VALUES
    ('Top', 'top'), ('Bottom', 'bottom'), ('Shoe', 'shoe'), ('Accessories', 'accessories')");

// Default admin: admin@shop.com / password
$db->exec("INSERT INTO users (user_id, name, email, password_hash, gender, confirm_status, role_id, is_active)
    VALUES ('ADM-001', 'Super Admin', 'admin@shop.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 1, 2, 1)");

$db->exec("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
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
    ('footer_description', 'Your modern shopping destination for quality products at great prices.', 'footer')");

$db->exec("INSERT INTO cms_sections (page, section_key, title, content, sort_order) VALUES
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
    ('about', 'timeline', 'Our Timeline', 'Key milestones in our journey.', 6)");

echo 'SQLite database created successfully at: ' . $dbPath . PHP_EOL;
echo 'Admin login: admin@shop.com / password' . PHP_EOL;
