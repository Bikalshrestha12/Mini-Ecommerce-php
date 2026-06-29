<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$perPage = 9;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$filterCat = $_GET['category'] ?? '';

$conditions = ['p.is_active = 1'];
$params = [];

if (!empty($filterCat)) {
    $conditions[] = 'p.category_id = ?';
    $params[] = (int)$filterCat;
}

$where = 'WHERE ' . implode(' AND ', $conditions);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM projects p $where");
$countStmt->execute($params);
$totalProjects = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalProjects / $perPage));

$stmt = $pdo->prepare("SELECT p.*, pc.name as category_name FROM projects p LEFT JOIN project_categories pc ON p.category_id = pc.category_id $where ORDER BY p.project_id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = getProjectCategories();

$urlPattern = APP_URL . '/projects.php?page={page}';
if (!empty($filterCat)) $urlPattern .= '&category=' . urlencode($filterCat);

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><i class="fas fa-project-diagram"></i> Our Projects</h1>
        <p data-aos="fade-up" data-aos-delay="100">Explore our completed projects and success stories</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">

        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" data-aos="fade-up">
            <a href="<?= APP_URL ?>/projects.php" class="btn btn-sm <?= empty($filterCat) ? 'btn-primary' : 'btn-ghost' ?> rounded-pill px-4">All</a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= APP_URL ?>/projects.php?category=<?= $c['category_id'] ?>" class="btn btn-sm <?= $filterCat == $c['category_id'] ? 'btn-primary' : 'btn-ghost' ?> rounded-pill px-4"><?= h($c['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($projects)): ?>
        <div class="empty-state" data-aos="fade-up">
            <i class="fas fa-folder-open"></i>
            <h3>No projects found</h3>
            <p>Check back later for new projects.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $i => $pr): ?>
            <?php
            $prImgStmt = $pdo->prepare("SELECT image FROM project_images WHERE project_id = ? AND is_primary = 1 LIMIT 1");
            $prImgStmt->execute([$pr['project_id']]);
            $prImg = $prImgStmt->fetch();
            ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 9) * 100 ?>">
                <div class="project-card shadow-sm">
                    <a href="<?= APP_URL ?>/project-details.php?id=<?= $pr['project_id'] ?>">
                        <div class="project-img-wrap">
                            <img src="<?= imgUrl($prImg['image'] ?? '') ?>" alt="<?= h($pr['title']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                        </div>
                    </a>
                    <div class="project-overlay">
                        <?php if (!empty($pr['category_name'])): ?>
                        <span class="project-category"><?= h($pr['category_name']) ?></span>
                        <?php endif; ?>
                        <h3 class="project-title"><?= h($pr['title']) ?></h3>
                        <p class="project-description"><?= h(mb_substr(strip_tags($pr['description'] ?? ''), 0, 150)) ?>...</p>
                        <a href="<?= APP_URL ?>/project-details.php?id=<?= $pr['project_id'] ?>" class="btn btn-white btn-sm mt-3 align-self-start">
                            View Details <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?= pagination($page, $totalPages, $urlPattern) ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
