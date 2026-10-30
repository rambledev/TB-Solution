/**
 * AI School Intelligence — page interactions: detail modals, teacher evaluation,
 * KPI selector, report preview/print, settings and filters.
 */
(function () {
    'use strict';

    var AIS = window.AIS;
    var esc = AIS.escapeHtml;
    var icon = AIS.icon;

    function list(items) {
        return '<ul class="dot-list">' + (items || []).map(function (i) { return '<li>' + esc(i) + '</li>'; }).join('') + '</ul>';
    }

    /* Auto-submit filter forms ------------------------------------------- */
    function initAutoSubmit() {
        document.querySelectorAll('form[data-autosubmit]').forEach(function (form) {
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () {
                    AIS.log('Pages', 'autoSubmit', 'filter changed', { name: select.name, value: select.value });
                    if (select.name === 'level') {
                        var grade = form.querySelector('select[name="grade"]');
                        if (grade) grade.value = '';
                    }
                    form.submit();
                });
            });
        });
    }

    /* Row navigation ------------------------------------------------------ */
    function initRowClicks() {
        document.addEventListener('click', function (event) {
            var row = event.target.closest('tr[data-row-click]');
            if (!row || event.target.closest('a, button, input, select')) return;
            if (row.hasAttribute('data-href')) {
                window.location.href = row.getAttribute('data-href');
            } else if (row.hasAttribute('data-student')) {
                openStudent(row.getAttribute('data-student'));
            }
        });
    }

    /* Insights ------------------------------------------------------------- */
    function openInsight(id) {
        var insights = AIS.readJson('ais-insights') || [];
        var insight = insights.filter(function (i) { return i.id === id; })[0];
        if (!insight) {
            AIS.toast('ไม่พบข้อมูล', 'info');
            return;
        }
        AIS.log('Pages', 'openInsight', 'START', { id: id });
        var priority = { high: 'สำคัญมาก', medium: 'ควรดำเนินการ', low: 'เฝ้าติดตาม' }[insight.priority];
        var html = '<div class="stat-row" style="grid-template-columns:repeat(2,minmax(0,1fr))">'
            + '<div><small>ตัวชี้วัด</small><strong>' + esc(insight.metric) + '</strong><small>' + esc(insight.metric_label) + '</small></div>'
            + '<div><small>ระดับความสำคัญ</small><strong><span class="badge badge--' + insight.priority + '">' + esc(priority) + '</span></strong></div>'
            + '</div>'
            + '<p style="margin-bottom:14px">' + esc(insight.summary) + '</p>'
            + (insight.chart ? '<div class="chart-box" style="--chart-h:220px;margin-bottom:14px"><canvas id="modal-chart"></canvas></div>' : '')
            + '<div class="detail-grid">'
            + '<div class="detail-box"><h4>' + icon('database', 'icon-xs') + 'ข้อมูลสนับสนุน</h4>' + list(insight.evidence) + '</div>'
            + '<div class="detail-box"><h4>' + icon('search', 'icon-xs') + 'สาเหตุที่เป็นไปได้</h4>' + list(insight.causes) + '</div>'
            + '<div class="detail-box" style="grid-column:1/-1"><h4>' + icon('lightbulb', 'icon-xs') + 'ข้อเสนอแนะ</h4><ol class="timeline">'
            + insight.actions.map(function (a, i) { return '<li data-step="' + (i + 1) + '">' + esc(a) + '</li>'; }).join('') + '</ol></div>'
            + '</div>'
            + '<p class="chart-note">สร้างจากกฎการวิเคราะห์ (Rule-based) บนข้อมูลจำลอง — ควรใช้ประกอบการพิจารณาร่วมกับข้อมูลบริบทของโรงเรียน</p>';
        AIS.openModal({
            eyebrow: 'AI Insight',
            title: insight.title,
            html: html,
            wide: true,
            foot: '<a class="btn btn--ghost" href="./?page=ai-insights&ask=' + encodeURIComponent('โรงเรียนควรปรับปรุงเรื่องใดก่อน') + '">' + icon('bot') + 'ถาม AI ต่อ</a>'
                + '<a class="btn btn--primary" href="' + esc(insight.link) + '">' + icon('arrow-right') + 'ไปยังข้อมูล</a>',
            onOpen: function () {
                if (insight.chart && AIS.charts) AIS.charts.create(document.getElementById('modal-chart'), insight.chart);
            }
        });
    }

    function initInsights() {
        document.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-insight-open]');
            if (btn) openInsight(btn.getAttribute('data-insight-open'));
        });
        var chips = document.querySelectorAll('[data-insight-filter]');
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                var value = chip.getAttribute('data-insight-filter');
                chips.forEach(function (c) {
                    c.classList.toggle('is-active', c === chip);
                    c.setAttribute('aria-pressed', String(c === chip));
                });
                document.querySelectorAll('[data-insight-card]').forEach(function (card) {
                    card.classList.toggle('is-hidden', value !== '*' && card.getAttribute('data-priority') !== value);
                });
                AIS.log('Pages', 'insightFilter', 'changed', { value: value });
            });
        });
        var params = new URLSearchParams(window.location.search);
        if (params.get('insight')) openInsight(params.get('insight'));
    }

    /* Student support plan modal ------------------------------------------ */
    function openStudent(id) {
        var data = AIS.readJson('student-data') || {};
        var s = data[id];
        if (!s) return;
        AIS.log('Pages', 'openStudent', 'START', { id: id });
        var academic = s.gpa !== null
            ? '<div><small>GPA</small><strong>' + Number(s.gpa).toFixed(2) + '</strong><small>ก่อนหน้า ' + Number(s.gpa_prev).toFixed(2) + '</small></div>'
            : '<div><small>คะแนนพัฒนาการ</small><strong>' + s.development + '</strong><small>/100</small></div>';
        var html = '<div class="stat-row">'
            + '<div><small>อัตราเข้าเรียน</small><strong>' + Number(s.attendance).toFixed(1) + '%</strong><small>ขาด ' + s.absent + ' วัน</small></div>'
            + academic
            + '<div><small>ส่งงาน</small><strong>' + s.submission + '%</strong></div>'
            + '<div><small>บันทึกการดูแล</small><strong>' + s.notes + '</strong><small>ครั้ง</small></div>'
            + '</div>'
            + '<div class="detail-grid">'
            + '<div class="detail-box"><h4>' + icon('scan-search', 'icon-xs') + 'สาเหตุที่ตรวจพบ</h4>' + (s.factors.length ? list(s.factors) : '<p class="muted">ไม่พบปัจจัยที่น่ากังวล</p>') + '</div>'
            + '<div class="detail-box"><h4>' + icon('user-round', 'icon-xs') + 'ผู้รับผิดชอบหลัก</h4><p>' + esc(s.homeroom) + '<br><small class="muted">ครูประจำชั้น ' + esc(s.room) + '</small></p></div>'
            + '<div class="detail-box" style="grid-column:1/-1"><h4>' + icon('hand-heart', 'icon-xs') + 'แนวทางช่วยเหลือที่แนะนำ</h4>'
            + (s.plan.length ? '<ol class="timeline">' + s.plan.map(function (p, i) { return '<li data-step="' + (i + 1) + '">' + esc(p) + '</li>'; }).join('') + '</ol>' : '<p class="muted">ติดตามตามปกติ</p>') + '</div>'
            + '</div>'
            + '<p class="chart-note">ข้อมูลนี้ใช้เพื่อการดูแลช่วยเหลือ ควรเปิดเผยเฉพาะผู้เกี่ยวข้อง · ชื่อเป็นชื่อสมมติ</p>';
        AIS.openModal({
            eyebrow: esc(s.code) + ' · ' + s.risk_label,
            title: s.name + ' (' + s.room + ')',
            html: html,
            foot: '<button type="button" class="btn btn--ghost" data-modal-close>ปิด</button><button type="button" class="btn btn--primary" data-coming-soon="การบันทึกแผนช่วยเหลือจะเปิดใช้งานพร้อมระบบ Login (Coming Soon)">' + icon('clipboard-plus') + 'บันทึกการติดตาม</button>'
        });
    }

    function initStudents() {
        document.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-student-open]');
            if (btn) openStudent(btn.getAttribute('data-student-open'));
        });
    }

    /* Teacher evaluation ---------------------------------------------------- */
    function initEvaluation() {
        var root = document.querySelector('[data-eval]');
        if (!root) return;
        AIS.log('Pages', 'initEvaluation', 'START');
        var payload = AIS.readJson('eval-data') || { teachers: {} };
        var teachers = payload.teachers;
        var listEl = root.querySelector('[data-eval-list]');
        var detail = root.querySelector('[data-eval-detail]');
        var search = root.querySelector('[data-eval-search]');
        var dept = root.querySelector('[data-eval-dept]');
        var sort = root.querySelector('[data-eval-sort]');

        function ring(score, tone) {
            var c = 2 * Math.PI * 44;
            var color = { good: '#22A06B', primary: '#2878E8', neutral: '#7DB3F5', warn: '#F0A030' }[tone] || '#2878E8';
            return '<div class="score-ring"><svg viewBox="0 0 104 104"><circle cx="52" cy="52" r="44" fill="none" stroke="#EAF1FB" stroke-width="10"/>'
                + '<circle cx="52" cy="52" r="44" fill="none" stroke="' + color + '" stroke-width="10" stroke-linecap="round" stroke-dasharray="' + (c * score / 100).toFixed(1) + ' ' + c.toFixed(1) + '"/></svg>'
                + '<div class="score-ring__value"><strong>' + score.toFixed(1) + '</strong><small>/100</small></div></div>';
        }

        function render(id, fromUser) {
            var t = teachers[id];
            if (!t) return;
            AIS.log('Pages', 'renderTeacher', 'START', { id: id });
            listEl.querySelectorAll('.teacher-item').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-eval-select') === id); });
            var lowData = t.completeness < 80;
            var dims = t.dimensions.map(function (d) {
                var tone = d.score >= 85 ? 'green' : (d.score < 70 ? 'orange' : '');
                return '<li><span>' + esc(d.label) + ' <small class="muted">(' + d.weight + '%)</small></span><div class="bar ' + (tone ? 'bar--' + tone : '') + '"><span style="width:' + d.score + '%"></span><i style="left:' + d.avg + '%" title="ค่าเฉลี่ยโรงเรียน ' + d.avg + '"></i></div><strong class="num">' + d.score + '</strong></li>';
            }).join('');
            var sources = t.sources.map(function (s) {
                return '<li><span>' + esc(s.label) + '<br><small class="muted">' + esc(s.dimension) + '</small></span><span class="badge badge--' + (s.complete >= 80 ? 'good' : 'warn') + '">' + s.complete + '%</span></li>';
            }).join('');

            detail.innerHTML = ''
                + '<article class="card" style="margin-bottom:var(--gap)"><div class="eval-profile">'
                + '<span class="eval-profile__avatar">' + esc(t.short.replace('ครู', '').charAt(0)) + '</span>'
                + '<div class="eval-profile__text"><h3>' + esc(t.name) + '</h3><p>' + esc(t.position) + ' · กลุ่มสาระ' + esc(t.dept_label) + ' · ประสบการณ์ ' + t.years + ' ปี</p>'
                + '<p>สอน ' + esc(t.classes.join(', ')) + (t.homeroom ? ' · ครูประจำชั้น ' + esc(t.homeroom) : '') + '</p>'
                + '<div class="gauge__meta"><span class="badge badge--' + t.band.tone + '">' + esc(t.band.label) + '</span>' + (lowData ? '<span class="badge badge--warn">' + icon('triangle-alert') + 'ข้อมูลไม่ครบถ้วน</span>' : '<span class="badge badge--good">' + icon('circle-check') + 'ข้อมูลครบถ้วน ' + t.completeness + '%</span>') + '</div></div>'
                + ring(t.overall, t.band.tone) + '</div></article>'
                + (lowData ? '<div class="ai-disclaimer" style="margin-bottom:var(--gap)">' + icon('triangle-alert') + '<div><strong>ยังไม่ควรใช้สรุปผล:</strong> ข้อมูลครบถ้วนเพียง ' + t.completeness + '% ระบบแนะนำให้เก็บข้อมูลเพิ่มเติมก่อนนำไปใช้ประกอบการตัดสินใจ</div></div>' : '')
                + '<div class="stat-row">'
                + '<div><small>ชั่วโมงสอน</small><strong>' + t.hours + '</strong><small> ชม./สัปดาห์ (เฉลี่ย ' + payload.avgHours + ')</small></div>'
                + '<div><small>งานพิเศษ / ภาระรวม</small><strong>' + t.duties + ' งาน</strong><small> ' + (t.load_vs_avg > 0 ? '+' : '') + t.load_vs_avg + '% จากค่าเฉลี่ย</small></div>'
                + '<div><small>บันทึกการสอน</small><strong>' + t.lesson_log + '%</strong><small> ติดตามงาน ' + t.followup + '%</small></div>'
                + '<div><small>อบรม / ผลงาน</small><strong>' + t.training + ' ชม.</strong><small> ' + t.works + ' ผลงาน</small></div>'
                + '</div>'
                + '<section class="bento">'
                + '<article class="span-6 card"><header class="card__head"><div><h3 class="card__title">ผลการวิเคราะห์แยกตามมิติ</h3><p class="card__sub">เส้นทึบ = ค่าเฉลี่ยโรงเรียน · (น้ำหนัก)</p></div></header><ul class="dim-list">' + dims + '</ul></article>'
                + '<article class="span-6 card"><header class="card__head"><div><h3 class="card__title">ภาพรวม 9 มิติ</h3><p class="card__sub">เทียบค่าเฉลี่ยโรงเรียน</p></div></header><div class="chart-box" style="--chart-h:300px"><canvas id="eval-radar"></canvas></div></article>'
                + '<article class="span-6 card"><h3 class="card__title" style="margin-bottom:10px">' + icon('thumbs-up', 'icon-xs') + ' จุดแข็ง</h3><ul class="check-list">' + t.strengths.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul>'
                + '<h3 class="card__title" style="margin:16px 0 10px">' + icon('sprout', 'icon-xs') + ' จุดที่ควรพัฒนา</h3>' + list(t.develop) + '</article>'
                + '<article class="span-6 card card--ai"><div class="ai-title" style="margin-bottom:12px"><span class="ai-orb">' + icon('sparkles') + '</span><div><h3 class="card__title">ข้อเสนอแนะจาก AI</h3><p class="card__sub">เพื่อการพัฒนา ไม่ใช่การตัดสิน</p></div></div><ul class="check-list">'
                + t.suggestions.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul></article>'
                + '<article class="span-6 card"><header class="card__head"><div><h3 class="card__title">ประวัติการประเมิน</h3><p class="card__sub">คะแนนหลายมิติ 3 ภาคเรียน</p></div></header><div class="chart-box" style="--chart-h:200px"><canvas id="eval-history"></canvas></div></article>'
                + '<article class="span-6 card"><header class="card__head"><div><h3 class="card__title">แหล่งข้อมูลและความครบถ้วน</h3><p class="card__sub">ความครบถ้วนรวม ' + t.completeness + '%</p></div></header>'
                + '<div class="completeness" style="margin-bottom:8px"><div class="bar ' + (lowData ? 'bar--orange' : 'bar--green') + '"><span style="width:' + t.completeness + '%"></span></div><strong>' + t.completeness + '%</strong></div><ul class="source-list">' + sources + '</ul></article>'
                + '<article class="span-12 card"><h3 class="card__title" style="margin-bottom:10px">' + icon('info', 'icon-xs') + ' ข้อจำกัดในการประเมิน</h3>' + list(t.limitations)
                + '<p class="chart-note">ผลสัมฤทธิ์ของนักเรียนมีน้ำหนัก 12% จาก 9 มิติ ระบบไม่ใช้ผลการเรียนของนักเรียนเพียงอย่างเดียวในการวิเคราะห์ครู</p></article>'
                + '</section>';
            AIS.renderIcons(detail);

            var labels = t.dimensions.map(function (d) { return d.label.length > 14 ? d.label.substring(0, 14) + '…' : d.label; });
            AIS.charts.create(document.getElementById('eval-radar'), {
                type: 'radar', labels: labels,
                datasets: [
                    { label: t.short, data: t.dimensions.map(function (d) { return d.score; }), color: 'primary', fill: true },
                    { label: 'ค่าเฉลี่ยโรงเรียน', data: t.dimensions.map(function (d) { return d.avg; }), color: 'yellow', dashed: true }
                ],
                options: { yMin: 40, yMax: 100, legend: true, decimals: 0 }
            });
            AIS.charts.create(document.getElementById('eval-history'), {
                type: 'line', labels: Object.keys(t.history).map(function (k) { return 'ภาคเรียน ' + k; }),
                datasets: [{ label: 'คะแนนหลายมิติ', data: Object.values(t.history), color: 'primary', fill: true }],
                options: { decimals: 1, yMin: 60, yMax: 100 }
            });
            if (window.location.hash !== '#' + id) history.replaceState(null, '', '#' + id);
            if (fromUser && window.innerWidth < 1100) detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
            AIS.log('Pages', 'renderTeacher', 'END', { id: id });
        }

        function filterList() {
            var q = search.value.trim().toLowerCase();
            var d = dept.value;
            var items = Array.prototype.slice.call(listEl.children);
            items.forEach(function (li) {
                li.hidden = (q && li.getAttribute('data-name').toLowerCase().indexOf(q) === -1) || (d && li.getAttribute('data-dept') !== d);
            });
            var mode = sort.value;
            items.sort(function (a, b) {
                if (mode === 'score-desc') return b.getAttribute('data-score') - a.getAttribute('data-score');
                if (mode === 'score-asc') return a.getAttribute('data-score') - b.getAttribute('data-score');
                if (mode === 'load') return b.getAttribute('data-load') - a.getAttribute('data-load');
                return (a.getAttribute('data-dept') + a.getAttribute('data-id')).localeCompare(b.getAttribute('data-dept') + b.getAttribute('data-id'), 'th');
            }).forEach(function (li) { listEl.appendChild(li); });
        }

        listEl.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-eval-select]');
            if (btn) render(btn.getAttribute('data-eval-select'), true);
        });
        search.addEventListener('input', filterList);
        dept.addEventListener('change', filterList);
        sort.addEventListener('change', filterList);

        var initial = window.location.hash.replace('#', '');
        render(teachers[initial] ? initial : Object.keys(teachers)[0]);
        AIS.log('Pages', 'initEvaluation', 'END', { teachers: Object.keys(teachers).length });
    }

    /* Performance KPI selector ---------------------------------------------- */
    function initKpiSelector() {
        var buttons = document.querySelectorAll('[data-kpi-select]');
        if (!buttons.length) return;
        var canvas = document.getElementById('chart-kpi-monthly');
        var caption = document.querySelector('[data-kpi-caption]');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-kpi-select');
                buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                if (canvas && canvas.$spec) {
                    AIS.charts.create(canvas, AIS.charts.viewSpec(canvas.$spec, key));
                    if (caption) caption.textContent = canvas.$spec.views[key].label;
                }
                AIS.log('Pages', 'kpiSelect', 'selected', { key: key });
            });
        });
    }

    /* Reports ------------------------------------------------------------- */
    function printReport(type) {
        AIS.log('Pages', 'printReport', 'START', { type: type });
        var frame = document.getElementById('print-frame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'print-frame';
            frame.style.cssText = 'position:fixed;width:0;height:0;border:0;right:0;bottom:0';
            frame.setAttribute('aria-hidden', 'true');
            document.body.appendChild(frame);
        }
        AIS.toast('กำลังเตรียมรายงานสำหรับพิมพ์…', 'printer');
        frame.onload = function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (error) {
                AIS.logError('Pages', 'printReport', error, { type: type });
                window.open('report.php?type=' + encodeURIComponent(type) + '&print=1', '_blank');
            }
        };
        frame.src = 'report.php?type=' + encodeURIComponent(type) + '&embed=1';
    }

    function initReports() {
        document.addEventListener('click', function (event) {
            var preview = event.target.closest('[data-report-preview]');
            if (preview) {
                var type = preview.getAttribute('data-report-preview');
                AIS.openModal({
                    eyebrow: 'Report Preview',
                    title: preview.getAttribute('data-report-title'),
                    wide: true,
                    html: '<iframe class="modal__frame" src="report.php?type=' + encodeURIComponent(type) + '&embed=1" title="ตัวอย่างรายงาน"></iframe>',
                    foot: '<button type="button" class="btn btn--ghost" data-modal-close>ปิด</button>'
                        + '<button type="button" class="btn btn--ghost" data-report-print="' + esc(type) + '">' + icon('printer') + 'Print</button>'
                        + '<a class="btn btn--primary" href="report.php?type=' + encodeURIComponent(type) + '&print=1" target="_blank" rel="noopener">' + icon('download') + 'Export PDF</a>'
                });
                return;
            }
            var print = event.target.closest('[data-report-print]');
            if (print) printReport(print.getAttribute('data-report-print'));
        });
    }

    /* Settings ------------------------------------------------------------- */
    function initSettings() {
        var sidebar = document.querySelector('[data-pref="sidebar"]');
        if (!sidebar) return;
        sidebar.checked = document.documentElement.classList.contains('sidebar-collapsed');
        sidebar.addEventListener('change', function () {
            document.documentElement.classList.toggle('sidebar-collapsed', sidebar.checked);
            try { localStorage.setItem('ais.sidebar', sidebar.checked ? 'collapsed' : 'expanded'); } catch (e) { /* ignore */ }
            AIS.toast('บันทึกการตั้งค่าแล้ว', 'check');
        });
        var privacy = document.querySelector('[data-pref="privacy"]');
        privacy.checked = AIS.store.get('privacy', false);
        privacy.addEventListener('change', function () {
            AIS.store.set('privacy', privacy.checked);
            AIS.toast('บันทึกการตั้งค่าแล้ว', 'check');
        });
        var period = document.querySelector('[data-pref="period"]');
        period.addEventListener('change', function () {
            window.location.href = './?page=settings&period=' + encodeURIComponent(period.value);
        });
    }

    function init() {
        AIS.log('Pages', 'init', 'START', { page: AIS.config.page });
        try {
            initAutoSubmit();
            initRowClicks();
            initInsights();
            initStudents();
            initEvaluation();
            initKpiSelector();
            initReports();
            initSettings();
        } catch (error) {
            AIS.logError('Pages', 'init', error);
        }
        AIS.log('Pages', 'init', 'END');
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
