<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

$order_id = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$items = [];
if ($order) {
    $itemStmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll();
}

header('Content-Type: application/json');
echo json_encode(['order' => $order, 'items' => $items]);
