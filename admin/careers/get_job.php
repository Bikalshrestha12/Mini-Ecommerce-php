<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM career_jobs WHERE job_id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode($job);
