<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if ($project) {
    $imgStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order");
    $imgStmt->execute([$id]);
    $project['images'] = $imgStmt->fetchAll();

    $vidStmt = $pdo->prepare("SELECT * FROM project_videos WHERE project_id = ? ORDER BY sort_order");
    $vidStmt->execute([$id]);
    $project['videos'] = $vidStmt->fetchAll();

    $docStmt = $pdo->prepare("SELECT * FROM project_documents WHERE project_id = ? ORDER BY created_at DESC");
    $docStmt->execute([$id]);
    $project['documents'] = $docStmt->fetchAll();
}

header('Content-Type: application/json');
echo json_encode($project);
