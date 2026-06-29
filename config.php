<?php
// ============================================================
// config.php – Clean Hybrid DB Configuration (MySQL + MongoDB)
// ============================================================

// Load Composer autoload safely
// $autoload = __DIR__ . '/vendor/autoload.php';
// if (file_exists($autoload)) {
//     require_once $autoload;
// }

/* =========================
   APP CONFIG
========================= */
// define('APP_NAME',    'Mini-Ecommerce');
// define('APP_URL',     'http://localhost/shop');
// define('APP_URL',     'mongodb://bikalshrestha_db_user:EGJOuoxPeprZgToL@ac-nxbeskc-shard-00-00.esuic3l.mongodb.net:27017,ac-nxbeskc-shard-00-01.esuic3l.mongodb.net:27017,ac-nxbeskc-shard-00-02.esuic3l.mongodb.net:27017/?ssl=true&replicaSet=atlas-vfwpnm-shard-0&authSource=admin&appName=Cluster0');
// define('APP_URL',     'mongodb+srv://bikalshrestha_db_user:EGJOuoxPeprZgToL@cluster0.esuic3l.mongodb.net/?appName=Cluster0');
// define('APP_VERSION', '2.0.0');

// define('COOKIE_NAME', 'remember_token');
// define('COOKIE_DAYS', 30);

/* =========================
   MYSQL CONFIG
========================= */
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'shop');
// define('DB_USER', 'root');
// define('DB_PASS', ''); // XAMPP default = empty password

/* =========================
   MYSQL CONNECTION (PDO)
========================= */
// try {
//     $pdo = new PDO(
//         "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
//         DB_USER,
//         DB_PASS
//     );

//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("MySQL Connection Failed: " . $e->getMessage());
// }

/* =========================
   MONGODB CONFIG (OPTIONAL)
========================= */

// define('MONGO_URI', 'mongodb+srv://bikalshrestha_db_user:EGJOuoxPeprZgToL@cluster0.esuic3l.mongodb.net/?appName=Cluster0');
// define('MONGO_DB', 'shop');

// $mongoClient = null;
// $mongoDB = null;

// if (class_exists('MongoDB\\Client')) {
//     try {
//         $mongoClient = new MongoDB\Client(MONGO_URI);
//         $mongoDB = $mongoClient->selectDatabase(MONGO_DB);
//     } catch (Throwable $e) {
//         error_log("MongoDB Connection Error: " . $e->getMessage());
//     }
// }

/* =========================
   ERROR SETTINGS
========================= */
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



// ============================================================
// config.php – Local PHP Bootstrap with MySQL fallback
// ============================================================

// Composer autoload (optional)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

/* =========================
   APP CONFIG
========================= */
define('APP_NAME', 'Mini-Ecommerce');

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('APP_URL', $protocol . '://' . $httpHost . $basePath);
define('APP_VERSION', '2.0.0');

define('COOKIE_NAME', 'remember_token');
define('COOKIE_DAYS', 30);

/* =========================
   MYSQL CONFIG
========================= */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'shop');
define('DB_USER', 'root');
define('DB_PASS', '');

/* =========================
   MONGODB CONFIG (OPTIONAL)
========================= */
define('MONGO_URI', 'mongodb+srv://bikalshrestha_db_user:EGJOuoxPeprZgToL@cluster0.esuic3l.mongodb.net/shop?appName=Cluster0');
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
   ERROR SETTINGS
========================= */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);