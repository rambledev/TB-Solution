/**
 * AI School Intelligence — core UI (Vanilla JS).
 *
 * Exposes window.AIS with shared helpers used by charts.js, pages.js, chat.js and import.js.
 * Dev logs print only when window.AIS_CONFIG.debug is true (start PHP with APP_DEBUG=1).
 */
(function () {
    'use strict';

    var CONFIG = window.AIS_CONFIG || {};
    var IS_DEV = Boolean(CONFIG.debug);
    var STORAGE_PREFIX = 'ais.';

    var MESSAGES = {
        soon: 'ฟีเจอร์นี้อยู่ระหว่างพัฒนา (Coming Soon) และจะเปิดให้ใช้งานในเฟสถัดไป',
        resetDone: 'รีเซ็ตข้อมูลสาธิตเรียบร้อยแล้ว',
        genericError: 'ระบบกำลังดำเนินการ กรุณาลองใหม่อีกครั้ง'
    };

    /* Logging ----------------------------------------------------------- */
    function log(module, fn, message, data) {
        if (!IS_DEV) return;
        var prefix = '[' + module + '][' + fn + '] ' + message;
        if (data === undefined) console.log(prefix); else console.log(prefix, data);
    }

    function logError(module, fn, error, context) {
        console.error('[' + module + '][' + fn + '] ERROR', { error: error, context: context });
    }

    function devGroup(title, entries) {
        if (!IS_DEV) return;
        console.group('[DEV] ' + title);
        Object.keys(entries).forEach(function (key) { console.log(key + ':', entries[key]); });
        console.groupEnd();
    }

    /* Storage (per-viewer conveniences only) ---------------------------- */
    var store = {
        get: function (key, fallback) {
            try {
                var raw = localStorage.getItem(STORAGE_PREFIX + key);
                return raw === null ? fallback : JSON.parse(raw);
            } catch (e) {
                return fallback;
            }
        },
        set: function (key, value) {
            try { localStorage.setItem(STORAGE_PREFIX + key, JSON.stringify(value)); } catch (e) { /* storage unavailable */ }
        },
        clearAll: function () {
            try {
                Object.keys(localStorage).forEach(function (k) {
                    if (k.indexOf(STORAGE_PREFIX) === 0) localStorage.removeItem(k);
                });
            } catch (e) { /* storage unavailable */ }
        }
    };

    /* Text helpers ------------------------------------------------------- */
    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /** Light markdown (escaped first): **bold**, _italic_, "- " bullets, "1. " lists. */
    function formatText(text) {
        var html = '';
        var list = null;
        escapeHtml(text).split('\n').forEach(function (line) {
            var bullet = /^\s*-\s+(.*)$/.exec(line);
            var ordered = /^\s*(\d+)\.\s+(.*)$/.exec(line);
            if (bullet || ordered) {
                var tag = bullet ? 'ul' : 'ol';
                if (list !== tag) { if (list) html += '</' + list + '>'; html += '<' + tag + '>'; list = tag; }
                html += '<li>' + (ordered ? ordered[1] + '. ' + ordered[2] : bullet[1]) + '</li>';
                return;
            }
            if (list) { html += '</' + list + '>'; list = null; }
            if (line.trim() !== '') html += '<p>' + line + '</p>';
        });
        if (list) html += '</' + list + '>';
        return html
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/(^|[\s>(])_(.+?)_(?=[\s<.)]|$)/g, '$1<em>$2</em>');
    }

    function icon(name, cls) {
        return '<i data-lucide="' + escapeHtml(name) + '"' + (cls ? ' class="' + escapeHtml(cls) + '"' : '') + ' aria-hidden="true"></i>';
    }

    function renderIcons(root) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            try {
                window.lucide.createIcons(root ? { root: root } : undefined);
            } catch (error) {
                logError('Core', 'renderIcons', error);
            }
        }
    }

    function readJson(id) {
        var el = document.getElementById(id);
        if (!el) return null;
        try {
            return JSON.parse(el.textContent);
        } catch (error) {
            logError('Core', 'readJson', error, { id: id });
            return null;
        }
    }

    /* Toast ---------------------------------------------------------------- */
    function toast(message, iconName) {
        log('Core', 'toast', 'show', { message: message });
        var stack = document.getElementById('toast-stack');
        if (!stack) return;
        var el = document.createElement('div');
        el.className = 'toast';
        el.setAttribute('role', 'status');
        el.innerHTML = icon(iconName || 'info') + '<span>' + escapeHtml(message) + '</span>';
        stack.appendChild(el);
        renderIcons(el);
        setTimeout(function () {
            el.classList.add('is-leaving');
            setTimeout(function () { el.remove(); }, 320);
        }, 3600);
    }

    /* Modal ---------------------------------------------------------------- */
    var modalState = { lastFocus: null, onClose: null };

    function openModal(options) {
        log('Core', 'openModal', 'START', { title: options.title });
        var modal = document.getElementById('modal');
        if (!modal) return;
        modalState.lastFocus = document.activeElement;
        modalState.onClose = options.onClose || null;
        modal.classList.toggle('modal--wide', Boolean(options.wide));
        document.getElementById('modal-eyebrow').textContent = options.eyebrow || '';
        document.getElementById('modal-title').textContent = options.title || '';
        document.getElementById('modal-body').innerHTML = options.html || '';
        var foot = document.getElementById('modal-foot');
        foot.innerHTML = options.foot || '';
        foot.hidden = !options.foot;
        modal.hidden = false;
        document.body.classList.add('is-locked');
        renderIcons(modal);
        var focusTarget = modal.querySelector('.modal__panel [data-modal-close]');
        if (focusTarget) focusTarget.focus();
        if (typeof options.onOpen === 'function') options.onOpen(modal);
        log('Core', 'openModal', 'END');
    }

    function closeModal() {
        var modal = document.getElementById('modal');
        if (!modal || modal.hidden) return;
        log('Core', 'closeModal', 'START');
        modal.hidden = true;
        document.body.classList.remove('is-locked');
        document.getElementById('modal-body').innerHTML = '';
        if (typeof modalState.onClose === 'function') modalState.onClose();
        modalState.onClose = null;
        if (modalState.lastFocus && modalState.lastFocus.focus) modalState.lastFocus.focus();
        log('Core', 'closeModal', 'END');
    }

    function initModal() {
        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-modal-close]')) closeModal();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeModal();
        });
    }

    /* Sidebar -------------------------------------------------------------- */
    function initSidebar() {
        log('Core', 'initSidebar', 'START');
        var sidebar = document.getElementById('sidebar');
        var backdrop = document.querySelector('.sidebar-backdrop');
        if (!sidebar) return;

        function setDrawer(open) {
            sidebar.classList.toggle('is-open', open);
            if (backdrop) backdrop.hidden = !open;
            document.body.classList.toggle('is-locked', open);
            var toggle = document.querySelector('[data-sidebar-open]');
            if (toggle) toggle.setAttribute('aria-expanded', String(open));
            log('Core', 'setDrawer', 'drawer toggled', { open: open });
        }

        document.querySelectorAll('[data-sidebar-open]').forEach(function (btn) {
            btn.addEventListener('click', function () { setDrawer(true); });
        });
        document.querySelectorAll('[data-sidebar-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { setDrawer(false); });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) setDrawer(false);
        });

        var collapse = document.querySelector('[data-sidebar-collapse]');
        if (collapse) {
            collapse.addEventListener('click', function () {
                var collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem('ais.sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) { /* ignore */ }
                collapse.querySelector('.nav-link__text').textContent = collapsed ? 'ขยายเมนู' : 'ย่อเมนู';
                window.dispatchEvent(new Event('resize'));
                log('Core', 'initSidebar', 'collapse toggled', { collapsed: collapsed });
            });
        }
        log('Core', 'initSidebar', 'END');
    }

    /* Dropdowns ------------------------------------------------------------ */
    function initDropdowns() {
        log('Core', 'initDropdowns', 'START');
        var dropdowns = document.querySelectorAll('[data-dropdown]');

        function closeAll(except) {
            dropdowns.forEach(function (dd) {
                if (dd === except) return;
                dd.classList.remove('is-open');
                var t = dd.querySelector('[data-dropdown-toggle]');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }

        dropdowns.forEach(function (dd) {
            var toggle = dd.querySelector('[data-dropdown-toggle]');
            if (!toggle) return;
            toggle.addEventListener('click', function (event) {
                event.stopPropagation();
                var open = !dd.classList.contains('is-open');
                closeAll(dd);
                dd.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', String(open));
            });
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-dropdown]')) closeAll(null);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeAll(null);
        });
        log('Core', 'initDropdowns', 'END', { count: dropdowns.length });
    }

    /* Period filter -------------------------------------------------------- */
    function initPeriod() {
        document.querySelectorAll('[data-period]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var period = btn.getAttribute('data-period');
                log('Core', 'initPeriod', 'period selected', { period: period });
                var url = new URL(window.location.href);
                url.searchParams.set('period', period);
                toast('กำลังอัปเดตข้อมูลตามช่วงเวลา…', 'calendar-days');
                window.location.href = url.toString();
            });
        });
    }

    /* Notifications -------------------------------------------------------- */
    function initNotifications() {
        var read = store.get('notifRead', []);
        var items = document.querySelectorAll('[data-notif-id]');
        var counter = document.querySelector('[data-notif-count]');

        function refresh() {
            var unread = 0;
            items.forEach(function (item) {
                var isRead = read.indexOf(item.getAttribute('data-notif-id')) !== -1;
                item.classList.toggle('is-read', isRead);
                if (!isRead) unread++;
            });
            if (counter) {
                counter.textContent = unread > 0 ? String(unread) : '';
                counter.toggleAttribute('data-zero', unread === 0);
            }
        }

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var id = item.getAttribute('data-notif-id');
                if (read.indexOf(id) === -1) read.push(id);
                store.set('notifRead', read);
            });
        });

        var readAll = document.querySelector('[data-notif-readall]');
        if (readAll) {
            readAll.addEventListener('click', function (event) {
                event.stopPropagation();
                items.forEach(function (item) {
                    var id = item.getAttribute('data-notif-id');
                    if (read.indexOf(id) === -1) read.push(id);
                });
                store.set('notifRead', read);
                refresh();
                toast('ทำเครื่องหมายว่าอ่านแล้วทั้งหมด', 'check');
            });
        }
        refresh();
    }

    /* Tabs ----------------------------------------------------------------- */
    function initTabs(root) {
        (root || document).querySelectorAll('[data-tabs]').forEach(function (box) {
            var tabs = box.querySelectorAll('[data-tab]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var key = tab.getAttribute('data-tab');
                    tabs.forEach(function (t) {
                        var active = t === tab;
                        t.classList.toggle('is-active', active);
                        t.setAttribute('aria-selected', String(active));
                    });
                    box.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
                        panel.hidden = panel.getAttribute('data-tab-panel') !== key;
                    });
                    log('Core', 'initTabs', 'tab selected', { key: key });
                });
            });
        });
    }

    /* Tables: search, sort, filter chips ----------------------------------- */
    function initTables(root) {
        (root || document).querySelectorAll('table[data-table]').forEach(function (table) {
            var id = table.id;
            var tbody = table.tBodies[0];
            if (!tbody) return;
            var state = { query: '', filters: {} };
            var emptyRow = null;

            function apply() {
                var visible = 0;
                Array.prototype.forEach.call(tbody.rows, function (row) {
                    if (row === emptyRow) return;
                    var text = (row.getAttribute('data-search') || row.textContent).toLowerCase();
                    var ok = state.query === '' || text.indexOf(state.query) !== -1;
                    Object.keys(state.filters).forEach(function (key) {
                        var value = state.filters[key];
                        if (value && value !== '*' && row.getAttribute('data-' + key) !== value) ok = false;
                    });
                    row.hidden = !ok;
                    if (ok) visible++;
                });
                if (!emptyRow) {
                    emptyRow = tbody.insertRow();
                    emptyRow.innerHTML = '<td class="table-empty" colspan="' + table.tHead.rows[0].cells.length + '">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</td>';
                }
                emptyRow.hidden = visible > 0;
                document.querySelectorAll('[data-table-count="' + id + '"]').forEach(function (el) { el.textContent = visible.toLocaleString('th-TH'); });
                log('Core', 'tableApply', 'filtered', { table: id, query: state.query, filters: state.filters, visible: visible });
            }

            document.querySelectorAll('[data-table-search="' + id + '"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    state.query = input.value.trim().toLowerCase();
                    apply();
                });
            });

            document.querySelectorAll('[data-table-filter="' + id + '"]').forEach(function (control) {
                var key = control.getAttribute('data-filter-key');
                if (control.tagName === 'SELECT') {
                    control.addEventListener('change', function () { state.filters[key] = control.value; apply(); });
                    return;
                }
                control.addEventListener('click', function () {
                    document.querySelectorAll('[data-table-filter="' + id + '"][data-filter-key="' + key + '"]').forEach(function (c) {
                        c.classList.toggle('is-active', c === control);
                        c.setAttribute('aria-pressed', String(c === control));
                    });
                    state.filters[key] = control.getAttribute('data-filter-value');
                    apply();
                });
                if (control.classList.contains('is-active')) state.filters[key] = control.getAttribute('data-filter-value');
            });

            table.querySelectorAll('th[data-sort]').forEach(function (th, index) {
                th.setAttribute('tabindex', '0');
                th.setAttribute('role', 'button');
                function sort() {
                    var type = th.getAttribute('data-sort');
                    var col = Array.prototype.indexOf.call(th.parentNode.cells, th);
                    var dir = th.getAttribute('data-dir') === 'asc' ? 'desc' : 'asc';
                    table.querySelectorAll('th[data-sort]').forEach(function (h) { h.removeAttribute('data-dir'); });
                    th.setAttribute('data-dir', dir);
                    var rows = Array.prototype.filter.call(tbody.rows, function (r) { return r !== emptyRow; });
                    rows.sort(function (a, b) {
                        var av = a.cells[col].getAttribute('data-value') || a.cells[col].textContent.trim();
                        var bv = b.cells[col].getAttribute('data-value') || b.cells[col].textContent.trim();
                        var cmp = type === 'num'
                            ? (parseFloat(av.replace(/,/g, '')) || 0) - (parseFloat(bv.replace(/,/g, '')) || 0)
                            : av.localeCompare(bv, 'th');
                        return dir === 'asc' ? cmp : -cmp;
                    });
                    rows.forEach(function (r) { tbody.appendChild(r); });
                    if (emptyRow) tbody.appendChild(emptyRow);
                    log('Core', 'tableSort', 'sorted', { table: id, column: index, dir: dir });
                }
                th.addEventListener('click', sort);
                th.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); sort(); } });
            });

            if (Object.keys(state.filters).length) apply();
        });
    }

    /* Spark lines (canvas, no library) ------------------------------------- */
    function drawSparks(root) {
        (root || document).querySelectorAll('canvas[data-spark]').forEach(function (canvas) {
            var values = canvas.getAttribute('data-spark').split(',').map(Number).filter(function (v) { return !isNaN(v); });
            if (values.length < 2) return;
            var ratio = window.devicePixelRatio || 1;
            var w = canvas.clientWidth || 96;
            var h = canvas.clientHeight || 32;
            canvas.width = w * ratio;
            canvas.height = h * ratio;
            var ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);
            var min = Math.min.apply(null, values);
            var max = Math.max.apply(null, values);
            var range = max - min || 1;
            var color = getComputedStyle(canvas.closest('.kpi') || document.body).getPropertyValue('--kpi-ink').trim() || '#2878E8';
            var points = values.map(function (v, i) {
                return [i / (values.length - 1) * (w - 4) + 2, h - 4 - (v - min) / range * (h - 8)];
            });
            var grad = ctx.createLinearGradient(0, 0, 0, h);
            grad.addColorStop(0, color + '33');
            grad.addColorStop(1, color + '00');
            ctx.beginPath();
            points.forEach(function (p, i) { if (i === 0) ctx.moveTo(p[0], p[1]); else ctx.lineTo(p[0], p[1]); });
            ctx.lineTo(points[points.length - 1][0], h);
            ctx.lineTo(points[0][0], h);
            ctx.closePath();
            ctx.fillStyle = grad;
            ctx.fill();
            ctx.beginPath();
            points.forEach(function (p, i) { if (i === 0) ctx.moveTo(p[0], p[1]); else ctx.lineTo(p[0], p[1]); });
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.lineJoin = 'round';
            ctx.stroke();
            var last = points[points.length - 1];
            ctx.beginPath();
            ctx.arc(last[0], last[1], 2.6, 0, Math.PI * 2);
            ctx.fillStyle = color;
            ctx.fill();
        });
    }

    /* Reset demo, coming soon, privacy ------------------------------------- */
    function resetDemo() {
        log('Core', 'resetDemo', 'START');
        store.clearAll();
        try { localStorage.removeItem('ais.sidebar'); } catch (e) { /* ignore */ }
        document.cookie = 'ais_period=; Max-Age=0; path=/';
        try { sessionStorage.setItem('ais.flash', MESSAGES.resetDone); } catch (e) { /* ignore */ }
        log('Core', 'resetDemo', 'END');
        window.location.href = './?page=overview';
    }

    function initActions() {
        document.addEventListener('click', function (event) {
            var reset = event.target.closest('[data-reset-demo]');
            if (reset) {
                event.preventDefault();
                openModal({
                    eyebrow: 'Demo Mode',
                    title: 'รีเซ็ตข้อมูลสาธิต?',
                    html: '<p class="muted">ระบบจะล้างข้อมูลที่ทดลองไว้ในเบราว์เซอร์นี้ เช่น ประวัติแชต รายการนำเข้าไฟล์ที่ทดลอง การแจ้งเตือนที่อ่านแล้ว และการตั้งค่าการแสดงผล แล้วกลับสู่ข้อมูลตั้งต้น</p>'
                        + '<p class="muted" style="margin-top:10px">ข้อมูลตัวอย่างของโรงเรียนจะไม่ถูกแก้ไข เพราะเป็นชุดข้อมูลจำลองแบบคงที่</p>',
                    foot: '<button type="button" class="btn btn--ghost" data-modal-close>ยกเลิก</button><button type="button" class="btn btn--primary" data-confirm-reset>' + icon('rotate-ccw') + 'รีเซ็ตข้อมูล</button>'
                });
                return;
            }
            if (event.target.closest('[data-confirm-reset]')) {
                resetDemo();
                return;
            }
            var soon = event.target.closest('[data-coming-soon]');
            if (soon) {
                event.preventDefault();
                toast(soon.getAttribute('data-coming-soon') || MESSAGES.soon, 'clock');
            }
        });

        document.querySelectorAll('[data-privacy-toggle]').forEach(function (input) {
            var target = document.querySelector(input.getAttribute('data-privacy-toggle'));
            var saved = store.get('privacy', false);
            input.checked = saved;
            if (target) target.classList.toggle('privacy-on', saved);
            input.addEventListener('change', function () {
                if (target) target.classList.toggle('privacy-on', input.checked);
                store.set('privacy', input.checked);
                log('Core', 'privacyToggle', 'changed', { on: input.checked });
            });
        });

        try {
            var flash = sessionStorage.getItem('ais.flash');
            if (flash) {
                sessionStorage.removeItem('ais.flash');
                setTimeout(function () { toast(flash, 'circle-check'); }, 300);
            }
        } catch (e) { /* ignore */ }
    }

    /* API helper ----------------------------------------------------------- */
    function postJson(url, body) {
        log('Api', 'postJson', 'REQUEST', { url: url, body: body });
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (res) {
            return res.json().catch(function () { return { ok: false }; }).then(function (data) {
                devGroup('API Response', { URL: url, Status: res.status, Response: data });
                if (!res.ok || !data.ok) {
                    var err = new Error('Request failed');
                    err.userMessage = data && data.message ? data.message : MESSAGES.genericError;
                    throw err;
                }
                return data;
            });
        });
    }

    window.AIS = {
        config: CONFIG,
        log: log,
        logError: logError,
        devGroup: devGroup,
        store: store,
        escapeHtml: escapeHtml,
        formatText: formatText,
        icon: icon,
        renderIcons: renderIcons,
        readJson: readJson,
        toast: toast,
        openModal: openModal,
        closeModal: closeModal,
        initTables: initTables,
        initTabs: initTabs,
        drawSparks: drawSparks,
        postJson: postJson,
        messages: MESSAGES
    };

    function init() {
        log('Core', 'init', 'START', { page: CONFIG.page, period: CONFIG.period });
        try {
            renderIcons();
            initModal();
            initSidebar();
            initDropdowns();
            initPeriod();
            initNotifications();
            initTabs();
            initTables();
            initActions();
            drawSparks();
            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () { drawSparks(); }, 150);
            });
        } catch (error) {
            logError('Core', 'init', error);
        }
        log('Core', 'init', 'END');
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
