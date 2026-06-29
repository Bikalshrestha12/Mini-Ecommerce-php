<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$perPage = 10;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$search     = trim($_GET['search'] ?? '');
$department = $_GET['department'] ?? '';

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

$where = 'WHERE ' . implode(' AND ', $conditions);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM career_jobs $where");
$countStmt->execute($params);
$totalJobs = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalJobs / $perPage));

$stmt = $pdo->prepare("SELECT * FROM career_jobs $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$departments = getJobDepartments();

$careerSection = getCMSSection('careers', 'intro');

$urlPattern = APP_URL . '/careers.php?page={page}';
if (!empty($search)) $urlPattern .= '&search=' . urlencode($search);
if (!empty($department)) $urlPattern .= '&department=' . urlencode($department);

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><i class="fas fa-briefcase"></i> Careers</h1>
        <p data-aos="fade-up" data-aos-delay="100"><?= h($careerSection['subtitle'] ?? 'Join our team and grow your career with us') ?></p>
    </div>
</div>

<section class="section-padding">
    <div class="container">

        <?php if ($careerSection): ?>
        <div class="row mb-5" data-aos="fade-up">
            <div class="col-lg-8 mx-auto text-center">
                <?php if (!empty($careerSection['title'])): ?>
                <span class="badge badge-primary px-3 py-2 mb-3">Join Us</span>
                <h2 class="section-title fw-bold"><?= h($careerSection['title']) ?></h2>
                <div class="section-divider mx-auto"></div>
                <?php endif; ?>
                <?php if (!empty($careerSection['content'])): ?>
                <p class="lead text-muted"><?= nl2br(h($careerSection['content'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="GET" action="" class="bg-white rounded-4 shadow-sm p-4 mb-5" data-aos="fade-up">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-muted small text-uppercase"><i class="fas fa-search"></i> Search Jobs</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by title, department, location..." value="<?= h($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted small text-uppercase"><i class="fas fa-building"></i> Department</label>
                    <select name="department" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= h($d) ?>" <?= $department === $d ? 'selected' : '' ?>><?= h($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small text-uppercase">&nbsp;</label>
                    <a href="<?= APP_URL ?>/careers.php" class="btn btn-outline w-100"><i class="fas fa-undo"></i> Clear Filters</a>
                </div>
            </div>
        </form>

        <?php if (!empty($departments)): ?>
        <div class="mb-4 d-flex flex-wrap gap-2" data-aos="fade-up">
            <a href="<?= APP_URL ?>/careers.php?search=<?= urlencode($search) ?>" class="btn btn-sm <?= empty($department) ? 'btn-primary' : 'btn-ghost' ?> rounded-pill px-3">All</a>
            <?php foreach ($departments as $d): ?>
            <a href="<?= APP_URL ?>/careers.php?department=<?= urlencode($d) ?>&search=<?= urlencode($search) ?>" class="btn btn-sm <?= $department === $d ? 'btn-primary' : 'btn-ghost' ?> rounded-pill px-3"><?= h($d) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>
        <div class="empty-state" data-aos="fade-up">
            <i class="fas fa-briefcase"></i>
            <h3>No open positions</h3>
            <p>We don't have any open positions right now. Check back later!</p>
        </div>
        <?php else: ?>
        <p class="text-muted mb-4" data-aos="fade-up">Showing <strong class="text-dark"><?= count($jobs) ?></strong> of <strong class="text-dark"><?= $totalJobs ?></strong> open positions</p>

        <div class="d-flex flex-column gap-4">
            <?php foreach ($jobs as $i => $j): ?>
            <div class="bg-white rounded-4 shadow-sm p-4 card-hover border-0 position-relative" style="border-left: 4px solid var(--primary-500) !important;" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 50 ?>">
                <div class="row align-items-center g-3">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <h3 class="fw-bold mb-0 fs-5"><?= h($j['title']) ?></h3>
                            <?php if (!empty($j['employment_type'])): ?>
                            <span class="badge badge-primary"><?= h($j['employment_type']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <?php if (!empty($j['department'])): ?>
                            <span><i class="fas fa-building text-primary"></i> <?= h($j['department']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['location'])): ?>
                            <span><i class="fas fa-map-marker-alt text-danger"></i> <?= h($j['location']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['salary_range'])): ?>
                            <span><i class="fas fa-money-bill-wave text-success"></i> <?= h($j['salary_range']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['application_deadline'])): ?>
                            <span><i class="fas fa-calendar-times text-warning"></i> Deadline: <?= date('M d, Y', strtotime($j['application_deadline'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <p class="text-muted small mb-0"><?= h(mb_substr(strip_tags($j['description'] ?? ''), 0, 200)) ?>...</p>
                    </div>
                    <div class="col-lg-2 text-lg-end">
                        <div class="d-flex gap-2 justify-content-lg-end">
                            <a href="<?= APP_URL ?>/job-detail.php?id=<?= $j['job_id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-info-circle"></i> Details</a>
                            <?php
                            $deadlinePassed = !empty($j['application_deadline']) && strtotime($j['application_deadline']) < time();
                            ?>
                            <a href="<?= APP_URL ?>/job-detail.php?id=<?= $j['job_id'] ?>#apply" class="btn btn-primary btn-sm <?= $deadlinePassed ? 'disabled' : '' ?>">
                                <i class="fas fa-paper-plane"></i> Apply Now
                            </a>
                        </div>
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
