<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM admission_programs WHERE program_id = ?");
$stmt->execute([$id]);
$program = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode($program);
