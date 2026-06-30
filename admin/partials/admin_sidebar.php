<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function isActive(string $dir, string $file = ''): string {
    global $currentDir, $currentPage;
    if ($file && $currentPage === $file) return 'active';
    if ($dir && $currentDir === $dir) return 'active';
    return '';
}
?>
<nav class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?= APP_URL ?>/admin/index.php" class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-store"></i></div>
            <div class="brand-text">
                <span class="brand-name"><?= h(APP_NAME) ?></span>
                <span class="brand-role">Admin Panel</span>
            </div>
        </a>
        <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
    </div>
    <div class="sidebar-user">
        <div class="d-flex align-items-center gap-3 px-3 py-3">
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
            <img src="<?= imgUrl($avatarPath) ?>" alt="" class="sidebar-avatar" style="width:42px;height:42px;border-radius:50%;object-fit:cover">
            <?php else: ?>
            <div class="sidebar-avatar-initials"><?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?></div>
            <?php endif; ?>
            <div class="user-info">
                <div class="user-name"><?= h($_SESSION['name'] ?? 'Admin') ?></div>
                <div class="user-role">Super Admin</div>
            </div>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/index.php" class="nav-link <?= isActive('', 'index.php') ?>">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-divider"><span>Content</span></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/cms/index.php" class="nav-link <?= isActive('cms') ?>">
                <i class="fas fa-edit"></i> <span>CMS Pages</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/sliders/index.php" class="nav-link <?= isActive('sliders') ?>">
                <i class="fas fa-images"></i> <span>Hero Sliders</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/testimonials/index.php" class="nav-link <?= isActive('testimonials') ?>">
                <i class="fas fa-quote-right"></i> <span>Testimonials</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/gallery/index.php" class="nav-link <?= isActive('gallery') ?>">
                <i class="fas fa-images"></i> <span>Gallery</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/partners/index.php" class="nav-link <?= isActive('partners') ?>">
                <i class="fas fa-handshake"></i> <span>Partners</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/team/index.php" class="nav-link <?= isActive('team') ?>">
                <i class="fas fa-users"></i> <span>Team Members</span>
            </a>
        </li>
        <li class="nav-divider"><span>Management</span></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/contact/index.php" class="nav-link <?= isActive('contact') ?>">
                <i class="fas fa-envelope"></i> <span>Contact Messages</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/products/index.php" class="nav-link <?= isActive('products') ?>">
                <i class="fas fa-box"></i> <span>Products</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/orders/index.php" class="nav-link <?= isActive('orders') ?>">
                <i class="fas fa-shopping-cart"></i> <span>Orders</span>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/projects/index.php" class="nav-link <?= isActive('projects') ?>">
                <i class="fas fa-project-diagram"></i> <span>Projects</span>
            </a>
        </li> -->
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/careers/index.php" class="nav-link <?= isActive('careers') ?>">
                <i class="fas fa-briefcase"></i> <span>Careers</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/admissions/index.php" class="nav-link <?= isActive('admissions') ?>">
                <i class="fas fa-graduation-cap"></i> <span>Admissions</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/users/index.php" class="nav-link <?= isActive('users') ?>">
                <i class="fas fa-user-shield"></i> <span>Users</span>
            </a>
        </li>
        <li class="nav-divider"><span>Settings</span></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/settings/index.php" class="nav-link <?= isActive('settings') ?>">
                <i class="fas fa-cog"></i> <span>Website Settings</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/reports/index.php" class="nav-link <?= isActive('reports') ?>">
                <i class="fas fa-chart-bar"></i> <span>Reports</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="<?= APP_URL ?>/index.php" class="sidebar-footer-btn" style="background:rgba(255,255,255,.06);color:#94a3b8">
            <i class="fas fa-external-link-alt"></i> View Site
        </a>
        <a href="<?= APP_URL ?>/logout.php" class="sidebar-footer-btn" style="background:rgba(239,68,68,.1);color:#fca5a5">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
.admin-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: var(--sidebar-width);
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    display: flex;
    flex-direction: column;
    z-index: 1040;
    overflow-y: auto;
    transition: transform 0.3s ease;
}
.sidebar-header {
    padding: 1.25rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sidebar-brand {
    color: #fff;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.brand-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #fff;
}
.brand-text { line-height: 1.2; }
.brand-name { font-size: 1rem; font-weight: 700; display: block; }
.brand-role { font-size: 0.7rem; color: var(--sidebar-text); font-weight: 400; }
.sidebar-close {
    display: none;
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 1.1rem;
    cursor: pointer;
}
.sidebar-user {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.sidebar-avatar-initials {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}
.user-info { min-width: 0; }
.user-name { color: #fff; font-size: 0.875rem; font-weight: 600; }
.user-role { font-size: 0.7rem; color: var(--sidebar-text); }
.sidebar-nav {
    list-style: none;
    padding: 0.5rem 0;
    flex: 1;
    margin: 0;
}
.sidebar-nav .nav-item { padding: 0; }
.sidebar-nav .nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1.25rem;
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    margin: 0.1rem 0;
}
.sidebar-nav .nav-link:hover {
    color: #fff;
    background: rgba(255,255,255,.04);
}
.sidebar-nav .nav-link.active {
    color: #fff;
    background: rgba(99,102,241,.15);
    border-left-color: var(--sidebar-active);
}
.sidebar-nav .nav-link i {
    width: 1.25rem;
    text-align: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.nav-divider {
    padding: 0.75rem 1.25rem 0.25rem;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,.2);
}
.sidebar-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(255,255,255,.06);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.sidebar-footer-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.sidebar-footer-btn:hover {
    transform: translateY(-1px);
}
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 1030;
}
@media (max-width: 991.98px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }
    .admin-sidebar.open {
        transform: translateX(0);
    }
    .sidebar-close { display: block; }
    .sidebar-overlay.show { display: block; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');
    const close = document.getElementById('sidebarClose');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('show');
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (close) close.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
});
</script>
