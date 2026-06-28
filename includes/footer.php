<?php
// ============================================================
// includes/footer.php – Shared Footer with Bootstrap
// ============================================================
?>
</main><!-- /.main-content -->

<footer class="site-footer bg-light border-top mt-5 py-4">
    <div class="footer-container">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="footer-brand d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-bag-shopping text-primary" style="font-size: 1.5rem;"></i>
                        <span class="fw-bold" style="font-size: 1.1rem;"><?= htmlspecialchars(APP_NAME) ?></span>
                    </div>
                    <p class="text-muted small">Your modern shopping destination for quality products at great prices.</p>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <div class="footer-links d-flex flex-column gap-2">
                        <a href="<?= APP_URL ?>/product/products.php" class="text-decoration-none text-muted small">
                            <i class="fas fa-shirt"></i> Products
                        </a>
                        <a href="<?= APP_URL ?>/orders/history.php" class="text-decoration-none text-muted small">
                            <i class="fas fa-box"></i> Orders
                        </a>
                        <a href="<?= APP_URL ?>/user/profile.php" class="text-decoration-none text-muted small">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <div class="footer-contact d-flex flex-column gap-2 small text-muted">
                        <span><i class="fas fa-envelope"></i> support@mini-ecommerce.local</span>
                        <span><i class="fas fa-phone"></i> +977-9800000000</span>
                        <span><i class="fas fa-map-marker-alt"></i> Kathmandu, Nepal</span>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-decoration-none text-primary"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-decoration-none text-primary"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-decoration-none text-primary"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-decoration-none text-primary"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <p class="footer-copy text-muted small mb-0">
                        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="small text-muted">
                        <a href="#" class="text-decoration-none text-muted">Privacy Policy</a> | 
                        <a href="#" class="text-decoration-none text-muted">Terms & Conditions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/script.js"></script>
</body>
</html>
