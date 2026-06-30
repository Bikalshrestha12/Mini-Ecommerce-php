<?php
// ============================================================
// db.php – SQLite Database Connection (no server needed)
// ============================================================

function getDB(): ?PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dbPath = __DIR__ . '/database/shop.sqlite';
            $pdo = new PDO("sqlite:$dbPath");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->exec('PRAGMA foreign_keys=ON');
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            $pdo = false;
        }
    }
    return $pdo === false ? null : $pdo;
}

function dbAvailable(): bool {
    return getDB() !== null;
}
