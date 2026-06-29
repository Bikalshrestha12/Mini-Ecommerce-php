(function () {
    'use strict';

    const doc = document;
    const win = window;

    /* ── DOM ready ─────────────────────────────────────────── */
    doc.addEventListener('DOMContentLoaded', function () {

        initAOS();
        initStickyHeader();
        initProgressBar();
        initBackToTop();
        initTypingAnimation();
        initCounterAnimation();
        initNavbarActiveLink();
        initMobileMenu();
        initCardTilt();
        initImageZoom();
        initSmoothScroll();
        initGalleryLightbox();
        initTestimonialCarousel();
        initContactForm();
        initNewsletterForm();
        initSkeletonLoaders();
        initFaqAccordion();
        initTabs();
        initLazyLoad();
        initProductSearch();
        initCartQuantity();
        initCountdownTimers();
        initAutoDismissAlerts();
        initPaymentFields();
    });

    /* ── AOS ───────────────────────────────────────────────── */
    function initAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1000,
                easing: 'ease-in-out',
                once: true,
                offset: 100,
                disable: 'mobile'
            });
        }
    }

    /* ── Sticky Header ─────────────────────────────────────── */
    function initStickyHeader() {
        const navbar = doc.querySelector('.navbar');
        if (!navbar) return;

        const toggleHeader = function () {
            navbar.classList.toggle('header-scrolled', win.scrollY > 50);
        };

        win.addEventListener('scroll', toggleHeader, { passive: true });
        toggleHeader();
    }

    /* ── Scroll Progress Bar ───────────────────────────────── */
    function initProgressBar() {
        const bar = doc.createElement('div');
        bar.className = 'scroll-progress-bar';
        Object.assign(bar.style, {
            position: 'fixed',
            top: '0',
            left: '0',
            height: '3px',
            background: 'var(--primary, #6366f1)',
            zIndex: '9999',
            width: '0%',
            transition: 'width 0.1s linear'
        });
        doc.body.appendChild(bar);

        win.addEventListener('scroll', function () {
            const h = doc.documentElement;
            const pct = (win.scrollY / (h.scrollHeight - h.clientHeight)) * 100;
            bar.style.width = Math.min(pct, 100) + '%';
        }, { passive: true });
    }

    /* ── Back to Top ───────────────────────────────────────── */
    function initBackToTop() {
        const btn = doc.getElementById('backToTop');
        if (!btn) return;

        win.addEventListener('scroll', function () {
            btn.classList.toggle('visible', win.scrollY > 500);
        }, { passive: true });

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            win.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Typing Animation ──────────────────────────────────── */
    function initTypingAnimation() {
        const el = doc.querySelector('.typing-text');
        if (!el) return;

        const texts = el.dataset.texts ? JSON.parse(el.dataset.texts) : ['Welcome', 'Shop Now', 'Best Deals'];
        let index = 0;
        let charIndex = 0;
        let isDeleting = false;
        let timer;

        function type() {
            const current = texts[index];
            if (isDeleting) {
                el.textContent = current.substring(0, charIndex - 1);
                charIndex--;
            } else {
                el.textContent = current.substring(0, charIndex + 1);
                charIndex++;
            }

            if (!isDeleting && charIndex === current.length) {
                timer = setTimeout(function () { isDeleting = true; type(); }, 2000);
                return;
            }

            if (isDeleting && charIndex === 0) {
                isDeleting = false;
                index = (index + 1) % texts.length;
                timer = setTimeout(type, 500);
                return;
            }

            timer = setTimeout(type, isDeleting ? 50 : 100);
        }

        type();
    }

    /* ── Counter Animation ─────────────────────────────────── */
    function initCounterAnimation() {
        const counters = doc.querySelectorAll('.counter-number');
        if (!counters.length) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const counter = entry.target;
                const target = parseInt(counter.dataset.target, 10) || parseInt(counter.textContent.replace(/,/g, ''), 10) || 0;
                const duration = 2000;
                const startTime = performance.now();

                function update(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = Math.floor(eased * target).toLocaleString();
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        counter.textContent = target.toLocaleString();
                    }
                }

                requestAnimationFrame(update);
                observer.unobserve(counter);
            });
        }, { threshold: 0.3 });

        counters.forEach(function (c) { observer.observe(c); });
    }

    /* ── Navbar Active Link ────────────────────────────────── */
    function initNavbarActiveLink() {
        const currentPath = win.location.pathname;
        doc.querySelectorAll('.navbar-nav .nav-link').forEach(function (link) {
            const href = link.getAttribute('href');
            if (href && href !== '#' && currentPath.indexOf(href.replace(/^.*\/\//, '')) !== -1) {
                link.classList.add('active');
            }
        });
    }

    /* ── Mobile Menu ───────────────────────────────────────── */
    function initMobileMenu() {
        const toggle = doc.querySelector('.navbar-toggler');
        const menu = doc.querySelector('.navbar-collapse');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function () {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
        });

        menu.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                const bsCollapse = bootstrap && bootstrap.Collapse && bootstrap.Collapse.getInstance(menu);
                if (bsCollapse) bsCollapse.hide();
            });
        });
    }

    /* ── Card Tilt Effect ──────────────────────────────────── */
    function initCardTilt() {
        doc.querySelectorAll('.tilt-card').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const cx = rect.width / 2;
                const cy = rect.height / 2;
                const dx = (x - cx) / cx;
                const dy = (y - cy) / cy;
                card.style.transform = 'perspective(600px) rotateX(' + (dy * -8) + 'deg) rotateY(' + (dx * 8) + 'deg) scale3d(1.02,1.02,1.02)';
            });

            card.addEventListener('mouseleave', function () {
                card.style.transform = 'perspective(600px) rotateX(0deg) rotateY(0deg) scale3d(1,1,1)';
                card.style.transition = 'transform 0.4s ease';
                setTimeout(function () { card.style.transition = ''; }, 400);
            });
        });
    }

    /* ── Image Zoom ────────────────────────────────────────── */
    function initImageZoom() {
        doc.querySelectorAll('.img-zoom').forEach(function (img) {
            img.addEventListener('mouseenter', function () {
                img.style.transform = 'scale(1.15)';
                img.style.transition = 'transform 0.3s ease';
            });
            img.addEventListener('mouseleave', function () {
                img.style.transform = 'scale(1)';
            });
        });
    }

    /* ── Smooth Scroll ─────────────────────────────────────── */
    function initSmoothScroll() {
        doc.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(function (a) {
            a.addEventListener('click', function (e) {
                const target = doc.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offset = 80;
                    const top = target.getBoundingClientRect().top + win.scrollY - offset;
                    win.scrollTo({ top: top, behavior: 'smooth' });
                }
            });
        });
    }

    /* ── Gallery Lightbox ──────────────────────────────────── */
    function initGalleryLightbox() {
        const items = doc.querySelectorAll('.gallery-item');
        if (!items.length) return;

        let lightbox = doc.getElementById('galleryLightbox');
        if (!lightbox) {
            lightbox = doc.createElement('div');
            lightbox.id = 'galleryLightbox';
            lightbox.className = 'gallery-lightbox';
            lightbox.innerHTML = '<div class="lightbox-overlay"></div>' +
                '<div class="lightbox-content">' +
                '<button class="lightbox-close" type="button">&times;</button>' +
                '<button class="lightbox-nav lightbox-prev" type="button">&#8249;</button>' +
                '<img src="" alt="Gallery">' +
                '<button class="lightbox-nav lightbox-next" type="button">&#8250;</button>' +
                '<div class="lightbox-caption"></div>' +
                '</div>';
            doc.body.appendChild(lightbox);
        }

        const lightboxImg = lightbox.querySelector('img');
        const lightboxCaption = lightbox.querySelector('.lightbox-caption');
        const lightboxClose = lightbox.querySelector('.lightbox-close');
        const lightboxPrev = lightbox.querySelector('.lightbox-prev');
        const lightboxNext = lightbox.querySelector('.lightbox-next');
        const overlay = lightbox.querySelector('.lightbox-overlay');

        let currentIndex = 0;
        const images = [];

        function openLightbox(index) {
            if (!images.length) return;
            currentIndex = index;
            const item = images[currentIndex];
            lightboxImg.src = item.src;
            lightboxCaption.textContent = item.caption;
            lightbox.classList.add('active');
            doc.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            doc.body.style.overflow = '';
        }

        function prevImage() {
            if (images.length) {
                openLightbox((currentIndex - 1 + images.length) % images.length);
            }
        }

        function nextImage() {
            if (images.length) {
                openLightbox((currentIndex + 1) % images.length);
            }
        }

        items.forEach(function (item, i) {
            const img = item.querySelector('img');
            const src = item.dataset.img || (img ? img.src : '');
            const caption = item.dataset.title || (img ? img.alt : '');
            images.push({ src: src, caption: caption });

            item.addEventListener('click', function (e) {
                const modalTrigger = item.closest('[data-bs-toggle="modal"]');
                if (modalTrigger) return;
                e.preventDefault();
                openLightbox(i);
            });
        });

        if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
        if (lightboxPrev) lightboxPrev.addEventListener('click', prevImage);
        if (lightboxNext) lightboxNext.addEventListener('click', nextImage);
        if (overlay) overlay.addEventListener('click', closeLightbox);

        doc.addEventListener('keydown', function (e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'ArrowRight') nextImage();
        });
    }

    /* ── Testimonial Carousel ──────────────────────────────── */
    function initTestimonialCarousel() {
        const carousel = doc.querySelector('.testimonials-carousel, #testimonialCarousel');
        if (!carousel || typeof bootstrap === 'undefined') return;

        try {
            const bsCarousel = new bootstrap.Carousel(carousel, {
                interval: 5000,
                ride: 'carousel',
                pause: 'hover',
                wrap: true
            });

            carousel.addEventListener('slid.bs.carousel', function () {
                const active = carousel.querySelector('.carousel-item.active');
                if (active) {
                    const h = active.scrollHeight;
                    const inner = carousel.querySelector('.carousel-inner');
                    if (inner) {
                        inner.style.height = h + 'px';
                        inner.style.transition = 'height 0.3s ease';
                    }
                }
            });

            const inner = carousel.querySelector('.carousel-inner');
            if (inner) {
                const active = carousel.querySelector('.carousel-item.active');
                if (active) inner.style.height = active.scrollHeight + 'px';
            }
        } catch (e) { }
    }

    /* ── Contact Form AJAX ─────────────────────────────────── */
    function initContactForm() {
        const form = doc.getElementById('contactForm');
        if (!form || form.getAttribute('data-ajax') !== 'true') return;

        const btn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const action = form.getAttribute('action') || win.location.href;

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            }

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        showToast(data.message || 'Message sent successfully!', 'success');
                        form.reset();
                    } else {
                        showToast(data.message || 'Failed to send message.', 'error');
                    }
                })
                .catch(function () {
                    showToast('A network error occurred. Please try again.', 'error');
                })
                .finally(function () {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Send Message';
                    }
                });
        });
    }

    /* ── Newsletter Form ───────────────────────────────────── */
    function initNewsletterForm() {
        doc.querySelectorAll('form[data-newsletter="true"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const input = form.querySelector('input[type="email"]');
                if (!input) return;
                const email = input.value.trim();
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showToast('Please enter a valid email address.', 'warning');
                    return;
                }
                showToast('Thank you for subscribing!', 'success');
                form.reset();
            });
        });
    }

    /* ── Toast Notifications ───────────────────────────────── */
    function showToast(message, type) {
        type = type || 'info';
        var toast = doc.createElement('div');
        toast.className = 'toast-notification toast-' + type;

        var icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        toast.innerHTML = '<div class="toast-icon"><i class="fas ' + (icons[type] || icons.info) + '"></i></div>' +
            '<div class="toast-message">' + message + '</div>' +
            '<button class="toast-close-btn" type="button">&times;</button>';

        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '-400px',
            padding: '12px 20px',
            borderRadius: '8px',
            fontSize: '14px',
            fontWeight: '500',
            zIndex: '99999',
            color: '#fff',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            maxWidth: '380px',
            display: 'flex',
            alignItems: 'center',
            gap: '10px',
            transition: 'right 0.4s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.4s ease',
            opacity: '0',
            cursor: 'default'
        });

        var colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
        toast.style.background = colors[type] || colors.info;

        doc.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.style.right = '20px';
            toast.style.opacity = '1';
        });

        var dismissTimer = setTimeout(function () {
            dismissToast(toast);
        }, 4000);

        toast.addEventListener('mouseenter', function () { clearTimeout(dismissTimer); });
        toast.addEventListener('mouseleave', function () {
            dismissTimer = setTimeout(function () { dismissToast(toast); }, 2000);
        });

        toast.querySelector('.toast-close-btn').addEventListener('click', function () {
            clearTimeout(dismissTimer);
            dismissToast(toast);
        });
    }

    function dismissToast(toast) {
        toast.style.right = '-400px';
        toast.style.opacity = '0';
        setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 400);
    }

    win.showToast = showToast;

    /* ── Skeleton Loaders ──────────────────────────────────── */
    function initSkeletonLoaders() {
        doc.querySelectorAll('.skeleton-loader').forEach(function (skeleton) {
            skeleton.classList.add('skeleton-active');
        });

        win.addEventListener('load', function () {
            setTimeout(function () {
                doc.querySelectorAll('.skeleton-loader').forEach(function (skeleton) {
                    skeleton.classList.remove('skeleton-active');
                    skeleton.classList.add('skeleton-loaded');
                });
            }, 800);
        });
    }

    /* ── FAQ Accordion ─────────────────────────────────────── */
    function initFaqAccordion() {
        doc.querySelectorAll('.faq-item').forEach(function (item) {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const arrow = item.querySelector('.faq-arrow, .accordion-arrow');
            if (!question || !answer) return;

            answer.style.maxHeight = '0';
            answer.style.overflow = 'hidden';
            answer.style.transition = 'max-height 0.35s ease, padding 0.35s ease, opacity 0.35s ease';
            answer.style.opacity = '0';

            question.addEventListener('click', function () {
                const isOpen = item.classList.contains('faq-open');

                doc.querySelectorAll('.faq-item.faq-open').forEach(function (openItem) {
                    if (openItem !== item) {
                        openItem.classList.remove('faq-open');
                        const openAnswer = openItem.querySelector('.faq-answer');
                        const openArrow = openItem.querySelector('.faq-arrow, .accordion-arrow');
                        if (openAnswer) {
                            openAnswer.style.maxHeight = '0';
                            openAnswer.style.opacity = '0';
                        }
                        if (openArrow) openArrow.style.transform = 'rotate(0deg)';
                    }
                });

                item.classList.toggle('faq-open');
                if (isOpen) {
                    answer.style.maxHeight = '0';
                    answer.style.opacity = '0';
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                } else {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    answer.style.opacity = '1';
                    if (arrow) arrow.style.transform = 'rotate(180deg)';
                }
            });
        });
    }

    /* ── Active Tabs ───────────────────────────────────────── */
    function initTabs() {
        doc.querySelectorAll('.tab-trigger').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.dataset.tabTarget || this.getAttribute('href');
                if (!targetId) return;
                const target = doc.querySelector(targetId);
                if (!target) return;

                const parent = this.closest('.tab-container, [class*="tab-"]');
                if (parent) {
                    parent.querySelectorAll('.tab-trigger').forEach(function (t) { t.classList.remove('active'); });
                    parent.querySelectorAll('.tab-pane').forEach(function (p) {
                        p.classList.remove('active', 'show');
                        p.style.opacity = '0';
                    });
                }

                this.classList.add('active');
                target.classList.add('active', 'show');
                target.style.opacity = '1';
                target.style.transition = 'opacity 0.3s ease';
            });
        });

        doc.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (trigger) {
            trigger.addEventListener('shown.bs.tab', function (e) {
                const target = doc.querySelector(e.target.getAttribute('data-bs-target') || e.target.getAttribute('href'));
                if (target) {
                    target.style.opacity = '1';
                    target.style.transition = 'opacity 0.3s ease';
                }
            });
        });
    }

    /* ── Lazy Load Images ──────────────────────────────────── */
    function initLazyLoad() {
        const images = doc.querySelectorAll('img[data-src]');
        if (!images.length) return;

        if ('loading' in HTMLImageElement.prototype) {
            images.forEach(function (img) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                    img.removeAttribute('data-srcset');
                }
            });
        } else {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        img.removeAttribute('data-srcset');
                    }
                    img.classList.add('lazy-loaded');
                    observer.unobserve(img);
                });
            }, { rootMargin: '200px' });

            images.forEach(function (img) { observer.observe(img); });
        }
    }

    /* ── Product Search (from existing) ────────────────────── */
    function initProductSearch() {
        const searchInput = doc.getElementById('searchInput');
        if (!searchInput) return;

        var debounceTimer;

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                var q = searchInput.value.trim().toLowerCase();
                doc.querySelectorAll('.product-card').forEach(function (card) {
                    if (!q) {
                        card.style.display = '';
                        return;
                    }
                    var name = (card.querySelector('.product-name') || {}).textContent || '';
                    var desc = (card.querySelector('.product-desc') || {}).textContent || '';
                    var cat = card.dataset.category || '';
                    card.style.display = (name.toLowerCase().includes(q) || desc.toLowerCase().includes(q) || cat.toLowerCase().includes(q)) ? '' : 'none';
                });
            }, 250);
        });
    }

    /* ── Cart Quantity ──────────────────────────────────────── */
    function initCartQuantity() {
        doc.querySelectorAll('.qty-input-sm').forEach(function (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        });
    }

    /* ── Countdown Timers ──────────────────────────────────── */
    function initCountdownTimers() {
        doc.querySelectorAll('.countdown-timer').forEach(function (timer) {
            tickTimer(timer);
        });
    }

    function tickTimer(el) {
        var ts = parseInt(el.dataset.deliveryTs, 10) * 1000;
        var diff = ts - Date.now();

        if (diff <= 0) {
            var wrap = el.closest('.delivery-countdown-wrap');
            if (wrap) {
                wrap.innerHTML = '<div class="delivery-delivered"><i class="fa-solid fa-circle-check"></i> Delivered</div>';
            }
            return;
        }

        var d = Math.floor(diff / 86400000); diff %= 86400000;
        var h = Math.floor(diff / 3600000); diff %= 3600000;
        var m = Math.floor(diff / 60000); diff %= 60000;
        var s = Math.floor(diff / 1000);

        function setVal(unit, val) {
            var u = el.querySelector('[data-unit="' + unit + '"]');
            if (u) u.textContent = String(val).padStart(2, '0');
        }

        setVal('days', d);
        setVal('hours', h);
        setVal('minutes', m);
        setVal('seconds', s);

        setTimeout(function () { tickTimer(el); }, 1000);
    }

    /* ── Auto Dismiss Alerts ───────────────────────────────── */
    function initAutoDismissAlerts() {
        doc.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease';
                alert.style.opacity = '0';
                alert.style.maxHeight = '0';
                alert.style.margin = '0';
                alert.style.overflow = 'hidden';
                setTimeout(function () { if (alert.parentNode) alert.parentNode.removeChild(alert); }, 550);
            }, 5000);
        });
    }

    /* ── Payment Fields ────────────────────────────────────── */
    function initPaymentFields() {
        var checked = doc.querySelector('input[name="payment_method"]:checked');
        if (checked) showPaymentFields(checked.value);

        doc.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (this.checked) showPaymentFields(this.value);
            });
        });
    }

    function showPaymentFields(method) {
        ['cardFields', 'esewaFields', 'khaltiFields'].forEach(function (id) {
            var el = doc.getElementById(id);
            if (el) el.style.display = 'none';
        });
        var map = { 'Card Payment': 'cardFields', 'eSewa': 'esewaFields', 'Khalti': 'khaltiFields' };
        var target = doc.getElementById(map[method]);
        if (target) target.style.display = 'block';
    }

    win.showPaymentFields = showPaymentFields;

    /* ── Utility: formatCard, formatExpiry ─────────────────── */
    win.formatCard = function (input) {
        var v = input.value.replace(/\D/g, '').substring(0, 16);
        input.value = v.replace(/(.{4})/g, '$1 ').trim();
    };

    win.formatExpiry = function (input) {
        var v = input.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2);
        input.value = v;
    };

    win.confirmAction = function (msg) {
        return confirm(msg || 'Are you sure?');
    };

    win.previewImage = function (input) {
        var preview = doc.getElementById('imagePreview');
        if (!preview || !input.files || !input.files[0]) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    };

    win.changeQty = function (delta) {
        var input = doc.getElementById('qty');
        if (!input) return;
        var max = parseInt(input.max, 10) || 9999;
        var val = parseInt(input.value, 10) + delta;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
    };

    win.showForm = function (which) {
        var loginForm = doc.getElementById('loginForm');
        var signupForm = doc.getElementById('signupForm');
        var tabs = doc.querySelectorAll('.auth-tab');
        if (!loginForm || !signupForm) return;

        if (which === 'login') {
            loginForm.classList.add('form-active');
            loginForm.classList.remove('form-hidden');
            signupForm.classList.add('form-hidden');
            signupForm.classList.remove('form-active');
        } else {
            signupForm.classList.add('form-active');
            signupForm.classList.remove('form-hidden');
            loginForm.classList.add('form-hidden');
            loginForm.classList.remove('form-active');
        }

        tabs.forEach(function (tab, i) {
            var isActive = (which === 'login' && i === 0) || (which === 'signup' && i === 1);
            tab.classList.toggle('active', isActive);
        });
    };

    win.togglePw = function (inputId, btn) {
        var input = doc.getElementById(inputId);
        if (!input) return;
        var isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        var icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye', isText);
            icon.classList.toggle('fa-eye-slash', !isText);
        }
    };

})();
