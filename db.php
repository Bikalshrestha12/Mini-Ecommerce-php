<?php
// ============================================================
// db.php – PDO Database Connection
// ============================================================

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
                    <h2>Database Connection Failed</h2>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    <p>Please check your <code>config.php</code> credentials and ensure MySQL is running.</p>
                 </div>');
        }
    }
    return $pdo;
}
