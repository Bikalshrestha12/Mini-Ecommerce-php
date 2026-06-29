<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

require_once __DIR__ . '/includes/public_header.php';
require __DIR__ . '/public/landing.php';
require_once __DIR__ . '/includes/footer.php';
