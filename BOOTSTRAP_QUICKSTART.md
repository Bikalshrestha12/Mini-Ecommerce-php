# Mini-Ecommerce Quickstart Guide

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache / Nginx
- mod_rewrite enabled

## Setup

### 1. Database
1. Open phpMyAdmin or MySQL CLI
2. Run `database/shop_full.sql` to create all tables and seed data
3. Default super admin account:
   - Email: `admin@shop.com`
   - Password: `password`

### 2. Configuration
Edit `config.php`:
- `DB_HOST` - Database host (default: localhost)
- `DB_NAME` - Database name (default: shop)
- `DB_USER` - Database username (default: root)
- `DB_PASS` - Database password
- `APP_URL` - Your application URL (e.g., http://localhost/shop)

### 3. File Permissions
Ensure the following directories are writable:
- `uploads/` and all subdirectories
- `assets/images/`

### 4. Application Structure

```
/
├── index.php          # Landing page
├── about.php          # About page (dynamic CMS)
├── careers.php        # Jobs listing
├── job-detail.php     # Job details
├── contact.php        # Contact page
├── projects.php       # Projects listing
├── project-details.php # Project details
├── apply.php          # Reusable Apply Form (admissions & careers)
├── product/
│   ├── products.php   # Products listing (public)
│   ├── details.php    # Product details (public)
│   └── manage.php     # Product CRUD
├── user/
│   ├── auth.php       # Login/Signup page
│   ├── login.php      # Login POST handler
│   ├── signup.php     # Signup POST handler
│   ├── verify.php     # Email verification
│   ├── dashboard.php  # User dashboard
│   ├── profile.php    # User profile
│   ├── orders.php     # User order history
│   └── invoice.php    # Order invoice
├── cart/
│   ├── cart.php       # Shopping cart
│   ├── checkout.php   # Checkout
│   ├── add.php        # Add to cart
│   └── remove.php     # Remove from cart
├── orders/
│   └── history.php    # Order history
├── admin/
│   ├── index.php      # Super Admin Dashboard
│   ├── users/         # User management
│   ├── products/      # Product management
│   ├── projects/      # Project management
│   ├── careers/       # Career management
│   ├── admissions/    # Admission management
│   ├── orders/        # Order management
│   ├── cms/           # Content management
│   ├── contact/       # Contact messages
│   ├── gallery/       # Gallery management
│   ├── testimonials/  # Testimonials
│   ├── sliders/       # Hero sliders
│   ├── partners/      # Partners/Clients
│   ├── team/          # Team members
│   ├── reports/       # Report generation
│   ├── settings/      # Website settings
│   └── partials/      # Admin header/sidebar/footer
├── includes/
│   ├── session.php    # Session & auth middleware
│   ├── helpers.php    # Utility functions
│   ├── header.php     # User header
│   ├── public_header.php # Public header
│   └── footer.php     # Shared footer
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript
│   ├── admin/         # Admin CSS/JS
│   └── images/        # Product images
├── uploads/           # User uploads
└── database/
    ├── shop.sql       # Original schema
    └── shop_full.sql  # Complete schema with new tables
```

## Roles & Access

### Guest (No login)
- Landing page, About, Products, Projects, Careers, Admissions, Contact

### User (Logged in)
- Profile management, Shopping, Order history (own orders only)

### Super Admin (admin@shop.com)
- Full system access: Users, Products, Projects, Careers, Admissions, Orders
- Content management (CMS, Sliders, Testimonials, Gallery, etc.)
- Reports and Settings

## URL Routes

| URL | Description |
|-----|-------------|
| `/index.php` | Landing page |
| `/user/auth.php` | Login/Signup |
| `/product/products.php` | Product listing |
| `/product/details.php?id=N` | Product details |
| `/projects.php` | Project listing |
| `/careers.php` | Job listing |
| `/contact.php` | Contact form |
| `/apply.php?type=career&job_id=N` | Apply for job |
| `/apply.php?type=admission` | Apply for admission |
| `/user/dashboard.php` | User dashboard |
| `/user/orders.php` | User orders |
| `/admin/index.php` | Admin dashboard |

## CSRF Protection
Forms include a CSRF token via `csrf_token()` function. Tokens are validated server-side on POST requests.

## File Uploads
Uploaded files are stored in `uploads/` directory organized by type:
- `uploads/products/` - Product images
- `uploads/brochures/` - Product brochures
- `uploads/admissions/` - Admission documents
- `uploads/avatars/` - User profile pictures
- `uploads/general/` - General uploads

Allowed file types: jpg, jpeg, png, webp, gif, pdf, doc, docx
Max file size: 5MB
