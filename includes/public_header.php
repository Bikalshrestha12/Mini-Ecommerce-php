<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/helpers.php';

$siteName = getSetting('site_name', APP_NAME);
$favicon  = getSetting('site_favicon', '');
$contactPhone = getSetting('contact_phone', '');
$contactEmail = getSetting('contact_email', '');
$facebookUrl  = getSetting('facebook_url', '#');
$twitterUrl   = getSetting('twitter_url', '#');
$instagramUrl = getSetting('instagram_url', '#');
$linkedinUrl  = getSetting('linkedin_url', '#');
$youtubeUrl   = getSetting('youtube_url', '');

$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$cartCount   = getCartCount();

function isActive(string $file, string $dir = ''): string {
    global $currentFile, $currentDir;
    if ($dir && $currentDir === $dir) return 'active';
    if ($currentFile === $file) return 'active';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($siteName) ?></title>

    <?php if ($favicon): ?>
    <link rel="icon" href="<?= imgUrl($favicon) ?>" type="image/x-icon">
    <?php endif; ?>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/bootstrap-custom.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/public.css">

    <style>
        .top-bar {
            background: var(--primary-500);
            color: #fff;
            padding: 0.4rem 0;
            font-size: 0.82rem;
        }
        .top-bar a { color: #fff; text-decoration: none; }
        .top-bar a:hover { text-decoration: underline; }
        .top-bar .social-links a {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(255,255,255,0.15); transition: 0.2s ease;
        }
        .top-bar .social-links a:hover { background: rgba(255,255,255,0.3); text-decoration: none; }
        .navbar { background: #fff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 0.6rem 0 !important; position: relative; }
        .navbar-brand { font-weight: 700; font-size: 1.3rem; color: var(--primary-500) !important; display: flex; align-items: center; gap: 0.5rem; }
        .navbar-brand i { font-size: 1.5rem; }
        .navbar-nav .nav-link {
            color: var(--secondary-500) !important; font-weight: 500; margin: 0 0.15rem;
            padding: 0.5rem 0.75rem !important; border-radius: 0.375rem; transition: all 0.2s ease;
            display: flex; align-items: center; gap: 0.4rem;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            background: var(--primary-100); color: var(--primary-500) !important;
        }
        .cart-link { position: relative; }
        .cart-badge {
            position: absolute; top: -6px; right: -6px;
            background: var(--danger); color: #fff; border-radius: 50%;
            width: 18px; height: 18px; font-size: 0.65rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-nav-login {
            background: var(--primary-500) !important; color: #fff !important;
            padding: 0.5rem 1rem !important; border-radius: 0.375rem;
        }
        .btn-nav-login:hover { background: var(--primary-600) !important; }
        .dropdown-menu { border-radius: 0.5rem; border: 1px solid var(--border-color); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .dropdown-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; }
        .dropdown-item:hover { background: var(--primary-100); }
        .dropdown-item.text-danger:hover { background: var(--danger-bg); }
        .user-avatar-nav {
            width: 28px; height: 28px; border-radius: 50%; object-fit: cover; margin-right: 0.25rem;
        }
        .search-nav-btn {
            background: none; border: none; color: var(--secondary-500); font-size: 1.1rem;
            padding: 0.5rem 0.65rem; border-radius: 0.375rem; transition: all 0.2s ease;
            display: flex; align-items: center;
        }
        .search-nav-btn:hover { background: var(--primary-100); color: var(--primary-500); }
        .hamburger-animated {
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            width: 36px; height: 36px; cursor: pointer; background: transparent; border: none; padding: 0;
        }
        .hamburger-animated span {
            display: block; width: 20px; height: 2px; background: var(--body-color);
            border-radius: 2px; transition: all 0.3s ease; position: relative;
        }
        .hamburger-animated span + span { margin-top: 5px; }
        .hamburger-animated.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
        .hamburger-animated.active span:nth-child(2) { opacity: 0; }
        .hamburger-animated.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }
        @media (max-width: 991.98px) {
            .navbar-nav .nav-link { padding: 0.5rem 0.75rem !important; }
            .top-bar .contact-info { display: none; }
            .navbar-collapse {
                background: #f8f9fa;
                border-top: 1px solid var(--border-color);
                margin-top: 0.5rem;
                padding: 0 !important;
                height: 0;
                overflow: hidden;
                transition: height 0.35s ease;
            }
            .navbar-collapse.show {
                height: auto;
                overflow: visible;
            }
            .navbar-nav {
                flex-direction: column;
                padding: 0.5rem 0 !important;
                margin: 0 !important;
            }
            .navbar-nav .nav-item {
                margin: 0 !important;
            }
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem !important;
                border-radius: 0 !important;
                margin: 0 !important;
                display: flex !important;
                align-items: center !important;
            }
            .navbar-nav .nav-link:hover {
                background: var(--primary-100) !important;
            }
            .navbar-nav .dropdown-menu {
                background: var(--primary-50);
                border: none;
                position: static;
                box-shadow: none;
                padding: 0.5rem 1rem;
                margin: 0 !important;
            }
            .navbar-nav .dropdown-item {
                padding: 0.5rem 0 !important;
            }
            .navbar-nav .dropdown-toggle::after {
                margin-left: auto;
            }
        }
    </style>
</head>
<body>

<div class="page-loader" id="pageLoader">
    <i class="fas fa-store loader-logo" style="font-size:3rem;color:var(--primary-500);"></i>
    <div class="loader-bar"></div>
    <noscript><style>.page-loader{display:none!important;}</style></noscript>
</div>

<div class="scroll-progress-bar" id="scrollProgressBar"></div>

<div class="top-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="contact-info d-flex gap-3">
                    <?php if ($contactPhone): ?>
                    <span><i class="fas fa-phone-alt"></i> <a href="tel:<?= h($contactPhone) ?>"><?= h($contactPhone) ?></a></span>
                    <?php endif; ?>
                    <?php if ($contactEmail): ?>
                    <span><i class="fas fa-envelope"></i> <a href="mailto:<?= h($contactEmail) ?>"><?= h($contactEmail) ?></a></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="social-links d-inline-flex gap-1">
                    <a href="<?= h($facebookUrl) ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?= h($twitterUrl) ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="<?= h($instagramUrl) ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="<?= h($linkedinUrl) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <?php if ($youtubeUrl): ?>
                    <a href="<?= h($youtubeUrl) ?>" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg site-header" id="mainNav" data-scroll-nav>
    <div class="container">
        <a class="navbar-brand" href="<?= APP_URL ?>/index.php">
            <i class="fas fa-store"></i>
            <span><?= h($siteName) ?></span>
        </a>

        <div class="d-flex align-items-center gap-2 d-lg-none">
            <button class="search-nav-btn" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-label="Search">
                <i class="fas fa-search"></i>
            </button>
            <button class="hamburger-animated navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('index.php') ?>" href="<?= APP_URL ?>/index.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('about.php') ?>" href="<?= APP_URL ?>/about.php"><i class="fas fa-info-circle"></i> About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('products.php', 'product') ?>" href="<?= APP_URL ?>/product/products.php"><i class="fas fa-box"></i> Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('projects.php') ?>" href="<?= APP_URL ?>/projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('careers.php') ?>" href="<?= APP_URL ?>/careers.php"><i class="fas fa-briefcase"></i> Careers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('contact.php') ?>" href="<?= APP_URL ?>/contact.php"><i class="fas fa-envelope"></i> Contact</a>
                </li>

                <li class="nav-item d-none d-lg-block">
                    <button class="search-nav-btn" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </li>

                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link cart-link" href="<?= APP_URL ?>/cart/cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php $avatar = $_SESSION['avatar'] ?? ''; ?>
                        <?php if ($avatar): ?>
                        <img src="<?= imgUrl($avatar) ?>" alt="" class="user-avatar-nav">
                        <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <?= h($_SESSION['name'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/user/profile.php"><i class="fas fa-id-card"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/user/orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                        <?php if (isSuperAdmin()): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/index.php"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link btn-nav-login" href="<?= APP_URL ?>/user/auth.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="collapse" id="searchCollapse">
    <div class="container py-3">
        <form action="<?= APP_URL ?>/product/products.php" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search products..." aria-label="Search">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

<main class="main-content">
