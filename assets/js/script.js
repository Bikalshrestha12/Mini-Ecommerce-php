/* ================================================================
   Mini-Ecommerce – script.js
   All client-side interactivity
   ================================================================ */

'use strict';

/* ── DOM ready helper ──────────────────────────────────────── */
function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
}

/* ================================================================
   1. LOGIN / SIGNUP FORM SWITCHER
   ================================================================ */
function showForm(which) {
    const loginForm  = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabs       = document.querySelectorAll('.auth-tab');

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

    tabs.forEach((tab, i) => {
        const isActive = (which === 'login' && i === 0) || (which === 'signup' && i === 1);
        tab.classList.toggle('active', isActive);
    });
}

/* ================================================================
   2. PASSWORD VISIBILITY TOGGLE
   ================================================================ */
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('fa-eye',      isText);
        icon.classList.toggle('fa-eye-slash', !isText);
    }
}

/* ================================================================
   3. MOBILE NAVBAR TOGGLE
   ================================================================ */
ready(function () {
    const toggle = document.getElementById('navToggle');
    const links  = document.getElementById('navLinks');
    if (!toggle || !links) return;

    toggle.addEventListener('click', function () {
        links.classList.toggle('nav-open');
        const icon = this.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-bars',  links.classList.contains('nav-open') === false);
            icon.classList.toggle('fa-xmark', links.classList.contains('nav-open'));
        }
    });

    // Close nav when a link is clicked (mobile)
    links.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            links.classList.remove('nav-open');
            const icon = toggle.querySelector('i');
            if (icon) { icon.classList.add('fa-bars'); icon.classList.remove('fa-xmark'); }
        });
    });

    // Close nav on outside click
    document.addEventListener('click', function (e) {
        if (!toggle.contains(e.target) && !links.contains(e.target)) {
            links.classList.remove('nav-open');
            const icon = toggle.querySelector('i');
            if (icon) { icon.classList.add('fa-bars'); icon.classList.remove('fa-xmark'); }
        }
    });
});

/* ================================================================
   4. DELIVERY COUNTDOWN TIMERS
   ================================================================ */
ready(function () {
    const timers = document.querySelectorAll('.countdown-timer');
    if (!timers.length) return;

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick(timerEl) {
        const deliveryTs = parseInt(timerEl.dataset.deliveryTs, 10) * 1000;
        const now        = Date.now();
        let   diff       = deliveryTs - now;

        if (diff <= 0) {
            // Replace with delivered badge
            const wrap = timerEl.closest('.delivery-countdown-wrap');
            if (wrap) {
                wrap.innerHTML = '<div class="delivery-delivered">' +
                    '<i class="fa-solid fa-circle-check"></i> Delivered</div>';
            }
            return; // stop ticking
        }

        const days    = Math.floor(diff / 86400000); diff %= 86400000;
        const hours   = Math.floor(diff / 3600000);  diff %= 3600000;
        const minutes = Math.floor(diff / 60000);    diff %= 60000;
        const seconds = Math.floor(diff / 1000);

        const set = (unit, val) => {
            const el = timerEl.querySelector('[data-unit="' + unit + '"]');
            if (el) el.textContent = pad(val);
        };

        set('days',    days);
        set('hours',   hours);
        set('minutes', minutes);
        set('seconds', seconds);

        setTimeout(() => tick(timerEl), 1000);
    }

    timers.forEach(t => tick(t));
});

/* ================================================================
   5. AUTO-DISMISS ALERTS
   ================================================================ */
ready(function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease';
            alert.style.opacity    = '0';
            alert.style.maxHeight  = '0';
            alert.style.margin     = '0';
            alert.style.overflow   = 'hidden';
            setTimeout(() => alert.remove(), 550);
        }, 5000); // dismiss after 5 s
    });
});

/* ================================================================
   6. PRODUCT SEARCH – Live filter hint (no reload)
   ================================================================ */
ready(function () {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    // Debounce utility
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // Highlight matching cards (pure CSS-class toggle, no DOM removal)
    searchInput.addEventListener('input', debounce(function () {
        const q     = this.value.trim().toLowerCase();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(function (card) {
            if (!q) {
                card.style.display = '';
                return;
            }
            const name = (card.querySelector('.product-name')?.textContent || '').toLowerCase();
            const desc = (card.querySelector('.product-desc')?.textContent || '').toLowerCase();
            const cat  = (card.dataset.category || '').toLowerCase();
            card.style.display = (name.includes(q) || desc.includes(q) || cat.includes(q)) ? '' : 'none';
        });
    }, 250));
});

/* ================================================================
   7. CART QUANTITY – KEYBOARD SAFEGUARD
   ================================================================ */
ready(function () {
    document.querySelectorAll('.qty-input-sm').forEach(function (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    });
});

/* ================================================================
   8. CHECKOUT – Card number formatter (called inline too)
   ================================================================ */
function formatCard(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2);
    input.value = v;
}

/* ================================================================
   9. PLACE ORDER BUTTON – Loading state
   ================================================================ */
ready(function () {
    const form = document.getElementById('checkoutForm');
    const btn  = document.getElementById('placeOrderBtn');
    if (!form || !btn) return;

    form.addEventListener('submit', function () {
        btn.disabled    = true;
        btn.innerHTML   = '<i class="fa-solid fa-spinner fa-spin"></i> Processing…';
    });
});

/* ================================================================
   10. CONFIRM DIALOGS – generic helper
   ================================================================ */
function confirmAction(msg) {
    return confirm(msg || 'Are you sure?');
}

/* ================================================================
   11. SMOOTH SCROLL for anchor links
   ================================================================ */
ready(function () {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});

/* ================================================================
   12. IMAGE PREVIEW FOR FILE INPUTS (manage.php)
   ================================================================ */
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src          = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/* ================================================================
   13. TOOLTIP on admin buttons (title attribute enhancement)
   ================================================================ */
ready(function () {
    // nothing extra needed – native title tooltips work fine
});

/* ================================================================
   14. PAYMENT METHOD FIELD SWITCHER (checkout.php)
   ================================================================ */
function showPaymentFields(method) {
    const cardFields   = document.getElementById('cardFields');
    const esewaFields  = document.getElementById('esewaFields');
    const khaltiFields = document.getElementById('khaltiFields');

    if (cardFields)   cardFields.style.display   = 'none';
    if (esewaFields)  esewaFields.style.display  = 'none';
    if (khaltiFields) khaltiFields.style.display = 'none';

    if (method === 'Card Payment' && cardFields)   cardFields.style.display   = 'block';
    if (method === 'eSewa'        && esewaFields)  esewaFields.style.display  = 'block';
    if (method === 'Khalti'       && khaltiFields) khaltiFields.style.display = 'block';
}

/* Restore on page reload with errors */
ready(function () {
    const checked = document.querySelector('input[name="payment_method"]:checked');
    if (checked) showPaymentFields(checked.value);
});

/* ================================================================
   15. PRODUCT DETAIL – quantity stepper (defined inline too,
       kept here as fallback)
   ================================================================ */
function changeQty(delta) {
    const input = document.getElementById('qty');
    if (!input) return;
    const max = parseInt(input.max, 10) || 9999;
    let val = parseInt(input.value, 10) + delta;
    if (val < 1)   val = 1;
    if (val > max) val = max;
    input.value = val;
}
