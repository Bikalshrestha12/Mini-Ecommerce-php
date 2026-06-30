<?php
// ============================================================
// includes/header.php – Shared Navigation Header with Bootstrap
// ============================================================

require_once __DIR__ . '/session.php';

// Cart item count
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)$item['quantity'];
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Bootstrap Custom CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/bootstrap-custom.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/public.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --text: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
        }
        
        .navbar {
            background-color: #fff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
            padding: 1rem 0;
        }
        
        .nav-logo {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-logo i { font-size: 1.5rem; }
        
        .navbar-nav .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            margin: 0 0.25rem;
            padding: 0.5rem 0.75rem !important;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary) !important;
        }
        
        .nav-link .badge {
            position: absolute;
            top: 0;
            right: -8px;
            font-size: 0.7rem;
        }
        
        .btn-logout {
            color: var(--danger) !important;
        }
        
        .btn-logout:hover {
            background-color: #fee2e2 !important;
        }
        
        .btn-nav-login {
            background-color: var(--primary) !important;
            color: #fff !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem;
        }
        
        .btn-nav-login:hover {
            background-color: var(--primary-dark) !important;
        }
        
        .cart-link {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
    </style>
</head>
<body>

<!-- Navbar with Bootstrap -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid px-4">
        <a href="<?= APP_URL ?>/index.php" class="navbar-brand nav-logo">
            <i class="fas fa-bag-shopping"></i>
            <span><?= htmlspecialchars(APP_NAME) ?></span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/index.php" 
                       class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/product/products.php"
                       class="nav-link <?= ($currentDir === 'product') ? 'active' : '' ?>">
                        <i class="fas fa-shirt"></i> Products
                    </a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/cart/cart.php"
                       class="nav-link cart-link <?= ($currentDir === 'cart') ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/orders/history.php"
                       class="nav-link <?= ($currentDir === 'orders') ? 'active' : '' ?>">
                        <i class="fas fa-box"></i> Orders
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/user/profile.php"
                       class="nav-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/logout.php" class="nav-link btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a href="<?= APP_URL ?>/index.php" class="nav-link btn-nav-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content">
