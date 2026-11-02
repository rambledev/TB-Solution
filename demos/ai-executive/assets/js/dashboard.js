/**
 * AI Executive Dashboard — core UI: router, filters, sidebar, theme, toast, modal,
 * tables (search/sort/pagination), Mock Data generator and the Overview view.
 *
 * Dev logs print only when window.EXD_CONFIG.debug is true (APP_DEBUG=1).
 */
(function () {
    'use strict';

    var CONFIG = window.EXD_CONFIG || {};
    var IS_DEV = Boolean(CONFIG.debug);
    var STORE = window.EXD_STORE;
    var AI = window.EXD_AI;
    var CH = window.EXD_CHARTS;
    var DEFAULT_FILTERS = { range: '6', year: '2569', semester: '1', room: 'all' };
    var LEVEL_META = {
        critical: { label: 'Critical Issue', th: 'วิกฤต', icon: 'octagon-alert' },
        warning: { label: 'Warning', th: 'เฝ้าระวัง', icon: 'triangle-alert' },
        opportunity: { label: 'Opportunity', th: 'โอกาส', icon: 'rocket' },
        recommendation: { label: 'Recommendation', th: 'ข้อเสนอแนะ', icon: 'lightbulb' }
    };

    var state = {
        view: 'overview',
        filters: Object.assign({}, DEFAULT_FILTERS),
        model: null,
        pageSize: 10,
        tables: {}
    };
    var views = {};

    /* Logging & helpers --------------------------------------------------- */
    function log(module, fn, message, data) {
        if (!IS_DEV) return;
        if (data === undefined) console.log('[' + module + '][' + fn + '] ' + message);
        else console.log('[' + module + '][' + fn + '] ' + message, data);
    }
    function logError(module, fn, error, context) {
        console.error('[' + module + '][' + fn + '] ERROR', { error: error, context: context });
    }
    function store(key, value) {
        try {
            if (value === undefined) return JSON.parse(localStorage.getItem('exd.' + key));
            localStorage.setItem('exd.' + key, JSON.stringify(value));
        } catch (e) { return null; }
        return null;
    }
    function esc(v) {
        return String(v === null || v === undefined ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function icon(name, cls) { return '<i data-lucide="' + esc(name) + '"' + (cls ? ' class="' + esc(cls) + '"' : '') + ' aria-hidden="true"></i>'; }
    function icons(root) {
        if (window.lucide) {
            try { window.lucide.createIcons(root ? { root: root } : undefined); } catch (e) { logError('UI', 'icons', e); }
        }
    }
    function num(v, d) {
        if (v === null || v === undefined || isNaN(v)) return '–';
        return Number(v).toLocaleString('th-TH', { minimumFractionDigits: d || 0, maximumFractionDigits: d || 0 });
    }
    function $(sel, root) { return (root || document).querySelector(sel); }
    function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    /* Toast ------------------------------------------------------------------ */
    function toast(message, type) {
        var box = $('#toasts');
        if (!box) return;
        var el = document.createElement('div');
        el.className = 'toast toast--' + (type || 'info');
        el.setAttribute('role', 'status');
        el.innerHTML = icon({ success: 'circle-check', error: 'circle-x', warning: 'triangle-alert', ai: 'sparkles' }[type] || 'info') + '<span>' + esc(message) + '</span>';
        box.appendChild(el);
        icons(el);
        setTimeout(function () { el.classList.add('is-leaving'); setTimeout(function () { el.remove(); }, 300); }, 3400);
    }

    /* Modal ------------------------------------------------------------------ */
    var lastFocus = null;
    function openModal(o) {
        log('UI', 'openModal', 'START', { title: o.title });
        var modal = $('#modal');
        lastFocus = document.activeElement;
        modal.classList.toggle('modal--wide', Boolean(o.wide));
        $('#modal-eyebrow').innerHTML = o.eyebrow || '';
        $('#modal-title').textContent = o.title || '';
        $('#modal-body').innerHTML = o.html || '';
        var foot = $('#modal-foot');
        foot.innerHTML = o.foot || '';
        foot.hidden = !o.foot;
        modal.hidden = false;
        document.body.classList.add('is-locked');
        icons(modal);
        var close = modal.querySelector('.modal__head [data-action="modal-close"]');
        if (close) close.focus();
        if (o.onOpen) o.onOpen(modal);
    }
    function closeModal() {
        var modal = $('#modal');
        if (modal.hidden) return;
        modal.hidden = true;
        $('#modal-body').innerHTML = '';
        document.body.classList.remove('is-locked');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    /* Table component -------------------------------------------------------- */
    /**
     * renderTable('tc-table', {columns, rows, onRow, empty}) — search, sort and pagination.
     * Search/filter controls are linked by data-table-search / data-table-filter attributes.
     */
    function renderTable(id, opts) {
        var host = document.getElementById(id);
        if (!host) return;
        var t = state.tables[id] || (state.tables[id] = { query: '', filters: {}, sort: null, dir: 1, page: 1, bound: false });
        t.opts = opts;

        if (!t.bound) {
            t.bound = true;
            $$('[data-table-search="' + id + '"]').forEach(function (input) {
                input.addEventListener('input', function () { t.query = input.value.trim().toLowerCase(); t.page = 1; draw(); });
            });
            $$('[data-table-filter="' + id + '"]').forEach(function (sel) {
                sel.addEventListener('change', function () { t.filters[sel.getAttribute('data-key')] = sel.value; t.page = 1; draw(); });
            });
            host.addEventListener('click', function (e) {
                var th = e.target.closest('th[data-sort]');
                if (th) {
                    var key = th.getAttribute('data-sort');
                    t.dir = t.sort === key ? -t.dir : 1;
                    t.sort = key;
                    log('Table', 'sort', 'sorted', { table: id, key: key, dir: t.dir });
                    draw();
                    return;
                }
                var pg = e.target.closest('[data-page]');
                if (pg && !pg.disabled) { t.page = parseInt(pg.getAttribute('data-page'), 10); draw(); return; }
                var tr = e.target.closest('tr[data-row]');
                if (tr && t.opts.onRow) t.opts.onRow(t.view[parseInt(tr.getAttribute('data-row'), 10)]);
            });
            host.addEventListener('keydown', function (e) {
                var tr = e.target.closest('tr[data-row]');
                if (tr && (e.key === 'Enter' || e.key === ' ') && t.opts.onRow) { e.preventDefault(); t.opts.onRow(t.view[parseInt(tr.getAttribute('data-row'), 10)]); }
            });
        }

        function draw() {
            var o = t.opts;
            var rows = o.rows.filter(function (r) {
                if (t.query && (o.searchText ? o.searchText(r) : JSON.stringify(r)).toLowerCase().indexOf(t.query) === -1) return false;
                return Object.keys(t.filters).every(function (k) { return !t.filters[k] || String(r[k]) === t.filters[k]; });
            });
            if (t.sort) {
                var col = o.columns.filter(function (c) { return c.key === t.sort; })[0];
                var val = col && col.value ? col.value : function (r) { return r[t.sort]; };
                rows.sort(function (a, b) {
                    var x = val(a), y = val(b);
                    return (typeof x === 'number' && typeof y === 'number' ? x - y : String(x).localeCompare(String(y), 'th')) * t.dir;
                });
            }
            var size = state.pageSize;
            var pages = Math.max(1, Math.ceil(rows.length / size));
            t.page = Math.min(t.page, pages);
            var start = (t.page - 1) * size;
            t.view = rows.slice(start, start + size);

            var head = '<tr>' + o.columns.map(function (c) {
                var sorted = t.sort === c.key ? (t.dir === 1 ? ' data-dir="asc"' : ' data-dir="desc"') : '';
                return '<th' + (c.num ? ' class="num"' : '') + (c.sortable === false ? '' : ' data-sort="' + c.key + '" tabindex="0"') + sorted + '>' + esc(c.label) + '</th>';
            }).join('') + '</tr>';
            var body = t.view.length ? t.view.map(function (r, i) {
                return '<tr data-row="' + i + '"' + (o.onRow ? ' tabindex="0" class="is-clickable"' : '') + '>' + o.columns.map(function (c) {
                    return '<td' + (c.num ? ' class="num"' : '') + '>' + (c.render ? c.render(r) : esc(r[c.key])) + '</td>';
                }).join('') + '</tr>';
            }).join('') : '<tr><td colspan="' + o.columns.length + '"><div class="state state--inline">' + icon('search-x') + '<p>' + esc(o.empty || 'ไม่พบข้อมูลที่ตรงกับเงื่อนไข') + '</p></div></td></tr>';

            var pager = '<div class="pager"><span>แสดง ' + (rows.length ? start + 1 : 0) + '–' + Math.min(start + size, rows.length) + ' จาก ' + num(rows.length) + ' รายการ</span><div class="pager__btns">'
                + '<button type="button" class="icon-btn" data-page="' + (t.page - 1) + '"' + (t.page <= 1 ? ' disabled' : '') + ' aria-label="หน้าก่อนหน้า">' + icon('chevron-left') + '</button>';
            var from = Math.max(1, Math.min(t.page - 2, pages - 4));
            for (var p = from; p <= Math.min(pages, from + 4); p++) {
                pager += '<button type="button" class="page-num' + (p === t.page ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            pager += '<button type="button" class="icon-btn" data-page="' + (t.page + 1) + '"' + (t.page >= pages ? ' disabled' : '') + ' aria-label="หน้าถัดไป">' + icon('chevron-right') + '</button></div></div>';

            host.innerHTML = '<div class="table-wrap"><table class="table"><thead>' + head + '</thead><tbody>' + body + '</tbody></table></div>' + pager;
            icons(host);
        }
        draw();
    }

    /* Shared renderers ---------------------------------------------------------- */
    function deltaBadge(k) {
        if (k.diff === null) return '<span class="trend trend--neutral">' + icon('minus') + 'ไม่มีข้อมูลเทียบ</span>';
        var arrow = k.dir === 'up' ? 'trending-up' : (k.dir === 'down' ? 'trending-down' : 'minus');
        var sign = k.diff > 0 ? '+' : '';
        return '<span class="trend trend--' + k.tone + '">' + icon(arrow) + sign + num(k.diff, k.decimals) + ' (' + sign + num(k.pct, 1) + '%)</span>';
    }

    function kpiCard(k) {
        return '<a class="kpi kpi--' + k.color + '" href="#' + k.view + '">'
            + '<div class="kpi__top"><span class="kpi__icon">' + icon(k.icon) + '</span>' + deltaBadge(k) + '</div>'
            + '<p class="kpi__label">' + esc(k.label) + '</p>'
            + '<p class="kpi__value">' + (k.value === null ? '–' : num(k.value, k.decimals)) + '<small>' + esc(k.unit) + '</small></p>'
            + '<div class="kpi__foot"><span>' + esc(k.meta || k.compare) + '</span>' + (k.spark ? '<canvas class="spark" data-spark="' + k.spark.join(',') + '"></canvas>' : '') + '</div></a>';
    }

    function drawSparks(root) {
        $$('canvas[data-spark]', root).forEach(function (cv) {
            var vals = cv.getAttribute('data-spark').split(',').map(Number).filter(function (v) { return !isNaN(v); });
            if (vals.length < 2) return;
            var r = window.devicePixelRatio || 1, w = cv.clientWidth || 100, h = cv.clientHeight || 34;
            cv.width = w * r; cv.height = h * r;
            var ctx = cv.getContext('2d');
            ctx.scale(r, r);
            var min = Math.min.apply(null, vals), max = Math.max.apply(null, vals), range = max - min || 1;
            var col = getComputedStyle(cv.closest('.kpi')).getPropertyValue('--kpi').trim() || '#2563EB';
            var pts = vals.map(function (v, i) { return [i / (vals.length - 1) * (w - 4) + 2, h - 4 - (v - min) / range * (h - 8)]; });
            ctx.beginPath();
            pts.forEach(function (p, i) { if (i) ctx.lineTo(p[0], p[1]); else ctx.moveTo(p[0], p[1]); });
            ctx.strokeStyle = col; ctx.lineWidth = 2; ctx.lineJoin = 'round'; ctx.stroke();
            ctx.lineTo(pts[pts.length - 1][0], h); ctx.lineTo(pts[0][0], h); ctx.closePath();
            ctx.globalAlpha = 0.14; ctx.fillStyle = col; ctx.fill(); ctx.globalAlpha = 1;
        });
    }

    function insightCard(i) {
        var m = LEVEL_META[i.level];
        return '<article class="insight insight--' + i.level + '" data-level="' + i.level + '">'
            + '<header class="insight__head"><span class="insight__icon">' + icon(i.icon) + '</span><span class="badge badge--' + i.level + '">' + icon(m.icon) + m.label + '</span>'
            + '<span class="prio prio--' + ({ 'สูง': 'high', 'กลาง': 'mid', 'ต่ำ': 'low' })[i.priority] + '">ความสำคัญ: ' + esc(i.priority) + '</span></header>'
            + '<h4 class="insight__title">' + esc(i.title) + '</h4>'
            + '<p class="insight__metric">' + esc(i.metric) + '</p>'
            + '<p class="insight__detail">' + esc(i.detail) + '</p>'
            + '<div class="insight__action"><p>' + icon('list-checks') + 'แนวทางดำเนินการ</p><ul>' + i.actions.map(function (a) { return '<li>' + esc(a) + '</li>'; }).join('') + '</ul></div>'
            + '<footer class="insight__foot"><button type="button" class="btn btn--soft btn--sm" data-insight="' + i.id + '">' + icon('eye') + 'รายละเอียด</button><a class="link" href="#' + i.view + '">ไปยังข้อมูล' + icon('arrow-right') + '</a></footer>'
            + '</article>';
    }

    function openInsight(id) {
        var i = state.model.insights.filter(function (x) { return x.id === id; })[0];
        if (!i) return;
        var m = LEVEL_META[i.level];
        openModal({
            eyebrow: '<span class="badge badge--' + i.level + '">' + m.label + '</span> <span class="tag-sim">AI Simulation</span>',
            title: i.title,
            html: '<div class="detail-stats"><div><small>ตัวชี้วัด</small><strong>' + esc(i.metric) + '</strong></div><div><small>ระดับความสำคัญ</small><strong>' + esc(i.priority) + '</strong></div><div><small>ประเภท</small><strong>' + m.th + '</strong></div></div>'
                + '<p class="lead">' + esc(i.detail) + '</p><h4 class="mini-title">' + icon('list-checks') + 'แนวทางดำเนินการที่แนะนำ</h4><ol class="steps">' + i.actions.map(function (a) { return '<li>' + esc(a) + '</li>'; }).join('') + '</ol>'
                + '<p class="note">ผลวิเคราะห์นี้สร้างจากกฎการวิเคราะห์บนข้อมูลจำลอง ควรใช้ประกอบการพิจารณาร่วมกับบริบทจริงของโรงเรียน</p>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ปิด</button><a class="btn btn--primary" href="#' + i.view + '" data-action="modal-close">' + icon('arrow-right') + 'ไปยังข้อมูล</a>'
        });
    }

    /* Overview ------------------------------------------------------------------ */
    function renderOverview(m) {
        log('View', 'overview', 'START');
        $('#ov-ai-summary').textContent = m.analysis.summary;
        $('#ov-ai-summary').classList.remove('skeleton-text');
        $('#ov-kpis').innerHTML = m.kpis.map(kpiCard).join('');
        drawSparks($('#ov-kpis'));

        var c = m.charts;
        CH.render('ov-att', {
            type: 'line',
            data: { labels: c.attendance.labels, datasets: [CH.line('อัตราการเข้าเรียน', c.attendance.values, 'primary'), CH.dashed('เป้าหมาย ' + c.attendance.target + '%', c.attendance.labels.map(function () { return c.attendance.target; }), 'amber')] },
            options: { scales: CH.axes({ min: Math.floor(Math.min.apply(null, c.attendance.values.concat([c.attendance.target])) - 2), max: 100, suffix: '%' }), plugins: { tooltip: CH.tooltip('%') } }
        });

        if (c.distribution) {
            CH.restore('ov-dist');
            CH.render('ov-dist', {
                type: 'doughnut',
                data: { labels: c.distribution.labels, datasets: [{ label: 'นักเรียน', data: c.distribution.values, backgroundColor: ['#16A34A', '#2563EB', '#F59E0B', '#DC2626'].map(function (x) { return x; }), borderColor: getComputedStyle(document.documentElement).getPropertyValue('--surface'), borderWidth: 3, hoverOffset: 8 }] },
                options: { cutout: '62%', interaction: { mode: 'nearest', intersect: true }, plugins: { legend: { position: 'bottom' }, tooltip: CH.tooltip(' คน', 0) } }
            });
            CH.restore('ov-acad');
            var acadSets = [];
            if (c.academic.previous) acadSets.push(CH.bar('ภาคเรียน ' + c.academic.prevKey, c.academic.previous, CH.alpha('sky', 0.55)));
            acadSets.push(CH.bar('ภาคเรียน ' + c.academic.termKey, c.academic.current, 'primary'));
            CH.render('ov-acad', { type: 'bar', data: { labels: c.academic.labels, datasets: acadSets }, options: { scales: CH.axes({ min: 50, max: 90 }), plugins: { tooltip: CH.tooltip(' คะแนน') } } });
        } else {
            CH.empty('ov-dist', 'ยังไม่มีข้อมูลภาคเรียน ' + esc(m.term.key));
            CH.empty('ov-acad', 'ยังไม่มีข้อมูลภาคเรียน ' + esc(m.term.key) + ' กรุณาเลือกภาคเรียนอื่น');
        }

        var subSets = [];
        if (c.submission.primary) subSets.push(CH.line('ประถมศึกษา', c.submission.primary, 'primary'));
        if (c.submission.secondary) subSets.push(CH.line('มัธยมศึกษา', c.submission.secondary, 'violet'));
        CH.render('ov-sub', { type: 'line', data: { labels: c.submission.labels, datasets: subSets }, options: { scales: CH.axes({ min: 40, max: 100, suffix: '%' }), plugins: { tooltip: CH.tooltip('%') } } });

        var rooms = c.rooms;
        CH.render('ov-rooms', {
            type: 'bar',
            data: { labels: rooms.map(function (r) { return r.name; }), datasets: [CH.bar('คะแนนภาพรวม', rooms.map(function (r) { return r.overall; }), rooms.map(function (r) { return r.overall >= 80 ? '#16A34A' : (r.overall >= 68 ? '#2563EB' : (r.overall >= 60 ? '#F59E0B' : '#DC2626')); }))] },
            options: {
                scales: CH.axes({ min: 40, max: 100 }), plugins: { legend: { display: false }, tooltip: CH.tooltip('/100') },
                onClick: function (e, els) { if (els.length) window.EXD.openRoom(rooms[els[0].index].id); },
                onHover: function (e, els) { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; }
            }
        });

        var devSets = [];
        if (c.development.primary) devSets.push(CH.line('ประถมศึกษา', c.development.primary, 'green'));
        if (c.development.secondary) devSets.push(CH.line('มัธยมศึกษา', c.development.secondary, 'violet'));
        CH.render('ov-dev', { type: 'line', data: { labels: c.development.labels, datasets: devSets }, options: { scales: CH.axes({ min: 50, max: 100 }), plugins: { tooltip: CH.tooltip(' คะแนน') } } });

        var top = m.insights.slice(0, 3);
        $('#ov-insights').innerHTML = top.length ? top.map(insightCard).join('') : '<div class="state">' + icon('party-popper') + '<p>ไม่พบประเด็นที่ต้องดำเนินการในขณะนี้</p></div>';
        icons($('#view-overview'));
        log('View', 'overview', 'END');
    }

    /* Filters, router & data flow ---------------------------------------------- */
    function syncRoomOptions(data) {
        var sel = $('#filter-room');
        var current = state.filters.room;
        var html = '<option value="all">ทุกห้องเรียน</option>';
        AI.GRADE_ORDER.forEach(function (g) {
            var rooms = data.classrooms.filter(function (r) { return r.grade === g; });
            if (!rooms.length) return;
            html += '<optgroup label="' + AI.GRADE_LABEL[g] + '"><option value="grade:' + g + '">ทั้งระดับชั้น ' + AI.GRADE_LABEL[g] + '</option>'
                + rooms.map(function (r) { return '<option value="room:' + r.id + '">ห้อง ' + r.name + '</option>'; }).join('') + '</optgroup>';
        });
        sel.innerHTML = html;
        var exists = $$('option', sel).some(function (o) { return o.value === current; });
        state.filters.room = exists ? current : 'all';
        sel.value = state.filters.room;
    }

    function recompute() {
        var data = STORE.get();
        if (!STORE.isValid(data)) throw new Error('Invalid dataset');
        state.model = AI.compute(data, state.filters);
        return state.model;
    }

    function updateBindings(m) {
        $$('[data-bind="scenario"]').forEach(function (el) { el.textContent = m.meta.scenarioLabel; });
        $$('[data-bind="school"]').forEach(function (el) { el.textContent = m.meta.school; });
        $$('[data-bind="scope-text"]').forEach(function (el) { el.textContent = m.scope.label + ' · ' + num(m.students.length) + ' คน · Scenario: ' + m.meta.scenarioTh; });
        $$('[data-bind="period-text"]').forEach(function (el) { el.textContent = m.scope.label + ' · ข้อมูลเดือนล่าสุด (ต.ค. 69) เทียบเดือนก่อน · ช่วงกราฟ ' + m.filters.range + ' เดือน'; });
        $$('[data-bind="term-text"]').forEach(function (el) { el.textContent = 'ระดับผลการเรียน ภาคเรียน ' + m.term.key; });

        var alerts = m.insights.filter(function (i) { return i.level === 'critical' || i.level === 'warning'; });
        $('#notif-count').textContent = alerts.length || '';
        $('#notif-list').innerHTML = alerts.length ? alerts.slice(0, 6).map(function (i) {
            return '<li><button type="button" class="notif" data-insight="' + i.id + '"><span class="notif__icon notif__icon--' + i.level + '">' + icon(i.icon) + '</span><span><strong>' + esc(i.title) + '</strong><small>' + LEVEL_META[i.level].label + ' · ความสำคัญ' + esc(i.priority) + '</small></span></button></li>';
        }).join('') : '<li class="state state--inline">' + icon('bell-off') + '<p>ไม่มีการแจ้งเตือน</p></li>';
        icons($('#notif-list'));
    }

    function renderCurrent() {
        log('UI', 'renderCurrent', 'START', { view: state.view, filters: state.filters });
        try {
            var m = recompute();
            $('#app-error').hidden = true;
            updateBindings(m);
            var fn = state.view === 'overview' ? renderOverview : views[state.view];
            if (fn) fn(m);
            icons($('#view-' + state.view));
        } catch (error) {
            logError('UI', 'renderCurrent', error, { view: state.view, filters: state.filters });
            $$('.view').forEach(function (v) { v.hidden = true; });
            $('#app-error').hidden = false;
        }
        log('UI', 'renderCurrent', 'END');
    }

    function navigate() {
        var id = (location.hash || '#overview').slice(1);
        var item = (CONFIG.menu || []).filter(function (x) { return x.id === id; })[0];
        if (!item) { id = 'overview'; item = CONFIG.menu[0]; }
        log('Router', 'navigate', 'view', { id: id });
        state.view = id;
        $$('.view').forEach(function (v) { v.hidden = v.getAttribute('data-view') !== id; });
        $$('[data-nav]').forEach(function (a) {
            var on = a.getAttribute('data-nav') === id;
            a.classList.toggle('is-active', on);
            if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
        });
        $('#page-title').textContent = item.label;
        document.title = item.label + ' · AI Executive Dashboard';
        $('#filters').hidden = ['mock', 'settings', 'reports'].indexOf(id) !== -1;
        setSidebar(false);
        renderCurrent();
        var content = $('#content');
        if (content) content.scrollTop = 0;
        window.scrollTo(0, 0);
    }

    function initFilters() {
        var saved = store('filters');
        if (saved) state.filters = Object.assign({}, DEFAULT_FILTERS, saved);
        $$('[data-filter]').forEach(function (sel) {
            var key = sel.getAttribute('data-filter');
            if (key !== 'room') sel.value = state.filters[key];
            sel.addEventListener('change', function () {
                state.filters[key] = sel.value;
                store('filters', state.filters);
                log('Filters', 'change', 'filter changed', { key: key, value: sel.value });
                renderCurrent();
                if (key === 'semester' || key === 'year') {
                    var m = state.model;
                    if (m && m.term.idx === null) toast('ยังไม่มีข้อมูลภาคเรียน ' + m.term.key + ' ส่วนผลการเรียนจะแสดงเป็นสถานะว่าง', 'warning');
                }
            });
        });
    }

    function resetFilters() {
        state.filters = Object.assign({}, DEFAULT_FILTERS);
        store('filters', state.filters);
        $$('[data-filter]').forEach(function (sel) { sel.value = state.filters[sel.getAttribute('data-filter')]; });
        renderCurrent();
        toast('ล้างตัวกรองแล้ว', 'info');
    }

    /* Sidebar, theme, dropdowns --------------------------------------------- */
    function setSidebar(open) {
        $('#sidebar').classList.toggle('is-open', open);
        $('.backdrop').hidden = !open;
        document.body.classList.toggle('is-locked', open);
    }
    function toggleCollapse(force) {
        var on = force === undefined ? !document.documentElement.classList.contains('sb-collapsed') : force;
        document.documentElement.classList.toggle('sb-collapsed', on);
        try { localStorage.setItem('exd.sidebar', on ? 'collapsed' : 'expanded'); } catch (e) { /* ignore */ }
        var sw = $('#set-sidebar');
        if (sw) sw.checked = on;
        setTimeout(function () { Object.keys(CH.instances).forEach(function (k) { CH.instances[k].resize(); }); }, 320);
    }
    function setTheme(mode) {
        var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        try { if (mode === 'system') localStorage.removeItem('exd.theme'); else localStorage.setItem('exd.theme', mode); } catch (e) { /* ignore */ }
        $$('[data-theme-set]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-theme-set') === mode); });
        CH.applyTheme();
        renderCurrent();
        log('UI', 'setTheme', 'theme changed', { mode: mode, dark: dark });
    }
    function currentThemeMode() {
        try { return localStorage.getItem('exd.theme') || 'system'; } catch (e) { return 'system'; }
    }

    function initDropdowns() {
        document.addEventListener('click', function (e) {
            var toggle = e.target.closest('[data-dropdown-toggle]');
            $$('[data-dropdown]').forEach(function (dd) {
                if (toggle && dd.contains(toggle)) {
                    var open = !dd.classList.contains('is-open');
                    dd.classList.toggle('is-open', open);
                    toggle.setAttribute('aria-expanded', String(open));
                } else if (!e.target.closest('[data-dropdown]') || !dd.contains(e.target) || e.target.closest('a, .notif, .dropdown__item')) {
                    dd.classList.remove('is-open');
                }
            });
        });
    }

    /* Mock data generator --------------------------------------------------- */
    function openGenerator() {
        var sc = CONFIG.scenarios;
        var current = STORE.get() ? STORE.get().meta.scenario : 'default';
        var opts = [
            ['default', 'Generate Default Dataset'], ['large', 'Generate Large Dataset'], ['excellent', 'Generate Excellent School'],
            ['average', 'Generate Average School'], ['at-risk', 'Generate At-Risk School']
        ];
        var html = '<p class="lead">เลือกรูปแบบข้อมูลจำลอง ระบบจะอัปเดต KPI กราฟ ตาราง AI Insights การประเมินครู และรายงานทั้งหมดจากข้อมูลชุดเดียวกัน</p><div class="gen-list">'
            + opts.map(function (o) {
                var s = sc[o[0]];
                return '<button type="button" class="gen-opt' + (o[0] === current ? ' is-current' : '') + '" data-generate="' + o[0] + '"><span class="gen-opt__icon gen-opt__icon--' + o[0] + '">' + icon(s.icon) + '</span><span class="gen-opt__text"><strong>' + o[1] + '</strong><small>' + esc(s.th) + ' — ' + esc(s.desc) + '</small></span>' + (o[0] === current ? '<span class="badge">ใช้งานอยู่</span>' : icon('chevron-right')) + '</button>';
            }).join('')
            + '<button type="button" class="gen-opt gen-opt--reset" data-action="reset"><span class="gen-opt__icon">' + icon('rotate-ccw') + '</span><span class="gen-opt__text"><strong>Reset Data</strong><small>กลับไปใช้ Default Dataset (ล้างข้อมูลใน LocalStorage)</small></span>' + icon('chevron-right') + '</button></div>';
        openModal({ eyebrow: icon('wand-sparkles') + ' Mock Data Generator', title: 'สร้างข้อมูลจำลอง', html: html });
    }

    function runGenerate(scenario, seed, label) {
        log('MockData', 'runGenerate', 'START', { scenario: scenario, seed: seed });
        var steps = ['สร้างโครงสร้างห้องเรียนและครู', 'สร้างข้อมูลนักเรียน ผลการเรียน และการเข้าเรียน', 'คำนวณ KPI และกราฟทั้งหมด', 'AI Simulation วิเคราะห์ข้อมูลชุดใหม่'];
        openModal({
            eyebrow: icon('loader-circle', 'spin') + ' กำลังดำเนินการ',
            title: label || 'กำลังสร้างข้อมูลจำลอง',
            html: '<ul class="progress-steps">' + steps.map(function (s, i) { return '<li data-step="' + i + '">' + icon('circle') + '<span>' + s + '</span></li>'; }).join('') + '</ul><div class="progress"><span id="gen-bar"></span></div>'
        });
        var i = 0;
        var timer = setInterval(function () {
            var li = $('.progress-steps [data-step="' + i + '"]');
            if (li) { li.classList.add('is-done'); }
            var bar = $('#gen-bar');
            if (bar) bar.style.width = Math.min(90, (i + 1) * 22) + '%';
            i++;
            if (i >= steps.length) clearInterval(timer);
        }, 260);
        var started = Date.now();

        return STORE.generate(scenario, seed).then(function (data) {
            var wait = Math.max(0, 1150 - (Date.now() - started));
            setTimeout(function () {
                clearInterval(timer);
                closeModal();
                toast('สร้างข้อมูลสำเร็จ: ' + data.meta.scenarioLabel + ' (seed ' + data.meta.seed + ')', 'success');
                log('MockData', 'runGenerate', 'END', { scenario: scenario, seed: data.meta.seed });
            }, wait);
        }).catch(function (error) {
            clearInterval(timer);
            logError('MockData', 'runGenerate', error, { scenario: scenario, seed: seed });
            closeModal();
            toast(error.userMessage || 'ไม่สามารถสร้างข้อมูลได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง', 'error');
        });
    }

    function confirmReset() {
        openModal({
            eyebrow: icon('rotate-ccw') + ' Reset Data',
            title: 'กลับไปใช้ Default Dataset?',
            html: '<p class="lead">ระบบจะล้างชุดข้อมูลจำลองที่เก็บใน LocalStorage แล้วกลับไปใช้ข้อมูลเริ่มต้น (นักเรียน 600 คน ครู 35 คน 18 ห้อง)</p>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ยกเลิก</button><button type="button" class="btn btn--danger" data-action="confirm-reset">' + icon('rotate-ccw') + 'Reset to Default</button>'
        });
    }

    /* Global click handling -------------------------------------------------- */
    function initActions() {
        document.addEventListener('click', function (e) {
            var gen = e.target.closest('[data-generate]');
            if (gen) {
                var key = gen.getAttribute('data-generate');
                runGenerate(key, CONFIG.defaultSeed, 'กำลังสร้าง ' + CONFIG.scenarios[key].label);
                return;
            }
            var ins = e.target.closest('[data-insight]');
            if (ins) { openInsight(ins.getAttribute('data-insight')); return; }
            var act = e.target.closest('[data-action]');
            if (!act) return;
            var a = act.getAttribute('data-action');
            log('UI', 'action', a);
            switch (a) {
                case 'modal-close': closeModal(); break;
                case 'sidebar-open': setSidebar(true); break;
                case 'sidebar-close': setSidebar(false); break;
                case 'sidebar-collapse': toggleCollapse(); break;
                case 'toggle-theme': setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); break;
                case 'open-generator': openGenerator(); break;
                case 'reset': confirmReset(); break;
                case 'confirm-reset':
                    closeModal();
                    STORE.reset();
                    toast('รีเซ็ตกลับเป็น Default Dataset แล้ว', 'success');
                    break;
                case 'randomize': {
                    var cur = STORE.get().meta.scenario;
                    runGenerate(cur, STORE.randomSeed(), 'กำลังสุ่มข้อมูลใหม่ (' + CONFIG.scenarios[cur].label + ')');
                    break;
                }
                case 'reset-filters': resetFilters(); break;
                default:
                    if (window.EXD.actions[a]) window.EXD.actions[a](act, e);
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeModal(); setSidebar(false); $$('[data-dropdown]').forEach(function (d) { d.classList.remove('is-open'); }); }
        });
    }

    /* Public API for views.js / reports.js ---------------------------------- */
    window.EXD = {
        state: state, views: views, actions: {},
        log: log, logError: logError, esc: esc, icon: icon, icons: icons, num: num, $: $, $$: $$,
        toast: toast, openModal: openModal, closeModal: closeModal, renderTable: renderTable,
        insightCard: insightCard, kpiCard: kpiCard, drawSparks: drawSparks, deltaBadge: deltaBadge,
        renderCurrent: renderCurrent, runGenerate: runGenerate, setTheme: setTheme, currentThemeMode: currentThemeMode, toggleCollapse: toggleCollapse,
        openRoom: function (id) { if (window.EXD.actions.openRoom) window.EXD.actions.openRoom(id); },
        setPageSize: function (n) { state.pageSize = n; store('pageSize', n); renderCurrent(); },
        levelMeta: LEVEL_META
    };

    function init() {
        log('UI', 'init', 'START');
        try {
            var size = store('pageSize');
            if (size) state.pageSize = size;
            CH.setReducedMotion(store('reducedMotion') === true);
            CH.applyTheme();
            var data = STORE.load();
            if (!STORE.isValid(data)) throw new Error('No dataset available');
            syncRoomOptions(data);
            initFilters();
            initDropdowns();
            initActions();
            STORE.onChange(function (d) { syncRoomOptions(d); Object.keys(state.tables).forEach(function (k) { state.tables[k].page = 1; }); renderCurrent(); });
            window.addEventListener('hashchange', navigate);
            navigate();
            icons();

            // Shareable demo links: ?scenario=at-risk[&seed=123] opens that dataset directly.
            var params = new URLSearchParams(location.search);
            var wanted = params.get('scenario');
            if (wanted && CONFIG.scenarios[wanted] && (data.meta.scenario !== wanted || (params.get('seed') && String(data.meta.seed) !== params.get('seed')))) {
                runGenerate(wanted, parseInt(params.get('seed'), 10) || CONFIG.defaultSeed, 'กำลังเปิด ' + CONFIG.scenarios[wanted].label);
            }
        } catch (error) {
            logError('UI', 'init', error);
            $('#app-error').hidden = false;
        }
        var loader = $('#loader');
        if (loader) { loader.classList.add('is-hidden'); setTimeout(function () { loader.remove(); }, 450); }
        log('UI', 'init', 'END', { view: state.view, source: STORE.source() });
    }

    // Run after views.js / reports.js have registered their renderers.
    document.addEventListener('DOMContentLoaded', init);
})();
