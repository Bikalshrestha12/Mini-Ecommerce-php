<?php
$pdo = getDB();

$sliders = getHeroSliders();

$aboutSection   = getCMSSection('home', 'about');
$servicesSection = getCMSSection('home', 'services');
$whyChooseUs    = getCMSSection('home', 'why_choose_us');
$ctaSection     = getCMSSection('home', 'cta');
$contactPreview = getCMSSection('home', 'contact_preview');

$featuredProducts = getFeaturedProducts(8);

$featuredProjects = getActiveProjects(6);

$testimonials = getTestimonials();

$partners = getPartners();

$galleryImages = getGallery(12);

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

$siteAddress = getSetting('contact_address', '');
$sitePhone   = getSetting('contact_phone', '');
$siteEmail   = getSetting('contact_email', '');
?>

<?php if (!empty($sliders)): ?>
<section class="hero-section" id="home">
    <?php $firstSlider = $sliders[0]; ?>
    <div class="hero-overlay" style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,0.85) 0%,rgba(30,27,75,0.7) 100%);z-index:1;"></div>
    <div class="hero-shapes">
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
        <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
            <div class="hero-badge">
                <i class="fas fa-star"></i> Welcome to <?= h($siteName) ?>
            </div>
            <h1 class="hero-title">
                <span class="typing-text" data-texts='<?= htmlspecialchars(json_encode(array_column($sliders, 'title')), ENT_QUOTES, 'UTF-8') ?>'></span>
            </h1>
            <p class="hero-subtitle"><?= h($firstSlider['subtitle'] ?? 'Discover quality products at unbeatable prices. Your satisfaction is our priority.') ?></p>
            <div class="hero-actions">
                <?php if (!empty($firstSlider['button_text']) && !empty($firstSlider['button_url'])): ?>
                <a href="<?= h($firstSlider['button_url']) ?>" class="btn btn-white btn-lg"><?= h($firstSlider['button_text']) ?> <i class="fas fa-arrow-right ms-1"></i></a>
                <?php else: ?>
                <a href="<?= APP_URL ?>/product/products.php" class="btn btn-white btn-lg">Shop Now <i class="fas fa-arrow-right ms-1"></i></a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/about.php" class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.4);">Learn More</a>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="mouse"></div>
        <span>Scroll</span>
    </div>
</section>
<?php endif; ?>

<?php if ($aboutSection): ?>
<section class="section-padding" data-aos="fade-up">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="100">
                <?php if (!empty($aboutSection['image'])): ?>
                <div class="position-relative">
                    <img src="<?= imgUrl($aboutSection['image']) ?>" alt="<?= h($aboutSection['title'] ?? 'About') ?>" class="img-fluid rounded-xl shadow-lg" style="width:100%;">
                    <div style="position:absolute;bottom:-1rem;right:-1rem;width:120px;height:120px;background:var(--primary-500);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;color:#fff;font-size:3rem;box-shadow:var(--shadow-lg);">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <?php if (!empty($aboutSection['subtitle'])): ?>
                <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                    <i class="fas fa-info-circle"></i> <?= h($aboutSection['subtitle']) ?>
                </span>
                <?php endif; ?>
                <h2 class="section-title"><?= h($aboutSection['title'] ?? 'About Us') ?></h2>
                <div class="section-divider"></div>
                <div style="color:var(--muted-color);line-height:1.8;"><?= $aboutSection['content'] ?></div>
                <a href="<?= APP_URL ?>/about.php" class="btn btn-primary mt-4">Read More <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($servicesSection): ?>
<section class="section-padding section-bg-light" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <?php if (!empty($servicesSection['subtitle'])): ?>
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-cogs"></i> <?= h($servicesSection['subtitle']) ?>
            </span>
            <?php endif; ?>
            <h2 class="section-title"><?= h($servicesSection['title'] ?? 'Our Services') ?></h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
            <?php if (!empty($servicesSection['content'])): ?>
            <p class="section-subtitle"><?= strip_tags($servicesSection['content']) ?></p>
            <?php endif; ?>
        </div>
        <div class="services-grid">
            <?php
            $services = json_decode($servicesSection['extra_json'] ?? '[]', true);
            if (empty($services)): ?>
            <div class="col-12 text-center text-muted">No services listed yet.</div>
            <?php else: ?>
            <?php foreach ($services as $i => $svc): ?>
            <div class="service-card" data-aos="fade-up" data-aos-delay="<?= 100 + $i * 100 ?>">
                <div class="service-icon"><i class="<?= h($svc['icon'] ?? 'fas fa-cogs') ?>"></i></div>
                <h3><?= h($svc['title'] ?? 'Service') ?></h3>
                <p><?= h($svc['description'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="section-padding" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-box"></i> Our Collection
            </span>
            <h2 class="section-title">Featured Products</h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
            <p class="section-subtitle">Discover our handpicked selection of featured products</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $i => $p): ?>
            <div class="product-card" data-aos="fade-up" data-aos-delay="<?= 100 + ($i % 4) * 100 ?>">
                <div class="product-img-wrap">
                    <a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>">
                        <img src="<?= imgUrl($p['image'] ?? '') ?>" alt="<?= h($p['name']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                    </a>
                    <?php if ($p['stock'] == 0): ?>
                    <span class="product-badge sale">Out of Stock</span>
                    <?php elseif ($p['stock'] <= 5): ?>
                    <span class="product-badge new">Only <?= $p['stock'] ?> left</span>
                    <?php endif; ?>
                    <?php if ($i === 0): ?>
                    <span class="product-badge featured" style="left:auto;right:var(--spacing-3);">Featured</span>
                    <?php endif; ?>
                    <div class="product-hover-overlay">
                        <a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>" class="btn-icon"><i class="fas fa-eye"></i></a>
                        <?php if (isLoggedIn() && $p['stock'] > 0): ?>
                        <form method="POST" action="<?= APP_URL ?>/cart/add.php" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-icon"><i class="fas fa-cart-plus"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category"><?= h($p['category'] ?? 'General') ?></span>
                    <h3 class="product-name"><a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>"><?= h($p['name']) ?></a></h3>
                    <div class="product-price">
                        <span class="current-price">$<?= number_format($p['price'], 2) ?></span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <form method="POST" action="<?= APP_URL ?>/cart/add.php">
                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-add-cart" <?= $p['stock'] == 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus"></i> <?= $p['stock'] == 0 ? 'Out of Stock' : 'Add to Cart' ?>
                        </button>
                    </form>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/user/auth.php" class="btn-add-cart" style="text-align:center;text-decoration:none;">
                        <i class="fas fa-sign-in-alt"></i> Login to Purchase
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= APP_URL ?>/product/products.php" class="btn btn-primary btn-lg">View All Products <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProjects)): ?>
<section class="section-padding section-bg-light" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-project-diagram"></i> Our Work
            </span>
            <h2 class="section-title">Featured Projects</h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
            <p class="section-subtitle">Take a look at some of our recent projects</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($featuredProjects as $i => $pr): ?>
            <div class="project-card" data-aos="fade-up" data-aos-delay="<?= 100 + ($i % 3) * 100 ?>">
                <div class="project-img-wrap">
                    <?php
                    $prImage = $pdo->prepare("SELECT image FROM project_images WHERE project_id = ? AND is_primary = 1 LIMIT 1");
                    $prImage->execute([$pr['project_id']]);
                    $pri = $prImage->fetch();
                    $img = $pri ? $pri['image'] : '';
                    ?>
                    <img src="<?= imgUrl($img) ?>" alt="<?= h($pr['title']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                </div>
                <div class="project-overlay">
                    <?php if (!empty($pr['category_name'])): ?>
                    <span class="project-category"><?= h($pr['category_name']) ?></span>
                    <?php endif; ?>
                    <h3 class="project-title"><?= h($pr['title']) ?></h3>
                    <p class="project-description"><?= h(mb_substr(strip_tags($pr['description'] ?? ''), 0, 120)) ?>...</p>
                    <a href="<?= APP_URL ?>/project-details.php?id=<?= $pr['project_id'] ?>" class="btn btn-white btn-sm" style="align-self:flex-start;margin-top:0.5rem;">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= APP_URL ?>/projects.php" class="btn btn-primary btn-lg">View All Projects <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="counter-section" data-aos="fade-up">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="counter-item">
                    <i class="fas fa-box"></i>
                    <div class="counter-number"><span class="counter-num" data-target="<?= $totalProducts ?>">0</span></div>
                    <div class="counter-label">Products</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="counter-item">
                    <i class="fas fa-project-diagram"></i>
                    <div class="counter-number"><span class="counter-num" data-target="<?= $totalProjects ?>">0</span></div>
                    <div class="counter-label">Projects</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="counter-item">
                    <i class="fas fa-users"></i>
                    <div class="counter-number"><span class="counter-num" data-target="<?= $totalUsers ?>">0</span></div>
                    <div class="counter-label">Users</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="counter-item">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="counter-number"><span class="counter-num" data-target="<?= $totalOrders ?>">0</span></div>
                    <div class="counter-label">Orders</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($whyChooseUs): ?>
<section class="section-padding" data-aos="fade-up">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                <?php if (!empty($whyChooseUs['subtitle'])): ?>
                <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                    <i class="fas fa-check-circle"></i> <?= h($whyChooseUs['subtitle']) ?>
                </span>
                <?php endif; ?>
                <h2 class="section-title"><?= h($whyChooseUs['title'] ?? 'Why Choose Us') ?></h2>
                <div class="section-divider"></div>
                <?php if (!empty($whyChooseUs['content'])): ?>
                <p style="color:var(--muted-color);line-height:1.8;margin-bottom:1.5rem;"><?= strip_tags($whyChooseUs['content']) ?></p>
                <?php endif; ?>
                <?php
                $whys = json_decode($whyChooseUs['extra_json'] ?? '[]', true);
                if (!empty($whys)):
                    foreach ($whys as $w):
                ?>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:36px;height:36px;border-radius:var(--radius-full);background:var(--success-bg);display:flex;align-items:center;justify-content:center;color:var(--success);flex-shrink:0;">
                        <i class="<?= h($w['icon'] ?? 'fas fa-check') ?>" style="font-size:0.9rem;"></i>
                    </div>
                    <div>
                        <h6 style="font-weight:700;margin-bottom:0.25rem;"><?= h($w['title'] ?? '') ?></h6>
                        <p style="font-size:0.9rem;color:var(--muted-color);margin-bottom:0;"><?= h($w['description'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:36px;height:36px;border-radius:var(--radius-full);background:var(--success-bg);display:flex;align-items:center;justify-content:center;color:var(--success);flex-shrink:0;"><i class="fas fa-medal"></i></div>
                    <div><h6 style="font-weight:700;margin-bottom:0.25rem;">Quality Products</h6><p style="font-size:0.9rem;color:var(--muted-color);margin-bottom:0;">We ensure the highest quality standards for all our products.</p></div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:36px;height:36px;border-radius:var(--radius-full);background:var(--success-bg);display:flex;align-items:center;justify-content:center;color:var(--success);flex-shrink:0;"><i class="fas fa-truck"></i></div>
                    <div><h6 style="font-weight:700;margin-bottom:0.25rem;">Fast Delivery</h6><p style="font-size:0.9rem;color:var(--muted-color);margin-bottom:0;">Quick and reliable delivery right to your doorstep.</p></div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:36px;height:36px;border-radius:var(--radius-full);background:var(--success-bg);display:flex;align-items:center;justify-content:center;color:var(--success);flex-shrink:0;"><i class="fas fa-headset"></i></div>
                    <div><h6 style="font-weight:700;margin-bottom:0.25rem;">24/7 Support</h6><p style="font-size:0.9rem;color:var(--muted-color);margin-bottom:0;">Our support team is always ready to help you.</p></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="200">
                <img src="<?= APP_URL ?>/assets/images/placeholder.jpg" alt="Why Choose Us" class="img-fluid rounded-xl shadow-lg" onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
<section class="section-padding section-bg-light" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-quote-left"></i> Testimonials
            </span>
            <h2 class="section-title">What Our Clients Say</h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
        </div>
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-aos="fade-up" data-aos-delay="100">
            <div class="carousel-inner">
                <?php foreach ($testimonials as $i => $t): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="testimonial-card" style="max-width:700px;margin:0 auto;">
                        <?php if ($t['rating']): ?>
                        <div class="testimonial-stars">
                            <?php for ($r = 0; $r < $t['rating']; $r++): ?>
                            <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($r = $t['rating']; $r < 5; $r++): ?>
                            <i class="far fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                        <p class="testimonial-text">"<?= h($t['content']) ?>"</p>
                        <div class="testimonial-author">
                            <?php if (!empty($t['avatar'])): ?>
                            <img src="<?= imgUrl($t['avatar']) ?>" alt="<?= h($t['client_name']) ?>" class="testimonial-avatar">
                            <?php endif; ?>
                            <div>
                                <div class="testimonial-name"><?= h($t['client_name']) ?></div>
                                <?php if (!empty($t['designation'])): ?>
                                <div class="testimonial-designation"><?= h($t['designation']) ?><?= !empty($t['company']) ? ', ' . h($t['company']) : '' ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-primary rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($partners)): ?>
<section class="section-padding" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <h2 class="section-title">Our Partners & Clients</h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
        </div>
        <div class="partners-grid">
            <?php foreach ($partners as $pt): ?>
            <div class="partner-logo" data-aos="fade-up" data-aos-delay="100">
                <?php if (!empty($pt['website'])): ?>
                <a href="<?= h($pt['website']) ?>" target="_blank" rel="noopener">
                    <img src="<?= imgUrl($pt['logo']) ?>" alt="<?= h($pt['name']) ?>" loading="lazy">
                </a>
                <?php else: ?>
                <img src="<?= imgUrl($pt['logo']) ?>" alt="<?= h($pt['name']) ?>" loading="lazy">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($galleryImages)): ?>
<section class="section-padding section-bg-light" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-images"></i> Gallery
            </span>
            <h2 class="section-title">Our Gallery</h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
            <p class="section-subtitle">A glimpse into our world</p>
        </div>
        <div class="gallery-grid" data-aos="fade-up" data-aos-delay="100">
            <?php foreach ($galleryImages as $g): ?>
            <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img="<?= imgUrl($g['image']) ?>" data-title="<?= h($g['title'] ?? '') ?>">
                <img src="<?= imgUrl($g['image']) ?>" alt="<?= h($g['title'] ?? 'Gallery') ?>" loading="lazy">
                <div class="gallery-overlay"><i class="fas fa-search-plus"></i><span><?= h($g['title'] ?? 'View') ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="galleryModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="galleryModalImg" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($ctaSection): ?>
<section class="cta-section" data-aos="fade-up">
    <div class="cta-overlay"></div>
    <div class="container" style="position:relative;z-index:1;">
        <h2 data-aos="fade-up"><?= h($ctaSection['title'] ?? 'Ready to Get Started?') ?></h2>
        <p data-aos="fade-up" data-aos-delay="100"><?= strip_tags($ctaSection['content'] ?? '') ?></p>
        <a href="<?= APP_URL ?>/contact.php" class="btn btn-white btn-lg" data-aos="fade-up" data-aos-delay="200">Contact Us <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
</section>
<?php endif; ?>

<?php if ($contactPreview): ?>
<section class="section-padding" data-aos="fade-up">
    <div class="container">
        <div class="section-title-center" data-aos="fade-up">
            <?php if (!empty($contactPreview['subtitle'])): ?>
            <span class="section-badge" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 1rem;background:var(--primary-50);color:var(--primary-600);border-radius:var(--radius-full);font-size:0.8rem;font-weight:600;margin-bottom:1rem;">
                <i class="fas fa-envelope"></i> <?= h($contactPreview['subtitle']) ?>
            </span>
            <?php endif; ?>
            <h2 class="section-title"><?= h($contactPreview['title'] ?? 'Get In Touch') ?></h2>
            <div class="section-divider" style="margin:var(--spacing-4) auto var(--spacing-6);"></div>
            <?php if (!empty($contactPreview['content'])): ?>
            <p class="section-subtitle"><?= strip_tags($contactPreview['content']) ?></p>
            <?php endif; ?>
        </div>
        <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <?php if ($siteAddress): ?>
            <div class="col-md-4">
                <div class="dash-card dash-primary text-center p-4" style="height:100%;">
                    <div class="dash-icon" style="margin:0 auto 1rem;"><i class="fas fa-map-marker-alt"></i></div>
                    <h6 style="font-weight:700;">Address</h6>
                    <p style="margin-bottom:0;"><?= h($siteAddress) ?></p>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($sitePhone): ?>
            <div class="col-md-4">
                <div class="dash-card dash-success text-center p-4" style="height:100%;">
                    <div class="dash-icon" style="margin:0 auto 1rem;"><i class="fas fa-phone-alt"></i></div>
                    <h6 style="font-weight:700;">Phone</h6>
                    <p style="margin-bottom:0;"><a href="tel:<?= h($sitePhone) ?>" style="color:var(--body-color);text-decoration:none;"><?= h($sitePhone) ?></a></p>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($siteEmail): ?>
            <div class="col-md-4">
                <div class="dash-card dash-info text-center p-4" style="height:100%;">
                    <div class="dash-icon" style="margin:0 auto 1rem;"><i class="fas fa-envelope"></i></div>
                    <h6 style="font-weight:700;">Email</h6>
                    <p style="margin-bottom:0;"><a href="mailto:<?= h($siteEmail) ?>" style="color:var(--body-color);text-decoration:none;"><?= h($siteEmail) ?></a></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(function(item) {
        item.addEventListener('click', function() {
            const img = this.dataset.img;
            const title = this.dataset.title;
            document.getElementById('galleryModalImg').src = img;
            document.getElementById('galleryModalTitle').textContent = title;
        });
    });

    const counters = document.querySelectorAll('.counter-num');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.target);
                    if (isNaN(target)) return;
                    let current = 0;
                    const increment = Math.ceil(target / 60);
                    const timer = setInterval(function() {
                        current += increment;
                        if (current >= target) {
                            el.textContent = target.toLocaleString();
                            clearInterval(timer);
                        } else {
                            el.textContent = current.toLocaleString();
                        }
                    }, 25);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function(c) { counterObserver.observe(c); });
    }
});
</script>
