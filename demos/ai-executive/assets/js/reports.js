/**
 * AI Executive Dashboard — AI Executive Report: formal 8-section report built from the
 * same dataset, with Print / Export PDF (print CSS) and Export Excel (SheetJS).
 */
(function () {
    'use strict';

    var X = window.EXD;
    var AI = window.EXD_AI;
    var STORE = window.EXD_STORE;
    var esc = X.esc, num = X.num, $ = X.$, $$ = X.$$;
    var opts = { range: '6', year: '2569', semester: '1' };
    var OWNER = { attendance: 'ฝ่ายกิจการนักเรียน', students: 'ฝ่ายวิชาการ / ครูประจำชั้น', classrooms: 'ฝ่ายวิชาการ', teachers: 'ฝ่ายบริหารงานบุคคล' };
    var TIMELINE = { critical: 'ภายใน 2 สัปดาห์', warning: 'ภายใน 1 เดือน', recommendation: 'ภาคเรียน 2/2569', opportunity: 'ภาคเรียน 2/2569' };

    function model() {
        return AI.compute(STORE.get(), { range: opts.range, year: opts.year, semester: opts.semester, room: 'all' });
    }

    function actionPlan(m) {
        return m.insights.filter(function (i) { return i.level !== 'opportunity'; }).slice(0, 8).map(function (i, n) {
            return { no: n + 1, issue: i.title, action: i.actions[0], owner: OWNER[i.view] || 'ฝ่ายบริหาร', timeline: TIMELINE[i.level], priority: i.priority };
        });
    }

    function table(head, rows) {
        return '<table class="rtable"><thead><tr>' + head.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr></thead><tbody>'
            + rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody></table>';
    }

    function render() {
        X.log('Report', 'render', 'START', opts);
        var m = model();
        var a = m.analysis;
        var t = m.teachers;
        var termText = m.term.idx === null ? 'ภาคเรียน ' + m.term.key + ' (ยังไม่มีข้อมูล ใช้ข้อมูลล่าสุด ' + m.meta.terms[2] + ')' : 'ภาคเรียน ' + m.term.key;
        var rangeText = opts.range === '1' ? 'เดือนล่าสุด' : opts.range + ' เดือนล่าสุด';
        var rooms = m.rooms.slice().sort(function (x, y) { return y.overall - x.overall; });
        var risk = m.students.filter(function (s) { return s.risk !== 'low'; });
        var high = m.students.filter(function (s) { return s.risk === 'high'; });
        var monthIdx = [0, 1, 2, 3, 4, 5].slice(6 - parseInt(opts.range, 10));
        var subjects = m.charts.subjects;
        var plan = actionPlan(m);
        var today = new Date().toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' });

        var html = '<header class="report__head"><div class="report__brand"><span class="report__logo">TB</span><div><strong>' + esc(m.meta.school) + '</strong><small>AI Executive Dashboard · TB-Solution</small></div></div>'
            + '<div class="report__meta">ปีการศึกษา ' + esc(opts.year) + ' · ' + esc(termText) + '<br>ช่วงข้อมูลการเข้าเรียน: ' + esc(rangeText) + '<br>จัดทำวันที่ ' + esc(today) + '<br><span class="tag-sim">Mock Data · ' + esc(m.meta.scenarioLabel) + '</span></div></header>'
            + '<h1 class="report__title">รายงานผลการดำเนินงานสำหรับผู้บริหารสถานศึกษา</h1><p class="report__sub">เสนอต่อผู้อำนวยการและคณะกรรมการสถานศึกษาขั้นพื้นฐาน</p>'

            + '<section><h2>1. บทสรุปผู้บริหาร (Executive Summary)</h2><p>' + esc(a.summary) + '</p>'
            + '<div class="report__kpis">' + m.kpis.map(function (k) { return '<div><small>' + esc(k.label) + '</small><strong>' + (k.value === null ? '–' : num(k.value, k.decimals)) + ' ' + esc(k.unit) + '</strong><em>' + (k.diff === null ? esc(k.compare) : (k.diff > 0 ? '+' : '') + num(k.diff, k.decimals) + ' ' + esc(k.compare)) + '</em></div>'; }).join('') + '</div></section>'

            + '<section><h2>2. ภาพรวมผลการดำเนินงานของโรงเรียน (School Performance Overview)</h2><p>ดัชนีสุขภาพโรงเรียน <strong>' + a.health + '/100</strong> ระดับ “' + esc(a.healthLabel) + '”</p>'
            + '<div class="report__cols"><div><h3>จุดแข็ง</h3><ul>' + a.strengths.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul></div><div><h3>จุดที่ควรพัฒนา</h3><ul>' + a.weaknesses.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul></div></div></section>'

            + '<section><h2>3. ผลสัมฤทธิ์ทางการเรียน (Academic Performance)</h2>'
            + (m.charts.academic ? table(['ระดับชั้น', 'คะแนนเฉลี่ย' + (m.charts.academic.prevKey ? ' ' + m.charts.academic.prevKey : ''), 'คะแนนเฉลี่ย ' + m.charts.academic.termKey, 'เปลี่ยนแปลง'], m.charts.academic.labels.map(function (l, i) {
                var p = m.charts.academic.previous ? m.charts.academic.previous[i] : null;
                return [esc(l), p === null ? '–' : num(p, 1), num(m.charts.academic.current[i], 1), p === null ? '–' : (m.charts.academic.current[i] - p > 0 ? '+' : '') + num(m.charts.academic.current[i] - p, 1)];
            })) : '<p class="report__empty">ยังไม่มีข้อมูลผลการเรียนของ' + esc(termText) + '</p>')
            + '<h3>คะแนนเฉลี่ยรายวิชา</h3>' + table(['รายวิชา', 'ภาคเรียนก่อน', 'ภาคเรียนที่เลือก'], subjects.labels.map(function (l, i) { return [esc(l), num(subjects.previous[i], 1), num(subjects.current[i], 1)]; })) + '</section>'

            + '<section><h2>4. รายงานการเข้าเรียน (Attendance Report)</h2>'
            + table(['เดือน', 'อัตราการเข้าเรียนเฉลี่ย', 'เทียบเป้าหมาย 95%'], monthIdx.map(function (i) {
                var v = AI.round(AI.mean(m.students.map(function (s) { return s.att[i]; })), 1);
                return [esc(AI.monthLabel(m.meta.months[i])), num(v, 1) + '%', v >= 95 ? 'ผ่าน' : 'ต่ำกว่า ' + num(95 - v, 1) + ' จุด'];
            }))
            + '<p>นักเรียนขาดเรียนต่อเนื่องตั้งแต่ 3 วัน ' + num(m.students.filter(function (s) { return s.streak >= 3; }).length) + ' คน · อัตราเข้าเรียนต่ำกว่า 90% ' + num(m.students.filter(function (s) { return s.attNow < 90; }).length) + ' คน</p></section>'

            + '<section><h2>5. ผลการปฏิบัติงานของครู (Teacher Performance)</h2>'
            + '<p>ครู ' + t.length + ' คน · คะแนนประเมินเฉลี่ย ' + num(AI.mean(t.map(function (x) { return x.overall; })), 1) + ' · โดดเด่น ' + t.filter(function (x) { return x.status === 'โดดเด่น'; }).length + ' คน · ควรได้รับการสนับสนุน ' + t.filter(function (x) { return x.status === 'ควรได้รับการสนับสนุน'; }).length + ' คน · ชั่วโมงสอนเฉลี่ย ' + num(AI.mean(t.map(function (x) { return x.hours; })), 1) + ' ชม./สัปดาห์</p>'
            + table(['สถานะ', 'จำนวน (คน)', 'สัดส่วน'], ['โดดเด่น', 'ดี', 'พัฒนาได้', 'ควรได้รับการสนับสนุน'].map(function (st) { var n = t.filter(function (x) { return x.status === st; }).length; return [st, n, num(n / t.length * 100, 1) + '%']; }))
            + '<p class="report__note">คะแนนเป็นตัวชี้วัดจำลองเพื่อสาธิตระบบ ไม่ใช่การตัดสินคุณภาพครูโดยอัตโนมัติ</p></section>'

            + '<section><h2>6. การวิเคราะห์นักเรียนกลุ่มเสี่ยง (Student Risk Analysis)</h2>'
            + '<p>นักเรียนที่ต้องติดตามเป็นพิเศษ ' + num(risk.length) + ' คน (เสี่ยงสูง ' + num(high.length) + ' คน · เฝ้าระวัง ' + num(risk.length - high.length) + ' คน)</p>'
            + (a.riskGroups.length ? table(['ปัจจัยเสี่ยง', 'จำนวน (คน)', 'ร้อยละของนักเรียน'], a.riskGroups.map(function (g) { return [esc(g.label), g.count, num(g.pct, 1) + '%']; })) : '<p class="report__empty">ไม่พบนักเรียนกลุ่มเสี่ยง</p>')
            + '<h3>ห้องเรียนที่มีผลการดำเนินงานโดดเด่น</h3>' + table(['อันดับ', 'ห้อง', 'คะแนนภาพรวม', 'เข้าเรียน', 'ส่งงาน'], rooms.slice(0, 5).map(function (r, i) { return [i + 1, esc(r.name), num(r.overall, 1), num(r.att, 1) + '%', num(r.sub, 1) + '%']; })) + '</section>'

            + '<section><h2>7. ข้อเสนอแนะจาก AI (AI Recommendations)</h2>'
            + (m.insights.length ? '<ol class="report__recs">' + m.insights.map(function (i) { return '<li><strong>[' + X.levelMeta[i.level].label + ' · ความสำคัญ' + esc(i.priority) + ']</strong> ' + esc(i.title) + ' — ' + esc(i.actions[0]) + '</li>'; }).join('') + '</ol>' : '<p class="report__empty">ไม่มีข้อเสนอแนะเพิ่มเติม</p>')
            + '<p class="report__note">ข้อเสนอแนะสร้างจาก AI Simulation (Rule-based) บนข้อมูลจำลอง</p></section>'

            + '<section><h2>8. แผนปฏิบัติการเชิงกลยุทธ์ (Strategic Action Plan)</h2>'
            + (plan.length ? table(['ลำดับ', 'ประเด็น', 'มาตรการ', 'ผู้รับผิดชอบ', 'ระยะเวลา', 'ความสำคัญ'], plan.map(function (p) { return [p.no, esc(p.issue), esc(p.action), esc(p.owner), esc(p.timeline), esc(p.priority)]; })) : '<p class="report__empty">ไม่มีประเด็นที่ต้องจัดทำแผนปฏิบัติการเพิ่มเติม</p>')
            + '</section>'
            + '<footer class="report__foot"><div><span>ลงชื่อ ..................................................</span><small>ผู้อำนวยการโรงเรียน</small></div><div><span>ลงชื่อ ..................................................</span><small>ประธานคณะกรรมการสถานศึกษา</small></div></footer>'
            + '<p class="report__disclaimer">รายงานนี้สร้างจากข้อมูลจำลองเพื่อการสาธิต ไม่ใช่ข้อมูลจริงของโรงเรียนหรือบุคคลใด</p>';

        $('#rp-doc').innerHTML = html;
        X.log('Report', 'render', 'END', { insights: m.insights.length });
        return m;
    }

    function printReport(asPdf) {
        X.log('Report', 'print', 'START', { pdf: asPdf });
        render();
        if (asPdf) X.toast('เลือกปลายทาง “บันทึกเป็น PDF / Save as PDF” ในหน้าต่างพิมพ์', 'info');
        document.body.classList.add('printing-report');
        setTimeout(function () {
            window.print();
            document.body.classList.remove('printing-report');
        }, asPdf ? 600 : 150);
    }

    var XLSX_URL = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';

    /** Load SheetJS on demand so it never delays the first paint. */
    function loadXlsx() {
        if (window.XLSX) return Promise.resolve(true);
        return new Promise(function (resolve) {
            var script = document.createElement('script');
            script.src = XLSX_URL;
            script.onload = function () { resolve(Boolean(window.XLSX)); };
            script.onerror = function () { X.logError('Report', 'loadXlsx', 'script failed', { url: XLSX_URL }); resolve(false); };
            document.head.appendChild(script);
        });
    }

    function exportExcel() {
        X.toast('กำลังเตรียมไฟล์ Excel…', 'info');
        loadXlsx().then(buildExcel);
    }

    function buildExcel() {
        X.log('Report', 'exportExcel', 'START');
        var m = model();
        var plan = actionPlan(m);
        var sheets = {
            'สรุปผู้บริหาร': [['รายการ', 'ค่า', 'เปลี่ยนแปลง', 'หมายเหตุ']].concat(m.kpis.map(function (k) { return [k.label, k.value, k.diff === null ? '' : AI.round(k.diff, 2), k.compare]; }))
                .concat([[], ['ดัชนีสุขภาพโรงเรียน', m.analysis.health, '', m.analysis.healthLabel], ['บทสรุป', m.analysis.summary]]),
            'ห้องเรียน': [['ห้อง', 'นักเรียน', 'คะแนนภาพรวม', 'คะแนนเฉลี่ย', 'GPA', 'เข้าเรียน (%)', 'ส่งงาน (%)', 'พัฒนาการ', 'ต้องติดตาม', 'ครูประจำชั้น']]
                .concat(m.rooms.map(function (r) { return [r.name, r.students, r.overall, r.score, r.gpa, r.att, r.sub, r.devDelta, r.risk, r.homeroom]; })),
            'ครู': [['ชื่อครู', 'กลุ่มสาระ', 'ชั่วโมงสอน', 'ภาระงาน', 'ผลสัมฤทธิ์นักเรียน', 'การส่งงาน', 'พัฒนาตนเอง', 'คะแนนรวม', 'สถานะ']]
                .concat(m.teachers.map(function (t) { return [t.name, t.department, t.hours, t.loadIndex, t.scoreAchievement, t.scoreSubmission, t.scoreSelfDev, t.overall, t.status]; })),
            'นักเรียนที่ต้องติดตาม': [['รหัส', 'ชื่อ', 'ห้อง', 'GPA', 'เข้าเรียน (%)', 'ส่งงาน (%)', 'ระดับ', 'ปัจจัย']]
                .concat(m.students.filter(function (s) { return s.risk !== 'low'; }).map(function (s) { return [s.code, s.name, s.roomName, s.gpa[2], s.attNow, s.subNow, s.riskLabel, s.factors.join(', ')]; })),
            'AI Recommendations': [['ประเภท', 'ความสำคัญ', 'ประเด็น', 'รายละเอียด', 'แนวทางดำเนินการ']]
                .concat(m.insights.map(function (i) { return [X.levelMeta[i.level].label, i.priority, i.title, i.detail, i.actions.join(' / ')]; })),
            'Action Plan': [['ลำดับ', 'ประเด็น', 'มาตรการ', 'ผู้รับผิดชอบ', 'ระยะเวลา', 'ความสำคัญ']]
                .concat(plan.map(function (p) { return [p.no, p.issue, p.action, p.owner, p.timeline, p.priority]; }))
        };
        var name = 'executive-report-' + m.meta.scenario + '-' + opts.year + '-' + opts.semester;

        if (window.XLSX) {
            try {
                var wb = window.XLSX.utils.book_new();
                Object.keys(sheets).forEach(function (title) {
                    var ws = window.XLSX.utils.aoa_to_sheet(sheets[title]);
                    ws['!cols'] = sheets[title][0].map(function () { return { wch: 22 }; });
                    window.XLSX.utils.book_append_sheet(wb, ws, title.slice(0, 31));
                });
                window.XLSX.writeFile(wb, name + '.xlsx');
                X.toast('Export Excel สำเร็จ (' + Object.keys(sheets).length + ' แผ่นงาน)', 'success');
                X.log('Report', 'exportExcel', 'END', { sheets: Object.keys(sheets).length });
                return;
            } catch (error) {
                X.logError('Report', 'exportExcel', error, { name: name });
            }
        }
        // Fallback: CSV of the summary sheet (Excel opens it directly).
        var csv = '﻿' + sheets['ห้องเรียน'].map(function (r) { return r.map(function (c) { return '"' + String(c === null || c === undefined ? '' : c).replace(/"/g, '""') + '"'; }).join(','); }).join('\n');
        var link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = name + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        X.toast('ไม่สามารถโหลดตัวสร้างไฟล์ Excel ได้ ระบบจึงส่งออกเป็น CSV แทน', 'warning');
    }

    X.views.reports = function () {
        $$('[data-report]').forEach(function (sel) { sel.value = opts[sel.getAttribute('data-report')]; });
        render();
    };
    X.actions['report-print'] = function () { printReport(false); };
    X.actions['report-pdf'] = function () { printReport(true); };
    X.actions['report-excel'] = exportExcel;

    document.addEventListener('DOMContentLoaded', function () {
        $$('[data-report]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                opts[sel.getAttribute('data-report')] = sel.value;
                render();
                X.toast('อัปเดตรายงานตามตัวเลือกแล้ว', 'success');
            });
        });
    });
})();
