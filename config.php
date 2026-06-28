<?php
// ============================================================
// config.php – Application Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'shop');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME',    'Mini-Ecommerce');
define('APP_URL',     'http://localhost/Mini-Ecommerce');
define('COOKIE_NAME', 'remember_token');
define('COOKIE_DAYS', 30);

// Error display (set false in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
