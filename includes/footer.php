<?php
$siteName    = getSetting('site_name', APP_NAME);
$siteEmail   = getSetting('contact_email', 'support@company.com');
$sitePhone   = getSetting('contact_phone', '+977-9800000000');
$siteAddress = getSetting('contact_address', 'Kathmandu, Nepal');
$footerDesc  = getSetting('footer_description', 'Your modern shopping destination for quality products at great prices.');
$facebookUrl  = getSetting('facebook_url', '#');
$twitterUrl   = getSetting('twitter_url', '#');
$instagramUrl = getSetting('instagram_url', '#');
$linkedinUrl  = getSetting('linkedin_url', '#');
$youtubeUrl   = getSetting('youtube_url', '');
?>
</main>

<footer class="site-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand" data-aos="fade-up">
                    <h4><i class="fas fa-store" style="color:var(--primary-500);margin-right:0.5rem;"></i><?= h($siteName) ?></h4>
                    <p><?= h($footerDesc) ?></p>
                    <div class="footer-social">
                        <a href="<?= h($facebookUrl) ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= h($twitterUrl) ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="<?= h($instagramUrl) ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="<?= h($linkedinUrl) ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <?php if ($youtubeUrl): ?>
                        <a href="<?= h($youtubeUrl) ?>" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="100">
                    <h5 class="footer-heading">Quick Links</h5>
                    <div class="footer-links">
                        <a href="<?= APP_URL ?>/index.php"><i class="fas fa-chevron-right"></i> Home</a>
                        <a href="<?= APP_URL ?>/about.php"><i class="fas fa-chevron-right"></i> About</a>
                        <a href="<?= APP_URL ?>/product/products.php"><i class="fas fa-chevron-right"></i> Products</a>
                        <a href="<?= APP_URL ?>/projects.php"><i class="fas fa-chevron-right"></i> Projects</a>
                        <a href="<?= APP_URL ?>/careers.php"><i class="fas fa-chevron-right"></i> Careers</a>
                        <a href="<?= APP_URL ?>/contact.php"><i class="fas fa-chevron-right"></i> Contact</a>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="200">
                    <h5 class="footer-heading">Services</h5>
                    <div class="footer-links">
                        <a href="<?= APP_URL ?>/product/products.php"><i class="fas fa-chevron-right"></i> Online Store</a>
                        <a href="<?= APP_URL ?>/projects.php"><i class="fas fa-chevron-right"></i> Our Projects</a>
                        <a href="<?= APP_URL ?>/about.php"><i class="fas fa-chevron-right"></i> About Us</a>
                        <a href="<?= APP_URL ?>/contact.php"><i class="fas fa-chevron-right"></i> Support</a>
                        <a href="#"><i class="fas fa-chevron-right"></i> FAQ</a>
                        <a href="#"><i class="fas fa-chevron-right"></i> Privacy Policy</a>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="300">
                    <h5 class="footer-heading">Contact Info</h5>
                    <div class="footer-links">
                        <?php if ($siteAddress): ?>
                        <a href="https://maps.google.com/?q=<?= urlencode($siteAddress) ?>" target="_blank"><i class="fas fa-map-marker-alt"></i> <?= h($siteAddress) ?></a>
                        <?php endif; ?>
                        <?php if ($sitePhone): ?>
                        <a href="tel:<?= h($sitePhone) ?>"><i class="fas fa-phone-alt"></i> <?= h($sitePhone) ?></a>
                        <?php endif; ?>
                        <?php if ($siteEmail): ?>
                        <a href="mailto:<?= h($siteEmail) ?>"><i class="fas fa-envelope"></i> <?= h($siteEmail) ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="footer-newsletter mt-4">
                        <p>Subscribe to our newsletter</p>
                        <form class="newsletter-form" method="POST" action="" data-newsletter>
                            <input type="email" name="email" placeholder="Your email" required>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <p>&copy; <?= date('Y') ?> <?= h($siteName) ?>. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms & Conditions</a>
                    <a href="<?= APP_URL ?>/contact.php">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.1.0/typed.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        window.addEventListener('load', function() {
            setTimeout(function() {
                loader.classList.add('loaded');
            }, 300);
        });
        setTimeout(function() {
            loader.classList.add('loaded');
        }, 2000);
    }

    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const progressBar = document.getElementById('scrollProgressBar');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = scrollPercent + '%';
        });
    }

    const hamburger = document.querySelector('.hamburger-animated');
    const navbarCollapse = document.getElementById('publicNavbar');
    
    if (hamburger && navbarCollapse) {
        // Handle hamburger click
        hamburger.addEventListener('click', function(e) {
            this.classList.toggle('active');
        });
        
        // Bootstrap collapse events
        navbarCollapse.addEventListener('shown.bs.collapse', function() {
            hamburger.classList.add('active');
            navbarCollapse.classList.add('show');
            hamburger.setAttribute('aria-expanded', 'true');
        });
        
        navbarCollapse.addEventListener('hidden.bs.collapse', function() {
            hamburger.classList.remove('active');
            navbarCollapse.classList.remove('show');
            hamburger.setAttribute('aria-expanded', 'false');
        });
        
        // Close menu when clicking on a link (mobile only)
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    hamburger.classList.remove('active');
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        });
        
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                hamburger.classList.remove('active');
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) bsCollapse.hide();
            }
        });
    }

    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    }
});
</script>
</body>
</html>
