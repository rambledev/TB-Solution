/**
 * AI Executive Dashboard — module views: AI School Analytics, AI Teacher Evaluation,
 * Student / Attendance / Classroom Analytics, Mock Data Center and Settings,
 * plus the detail modals for teachers, students and classrooms.
 */
(function () {
    'use strict';

    var X = window.EXD;
    var AI = window.EXD_AI;
    var CH = window.EXD_CHARTS;
    var STORE = window.EXD_STORE;
    var CONFIG = window.EXD_CONFIG;
    var esc = X.esc, icon = X.icon, num = X.num, $ = X.$, $$ = X.$$;
    var ui = { insightTab: 'all', roomMetric: 'overall', mockSelected: null, mockScenario: null };

    function list(items, cls) {
        return items.map(function (t) { return '<li' + (cls ? ' class="' + cls + '"' : '') + '>' + esc(t) + '</li>'; }).join('');
    }
    function statusBadge(status) {
        var tone = { 'โดดเด่น': 'success', 'ดี': 'primary', 'พัฒนาได้': 'warning', 'ควรได้รับการสนับสนุน': 'danger' }[status] || 'neutral';
        return '<span class="badge badge--' + tone + '">' + esc(status) + '</span>';
    }
    function riskBadge(s) {
        var tone = s.risk === 'high' ? 'danger' : (s.risk === 'medium' ? 'warning' : 'success');
        return '<span class="badge badge--' + tone + '">' + esc(s.riskLabel) + '</span>';
    }
    function bar(value, max, tone) {
        return '<span class="meter"><span class="meter__fill meter__fill--' + (tone || 'primary') + '" style="width:' + Math.max(2, Math.min(100, value / (max || 100) * 100)) + '%"></span></span>';
    }
    function stat(iconName, label, value, sub, tone) {
        return '<div class="stat stat--' + (tone || 'blue') + '"><span class="stat__icon">' + icon(iconName) + '</span><div><p class="stat__label">' + esc(label) + '</p><p class="stat__value">' + value + '</p>' + (sub ? '<p class="stat__sub">' + sub + '</p>' : '') + '</div></div>';
    }
    function mean(a) { return AI.mean(a); }

    /* AI School Analytics ---------------------------------------------------- */
    function renderAI(m) {
        X.log('View', 'ai', 'START');
        var a = m.analysis;
        $('#ai-summary').textContent = a.summary;
        $('#ai-meta').textContent = m.scope.label + ' · ' + m.meta.scenarioTh + ' · นักเรียน ' + num(m.students.length) + ' คน · ครู ' + m.teachers.length + ' คน';
        var tone = a.health >= 85 ? 'success' : (a.health >= 72 ? 'primary' : (a.health >= 60 ? 'warning' : 'danger'));
        $('#ai-health').innerHTML = '<div class="health__ring health__ring--' + tone + '" style="--p:' + a.health + '"><strong>' + a.health + '</strong><small>/100</small></div><span>ดัชนีสุขภาพโรงเรียน<br><b>' + esc(a.healthLabel) + '</b></span>';
        $('#ai-scan').innerHTML = ['การเข้าเรียน', 'ผลการเรียน', 'คะแนนเฉลี่ย', 'การส่งงาน', 'พฤติกรรม', 'พัฒนาการ', 'กิจกรรม', 'ระดับห้องเรียน', 'ระดับชั้นเรียน']
            .map(function (t) { return '<span>' + icon('check') + t + '</span>'; }).join('');
        $('#ai-strengths').innerHTML = list(a.strengths);
        $('#ai-weak').innerHTML = list(a.weaknesses);
        $('#ai-improve').innerHTML = list(a.improving);
        $('#ai-urgent').innerHTML = list(a.urgent);
        $('#ai-risk').innerHTML = a.riskGroups.length
            ? '<ul class="bar-list">' + a.riskGroups.map(function (g) { return '<li><span>' + esc(g.label) + '</span>' + bar(g.pct, Math.max(10, a.riskGroups[0].pct), 'warning') + '<b>' + g.count + ' คน</b></li>'; }).join('') + '</ul>'
            : '<div class="state state--inline">' + icon('smile') + '<p>ไม่พบกลุ่มนักเรียนที่มีความเสี่ยง</p></div>';
        $('#ai-stars').innerHTML = a.stars.map(function (r, i) {
            return '<li><button type="button" class="rank-item" data-room="' + r.id + '"><span class="rank-no">' + (i + 1) + '</span><span class="rank-text"><strong>ห้อง ' + esc(r.name) + '</strong><small>เข้าเรียน ' + r.att + '% · ส่งงาน ' + r.sub + '%</small></span><b>' + r.overall + '</b></button></li>';
        }).join('');

        var f = a.forecast;
        var actual = f.actual.concat([null, null, null]);
        var predicted = f.actual.map(function (v, i) { return i === f.actual.length - 1 ? v : null; }).concat(f.predicted);
        CH.render('ai-forecast', {
            type: 'line',
            data: { labels: f.labels, datasets: [CH.line('ข้อมูลจริง', actual, 'primary'), CH.line('คาดการณ์ (AI Simulation)', predicted, 'violet', { borderDash: [6, 5], fill: false }), CH.dashed('เป้าหมาย 95%', f.labels.map(function () { return 95; }), 'amber')] },
            options: { scales: CH.axes({ min: Math.floor(Math.min.apply(null, f.actual.concat(f.predicted)) - 2), max: 100, suffix: '%' }), plugins: { tooltip: CH.tooltip('%') } }
        });
        $('#ai-forecast-notes').innerHTML = '<ul class="check-list">' + list(f.notes) + '</ul><p class="note">การคาดการณ์ใช้แนวโน้มเชิงเส้นอย่างง่ายเพื่อการสาธิต ไม่ใช่โมเดลพยากรณ์จริง</p>';

        drawInsights(m);
        X.log('View', 'ai', 'END', { insights: m.insights.length });
    }

    function drawInsights(m) {
        var counts = { all: m.insights.length };
        m.insights.forEach(function (i) { counts[i.level] = (counts[i.level] || 0) + 1; });
        $$('#ai-tabs [data-count]').forEach(function (el) { el.textContent = counts[el.getAttribute('data-count')] || 0; });
        $$('#ai-tabs [data-insight-tab]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-insight-tab') === ui.insightTab); });
        var items = m.insights.filter(function (i) { return ui.insightTab === 'all' || i.level === ui.insightTab; });
        $('#ai-insights').innerHTML = items.length ? items.map(X.insightCard).join('')
            : '<div class="state">' + icon('shield-check') + '<h4>ไม่พบประเด็นในหมวดนี้</h4><p>ข้อมูลชุดปัจจุบันไม่มีรายการที่เข้าเงื่อนไขของหมวดนี้</p></div>';
        X.icons($('#ai-insights'));
    }

    /* AI Teacher Evaluation ------------------------------------------------- */
    function renderTeachers(m) {
        X.log('View', 'teachers', 'START');
        var t = m.teachers;
        var stars = t.filter(function (x) { return x.status === 'โดดเด่น'; });
        var support = t.filter(function (x) { return x.status === 'ควรได้รับการสนับสนุน'; });
        var selfDev = t.filter(function (x) { return x.training >= 20; }).length / t.length * 100;
        $('#tc-summary').innerHTML = [
            stat('users', 'จำนวนครูทั้งหมด', num(t.length) + '<small> คน</small>', 'บุคลากรสนับสนุน ' + m.meta.staff + ' คน', 'blue'),
            stat('gauge', 'คะแนนประเมินเฉลี่ย', num(mean(t.map(function (x) { return x.overall; })), 1) + '<small>/100</small>', 'ตัวชี้วัดจำลอง 10 ด้าน', 'violet'),
            stat('star', 'ครูที่มีผลงานโดดเด่น', stars.length + '<small> คน</small>', 'คะแนนรวม 83 ขึ้นไป', 'green'),
            stat('hand-heart', 'ครูที่ควรได้รับการสนับสนุน', support.length + '<small> คน</small>', 'ใช้ประกอบการนิเทศ', 'red'),
            stat('clock', 'ภาระงานเฉลี่ย', num(mean(t.map(function (x) { return x.hours; })), 1) + '<small> ชม./สัปดาห์</small>', 'งานพิเศษเฉลี่ย ' + num(mean(t.map(function (x) { return x.duties; })), 1) + ' งาน', 'amber'),
            stat('graduation-cap', 'อัตราการพัฒนาตนเอง', num(selfDev, 0) + '<small>%</small>', 'อบรม 20 ชม. ขึ้นไป', 'indigo')
        ].join('');

        // AI teacher insights (named examples from the current data).
        var byDev = t.slice().sort(function (a, b) { return b.devTrend - a.devTrend; })[0];
        var byLoad = t.slice().sort(function (a, b) { return b.loadVsAvg - a.loadVsAvg; })[0];
        var byOverall = t.slice().sort(function (a, b) { return b.overall - a.overall; })[0];
        var lowest = t.slice().sort(function (a, b) { return a.overall - b.overall; })[0];
        var notes = [
            ['trending-up', 'success', byDev.name, 'นักเรียนในความรับผิดชอบมีพัฒนาการดีขึ้นต่อเนื่อง (+' + byDev.devTrend + ' คะแนนจากภาคเรียนก่อน)', byDev.id],
            ['briefcase', byLoad.loadVsAvg >= 20 ? 'warning' : 'primary', byLoad.name, 'มีภาระงานสูงกว่าค่าเฉลี่ย ' + byLoad.loadVsAvg + '% (' + byLoad.hours + ' ชม./สัปดาห์ งานพิเศษ ' + byLoad.duties + ' งาน)', byLoad.id],
            ['award', 'violet', byOverall.name, 'มีผลการจัดการเรียนการสอนโดดเด่น (คะแนนรวม ' + byOverall.overall + ') เหมาะเป็นพี่เลี้ยงใน PLC', byOverall.id],
            ['hand-heart', 'danger', lowest.name, 'ควรได้รับการสนับสนุนด้าน' + lowest.improve[0].label + ' (' + lowest.improve[0].score + ') ผ่านการนิเทศแบบกัลยาณมิตร', lowest.id]
        ];
        $('#tc-insights').innerHTML = notes.map(function (n) {
            return '<li><button type="button" class="ins-row" data-teacher="' + n[4] + '"><span class="ins-row__icon tone-' + n[1] + '">' + icon(n[0]) + '</span><span><strong>' + esc(n[2]) + '</strong><small>' + esc(n[3]) + '</small></span>' + icon('chevron-right') + '</button></li>';
        }).join('') + '<li class="note">ตัวชี้วัดจำลองเพื่อสาธิตระบบ ต้องพิจารณาบริบทและปัจจัยประกอบเสมอ</li>';

        var depts = [];
        t.forEach(function (x) { if (depts.indexOf(x.department) === -1) depts.push(x.department); });
        var deptSel = $('#tc-dept-filter');
        if (deptSel.options.length !== depts.length + 1) {
            deptSel.innerHTML = '<option value="">ทุกกลุ่มสาระ</option>' + depts.map(function (d) { return '<option value="' + esc(d) + '">' + esc(d) + '</option>'; }).join('');
        }
        CH.render('tc-dept', {
            type: 'bar',
            data: {
                labels: depts,
                datasets: [
                    CH.bar('คะแนนประเมินเฉลี่ย', depts.map(function (d) { return AI.round(mean(t.filter(function (x) { return x.department === d; }).map(function (x) { return x.overall; })), 1); }), 'primary', { yAxisID: 'y' }),
                    Object.assign(CH.line('ชั่วโมงสอนเฉลี่ย', depts.map(function (d) { return AI.round(mean(t.filter(function (x) { return x.department === d; }).map(function (x) { return x.hours; })), 1); }), 'violet', { fill: false }), { yAxisID: 'y1' })
                ]
            },
            options: { scales: Object.assign(CH.axes({ min: 40, max: 100 }), { y1: { position: 'right', min: 10, max: 30, grid: { display: false }, border: { display: false }, ticks: { callback: function (v) { return v + ' ชม.'; } } } }), plugins: { tooltip: CH.tooltip('') } }
        });

        X.renderTable('tc-table', {
            rows: t,
            searchText: function (r) { return r.name + ' ' + r.department; },
            onRow: function (r) { openTeacher(r.id); },
            columns: [
                { key: 'name', label: 'ชื่อครู', render: function (r) { return '<div class="cell-user"><span class="avatar-sm">' + esc(r.name.replace(/^(นางสาว|นาง|นาย)/, '').charAt(0)) + '</span><div><strong>' + esc(r.name) + '</strong><small>' + esc(r.position) + '</small></div></div>'; } },
                { key: 'department', label: 'กลุ่มสาระ' },
                { key: 'hours', label: 'ชั่วโมงสอน', num: true, render: function (r) { return r.hours + ' ชม.'; } },
                { key: 'loadIndex', label: 'ภาระงาน', num: true, render: function (r) { return '<span class="' + (r.loadVsAvg >= 20 ? 'txt-warning' : '') + '">' + r.loadIndex + '</span>'; } },
                { key: 'scoreAchievement', label: 'ผลสัมฤทธิ์นักเรียน', num: true },
                { key: 'scoreSubmission', label: 'การส่งงาน', num: true },
                { key: 'scoreSelfDev', label: 'พัฒนาตนเอง', num: true },
                { key: 'overall', label: 'คะแนนรวม', num: true, render: function (r) { return '<strong>' + num(r.overall, 1) + '</strong>'; } },
                { key: 'status', label: 'สถานะ', render: function (r) { return statusBadge(r.status); } }
            ]
        });
        X.log('View', 'teachers', 'END');
    }

    function openTeacher(id) {
        var t = X.state.model.teachers.filter(function (x) { return x.id === id; })[0];
        if (!t) return;
        X.log('View', 'openTeacher', 'START', { id: id });
        var notes = AI.teacherNotes(t);
        X.openModal({
            wide: true,
            eyebrow: icon('user-check') + ' AI Teacher Evaluation <span class="tag-sim">AI Simulation</span>',
            title: t.name,
            html: '<div class="profile-head"><span class="avatar-lg">' + esc(t.name.replace(/^(นางสาว|นาง|นาย)/, '').charAt(0)) + '</span><div><p>' + esc(t.position) + ' · กลุ่มสาระ' + esc(t.department) + ' · ประสบการณ์ ' + t.years + ' ปี</p><p class="muted">สอน ' + t.classes.length + ' ห้อง · นักเรียนที่รับผิดชอบ ' + t.studentsCount + ' คน' + (t.homeroom ? ' · ครูประจำชั้น' : '') + '</p></div><div class="score-big"><strong>' + num(t.overall, 1) + '</strong><small>คะแนนรวม</small>' + statusBadge(t.status) + '</div></div>'
                + '<div class="modal-grid"><div><h4 class="mini-title">' + icon('list') + 'ผลการวิเคราะห์ 10 ด้าน</h4><ul class="factor-list">'
                + t.factors.map(function (f) { return '<li><span>' + esc(f.label) + ' <small>(' + f.weight + '%)</small></span>' + bar(f.score, 100, f.score >= 80 ? 'success' : (f.score < 65 ? 'warning' : 'primary')) + '<b>' + f.score + '</b></li>'; }).join('')
                + '</ul></div><div><div class="chart" style="--h:280px"><canvas id="tc-radar"></canvas></div></div></div>'
                + '<div class="modal-grid"><div class="box"><h4 class="mini-title">' + icon('thumbs-up') + 'จุดแข็ง</h4><ul class="check-list">' + list(t.strengths.map(function (s) { return s.label + ' (' + s.score + ')'; })) + '</ul></div>'
                + '<div class="box box--ai"><h4 class="mini-title">' + icon('sparkles') + 'AI Insights</h4><ul class="check-list">' + list(notes) + '</ul></div></div>'
                + '<p class="note">' + icon('scale') + ' คะแนนเป็นตัวชี้วัดจำลองเพื่อสาธิตระบบ ไม่ใช่การตัดสินคุณภาพครูโดยอัตโนมัติ ต้องพิจารณาบริบทของห้องเรียนและปัจจัยประกอบร่วมกับการพูดคุยกับครู</p>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ปิด</button>',
            onOpen: function () {
                CH.render('tc-radar', {
                    type: 'radar',
                    data: { labels: t.factors.map(function (f) { return f.label.length > 16 ? f.label.slice(0, 15) + '…' : f.label; }), datasets: [{ label: 'คะแนน', data: t.factors.map(function (f) { return f.score; }), borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,.18)', pointBackgroundColor: '#7C3AED', borderWidth: 2 }] },
                    options: { interaction: { mode: 'nearest', intersect: true }, scales: { r: { min: 0, max: 100, ticks: { display: false, stepSize: 20 }, pointLabels: { font: { size: 10 } } } }, plugins: { legend: { display: false }, tooltip: CH.tooltip('') } }
                });
            }
        });
    }

    /* Student Analytics ------------------------------------------------------ */
    function renderStudents(m) {
        X.log('View', 'students', 'START');
        var s = m.students;
        var ti = m.term.idx === null ? 2 : m.term.idx;
        var below = s.filter(function (x) { return x.gpa[2] < 2; });
        var excellent = s.filter(function (x) { return x.gpa[2] >= 3.5; });

        var grades = [];
        m.allStudents.forEach(function (x) { if (grades.indexOf(x.grade) === -1) grades.push(x.grade); });
        var roomFilter = m.filters.room;
        $('#st-grade-tabs').innerHTML = '<button type="button" class="gtab' + (roomFilter === 'all' ? ' is-active' : '') + '" data-scope="all">ทุกชั้น</button>'
            + AI.GRADE_ORDER.filter(function (g) { return grades.indexOf(g) !== -1; }).map(function (g) {
                return '<button type="button" class="gtab' + (roomFilter === 'grade:' + g ? ' is-active' : '') + '" data-scope="grade:' + g + '">' + AI.GRADE_LABEL[g] + '</button>';
            }).join('');

        $('#st-summary').innerHTML = [
            stat('users', 'นักเรียนในขอบเขต', num(s.length) + '<small> คน</small>', esc(m.scope.label), 'blue'),
            stat('award', 'GPA เฉลี่ย', num(mean(s.map(function (x) { return x.gpa[2]; })), 2), 'ภาคเรียน ' + m.meta.terms[2], 'violet'),
            stat('medal', 'ผลการเรียนดีเด่น', num(excellent.length) + '<small> คน</small>', 'GPA 3.50 ขึ้นไป', 'green'),
            stat('life-buoy', 'ต่ำกว่าเกณฑ์', num(below.length) + '<small> คน</small>', 'GPA ต่ำกว่า 2.00', 'red'),
            stat('trending-up', 'พัฒนาการดีขึ้น', num(s.filter(function (x) { return x.gpaDelta >= 0.2; }).length) + '<small> คน</small>', 'GPA เพิ่มขึ้น 0.20 ขึ้นไป', 'amber'),
            stat('heart-handshake', 'นักเรียนที่มีความเสี่ยง', num(s.filter(function (x) { return x.risk !== 'low'; }).length) + '<small> คน</small>', 'เสี่ยงสูง ' + s.filter(function (x) { return x.risk === 'high'; }).length + ' คน', 'indigo')
        ].join('');

        var gg = m.charts.gradeGpa;
        CH.render('st-grade', {
            type: 'bar',
            data: { labels: gg.labels, datasets: gg.values.map(function (v, i) { return CH.bar('ภาคเรียน ' + gg.terms[i], v, ['#BFDBFE', '#60A5FA', '#2563EB'][i]); }) },
            options: { scales: CH.axes({ min: 1.5, max: 4 }), plugins: { tooltip: CH.tooltip('', 2) } }
        });
        var sj = m.charts.subjects;
        CH.render('st-subject', {
            type: 'bar',
            data: { labels: sj.labels, datasets: [CH.bar('ภาคเรียนก่อน', sj.previous, '#C4B5FD'), CH.bar('ภาคเรียนที่เลือก', sj.current, 'violet')] },
            options: { indexAxis: 'y', scales: CH.axes({ min: 40, max: 90, horizontal: true }), plugins: { tooltip: CH.tooltip(' คะแนน') } }
        });

        function rankItem(x, i, value) {
            return '<li><button type="button" class="rank-item" data-student="' + x.id + '"><span class="rank-no">' + (i + 1) + '</span><span class="rank-text"><strong>' + esc(x.name) + '</strong><small>ห้อง ' + esc(x.roomName) + '</small></span><b>' + value + '</b></button></li>';
        }
        var top = s.slice().sort(function (a, b) { return b.gpa[ti] - a.gpa[ti]; }).slice(0, 8);
        var improved = s.slice().sort(function (a, b) { return b.gpaDelta - a.gpaDelta; }).filter(function (x) { return x.gpaDelta > 0; }).slice(0, 8);
        var low = below.slice().sort(function (a, b) { return a.gpa[2] - b.gpa[2]; }).slice(0, 8);
        $('#st-top').innerHTML = top.map(function (x, i) { return rankItem(x, i, num(x.gpa[ti], 2)); }).join('') || '<li class="state state--inline"><p>ไม่พบข้อมูล</p></li>';
        $('#st-improved').innerHTML = improved.map(function (x, i) { return rankItem(x, i, '+' + num(x.gpaDelta, 2)); }).join('') || '<li class="state state--inline"><p>ไม่พบนักเรียนที่มีผลการเรียนดีขึ้น</p></li>';
        $('#st-below').innerHTML = low.map(function (x, i) { return rankItem(x, i, num(x.gpa[2], 2)); }).join('') || '<li class="state state--inline">' + icon('smile') + '<p>ไม่มีนักเรียนที่ต่ำกว่าเกณฑ์</p></li>';

        X.renderTable('st-table', {
            rows: s,
            searchText: function (r) { return r.name + ' ' + r.code + ' ' + r.roomName; },
            onRow: function (r) { openStudent(r.id); },
            columns: [
                { key: 'name', label: 'นักเรียน', render: function (r) { return '<div class="cell-user"><span class="avatar-sm">' + esc(r.first.charAt(0)) + '</span><div><strong>' + esc(r.name) + '</strong><small>' + esc(r.code) + '</small></div></div>'; } },
                { key: 'roomName', label: 'ห้อง' },
                { key: 'gpa', label: 'GPA', num: true, value: function (r) { return r.gpa[2]; }, render: function (r) { return num(r.gpa[2], 2); } },
                { key: 'gpaDelta', label: 'เปลี่ยนแปลง', num: true, render: function (r) { return '<span class="' + (r.gpaDelta > 0 ? 'txt-success' : (r.gpaDelta < 0 ? 'txt-danger' : '')) + '">' + (r.gpaDelta > 0 ? '+' : '') + num(r.gpaDelta, 2) + '</span>'; } },
                { key: 'attNow', label: 'เข้าเรียน', num: true, render: function (r) { return num(r.attNow, 1) + '%'; } },
                { key: 'subNow', label: 'ส่งงาน', num: true, render: function (r) { return r.subNow + '%'; } },
                { key: 'riskScore', label: 'ความเสี่ยง', render: riskBadge }
            ]
        });
        X.log('View', 'students', 'END');
    }

    function openStudent(id) {
        var s = X.state.model.allStudents.filter(function (x) { return x.id === id; })[0];
        if (!s) return;
        X.log('View', 'openStudent', 'START', { id: id });
        var subj = X.state.model.subjects;
        X.openModal({
            wide: true,
            eyebrow: icon('graduation-cap') + ' Academic Progress Tracking',
            title: s.name + ' · ห้อง ' + s.roomName,
            html: '<div class="detail-stats"><div><small>GPA ล่าสุด</small><strong>' + num(s.gpa[2], 2) + '</strong></div><div><small>เข้าเรียน (ต.ค.)</small><strong>' + num(s.attNow, 1) + '%</strong></div><div><small>ส่งงาน</small><strong>' + s.subNow + '%</strong></div><div><small>พฤติกรรม</small><strong>' + s.behavior + '</strong></div><div><small>กิจกรรม</small><strong>' + s.act + ' ครั้ง</strong></div><div><small>สถานะ</small><strong>' + riskBadge(s) + '</strong></div></div>'
                + '<div class="modal-grid"><div><h4 class="mini-title">' + icon('chart-line') + 'GPA และพัฒนาการ 3 ภาคเรียน</h4><div class="chart" style="--h:230px"><canvas id="st-progress"></canvas></div></div>'
                + '<div><h4 class="mini-title">' + icon('book-open') + 'คะแนนรายวิชา (ล่าสุด)</h4><ul class="factor-list">' + Object.keys(subj).map(function (k) { return '<li><span>' + esc(subj[k].name) + '</span>' + bar(s.scores[2][k], 100, s.scores[2][k] < 50 ? 'danger' : 'primary') + '<b>' + s.scores[2][k] + '</b></li>'; }).join('') + '</ul></div></div>'
                + (s.factors.length ? '<div class="box box--warning"><h4 class="mini-title">' + icon('heart-handshake') + 'ปัจจัยที่ควรติดตาม</h4><p>' + esc(s.factors.join(' · ')) + '</p><p class="muted">แนะนำ: ครูประจำชั้นพูดคุยรายบุคคลและติดต่อผู้ปกครองเพื่อวางแผนช่วยเหลือร่วมกัน</p></div>' : '<div class="box box--success"><p>' + icon('smile') + ' ไม่พบปัจจัยเสี่ยง</p></div>')
                + '<p class="note">ชื่อนักเรียนทั้งหมดเป็นชื่อสมมติ ข้อมูลใช้เพื่อการดูแลช่วยเหลือ ไม่ใช่การตีตรา</p>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ปิด</button>',
            onOpen: function () {
                CH.render('st-progress', {
                    type: 'line',
                    data: { labels: X.state.model.meta.terms.map(function (t) { return 'ภาคเรียน ' + t; }), datasets: [Object.assign(CH.line('GPA', s.gpa, 'primary'), { yAxisID: 'y' }), Object.assign(CH.line('พัฒนาการ', s.dev, 'violet', { fill: false }), { yAxisID: 'y1' })] },
                    options: { scales: { x: { grid: { display: false } }, y: { min: 0, max: 4, title: { display: true, text: 'GPA' } }, y1: { position: 'right', min: 0, max: 100, grid: { display: false }, title: { display: true, text: 'พัฒนาการ' } } }, plugins: { tooltip: CH.tooltip('', 2) } }
                });
            }
        });
    }

    /* Attendance Analytics --------------------------------------------------- */
    function renderAttendance(m) {
        X.log('View', 'attendance', 'START');
        var s = m.students;
        var ratio = s.length / Math.max(1, m.allStudents.length);
        var days = m.daily;
        var last = days[days.length - 1];
        var dateLabel = function (d) { var p = d.split('-'); return parseInt(p[2], 10) + ' ต.ค.'; };
        var streak = s.filter(function (x) { return x.streak >= 3; });
        var lateAvg = mean(s.map(function (x) { return x.late; }));

        $('#at-summary').innerHTML = [
            stat('calendar-check', 'อัตราเข้าเรียนวันล่าสุด', num(last.rate, 1) + '<small>%</small>', dateLabel(last.date) + ' 2569 (ทั้งโรงเรียน)', 'green'),
            stat('calendar-range', 'อัตราเข้าเรียนรายเดือน', num(mean(s.map(function (x) { return x.att[5]; })), 1) + '<small>%</small>', esc(m.scope.label), 'blue'),
            stat('user-x', 'นักเรียนขาดเรียนวันล่าสุด', num(Math.round(last.absent * ratio)) + '<small> คน</small>', 'ประมาณการตามขอบเขตที่เลือก', 'red'),
            stat('alarm-clock', 'นักเรียนมาสายวันล่าสุด', num(Math.round(last.late * ratio)) + '<small> คน</small>', 'เฉลี่ย ' + num(lateAvg, 1) + ' ครั้ง/คน/เดือน', 'amber'),
            stat('repeat', 'ขาดเรียนต่อเนื่อง ≥ 3 วัน', num(streak.length) + '<small> คน</small>', 'ควรติดตามภายในสัปดาห์นี้', 'indigo'),
            stat('target', 'ต่ำกว่า 90%', num(s.filter(function (x) { return x.attNow < 90; }).length) + '<small> คน</small>', 'เป้าหมายโรงเรียน 95%', 'violet')
        ].join('');

        CH.render('at-daily', {
            type: 'bar',
            data: { labels: days.map(function (d) { return dateLabel(d.date); }), datasets: [Object.assign(CH.line('อัตราเข้าเรียน (%)', days.map(function (d) { return d.rate; }), 'primary'), { yAxisID: 'y' }), Object.assign(CH.bar('ขาดเรียน (คน)', days.map(function (d) { return d.absent; }), CH.alpha('red', 0.55)), { yAxisID: 'y1' })] },
            options: { scales: { x: { grid: { display: false } }, y: { min: Math.floor(Math.min.apply(null, days.map(function (d) { return d.rate; })) - 3), max: 100, ticks: { callback: function (v) { return v + '%'; } } }, y1: { position: 'right', beginAtZero: true, grid: { display: false } } }, plugins: { tooltip: CH.tooltip('') } }
        });

        var monthIdx = [0, 1, 2, 3, 4, 5].slice(6 - (parseInt(m.filters.range, 10) || 6));
        var labels = monthIdx.map(function (i) { return AI.monthLabel(m.meta.months[i]); });
        var present = monthIdx.map(function (i) { return AI.round(mean(s.map(function (x) { return x.att[i]; })), 1); });
        CH.render('at-monthly', {
            type: 'line',
            data: { labels: labels, datasets: [CH.line('มาเรียน (%)', present, 'green'), CH.dashed('เป้าหมาย 95%', labels.map(function () { return 95; }), 'amber')] },
            options: { scales: CH.axes({ min: Math.floor(Math.min.apply(null, present.concat([95])) - 2), max: 100, suffix: '%' }), plugins: { tooltip: CH.tooltip('%') } }
        });

        var rooms = m.rooms;
        CH.render('at-rooms', {
            type: 'bar',
            data: { labels: rooms.map(function (r) { return r.name; }), datasets: [CH.bar('เดือนก่อน', rooms.map(function (r) { return r.attPrev; }), '#BFDBFE'), CH.bar('เดือนล่าสุด', rooms.map(function (r) { return r.att; }), rooms.map(function (r) { return r.att >= 95 ? '#16A34A' : (r.att >= 90 ? '#2563EB' : '#DC2626'); })), CH.dashed('เป้าหมาย', rooms.map(function () { return 95; }), 'amber')] },
            options: {
                scales: CH.axes({ min: Math.floor(Math.min.apply(null, rooms.map(function (r) { return r.att; }).concat([90])) - 2), max: 100, suffix: '%' }), plugins: { tooltip: CH.tooltip('%') },
                onClick: function (e, els) { if (els.length) openRoom(rooms[els[0].index].id); }
            }
        });

        var follow = s.filter(function (x) { return x.attNow < 90 || x.streak >= 3; }).sort(function (a, b) { return a.attNow - b.attNow; });
        X.renderTable('at-table', {
            rows: follow,
            empty: 'ไม่มีนักเรียนที่ต้องติดตามด้านการเข้าเรียน',
            searchText: function (r) { return r.name + ' ' + r.code + ' ' + r.roomName; },
            onRow: function (r) { openStudent(r.id); },
            columns: [
                { key: 'name', label: 'นักเรียน', render: function (r) { return '<div class="cell-user"><span class="avatar-sm">' + esc(r.first.charAt(0)) + '</span><div><strong>' + esc(r.name) + '</strong><small>' + esc(r.code) + '</small></div></div>'; } },
                { key: 'roomName', label: 'ห้อง' },
                { key: 'attNow', label: 'อัตราเข้าเรียน', num: true, render: function (r) { return '<span class="' + (r.attNow < 85 ? 'txt-danger' : 'txt-warning') + '">' + num(r.attNow, 1) + '%</span>'; } },
                { key: 'absent', label: 'ขาด (วัน)', num: true },
                { key: 'late', label: 'มาสาย (ครั้ง)', num: true },
                { key: 'streak', label: 'ขาดต่อเนื่อง', num: true, render: function (r) { return r.streak >= 3 ? '<span class="badge badge--danger">' + r.streak + ' วัน</span>' : (r.streak ? r.streak + ' วัน' : '–'); } },
                { key: 'trend', label: 'แนวโน้ม 6 เดือน', sortable: false, render: function (r) { return '<span class="mini-spark">' + r.att.map(function (v) { return '<i style="height:' + Math.max(8, (v - 60) / 40 * 100) + '%"></i>'; }).join('') + '</span>'; } }
            ]
        });
        X.log('View', 'attendance', 'END', { follow: follow.length });
    }

    /* Classroom Analytics ----------------------------------------------------- */
    var METRICS = {
        overall: ['คะแนนภาพรวมของห้อง', '/100', 40, 100],
        score: ['คะแนนเฉลี่ยของห้อง', ' คะแนน', 40, 90],
        att: ['อัตราการเข้าเรียน (เดือนล่าสุด)', '%', 80, 100],
        sub: ['อัตราการส่งงาน (เดือนล่าสุด)', '%', 40, 100],
        devDelta: ['พัฒนาการนักเรียน (เปลี่ยนแปลงจากภาคเรียนก่อน)', ' คะแนน', -6, 6]
    };

    function renderClassrooms(m) {
        X.log('View', 'classrooms', 'START', { metric: ui.roomMetric });
        var rooms = m.rooms;
        var mt = METRICS[ui.roomMetric];
        $$('#cr-metric [data-metric]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-metric') === ui.roomMetric); });
        $('#cr-chart-sub').textContent = mt[0] + ' · ' + m.scope.label;
        var values = rooms.map(function (r) { return r[ui.roomMetric]; });
        var avg = mean(values);
        CH.render('cr-chart', {
            type: 'bar',
            data: { labels: rooms.map(function (r) { return r.name; }), datasets: [CH.bar(mt[0], values, values.map(function (v) { return v >= avg ? '#2563EB' : '#93C5FD'; })), CH.dashed('ค่าเฉลี่ย ' + num(avg, 1), rooms.map(function () { return AI.round(avg, 1); }), 'violet')] },
            options: {
                scales: CH.axes({ min: mt[2], max: mt[3] }), plugins: { tooltip: CH.tooltip(mt[1]) },
                onClick: function (e, els) { if (els.length) openRoom(rooms[els[0].index].id); },
                onHover: function (e, els) { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; }
            }
        });
        $('#cr-grid').innerHTML = rooms.length ? rooms.slice().sort(function (a, b) { return b.overall - a.overall; }).map(function (r, i) {
            var tone = r.overall >= 80 ? 'success' : (r.overall >= 68 ? 'primary' : (r.overall >= 60 ? 'warning' : 'danger'));
            return '<button type="button" class="room-card" data-room="' + r.id + '">'
                + '<span class="room-card__head"><strong>ห้อง ' + esc(r.name) + '</strong><span class="badge badge--' + tone + '">#' + (i + 1) + '</span></span>'
                + '<span class="room-card__score">' + num(r.overall, 1) + '<small>/100</small></span>'
                + '<span class="room-card__rows"><span>นักเรียน<b>' + r.students + '</b></span><span>คะแนนเฉลี่ย<b>' + num(r.score, 1) + '</b></span><span>เข้าเรียน<b>' + num(r.att, 1) + '%</b></span><span>ส่งงาน<b>' + num(r.sub, 1) + '%</b></span><span>พัฒนาการ<b>' + (r.devDelta > 0 ? '+' : '') + num(r.devDelta, 1) + '</b></span><span>ต้องติดตาม<b>' + r.risk + '</b></span></span>'
                + bar(r.overall, 100, tone) + '</button>';
        }).join('') : '<div class="state">' + icon('inbox') + '<p>ไม่พบห้องเรียนในขอบเขตที่เลือก</p></div>';
        X.log('View', 'classrooms', 'END');
    }

    function openRoom(id) {
        var m = X.state.model;
        var r = m.allRooms.filter(function (x) { return x.id === id; })[0];
        if (!r) return;
        X.log('View', 'openRoom', 'START', { id: id });
        var students = m.allStudents.filter(function (s) { return s.room === id; }).sort(function (a, b) { return b.gpa[2] - a.gpa[2]; });
        var rank = m.allRooms.slice().sort(function (a, b) { return b.overall - a.overall; }).map(function (x) { return x.id; }).indexOf(id) + 1;
        var subjects = m.subjects;
        X.openModal({
            wide: true,
            eyebrow: icon('school') + ' Classroom Detail',
            title: 'ห้อง ' + r.name + ' · อันดับ ' + rank + ' จาก ' + m.allRooms.length + ' ห้อง',
            html: '<div class="detail-stats"><div><small>คะแนนภาพรวม</small><strong>' + num(r.overall, 1) + '</strong></div><div><small>นักเรียน</small><strong>' + r.students + ' คน</strong></div><div><small>คะแนนเฉลี่ย</small><strong>' + num(r.score, 1) + '</strong></div><div><small>เข้าเรียน</small><strong>' + num(r.att, 1) + '%</strong></div><div><small>ส่งงาน</small><strong>' + num(r.sub, 1) + '%</strong></div><div><small>ต้องติดตาม</small><strong>' + r.risk + ' คน</strong></div></div>'
                + '<p class="muted">ครูประจำชั้น: ' + esc(r.homeroom) + ' · GPA เฉลี่ย ' + num(r.gpa, 2) + ' · พัฒนาการ ' + (r.devDelta > 0 ? '+' : '') + num(r.devDelta, 1) + '</p>'
                + '<div class="modal-grid"><div><h4 class="mini-title">' + icon('book-open') + 'คะแนนเฉลี่ยรายวิชา</h4><div class="chart" style="--h:230px"><canvas id="room-subjects"></canvas></div></div>'
                + '<div><h4 class="mini-title">' + icon('medal') + 'ผลการเรียนสูงสุด 5 อันดับ</h4><ol class="rank-list">' + students.slice(0, 5).map(function (s, i) { return '<li><button type="button" class="rank-item" data-student="' + s.id + '"><span class="rank-no">' + (i + 1) + '</span><span class="rank-text"><strong>' + esc(s.name) + '</strong></span><b>' + num(s.gpa[2], 2) + '</b></button></li>'; }).join('') + '</ol>'
                + '<h4 class="mini-title">' + icon('heart-handshake') + 'นักเรียนที่ต้องติดตาม</h4><ol class="rank-list">' + (students.filter(function (s) { return s.risk !== 'low'; }).slice(0, 5).map(function (s, i) { return '<li><button type="button" class="rank-item" data-student="' + s.id + '"><span class="rank-no">' + (i + 1) + '</span><span class="rank-text"><strong>' + esc(s.name) + '</strong><small>' + esc(s.factors.join(' · ')) + '</small></span>' + riskBadge(s) + '</button></li>'; }).join('') || '<li class="muted">ไม่มีนักเรียนที่ต้องติดตาม</li>') + '</ol></div></div>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ปิด</button>',
            onOpen: function () {
                CH.render('room-subjects', {
                    type: 'bar',
                    data: { labels: Object.keys(subjects).map(function (k) { return subjects[k].name; }), datasets: [CH.bar('ห้อง ' + r.name, Object.keys(subjects).map(function (k) { return r.subjects[k]; }), 'primary'), CH.bar('ค่าเฉลี่ยโรงเรียน', Object.keys(subjects).map(function (k) { return AI.round(mean(m.allRooms.map(function (x) { return x.subjects[k]; })), 1); }), '#C4B5FD')] },
                    options: { scales: CH.axes({ min: 40, max: 90 }), plugins: { tooltip: CH.tooltip(' คะแนน') } }
                });
            }
        });
    }

    /* Mock Data Center -------------------------------------------------------- */
    function renderMock(m) {
        X.log('View', 'mock', 'START');
        var d = STORE.get();
        var meta = d.meta;
        var source = STORE.source();
        $('#mk-source').textContent = source === 'default' ? 'Default (ฝังในหน้า)' : (source === 'storage' ? 'LocalStorage' : 'Memory');
        $('#mk-current').innerHTML = '<dt>Scenario</dt><dd>' + esc(meta.scenarioLabel) + ' · ' + esc(meta.scenarioTh) + '</dd>'
            + '<dt>Seed</dt><dd>' + esc(meta.seed) + '</dd><dt>สร้างเมื่อ</dt><dd>' + esc(new Date(meta.generatedAt).toLocaleString('th-TH')) + '</dd>'
            + '<dt>โรงเรียน</dt><dd>' + esc(meta.school) + '</dd><dt>ภาคเรียนย้อนหลัง</dt><dd>' + esc(meta.terms.join(', ')) + '</dd>'
            + '<dt>ข้อมูลการเข้าเรียน</dt><dd>' + esc(AI.monthLabel(meta.months[0])) + ' – ' + esc(AI.monthLabel(meta.months[5])) + ' (6 เดือน)</dd>';
        var scores = d.students.length * 15;
        $('#mk-summary').innerHTML = [
            stat('graduation-cap', 'นักเรียน', num(d.students.length), 'ป.1 – ม.6', 'blue'),
            stat('users', 'ครู', num(d.teachers.length), 'บุคลากร ' + meta.staff + ' คน', 'indigo'),
            stat('school', 'ห้องเรียน', num(d.classrooms.length), '12 ระดับชั้น', 'green'),
            stat('calendar-check', 'บันทึกการเข้าเรียน', num(d.students.length * 6), 'รายเดือน + รายวัน ' + d.daily.length + ' วัน', 'amber'),
            stat('book-open-check', 'ผลการเรียนรายวิชา', num(scores), '5 วิชา × 3 ภาคเรียน', 'violet'),
            stat('party-popper', 'กิจกรรมโรงเรียน', num(d.activities.length), 'พร้อมจำนวนผู้เข้าร่วม', 'red')
        ].join('');

        if (ui.mockScenario !== meta.scenario + ':' + meta.seed) {
            ui.mockScenario = meta.scenario + ':' + meta.seed;
            ui.mockSelected = meta.scenario;
        }
        $('#mk-scenarios').innerHTML = Object.keys(CONFIG.scenarios).map(function (key) {
            var s = CONFIG.scenarios[key];
            var active = key === meta.scenario;
            return '<article class="scenario' + (active ? ' is-active' : '') + (ui.mockSelected === key ? ' is-selected' : '') + '" data-scenario="' + key + '" tabindex="0" role="button" aria-pressed="' + (ui.mockSelected === key) + '">'
                + '<span class="scenario__icon scenario__icon--' + key + '">' + icon(s.icon) + '</span>'
                + '<h4>' + esc(s.label) + '</h4><p class="scenario__th">' + esc(s.th) + '</p><p class="scenario__desc">' + esc(s.desc) + '</p>'
                + (active ? '<span class="badge badge--success">' + icon('check') + 'ใช้งานอยู่</span>' : '<button type="button" class="btn btn--soft btn--sm" data-generate="' + key + '">' + icon('play') + 'ใช้ Scenario นี้</button>')
                + '</article>';
        }).join('');
        X.log('View', 'mock', 'END');
    }

    /* Settings ------------------------------------------------------------------- */
    function renderSettings() {
        var mode = X.currentThemeMode();
        $$('[data-theme-set]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-theme-set') === mode); });
        $('#set-sidebar').checked = document.documentElement.classList.contains('sb-collapsed');
        $('#set-pagesize').value = String(X.state.pageSize);
        try { $('#set-motion').checked = JSON.parse(localStorage.getItem('exd.reducedMotion')) === true; } catch (e) { /* ignore */ }
    }

    function initSettings() {
        $$('[data-theme-set]').forEach(function (b) {
            b.addEventListener('click', function () { X.setTheme(b.getAttribute('data-theme-set')); X.toast('เปลี่ยนธีมเรียบร้อย', 'success'); });
        });
        $('#set-sidebar').addEventListener('change', function (e) { X.toggleCollapse(e.target.checked); X.toast('บันทึกการตั้งค่าแล้ว', 'success'); });
        $('#set-pagesize').addEventListener('change', function (e) { X.setPageSize(parseInt(e.target.value, 10)); X.toast('แสดงตาราง ' + e.target.value + ' แถวต่อหน้า', 'success'); });
        $('#set-motion').addEventListener('change', function (e) {
            try { localStorage.setItem('exd.reducedMotion', JSON.stringify(e.target.checked)); } catch (err) { /* ignore */ }
            CH.setReducedMotion(e.target.checked);
            X.toast('บันทึกการตั้งค่าแล้ว', 'success');
        });
    }

    /* Wiring --------------------------------------------------------------------- */
    X.views.ai = renderAI;
    X.views.teachers = renderTeachers;
    X.views.students = renderStudents;
    X.views.attendance = renderAttendance;
    X.views.classrooms = renderClassrooms;
    X.views.mock = renderMock;
    X.views.settings = renderSettings;
    X.actions.openRoom = openRoom;

    X.actions['rerun-ai'] = function () {
        X.openModal({
            eyebrow: icon('loader-circle', 'spin') + ' AI Simulation',
            title: 'กำลังวิเคราะห์ข้อมูลชุดปัจจุบัน',
            html: '<ul class="progress-steps"><li data-step="0">' + icon('circle') + '<span>อ่านข้อมูลนักเรียน ครู และห้องเรียน</span></li><li data-step="1">' + icon('circle') + '<span>ประเมินแนวโน้มและความเสี่ยง</span></li><li data-step="2">' + icon('circle') + '<span>สร้างข้อเสนอแนะเชิงกลยุทธ์</span></li></ul><div class="progress"><span id="gen-bar"></span></div>'
        });
        [0, 1, 2].forEach(function (i) {
            setTimeout(function () {
                var li = $('.progress-steps [data-step="' + i + '"]');
                if (li) li.classList.add('is-done');
                var b = $('#gen-bar');
                if (b) b.style.width = (i + 1) * 33 + '%';
            }, 300 * (i + 1));
        });
        setTimeout(function () { X.closeModal(); X.renderCurrent(); X.toast('วิเคราะห์เสร็จแล้ว (AI Simulation)', 'ai'); }, 1200);
    };
    X.actions['generate-current'] = function () {
        var key = ui.mockSelected || STORE.get().meta.scenario;
        X.runGenerate(key, CONFIG.defaultSeed, 'กำลังสร้าง ' + CONFIG.scenarios[key].label);
    };
    X.actions['download-json'] = function () {
        var d = STORE.get();
        var blob = new Blob([JSON.stringify(d, null, 2)], { type: 'application/json' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'mock-data-' + d.meta.scenario + '-' + d.meta.seed + '.json';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
        X.toast('ดาวน์โหลดไฟล์ JSON แล้ว', 'success');
    };
    X.actions['clear-all'] = function () {
        X.openModal({
            eyebrow: icon('trash-2') + ' Settings',
            title: 'ล้างข้อมูลทั้งหมดในเบราว์เซอร์?',
            html: '<p class="lead">ระบบจะลบชุดข้อมูลจำลอง ตัวกรอง และการตั้งค่าทั้งหมด แล้วโหลดหน้าใหม่ด้วยค่าเริ่มต้น</p>',
            foot: '<button type="button" class="btn btn--ghost" data-action="modal-close">ยกเลิก</button><button type="button" class="btn btn--danger" data-action="confirm-clear">' + icon('trash-2') + 'ล้างข้อมูล</button>'
        });
    };
    X.actions['confirm-clear'] = function () {
        try { Object.keys(localStorage).forEach(function (k) { if (k.indexOf('exd.') === 0) localStorage.removeItem(k); }); } catch (e) { /* ignore */ }
        location.hash = '#overview';
        location.reload();
    };

    document.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-insight-tab]');
        if (tab) { ui.insightTab = tab.getAttribute('data-insight-tab'); drawInsights(X.state.model); return; }
        var metric = e.target.closest('[data-metric]');
        if (metric) { ui.roomMetric = metric.getAttribute('data-metric'); renderClassrooms(X.state.model); return; }
        var room = e.target.closest('[data-room]');
        if (room) { openRoom(room.getAttribute('data-room')); return; }
        var stu = e.target.closest('[data-student]');
        if (stu) { openStudent(stu.getAttribute('data-student')); return; }
        var teacher = e.target.closest('[data-teacher]');
        if (teacher) { openTeacher(teacher.getAttribute('data-teacher')); return; }
        var scope = e.target.closest('[data-scope]');
        if (scope) {
            var sel = $('#filter-room');
            sel.value = scope.getAttribute('data-scope');
            sel.dispatchEvent(new Event('change'));
            return;
        }
        var card = e.target.closest('[data-scenario]');
        if (card && !e.target.closest('button')) { ui.mockSelected = card.getAttribute('data-scenario'); renderMock(X.state.model); X.icons($('#view-mock')); }
    });
    document.addEventListener('keydown', function (e) {
        var card = e.target.closest && e.target.closest('[data-scenario]');
        if (card && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); ui.mockSelected = card.getAttribute('data-scenario'); renderMock(X.state.model); X.icons($('#view-mock')); }
    });
    document.addEventListener('DOMContentLoaded', initSettings);
})();
