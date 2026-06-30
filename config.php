<?php
// ============================================================
// config.php – Application Configuration (SQLite + MongoDB)
// ============================================================

/* =========================
   APP CONFIG
========================= */
define('APP_NAME', 'Mini-Ecommerce');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('APP_URL', $protocol . '://' . $host);
define('APP_VERSION', '2.0.0');
define('COOKIE_NAME', 'remember_token');
define('COOKIE_DAYS', 30);

/* =========================
   MONGODB CONFIG (OPTIONAL)
========================= */
define('MONGO_URI', 'mongodb+srv://bikalshrestha_db_user:EGJOuoxPeprZgToL@cluster0.esuic3l.mongodb.net/');
define('MONGO_DB', 'shop');

$mongoClient = null;
$mongoDB = null;

if (class_exists('MongoDB\\Client')) {
    try {
        $mongoClient = new MongoDB\Client(MONGO_URI);
        $mongoDB = $mongoClient->selectDatabase(MONGO_DB);
    } catch (Throwable $e) {
        error_log('MongoDB connection skipped: ' . $e->getMessage());
    }
}

/* =========================
   ERROR REPORTING
========================= */
ini_set('display_errors', 1);
error_reporting(E_ALL);