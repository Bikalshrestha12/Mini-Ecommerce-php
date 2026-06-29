<?php
if (!isset($pageTitle)) $pageTitle = 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> - <?= h(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/admin/css/admin.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-active: #6366f1;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }
        .admin-wrapper { display: flex; width: 100%; min-height: 100vh; }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 1.5rem 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }
        .navbar-admin {
            background: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .navbar-admin .nav-search {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 0.4rem 1rem;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .navbar-admin .nav-search:focus-within {
            border-color: var(--primary);
            background: #fff;
        }
        .navbar-admin .nav-search input {
            border: none;
            background: none;
            outline: none;
            padding: 0.3rem 0.5rem;
            font-size: 0.85rem;
            color: #1e293b;
            min-width: 200px;
        }
        .navbar-admin .nav-search input::placeholder { color: #94a3b8; }
        .navbar-admin .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .navbar-admin .nav-icon-btn {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 50%;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s;
        }
        .navbar-admin .nav-icon-btn:hover {
            background: #e0e7ff;
            color: var(--primary);
        }
        .navbar-admin .nav-icon-btn .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .navbar-admin .admin-avatar-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem 0.25rem 0.25rem;
            border-radius: 9999px;
            background: #f8fafc;
            cursor: pointer;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.2s;
            border: none;
        }
        .navbar-admin .admin-avatar-dropdown:hover {
            background: #e0e7ff;
        }
        .navbar-admin .admin-avatar-dropdown .avatar-sm {
            width: 34px; height: 34px; border-radius: 50%;
            object-fit: cover;
        }
        .navbar-admin .admin-avatar-dropdown .avatar-initials {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .table th { font-weight: 600; color: #475569; border-top: none; }
        .table td { vertical-align: middle; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 0.5rem; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fef2f2; color: #991b1b; }
        .status-read { background: #dbeafe; color: #1e40af; }
        .status-unread { background: #fef3c7; color: #92400e; }
        .dt-buttons { margin-bottom: 0.5rem; }
        .dataTables_filter { margin-bottom: 0.5rem; }
        .avatar-thumb { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .preview-img { max-width: 200px; max-height: 200px; border-radius: 0.5rem; margin-top: 0.5rem; }
        .nav-tabs .nav-link { color: #475569; font-weight: 500; }
        .nav-tabs .nav-link.active { color: var(--primary); border-bottom: 2px solid var(--primary); }
        .tab-content { padding-top: 1.5rem; }
        .report-card { transition: transform 0.2s; cursor: pointer; }
        .report-card:hover { transform: translateY(-2px); }
        .footer-text { font-size: 0.875rem; color: #94a3b8; }
        .sort-handle { cursor: grab; color: #94a3b8; }
        .dropdown-menu { border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,.1); padding: 0.5rem; }
        .dropdown-item { border-radius: 0.5rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
        .dropdown-item:hover { background: #f1f5f9; }
        .dropdown-item.text-danger:hover { background: #fee2e2; }
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
        }
        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; }
            .navbar-admin .nav-search input { min-width: 120px; }
            .sidebar-toggle { display: block; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <div class="main-content">
        <nav class="navbar-admin">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <div class="nav-search">
                    <i class="fas fa-search" style="color:#94a3b8"></i>
                    <input type="text" placeholder="Search anything...">
                </div>
            </div>
            <div class="nav-actions">
                <a href="#" class="nav-icon-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge">3</span>
                </a>
                <a href="#" class="nav-icon-btn" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="notif-badge">1</span>
                </a>
                <div class="dropdown">
                    <button class="admin-avatar-dropdown dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        $avatarPath = '';
                        $stmt = $pdo ?? null;
                        if ($stmt = getDB()) {
                            $q = $stmt->prepare("SELECT avatar FROM users WHERE user_id = ?");
                            $q->execute([$_SESSION['user'] ?? 0]);
                            $avatarPath = $q->fetchColumn();
                        }
                        ?>
                        <?php if ($avatarPath): ?>
                        <img src="<?= imgUrl($avatarPath) ?>" alt="" class="avatar-sm">
                        <?php else: ?>
                        <div class="avatar-initials"><?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?></div>
                        <?php endif; ?>
                        <span class="fw-medium small"><?= h($_SESSION['name'] ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/users/profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/settings/"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
