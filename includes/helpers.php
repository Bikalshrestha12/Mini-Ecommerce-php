<?php
// ============================================================
// includes/helpers.php – Utility Functions
// ============================================================

require_once __DIR__ . '/../db.php';

/**
 * Get a single setting value
 */
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['setting_value'] : $default;
    }
    return $cache[$key];
}

/**
 * Get all settings as key-value pairs
 */
function getAllSettings(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * Get CMS section content
 */
function getCMSSection(string $page, string $sectionKey): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM cms_sections WHERE page = ? AND section_key = ?");
    $stmt->execute([$page, $sectionKey]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Get all active CMS sections for a page
 */
function getCMSSections(string $page): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM cms_sections WHERE page = ? AND is_active = 1 ORDER BY sort_order");
    $stmt->execute([$page]);
    return $stmt->fetchAll();
}

/**
 * Upload file to uploads directory
 */
function uploadFile(array $file, string $subdir = 'general', array $allowed = ['jpg','jpeg','png','webp','gif','pdf','doc','docx']): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) return null;
    
    $uploadDir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $uploadDir . '/' . $filename;
    
    return move_uploaded_file($file['tmp_name'], $dest) ? 'uploads/' . $subdir . '/' . $filename : null;
}

/**
 * Generate slug from string
 */
function createSlug(string $string): string {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Get user role name
 */
function getUserRole(?int $roleId): string {
    if (!$roleId) return 'Guest';
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    $row = $stmt->fetch();
    return $row ? $row['role_name'] : 'User';
}

/**
 * Check if user has super admin role
 */
function isSuperAdmin(): bool {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 2;
}

/**
 * Check if user has user role
 */
function isUser(): bool {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

/**
 * Generate pagination HTML
 */
function pagination(int $currentPage, int $totalPages, string $urlPattern): string {
    if ($totalPages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '">';
    $html .= '<a class="page-link" href="' . str_replace('{page}', $currentPage - 1, $urlPattern) . '">&laquo;</a></li>';
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . str_replace('{page}', $i, $urlPattern) . '">' . $i . '</a></li>';
    }
    
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="' . str_replace('{page}', $currentPage + 1, $urlPattern) . '">&raquo;</a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Sanitize output
 */
function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with message
 */
function redirectWith(string $url, string $msg = '', string $type = 'success'): void {
    if ($msg) {
        $_SESSION['flash_' . $type] = $msg;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Display flash message
 */
function flashMessage(): string {
    $types = ['success', 'error', 'info', 'warning'];
    $html = '';
    foreach ($types as $type) {
        if (!empty($_SESSION['flash_' . $type])) {
            $icon = ['success' => 'check-circle', 'error' => 'exclamation-circle', 'info' => 'info-circle', 'warning' => 'exclamation-triangle'][$type];
            $html .= '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
            $html .= '<i class="fas fa-' . $icon . '"></i> ' . h($_SESSION['flash_' . $type]);
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            unset($_SESSION['flash_' . $type]);
        }
    }
    return $html;
}

/**
 * Get active hero sliders
 */
function getHeroSliders(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM hero_sliders WHERE is_active = 1 ORDER BY sort_order");
    return $stmt->fetchAll();
}

/**
 * Get active testimonials
 */
function getTestimonials(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order");
    return $stmt->fetchAll();
}

/**
 * Get active gallery images
 */
function getGallery(int $limit = 0): array {
    $pdo = getDB();
    $sql = "SELECT * FROM gallery WHERE is_active = 1 ORDER BY sort_order";
    if ($limit > 0) $sql .= " LIMIT " . (int)$limit;
    return $pdo->query($sql)->fetchAll();
}

/**
 * Get active partners/clients
 */
function getPartners(): array {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM partners WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
}

/**
 * Get active team members
 */
function getTeamMembers(): array {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
}

/**
 * Get featured products
 */
function getFeaturedProducts(int $limit = 8): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY product_id DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get active projects
 */
function getActiveProjects(int $limit = 6): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, pc.name as category_name FROM projects p LEFT JOIN project_categories pc ON p.category_id = pc.category_id WHERE p.is_active = 1 ORDER BY p.project_id DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get latest news/blog
 */
function getLatestNews(int $limit = 3): array {
    $cms = getDB()->prepare("SELECT * FROM cms_sections WHERE page = 'news' AND is_active = 1 ORDER BY created_at DESC LIMIT ?");
    $cms->bindValue(1, $limit, PDO::PARAM_INT);
    $cms->execute();
    return $cms->fetchAll();
}

/**
 * Get image URL from database path
 */
function imgUrl(?string $image, string $default = 'placeholder.jpg'): string {
    if (empty($image)) return APP_URL . '/assets/images/' . $default;
    if (strpos($image, 'uploads/') === 0) return APP_URL . '/' . $image;
    if (strpos($image, 'http') === 0) return $image;
    return APP_URL . '/assets/images/' . $image;
}

/**
 * Get cart count from session
 */
function getCartCount(): int {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)($item['quantity'] ?? 0);
        }
    }
    return $count;
}

/**
 * Get project categories
 */
function getProjectCategories(): array {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM project_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
}

/**
 * Get active job listings
 */
function getActiveJobs(int $limit = 0, string $department = '', string $search = ''): array {
    $pdo = getDB();
    $conditions = ['is_active = 1'];
    $params = [];
    if (!empty($department)) {
        $conditions[] = 'department = ?';
        $params[] = $department;
    }
    if (!empty($search)) {
        $conditions[] = '(title LIKE ? OR description LIKE ? OR department LIKE ? OR location LIKE ?)';
        $s = '%' . $search . '%';
        $params = array_merge($params, [$s, $s, $s, $s]);
    }
    $where = implode(' AND ', $conditions);
    $sql = "SELECT * FROM career_jobs WHERE $where ORDER BY created_at DESC";
    if ($limit > 0) $sql .= " LIMIT " . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get departments for filter
 */
function getJobDepartments(): array {
    $pdo = getDB();
    return $pdo->query("SELECT DISTINCT department FROM career_jobs WHERE is_active = 1 AND department IS NOT NULL AND department != '' ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Generate CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF hidden input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
