<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, pc.name as category_name FROM projects p LEFT JOIN project_categories pc ON p.category_id = pc.category_id WHERE p.project_id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: ' . APP_URL . '/projects.php');
    exit;
}

$images = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order, image_id");
$images->execute([$id]);
$projectImages = $images->fetchAll();

$videos = $pdo->prepare("SELECT * FROM project_videos WHERE project_id = ? ORDER BY sort_order");
$videos->execute([$id]);
$projectVideos = $videos->fetchAll();

$docs = $pdo->prepare("SELECT * FROM project_documents WHERE project_id = ? ORDER BY doc_id");
$docs->execute([$id]);
$projectDocs = $docs->fetchAll();

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><?= h($project['title']) ?></h1>
        <p data-aos="fade-up" data-aos-delay="100"><?= !empty($project['category_name']) ? h($project['category_name']) : 'Project Details' ?></p>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <a href="<?= APP_URL ?>/projects.php" class="btn btn-ghost mb-4 px-0" data-aos="fade-up"><i class="fas fa-arrow-left me-2"></i> Back to Projects</a>

        <div class="row g-5">
            <div class="col-lg-8">
                <?php if (!empty($projectImages)): ?>
                <div id="projectImageCarousel" class="carousel slide mb-4 rounded-4 overflow-hidden shadow-lg" data-bs-ride="carousel" data-aos="fade-up">
                    <div class="carousel-indicators">
                        <?php foreach ($projectImages as $i => $img): ?>
                        <button type="button" data-bs-target="#projectImageCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" style="width: 12px; height: 12px; border-radius: 50%;"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($projectImages as $i => $img): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= imgUrl($img['image']) ?>" class="d-block w-100" alt="<?= h($project['title']) ?>" style="max-height: 500px; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#projectImageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark bg-opacity-50 rounded-circle p-3" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#projectImageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark bg-opacity-50 rounded-circle p-3" aria-hidden="true"></span>
                    </button>
                </div>
                <?php elseif (!empty($projectImages[0]['image'] ?? '')): ?>
                <img src="<?= imgUrl($projectImages[0]['image']) ?>" alt="<?= h($project['title']) ?>" class="img-fluid rounded-4 shadow-lg mb-4" data-aos="fade-up">
                <?php endif; ?>

                <div data-aos="fade-up">
                    <span class="badge badge-primary px-3 py-2 mb-3"><?= h($project['category_name'] ?? 'Project') ?></span>
                    <h2 class="fw-bold mb-4"><?= h($project['title']) ?></h2>
                    <div class="text-muted lh-lg"><?= nl2br(h($project['description'] ?? '')) ?></div>
                </div>

                <?php if (!empty($projectVideos)): ?>
                <div class="mt-5" data-aos="fade-up">
                    <h3 class="fw-bold mb-4"><i class="fas fa-video text-primary me-2"></i> Videos</h3>
                    <div class="row g-4">
                        <?php foreach ($projectVideos as $v): ?>
                        <div class="col-md-6">
                            <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
                                <?php
                                $url = $v['video_url'];
                                if (str_contains($url, 'youtube.com/watch')) {
                                    parse_str(parse_url($url, PHP_URL_QUERY), $params);
                                    $vid = $params['v'] ?? '';
                                    echo '<iframe src="https://www.youtube.com/embed/' . h($vid) . '" allowfullscreen></iframe>';
                                } elseif (str_contains($url, 'youtu.be')) {
                                    $vid = trim(parse_url($url, PHP_URL_PATH), '/');
                                    echo '<iframe src="https://www.youtube.com/embed/' . h($vid) . '" allowfullscreen></iframe>';
                                } elseif (str_contains($url, 'vimeo')) {
                                    $vid = trim(parse_url($url, PHP_URL_PATH), '/');
                                    echo '<iframe src="https://player.vimeo.com/video/' . h($vid) . '" allowfullscreen></iframe>';
                                } else {
                                    echo '<video controls class="w-100"><source src="' . h($url) . '" type="video/mp4"></video>';
                                }
                                ?>
                            </div>
                            <?php if (!empty($v['title'])): ?>
                            <p class="mt-2 fw-medium text-center"><?= h($v['title']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="100">
                <div class="bg-white rounded-4 shadow-sm border-0 sticky-top" style="top: 90px; z-index: 1;">
                    <div class="bg-primary text-white rounded-top-4 p-4">
                        <h5 class="fw-bold mb-0 text-white"><i class="fas fa-info-circle me-2"></i> Project Info</h5>
                    </div>
                    <div class="p-4">
                        <table class="table table-borderless mb-0">
                            <?php if (!empty($project['category_name'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase">Category</td><td class="pe-0 py-2 text-end"><?= h($project['category_name']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($project['client_name'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Client</td><td class="pe-0 py-2 text-end border-top"><?= h($project['client_name']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($project['completion_date'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Completed</td><td class="pe-0 py-2 text-end border-top fw-bold text-primary"><?= date('F Y', strtotime($project['completion_date'])) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($project['project_url'])): ?>
                            <tr><td class="fw-semibold ps-0 py-2 text-muted small text-uppercase border-top">Website</td><td class="pe-0 py-2 text-end border-top"><a href="<?= h($project['project_url']) ?>" target="_blank" rel="noopener" class="fw-semibold">Visit <i class="fas fa-external-link-alt ms-1"></i></a></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if (!empty($projectDocs)): ?>
                <div class="bg-white rounded-4 shadow-sm border-0 mt-4" data-aos="fade-up">
                    <div class="bg-primary text-white rounded-top-4 p-4">
                        <h5 class="fw-bold mb-0 text-white"><i class="fas fa-file-download me-2"></i> Documents</h5>
                    </div>
                    <div class="p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($projectDocs as $d): ?>
                            <a href="<?= imgUrl($d['document']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3 border-0 border-bottom">
                                <i class="fas fa-file-pdf text-danger fs-4"></i>
                                <div>
                                    <strong class="d-block"><?= h($d['title'] ?? 'Document') ?></strong>
                                    <small class="text-muted">Click to download</small>
                                </div>
                                <i class="fas fa-download ms-auto text-muted"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
