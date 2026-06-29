(function () {
    'use strict';

    var doc = document;
    var win = window;

    function ready(fn) {
        if (doc.readyState !== 'loading') fn();
        else doc.addEventListener('DOMContentLoaded', fn);
    }

    /* ── Run on DOM ready ──────────────────────────────────── */
    ready(function () {

        initAutoHideAlerts();
        initDataTableBinding();
        initSidebar();
        initActiveNavHighlight();
        initImagePreviews();

        initChartWidgets();
        initTableRowEffects();
        initNotificationSound();
        initAutoRefreshStats();
        initFormValidationBinding();
        initTooltips();
        initSortableTables();
        initBulkSelect();
        initSearchHighlight();
    });

    /* ── 1. Auto-hide alerts ───────────────────────────────── */
    function initAutoHideAlerts() {
        doc.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease';
                alert.style.opacity = '0';
                alert.style.maxHeight = '0';
                alert.style.margin = '0';
                alert.style.padding = '0';
                alert.style.overflow = 'hidden';
                setTimeout(function () { if (alert.parentNode) alert.parentNode.removeChild(alert); }, 550);
            }, 5000);
        });
    }

    /* ── 2. DataTable binding ──────────────────────────────── */
    function initDataTableBinding() {
        doc.querySelectorAll('.datatable').forEach(function (table) {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;
            try {
                $(table).DataTable({
                    pageLength: 10,
                    language: {
                        search: '<i class="fas fa-search"></i> Search:',
                        searchPlaceholder: 'Search...'
                    },
                    dom: '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rtip',
                    responsive: true,
                    autoWidth: false,
                    order: []
                });
            } catch (e) {
                console.warn('DataTable init failed for', table, e);
            }
        });
    }

    /* ── 3. Sidebar toggle ────────────────────────────────── */
    function initSidebar() {
        var toggleBtn = doc.querySelector('.sidebar-toggle');
        var overlay = doc.querySelector('.sidebar-overlay');
        var sidebar = doc.querySelector('.admin-sidebar');

        function toggleSidebar() {
            if (!sidebar) return;
            sidebar.classList.toggle('sidebar-open');
            if (overlay) overlay.classList.toggle('active');
        }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);

        doc.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (win.innerWidth <= 991.98) {
                    if (sidebar) sidebar.classList.remove('sidebar-open');
                    if (overlay) overlay.classList.remove('active');
                }
            });
        });

        win.toggleSidebar = toggleSidebar;
    }

    /* ── 4. Active nav highlight ───────────────────────────── */
    function initActiveNavHighlight() {
        var currentPath = win.location.pathname;
        doc.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
            var href = link.getAttribute('href');
            if (href && href !== '#' && currentPath.indexOf(href) !== -1) {
                link.classList.add('active');
            }
        });
    }

    /* ── 5. Image previews ─────────────────────────────────── */
    function initImagePreviews() {
        doc.querySelectorAll('[data-preview-target]').forEach(function (input) {
            input.addEventListener('change', function () {
                var targetId = this.getAttribute('data-preview-target');
                previewImage(this, targetId);
            });
        });
    }

    function previewImage(input, previewId) {
        var preview = doc.getElementById(previewId || 'imagePreview');
        if (!preview || !input.files || !input.files[0]) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }

    win.previewImage = previewImage;

    /* ── 6. Chart.js Dashboard Widgets ─────────────────────── */
    function initChartWidgets() {
        if (typeof Chart === 'undefined') return;

        var revenueChart = doc.getElementById('revenueChart');
        if (revenueChart) {
            try {
                new Chart(revenueChart, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Revenue',
                            data: revenueChart.dataset.values ? JSON.parse(revenueChart.dataset.values) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.1)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#fff',
                                bodyColor: '#e2e8f0',
                                cornerRadius: 8,
                                padding: 12
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 11 } }
                            },
                            y: {
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 11 },
                                    callback: function (val) { return '$' + val; }
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.warn('Revenue chart init failed', e);
            }
        }

        var ordersChart = doc.getElementById('ordersChart');
        if (ordersChart) {
            try {
                new Chart(ordersChart, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Orders',
                            data: ordersChart.dataset.values ? JSON.parse(ordersChart.dataset.values) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                            backgroundColor: 'rgba(99,102,241,0.7)',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            borderRadius: 4,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                cornerRadius: 8,
                                padding: 12
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 11 } }
                            },
                            y: {
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { color: '#94a3b8', font: { size: 11 } }
                            }
                        }
                    }
                });
            } catch (e) {
                console.warn('Orders chart init failed', e);
            }
        }

        var canvasList = doc.querySelectorAll('canvas.chart-doughnut, canvas.chart-pie');
        canvasList.forEach(function (canvas) {
            try {
                var isDoughnut = canvas.classList.contains('chart-doughnut');
                var labels = canvas.dataset.labels ? JSON.parse(canvas.dataset.labels) : ['A', 'B', 'C'];
                var values = canvas.dataset.values ? JSON.parse(canvas.dataset.values) : [1, 1, 1];
                var colors = canvas.dataset.colors ? JSON.parse(canvas.dataset.colors) : ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'];

                new Chart(canvas, {
                    type: isDoughnut ? 'doughnut' : 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#64748b', padding: 16, font: { size: 12 } }
                            }
                        },
                        cutout: isDoughnut ? '60%' : '0%'
                    }
                });
            } catch (e) {
                console.warn('Doughnut/pie chart init failed', e);
            }
        });

        initStatSparklines();
    }

    /* ── 6a. Mini sparkline charts on stat cards ──────────── */
    function initStatSparklines() {
        doc.querySelectorAll('.sparkline-canvas').forEach(function (canvas) {
            if (typeof Chart === 'undefined') return;
            try {
                var values = canvas.dataset.values ? JSON.parse(canvas.dataset.values) : [1, 2, 3, 4, 5];
                var color = canvas.dataset.color || '#6366f1';

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: values.map(function (_, i) { return '' + i; }),
                        datasets: [{
                            data: values,
                            borderColor: color,
                            backgroundColor: 'rgba(255,255,255,0)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        },
                        elements: { point: { radius: 0 } }
                    }
                });
            } catch (e) { }
        });
    }

    /* ── 7. Table row hover effects ────────────────────────── */
    function initTableRowEffects() {
        doc.querySelectorAll('.table-hover tbody tr').forEach(function (row) {
            row.addEventListener('mouseenter', function () {
                this.style.transition = 'background-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease';
                this.style.transform = 'translateX(4px)';
                this.style.boxShadow = '0 2px 8px rgba(99,102,241,0.1)';
                this.style.position = 'relative';
                this.style.zIndex = '1';
            });
            row.addEventListener('mouseleave', function () {
                this.style.transform = 'translateX(0)';
                this.style.boxShadow = 'none';
            });
        });
    }

    /* ── 8. Notification sound (Web Audio API) ────────────── */
    function initNotificationSound() {
        var soundEnabled = localStorage.getItem('admin_sound_enabled') !== 'false';
        if (!soundEnabled) return;

        var audioCtx = null;
        var hasNotified = false;

        win.alertSound = function () {
            try {
                if (!audioCtx) {
                    audioCtx = new (win.AudioContext || win.webkitAudioContext)();
                }
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.frequency.setValueAtTime(1000, audioCtx.currentTime + 0.1);

                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);

                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.3);
            } catch (e) {
            }
        };

        var soundToggle = doc.getElementById('soundToggle');
        if (soundToggle) {
            soundToggle.addEventListener('change', function () {
                localStorage.setItem('admin_sound_enabled', String(this.checked));
            });
        }

        var notifyEl = doc.querySelector('[data-notify="true"]');
        if (notifyEl && !hasNotified) {
            hasNotified = true;
            setTimeout(function () { if (win.alertSound) win.alertSound(); }, 1000);
        }
    }

    /* ── 9. Auto-refresh dashboard stats ──────────────────── */
    function initAutoRefreshStats() {
        var refreshInterval = parseInt(doc.body.dataset.refreshInterval, 10) || 0;
        if (!refreshInterval) return;

        var container = doc.querySelector('.dashboard-stats');
        if (!container) return;

        setInterval(function () {
            var url = win.location.href.split('?')[0] + '?ajax=stats&_=' + Date.now();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && typeof data === 'object') {
                        Object.keys(data).forEach(function (key) {
                            var el = doc.querySelector('[data-stat="' + key + '"]');
                            if (el) {
                                var old = el.textContent;
                                el.textContent = data[key];
                                el.classList.add('stat-updated');
                                setTimeout(function () { el.classList.remove('stat-updated'); }, 1000);
                            }
                        });
                    }
                })
                .catch(function () { });
        }, refreshInterval * 1000);
    }

    /* ── 10. Form validation binding ───────────────────────── */
    function initFormValidationBinding() {
        doc.querySelectorAll('form[data-validate="true"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var rules = {};
                try {
                    rules = JSON.parse(form.getAttribute('data-rules') || '{}');
                } catch (x) {
                    rules = {};
                }
                if (!validateForm(form, rules)) {
                    e.preventDefault();
                }
            });
        });
    }

    function validateForm(form, rules) {
        if (typeof form === 'string') form = doc.querySelector(form);
        if (!form) return true;

        var isValid = true;

        form.querySelectorAll('.is-invalid, .form-error').forEach(function (el) {
            el.classList.remove('is-invalid');
            var err = el.parentNode.querySelector('.form-error');
            if (err) err.remove();
        });

        rules = rules || {};

        Object.keys(rules).forEach(function (fieldName) {
            var field = form.querySelector('[name="' + fieldName + '"]');
            if (!field) return;

            var fieldRules = rules[fieldName];
            var value = field.value ? field.value.trim() : '';

            for (var i = 0; i < fieldRules.length; i++) {
                var rule = fieldRules[i];

                if (rule.required && !value) {
                    showFieldError(field, rule.message || fieldName + ' is required');
                    isValid = false;
                    break;
                }

                if (rule.minLength && value.length < rule.minLength) {
                    showFieldError(field, rule.message || fieldName + ' must be at least ' + rule.minLength + ' characters');
                    isValid = false;
                    break;
                }

                if (rule.maxLength && value.length > rule.maxLength) {
                    showFieldError(field, rule.message || fieldName + ' must not exceed ' + rule.maxLength + ' characters');
                    isValid = false;
                    break;
                }

                if (rule.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showFieldError(field, rule.message || 'Please enter a valid email address');
                    isValid = false;
                    break;
                }

                if (rule.min && field.type === 'number' && parseFloat(value) < rule.min) {
                    showFieldError(field, rule.message || fieldName + ' must be at least ' + rule.min);
                    isValid = false;
                    break;
                }

                if (rule.pattern && value && !rule.pattern.test(value)) {
                    showFieldError(field, rule.message || 'Invalid format');
                    isValid = false;
                    break;
                }

                if (rule.match && value) {
                    var matchField = form.querySelector('[name="' + rule.match + '"]');
                    if (matchField && value !== matchField.value) {
                        showFieldError(field, rule.message || 'Fields do not match');
                        isValid = false;
                        break;
                    }
                }
            }
        });

        return isValid;
    }

    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        var errorEl = doc.createElement('div');
        errorEl.className = 'form-error';
        errorEl.style.cssText = 'color:#ef4444;font-size:0.8rem;margin-top:0.25rem;';
        errorEl.textContent = message;

        var parent = field.parentNode;
        var existing = parent.querySelector('.form-error');
        if (existing) existing.remove();
        parent.appendChild(errorEl);
    }

    win.validateForm = validateForm;

    /* ── 11. Tooltips ──────────────────────────────────────── */
    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            try {
                var tooltipList = doc.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipList.forEach(function (el) {
                    new bootstrap.Tooltip(el);
                });
            } catch (e) { }
        }
    }

    /* ── 12. Sortable tables (drag handle) ─────────────────── */
    function initSortableTables() {
        doc.querySelectorAll('.sortable-table tbody').forEach(function (tbody) {
            tbody.addEventListener('dragstart', function (e) {
                var row = e.target.closest('tr');
                if (!row) return;
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.rowIndex);
            });

            tbody.addEventListener('dragend', function (e) {
                var row = e.target.closest('tr');
                if (row) row.classList.remove('dragging');
            });

            tbody.addEventListener('dragover', function (e) {
                e.preventDefault();
                var target = e.target.closest('tr');
                if (!target) return;
                var dragging = tbody.querySelector('.dragging');
                if (!dragging || dragging === target) return;
                var rect = target.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    tbody.insertBefore(dragging, target);
                } else {
                    tbody.insertBefore(dragging, target.nextSibling);
                }
            });
        });

        doc.querySelectorAll('.sort-handle').forEach(function (handle) {
            handle.setAttribute('draggable', 'true');
            handle.style.cursor = 'grab';
        });
    }

    /* ── 13. Bulk select checkboxes ────────────────────────── */
    function initBulkSelect() {
        var selectAll = doc.getElementById('selectAll');
        if (!selectAll) return;

        selectAll.addEventListener('change', function () {
            var checked = this.checked;
            doc.querySelectorAll('.bulk-checkbox').forEach(function (cb) {
                cb.checked = checked;
            });
            updateBulkActions();
        });

        doc.querySelectorAll('.bulk-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var all = doc.querySelectorAll('.bulk-checkbox');
                var checked = doc.querySelectorAll('.bulk-checkbox:checked');
                if (selectAll) selectAll.checked = all.length === checked.length;
                if (selectAll) selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
                updateBulkActions();
            });
        });

        function updateBulkActions() {
            var count = doc.querySelectorAll('.bulk-checkbox:checked').length;
            var el = doc.getElementById('bulkCount');
            if (el) {
                el.textContent = count > 0 ? count + ' selected' : '';
            }
            var actions = doc.getElementById('bulkActions');
            if (actions) {
                actions.style.display = count > 0 ? 'block' : 'none';
            }
        }
    }

    /* ── 14. Search highlight ──────────────────────────────── */
    function initSearchHighlight() {
        var searchInput = doc.querySelector('.dataTables_filter input');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            doc.querySelectorAll('.table-hover tbody tr').forEach(function (row) {
                row.querySelectorAll('td').forEach(function (cell) {
                    cell.querySelectorAll('.search-highlight').forEach(function (hl) {
                        var parent = hl.parentNode;
                        parent.replaceChild(doc.createTextNode(hl.textContent), hl);
                        parent.normalize();
                    });

                    if (!q) return;
                    var text = cell.textContent.toLowerCase();
                    if (text.indexOf(q) > -1) {
                        var idx = text.indexOf(q);
                        var original = cell.textContent;
                        var before = original.substring(0, idx);
                        var match = original.substring(idx, idx + q.length);
                        var after = original.substring(idx + q.length);
                        cell.innerHTML = before + '<span class="search-highlight" style="background:#fef3c7;border-radius:2px;padding:0 2px;">' + match + '</span>' + after;
                    }
                });
            });
        });
    }

    /* ── Expose public helpers ─────────────────────────────── */
    win.showToast = function (message, type, title) {
        type = type || 'success';
        title = title || '';

        if (typeof Swal !== 'undefined') {
            var iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
            Swal.fire({
                icon: iconMap[type] || 'info',
                title: title || (type.charAt(0).toUpperCase() + type.slice(1)),
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: function (toast) {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            return;
        }

        var toast = doc.createElement('div');
        toast.className = 'admin-toast toast-' + type;
        toast.innerHTML = '<div class="toast-body">' +
            (title ? '<strong>' + title + '</strong><br>' : '') +
            message +
            '<button class="toast-close">&times;</button></div>';

        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '8px',
            fontSize: '14px',
            fontWeight: '500',
            zIndex: '9999',
            color: '#fff',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            maxWidth: '350px',
            opacity: '0',
            transform: 'translateY(-20px)',
            transition: 'all 0.3s ease'
        });

        var colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
        toast.style.background = colors[type] || colors.info;

        doc.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        }, 3500);

        toast.querySelector('.toast-close').addEventListener('click', function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        });
    };

    win.initDataTable = function (selector, options) {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            console.warn('DataTables not loaded');
            return null;
        }
        var els = doc.querySelectorAll(selector || '.datatable');
        if (!els.length) return null;

        var defaults = {
            pageLength: 10,
            language: {
                search: '<i class="fas fa-search"></i> Search:',
                searchPlaceholder: 'Search...'
            },
            dom: '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rtip',
            responsive: true,
            autoWidth: false,
            order: []
        };

        try {
            return $(selector).DataTable(Object.assign({}, defaults, options || {}));
        } catch (e) {
            console.warn('DataTable init failed', e);
            return null;
        }
    };

})();
