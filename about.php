<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$sections = getCMSSections('about');
$teamMembers = getTeamMembers();
$gallery = getGallery();

$pageTitle = 'About Us';
foreach ($sections as $s) {
    if ($s['section_key'] === 'intro') {
        $pageTitle = $s['title'] ?? 'About Us';
        break;
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><?= h($pageTitle) ?></h1>
        <p data-aos="fade-up" data-aos-delay="100">Learn more about our company and what drives us</p>
    </div>
</div>

<?php
$intro = $mission = $vision = $coreValues = $history = $timeline = null;
foreach ($sections as $s) {
    switch ($s['section_key']) {
        case 'intro': $intro = $s; break;
        case 'mission': $mission = $s; break;
        case 'vision': $vision = $s; break;
        case 'core_values': $coreValues = $s; break;
        case 'history': $history = $s; break;
        case 'timeline': $timeline = $s; break;
    }
}
?>

<?php if ($intro): ?>
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <?php if (!empty($intro['image'])): ?>
            <div class="col-lg-6" data-aos="fade-right">
                <div class="position-relative">
                    <img src="<?= imgUrl($intro['image']) ?>" alt="<?= h($intro['title'] ?? 'About') ?>" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 end-0 translate-middle-y me-4 d-none d-lg-block">
                        <div class="bg-primary text-white rounded-4 p-4 shadow-colored">
                            <div class="display-6 fw-bold"><?= count($teamMembers) ?>+</div>
                            <div class="small opacity-75">Team Members</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
            <?php else: ?>
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
            <?php endif; ?>
                <?php if (!empty($intro['subtitle'])): ?>
                <span class="badge badge-primary px-3 py-2 mb-3"><?= h($intro['subtitle']) ?></span>
                <?php endif; ?>
                <h2 class="section-title fw-bold mb-4"><?= h($intro['title'] ?? '') ?></h2>
                <div class="section-divider"></div>
                <div class="lead text-muted lh-lg"><?= nl2br(h($intro['content'] ?? '')) ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($mission || $vision): ?>
<section class="section-padding section-bg-light">
    <div class="container">
        <div class="row g-4">
            <?php if ($mission): ?>
            <div class="col-md-6" data-aos="fade-up">
                <div class="card-hover border-0 shadow-sm rounded-4 p-5 h-100 text-center bg-white">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary px-4 py-3 mb-4">
                        <i class="fas fa-bullseye fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-3"><?= h($mission['title'] ?? 'Our Mission') ?></h4>
                    <p class="text-muted mb-0 lh-lg"><?= nl2br(h($mission['content'] ?? '')) ?></p>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($vision): ?>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card-hover border-0 shadow-sm rounded-4 p-5 h-100 text-center bg-white">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary px-4 py-3 mb-4">
                        <i class="fas fa-eye fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-3"><?= h($vision['title'] ?? 'Our Vision') ?></h4>
                    <p class="text-muted mb-0 lh-lg"><?= nl2br(h($vision['content'] ?? '')) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($coreValues): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-title-center text-center mb-5" data-aos="fade-up">
            <?php if (!empty($coreValues['subtitle'])): ?>
            <span class="badge badge-primary px-3 py-2 mb-3"><?= h($coreValues['subtitle']) ?></span>
            <?php endif; ?>
            <h2 class="section-title fw-bold"><?= h($coreValues['title'] ?? 'Our Core Values') ?></h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <?php
            $values = json_decode($coreValues['extra_json'] ?? '[]', true);
            if (!empty($values)):
                foreach ($values as $i => $v):
            ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?= h($v['icon'] ?? 'fas fa-star') ?>"></i>
                    </div>
                    <h3><?= h($v['title'] ?? '') ?></h3>
                    <p><?= h($v['description'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12 text-center text-muted" data-aos="fade-up"><?= nl2br(h($coreValues['content'] ?? '')) ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($history): ?>
<section class="section-padding section-bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <?php if (!empty($history['image'])): ?>
            <div class="col-lg-6" data-aos="fade-right">
                <img src="<?= imgUrl($history['image']) ?>" alt="<?= h($history['title'] ?? 'History') ?>" class="img-fluid rounded-4 shadow-lg">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
            <?php else: ?>
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
            <?php endif; ?>
                <?php if (!empty($history['subtitle'])): ?>
                <span class="badge badge-primary px-3 py-2 mb-3"><?= h($history['subtitle']) ?></span>
                <?php endif; ?>
                <h2 class="section-title fw-bold mb-4"><?= h($history['title'] ?? 'Our History') ?></h2>
                <div class="section-divider"></div>
                <div class="text-muted lh-lg"><?= nl2br(h($history['content'] ?? '')) ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($timeline): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-title-center text-center mb-5" data-aos="fade-up">
            <?php if (!empty($timeline['subtitle'])): ?>
            <span class="badge badge-primary px-3 py-2 mb-3"><?= h($timeline['subtitle']) ?></span>
            <?php endif; ?>
            <h2 class="section-title fw-bold"><?= h($timeline['title'] ?? 'Our Timeline') ?></h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="timeline">
            <?php
            $timelineItems = json_decode($timeline['extra_json'] ?? '[]', true);
            if (!empty($timelineItems)):
                foreach ($timelineItems as $ti):
            ?>
            <div class="timeline-item" data-aos="fade-up">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="timeline-date"><?= h($ti['year'] ?? '') ?></span>
                    <h5 class="timeline-title"><?= h($ti['title'] ?? '') ?></h5>
                    <p class="timeline-text"><?= h($ti['event'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="text-center text-muted"><?= nl2br(h($timeline['content'] ?? '')) ?></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($teamMembers)): ?>
<section class="section-padding section-bg-light">
    <div class="container">
        <div class="section-title-center text-center mb-5" data-aos="fade-up">
            <span class="badge badge-primary px-3 py-2 mb-3">Our Team</span>
            <h2 class="section-title fw-bold">Meet Our Team</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($teamMembers as $i => $m): ?>
            <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="team-card">
                    <div class="team-img-wrap">
                        <img src="<?= imgUrl($m['image'] ?? '') ?>" alt="<?= h($m['name']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                        <div class="team-social-overlay">
                            <?php if (!empty($m['email'])): ?>
                            <a href="mailto:<?= h($m['email']) ?>" title="Email"><i class="fas fa-envelope"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($m['facebook_url'])): ?>
                            <a href="<?= h($m['facebook_url']) ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($m['twitter_url'])): ?>
                            <a href="<?= h($m['twitter_url']) ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($m['linkedin_url'])): ?>
                            <a href="<?= h($m['linkedin_url']) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="team-info">
                        <div class="team-name"><?= h($m['name']) ?></div>
                        <div class="team-designation"><?= h($m['designation'] ?? 'Staff') ?></div>
                        <?php if (!empty($m['bio'])): ?>
                        <p class="team-bio"><?= h(mb_substr($m['bio'], 0, 100)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($gallery)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-title-center text-center mb-5" data-aos="fade-up">
            <span class="badge badge-primary px-3 py-2 mb-3">Gallery</span>
            <h2 class="section-title fw-bold">Our Gallery</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        <div class="gallery-grid">
            <?php foreach ($gallery as $i => $g): ?>
            <div class="gallery-item" data-aos="zoom-in" data-aos-delay="<?= ($i % 4) * 100 ?>" data-bs-toggle="modal" data-bs-target="#aboutGalleryModal" data-img="<?= imgUrl($g['image']) ?>" data-title="<?= h($g['title'] ?? '') ?>">
                <img src="<?= imgUrl($g['image']) ?>" alt="<?= h($g['title'] ?? 'Gallery') ?>" loading="lazy">
                <div class="gallery-overlay">
                    <i class="fas fa-search-plus"></i>
                    <?php if (!empty($g['title'])): ?>
                    <span><?= h($g['title']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="aboutGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="aboutGalleryModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="aboutGalleryModalImg" class="img-fluid rounded-bottom" alt="">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.gallery-item').forEach(function(item) {
        item.addEventListener('click', function() {
            document.getElementById('aboutGalleryModalImg').src = this.dataset.img;
            document.getElementById('aboutGalleryModalTitle').textContent = this.dataset.title;
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
