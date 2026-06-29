<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
$pdo = getDB();

$siteAddress = getSetting('contact_address', '');
$sitePhone   = getSetting('contact_phone', '');
$siteEmail   = getSetting('contact_email', '');
$businessHours = getSetting('business_hours', '');
$googleMap    = getSetting('google_map', '');
$facebookUrl  = getSetting('facebook_url', '#');
$twitterUrl   = getSetting('twitter_url', '#');
$instagramUrl = getSetting('instagram_url', '#');
$linkedinUrl  = getSetting('linkedin_url', '#');
$youtubeUrl   = getSetting('youtube_url', '');

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Thank you! Your message has been sent successfully. We will get back to you soon.';
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up">Contact Us</h1>
        <p data-aos="fade-up" data-aos-delay="100">We'd love to hear from you. Get in touch with us.</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="mb-4">
                    <span class="badge badge-primary px-3 py-2 mb-3">Get In Touch</span>
                    <h2 class="section-title fw-bold mb-0">Send Us a Message</h2>
                    <div class="section-divider"></div>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
                    <i class="fas fa-check-circle fs-5"></i>
                    <div class="alert-content"><?= h($success) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
                    <i class="fas fa-exclamation-circle fs-5"></i>
                    <div class="alert-content"><?= h($error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="bg-white rounded-4 shadow-sm p-5 border-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Your full name" required>
                                <label for="floatingName"><i class="fas fa-user text-primary me-2"></i>Name <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Your email address" required>
                                <label for="floatingEmail"><i class="fas fa-envelope text-primary me-2"></i>Email <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Your phone number">
                                <label for="floatingPhone"><i class="fas fa-phone text-primary me-2"></i>Phone</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="subject" class="form-control" id="floatingSubject" placeholder="Subject">
                                <label for="floatingSubject"><i class="fas fa-tag text-primary me-2"></i>Subject</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea name="message" class="form-control" id="floatingMessage" placeholder="Write your message here..." style="min-height: 150px" required></textarea>
                                <label for="floatingMessage"><i class="fas fa-comment-dots text-primary me-2"></i>Message <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-5" data-aos="fade-left">
                <div class="mb-4">
                    <span class="badge badge-primary px-3 py-2 mb-3">Contact Info</span>
                    <h2 class="section-title fw-bold mb-0">Get in Touch</h2>
                    <div class="section-divider"></div>
                </div>

                <div class="d-flex flex-column gap-4">
                    <?php if ($siteAddress): ?>
                    <div class="d-flex align-items-start gap-3 bg-white rounded-4 shadow-sm p-4 card-hover border-0">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-map-marker-alt text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Address</h6>
                            <p class="text-muted mb-0"><?= h($siteAddress) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($sitePhone): ?>
                    <div class="d-flex align-items-start gap-3 bg-white rounded-4 shadow-sm p-4 card-hover border-0">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-phone-alt text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Phone</h6>
                            <p class="mb-0"><a href="tel:<?= h($sitePhone) ?>" class="text-decoration-none text-muted"><?= h($sitePhone) ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($siteEmail): ?>
                    <div class="d-flex align-items-start gap-3 bg-white rounded-4 shadow-sm p-4 card-hover border-0">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-envelope text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email</h6>
                            <p class="mb-0"><a href="mailto:<?= h($siteEmail) ?>" class="text-decoration-none text-muted"><?= h($siteEmail) ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($businessHours): ?>
                    <div class="d-flex align-items-start gap-3 bg-white rounded-4 shadow-sm p-4 card-hover border-0">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fas fa-clock text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Business Hours</h6>
                            <p class="text-muted mb-0"><?= h($businessHours) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-5">
                    <h5 class="fw-bold mb-4">Follow Us</h5>
                    <div class="d-flex gap-3">
                        <a href="<?= h($facebookUrl) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white text-decoration-none" style="width: 48px; height: 48px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= h($twitterUrl) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white text-decoration-none" style="width: 48px; height: 48px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''"><i class="fab fa-twitter"></i></a>
                        <a href="<?= h($instagramUrl) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white text-decoration-none" style="width: 48px; height: 48px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''"><i class="fab fa-instagram"></i></a>
                        <a href="<?= h($linkedinUrl) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white text-decoration-none" style="width: 48px; height: 48px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''"><i class="fab fa-linkedin-in"></i></a>
                        <?php if ($youtubeUrl): ?>
                        <a href="<?= h($youtubeUrl) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white text-decoration-none" style="width: 48px; height: 48px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(99,102,241,0.4)'" onmouseout="this.style.transform='';this.style.boxShadow=''"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($googleMap): ?>
        <div class="mt-5" data-aos="fade-up">
            <div class="mb-4">
                <span class="badge badge-primary px-3 py-2 mb-3">Location</span>
                <h2 class="section-title fw-bold mb-0">Find Us on Map</h2>
                <div class="section-divider"></div>
            </div>
            <div class="ratio ratio-21x9 rounded-4 overflow-hidden shadow-lg border-0">
                <?= $googleMap ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
