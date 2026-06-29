<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM career_jobs WHERE job_id = ? AND is_active = 1");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . APP_URL . '/careers.php');
    exit;
}

$deadlinePassed = !empty($job['application_deadline']) && strtotime($job['application_deadline']) < time();

$applySuccess = $_SESSION['apply_success'] ?? '';
$applyError   = $_SESSION['apply_error'] ?? '';
unset($_SESSION['apply_success'], $_SESSION['apply_error']);

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><?= h($job['title']) ?></h1>
        <p data-aos="fade-up" data-aos-delay="100"><?= h($job['department'] ?? 'Career Opportunity') ?></p>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <a href="<?= APP_URL ?>/careers.php" class="btn btn-ghost mb-4 px-0" data-aos="fade-up"><i class="fas fa-arrow-left me-2"></i> Back to Careers</a>

        <?php if ($applySuccess): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-check-circle"></i> <?= h($applySuccess) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($applyError): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-exclamation-circle"></i> <?= h($applyError) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row g-5">
            <div class="col-lg-8">
                <div class="bg-white rounded-4 shadow-sm p-5 mb-4 border-0" data-aos="fade-up">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <h2 class="fw-bold mb-0"><?= h($job['title']) ?></h2>
                        <?php if (!empty($job['employment_type'])): ?>
                        <span class="badge badge-primary badge-lg"><?= h($job['employment_type']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <?php if (!empty($job['department'])): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-normal px-3 py-2 rounded-pill"><i class="fas fa-building me-1"></i> <?= h($job['department']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['location'])): ?>
                        <span class="badge bg-info bg-opacity-10 text-info fs-6 fw-normal px-3 py-2 rounded-pill"><i class="fas fa-map-marker-alt me-1"></i> <?= h($job['location']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['salary_range'])): ?>
                        <span class="badge bg-success bg-opacity-10 text-success fs-6 fw-normal px-3 py-2 rounded-pill"><i class="fas fa-money-bill-wave me-1"></i> <?= h($job['salary_range']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($job['application_deadline'])): ?>
                    <div class="alert <?= $deadlinePassed ? 'alert-danger' : 'alert-info' ?> d-flex align-items-center gap-2 border-0 shadow-sm">
                        <i class="fas fa-calendar-alt fs-5"></i>
                        <strong>Application Deadline:</strong>
                        <?= date('F d, Y', strtotime($job['application_deadline'])) ?>
                        <?php if ($deadlinePassed): ?>
                        <span class="ms-2 badge bg-danger">Expired</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($job['description'])): ?>
                <div class="bg-white rounded-4 shadow-sm p-5 mb-4 border-0" data-aos="fade-up">
                    <h4 class="fw-bold mb-4"><i class="fas fa-align-left text-primary me-2"></i> Job Description</h4>
                    <div class="text-muted lh-lg"><?= nl2br(h($job['description'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($job['requirements'])): ?>
                <div class="bg-white rounded-4 shadow-sm p-5 mb-4 border-0" data-aos="fade-up">
                    <h4 class="fw-bold mb-4"><i class="fas fa-check-circle text-primary me-2"></i> Requirements</h4>
                    <div class="text-muted lh-lg">
                        <?php
                        $reqLines = explode("\n", $job['requirements']);
                        echo '<ul class="list-unstyled">';
                        foreach ($reqLines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                echo '<li class="mb-2 d-flex align-items-start gap-2"><i class="fas fa-check-circle text-success mt-1 flex-shrink-0"></i> ' . h($line) . '</li>';
                            }
                        }
                        echo '</ul>';
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($job['responsibilities'])): ?>
                <div class="bg-white rounded-4 shadow-sm p-5 mb-4 border-0" data-aos="fade-up">
                    <h4 class="fw-bold mb-4"><i class="fas fa-tasks text-primary me-2"></i> Responsibilities</h4>
                    <div class="text-muted lh-lg">
                        <?php
                        $respLines = explode("\n", $job['responsibilities']);
                        echo '<ul class="list-unstyled">';
                        foreach ($respLines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                echo '<li class="mb-2 d-flex align-items-start gap-2"><i class="fas fa-arrow-right text-primary mt-1 flex-shrink-0"></i> ' . h($line) . '</li>';
                            }
                        }
                        echo '</ul>';
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-gradient rounded-4 p-5 text-white text-center" data-aos="fade-up" id="apply">
                    <h3 class="fw-bold text-white mb-3">Apply for this Position</h3>
                    <?php if ($deadlinePassed): ?>
                    <div class="alert alert-warning border-0 d-inline-flex align-items-center gap-2"><i class="fas fa-exclamation-triangle"></i> The application deadline has passed. We are no longer accepting applications for this position.</div>
                    <?php else: ?>
                    <p class="text-white opacity-90 mb-4">Ready to join our team? Send us your application today.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="mailto:<?= h(getSetting('contact_email', 'hr@company.com')) ?>?subject=Application for <?= h($job['title']) ?>" class="btn btn-white btn-lg"><i class="fas fa-paper-plane"></i> Apply via Email</a>
                        <a href="mailto:<?= h(getSetting('contact_email', 'hr@company.com')) ?>" class="btn btn-outline-light btn-lg"><i class="fas fa-envelope"></i> Contact HR</a>
                    </div>
                    <p class="text-white opacity-75 small mt-3">Please include the job title "<strong><?= h($job['title']) ?></strong>" in the subject line.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="100">
                <div class="bg-white rounded-4 shadow-sm border-0 sticky-top" style="top: 90px; z-index: 1;">
                    <div class="bg-primary text-white rounded-top-4 p-4">
                        <h5 class="fw-bold mb-0 text-white"><i class="fas fa-info-circle me-2"></i> Job Summary</h5>
                    </div>
                    <div class="p-4">
                        <table class="table table-borderless mb-0">
                            <?php if (!empty($job['department'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase">Department</td><td class="pe-0 py-2 text-end"><?= h($job['department']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($job['location'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Location</td><td class="pe-0 py-2 text-end border-top"><?= h($job['location']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($job['employment_type'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Type</td><td class="pe-0 py-2 text-end border-top"><?= h($job['employment_type']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($job['salary_range'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Salary</td><td class="pe-0 py-2 text-end border-top fw-bold text-primary"><?= h($job['salary_range']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($job['application_deadline'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Deadline</td><td class="pe-0 py-2 text-end border-top"><?= date('M d, Y', strtotime($job['application_deadline'])) ?></td></tr>
                            <?php endif; ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Posted</td><td class="pe-0 py-2 text-end border-top"><?= date('M d, Y', strtotime($job['created_at'])) ?></td></tr>
                        </table>
                    </div>
                    <?php if (!$deadlinePassed): ?>
                    <div class="p-4 pt-0">
                        <a href="mailto:<?= h(getSetting('contact_email', 'hr@company.com')) ?>?subject=Application for <?= h($job['title']) ?>" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane"></i> Apply Now</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
