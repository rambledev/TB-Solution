/**
 * EduCare — Executive dashboard (8 menus). All statistics are computed live from the
 * shared store, so attendance a teacher records changes these numbers immediately.
 */
(function () {
    'use strict';

    var E = window.EDU, Q = E.Q, esc = E.esc, P = window.EDU_PARTS;
    var FILTER_KEY = 'educare.exec.filter';
    var ui = { query: '', room: '', limit: 30, report: 'attendance' };
    var PERIODS = { 7: '7 วันล่าสุด', 30: '30 วันล่าสุด', month: 'เดือนนี้' };
    var CATS = ['น้ำใจ', 'การเรียนรู้', 'ความรับผิดชอบ', 'ความกล้าแสดงออก', 'ระเบียบวินัย'];

    function filter() {
        var f = null;
        try { f = JSON.parse(localStorage.getItem(FILTER_KEY)); } catch (e) { /* ignore */ }
        f = f || {};
        return { school: ['all', 'SCH1', 'SCH2'].indexOf(f.school) !== -1 ? f.school : 'all', period: PERIODS[f.period] ? String(f.period) : '30' };
    }
    function setFilter(key, value) {
        var f = filter(); f[key] = value;
        try { localStorage.setItem(FILTER_KEY, JSON.stringify(f)); } catch (e) { /* ignore */ }
        E.log('Executive', 'setFilter', 'filter changed', f);
        E.app.render();
    }
    /** Calendar range for the period; days = school days that have attendance records. */
    function range() {
        var p = filter().period, today = E.today();
        var from = p === 'month' ? today.slice(0, 8) + '01' : E.addDays(today, -(parseInt(p, 10) - 1));
        return { from: from, to: today, days: Q.schoolDays(from, today) };
    }
    function rooms() { var s = filter().school; return E.data().classrooms.filter(function (r) { return s === 'all' || r.school === s; }); }
    function students() { var ids = rooms().map(function (r) { return r.id; }); return E.data().students.filter(function (s) { return ids.indexOf(s.classroom) !== -1; }); }
    function head(title, sub, actions) {
        return '<div class="page-head"><div><h2>' + title + '</h2>' + (sub ? '<p>' + sub + '</p>' : '') + '</div>' + (actions ? '<div class="page-head__actions">' + actions + '</div>' : '') + '</div>';
    }
    function scopeText() { var f = filter(); return (f.school === 'all' ? 'ทุกโรงเรียน' : Q.school(f.school).name) + ' · ' + PERIODS[f.period]; }

    /** Attendance counts for a list of students over days. */
    function counts(list, days) {
        var c = { present: 0, late: 0, absent: 0, leave: 0, none: 0 };
        days.forEach(function (d) { list.forEach(function (s) { c[Q.status(d, s.id)]++; }); });
        c.checked = c.present + c.late + c.absent + c.leave;
        c.rate = c.checked ? Math.round((c.present + c.late) / c.checked * 1000) / 10 : null;
        return c;
    }
    function roomStats(r, days) {
        var list = Q.studentsIn(r.id), ids = list.map(function (s) { return s.id; }), d = E.data();
        var inRange = function (x) { return x.date >= days[0] && x.date <= E.today(); };
        return {
            room: r, students: list.length, today: counts(list, [E.today()]), period: counts(list, days),
            activities: d.activities.filter(function (a) { return a.classroom === r.id && inRange(a); }).length,
            stars: d.stars.filter(function (x) { return ids.indexOf(x.studentId) !== -1 && inRange(x); }).reduce(function (n, x) { return n + x.count; }, 0),
            works: d.portfolio.filter(function (x) { return ids.indexOf(x.studentId) !== -1 && inRange(x); }).length
        };
    }

    /* Overview ------------------------------------------------------------------- */
    function overview() {
        var rg = range(), list = students(), today = counts(list, [E.today()]);
        var stats = rooms().map(function (r) { return roomStats(r, rg.days.length ? rg.days : [E.today()]); });
        var teachers = E.data().teachers.filter(function (t) { return filter().school === 'all' || t.school === filter().school; });
        var notChecked = stats.filter(function (s) { return s.today.none > 0; });
        return head('Executive Overview', scopeText() + ' · ข้อมูล ณ ' + E.thaiDate(E.today(), 'long'))
            + '<div class="stat-grid stat-grid--6">'
            + E.stat('bi-people', 'นักเรียนทั้งหมด', list.length + ' <small>คน</small>', 'primary')
            + E.stat('bi-person-badge', 'ครู', teachers.length + ' <small>คน</small>', 'info')
            + E.stat('bi-door-open', 'ห้องเรียน', rooms().length + ' <small>ห้อง</small>', 'secondary')
            + E.stat('bi-calendar-check', 'เข้าเรียนวันนี้', (today.present + today.late) + ' <small>คน</small>', 'success', today.checked ? 'อัตรา ' + today.rate + '% ของที่เช็คแล้ว' : 'ยังไม่มีการเช็คชื่อ')
            + E.stat('bi-x-circle', 'ขาดเรียนวันนี้', today.absent + ' <small>คน</small>', 'danger', '<a href="#" data-act="absent-list">ดูรายชื่อ →</a>')
            + E.stat('bi-hourglass-split', 'ยังไม่เช็คชื่อ', today.none + ' <small>คน</small>', today.none ? 'warning' : 'secondary', notChecked.length ? notChecked.map(function (s) { return esc(s.room.name); }).join(', ') : 'ครบทุกห้อง')
            + '</div><div class="row g-3">'
            + '<div class="col-xl-8"><div class="card-x"><div class="card-x__head"><h3>สถิติการเข้าเรียนรายวัน</h3><a href="#attendance">รายละเอียด</a></div><div class="chart-box"><canvas id="ex-att"></canvas></div></div></div>'
            + '<div class="col-xl-4"><div class="card-x"><div class="card-x__head"><h3>สถานะวันนี้</h3></div><div class="chart-box"><canvas id="ex-today"></canvas></div></div></div>'
            + '<div class="col-xl-6"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมและผลงานรายห้อง</h3><a href="#activities">สถิติกิจกรรม</a></div><div class="chart-box"><canvas id="ex-acts"></canvas></div></div></div>'
            + '<div class="col-xl-6"><div class="card-x"><div class="card-x__head"><h3>ดาวสะสมตามหมวดพัฒนาการ</h3><a href="#development">พัฒนาการ</a></div><div class="chart-box"><canvas id="ex-stars"></canvas></div></div></div>'
            + '</div><div class="card-x mt-3"><div class="card-x__head"><h3>สถิติรายห้องเรียน</h3><small class="text-secondary">คลิกแถวเพื่อดูรายละเอียด</small></div>' + roomTable(stats) + '</div>';
    }
    function roomTable(stats) {
        return '<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ห้อง</th><th>โรงเรียน</th><th class="text-end">นักเรียน</th><th class="text-end">มาวันนี้</th><th class="text-end">ขาดวันนี้</th><th>อัตรามาเรียน (ช่วงที่เลือก)</th><th class="text-end">กิจกรรม</th><th class="text-end">ผลงาน</th><th class="text-end">ดาว</th></tr></thead><tbody>'
            + stats.map(function (s) {
                var rate = s.period.rate;
                return '<tr class="row-link" data-act="room" data-id="' + s.room.id + '" tabindex="0"><td><strong>' + esc(s.room.name) + '</strong><small class="d-block text-secondary">' + esc(s.room.label) + '</small></td><td>' + esc(Q.school(s.room.school).short) + '</td><td class="text-end">' + s.students + '</td><td class="text-end">' + (s.today.checked ? s.today.present + s.today.late : '<span class="text-warning">รอเช็ค</span>') + '</td><td class="text-end' + (s.today.absent ? ' text-danger fw-semibold' : '') + '">' + s.today.absent + '</td>'
                    + '<td><div class="bar-cell"><div class="progress flex-fill" style="height:8px"><div class="progress-bar ' + (rate >= 95 ? 'bg-success' : (rate >= 90 ? 'bg-primary' : 'bg-warning')) + '" style="width:' + (rate || 0) + '%"></div></div><span>' + (rate === null ? '–' : rate + '%') + '</span></div></td><td class="text-end">' + s.activities + '</td><td class="text-end">' + s.works + '</td><td class="text-end">' + s.stars + '</td></tr>';
            }).join('') + '</tbody></table></div>';
    }
    function roomModal(id) {
        var r = Q.classroom(id), s = roomStats(r, range().days), list = Q.studentsIn(id);
        var absent = list.filter(function (x) { return Q.status(E.today(), x.id) === 'absent'; });
        var recent = E.data().activities.filter(function (a) { return a.classroom === id; }).sort(function (a, b) { return (b.date + b.time).localeCompare(a.date + a.time); }).slice(0, 3);
        E.modal({
            size: 'lg',
            title: '<i class="bi bi-door-open text-primary me-2"></i>ห้อง ' + esc(r.name) + ' <small class="text-secondary">' + esc(r.label) + ' · ' + esc(Q.school(r.school).name) + '</small>',
            body: '<div class="stat-grid">' + E.stat('bi-people', 'นักเรียน', s.students + ' คน', 'primary') + E.stat('bi-calendar-check', 'อัตรามาเรียน', (s.period.rate === null ? '–' : s.period.rate + '%'), 'success', PERIODS[filter().period]) + E.stat('bi-journal-text', 'กิจกรรม', s.activities, 'info') + E.stat('bi-star', 'ดาว', s.stars, 'warning') + '</div>'
                + '<p><i class="bi bi-person-badge"></i> ครูประจำชั้น: <strong>' + esc(Q.teacher(r.homeroom).fullName) + '</strong></p>'
                + '<h4 class="mini-title">ขาดเรียนวันนี้ (' + absent.length + ')</h4>' + (absent.length ? '<ul class="list-plain">' + absent.map(function (x) { return '<li>' + E.avatar(x, 'xs') + ' ' + esc(x.nickname) + ' · ' + esc(Q.fullName(x)) + '</li>'; }).join('') + '</ul>' : '<p class="text-secondary">ไม่มี</p>')
                + '<h4 class="mini-title">กิจกรรมล่าสุด</h4>' + (recent.length ? recent.map(P.activityItem).join('') : '<p class="text-secondary">ยังไม่มีกิจกรรม</p>')
        });
    }

    /* Students ---------------------------------------------------------------------- */
    function studentsView() {
        var q = ui.query.toLowerCase();
        var list = students().filter(function (s) { return (!ui.room || s.classroom === ui.room) && (!q || (Q.fullName(s) + s.nickname + s.code).toLowerCase().indexOf(q) !== -1); });
        var genders = { M: 0, F: 0 };
        students().forEach(function (s) { genders[s.gender]++; });
        return head('ข้อมูลนักเรียน', scopeText() + ' · ชาย ' + genders.M + ' คน · หญิง ' + genders.F + ' คน')
            + '<div class="toolbar"><div class="search"><i class="bi bi-search"></i><input type="search" class="form-control" placeholder="ค้นหาชื่อ ชื่อเล่น หรือรหัส" value="' + esc(ui.query) + '" data-input="q" aria-label="ค้นหานักเรียน"></div>'
            + '<select class="form-select w-auto" data-input="room" aria-label="ห้องเรียน"><option value="">ทุกห้อง</option>' + rooms().map(function (r) { return '<option value="' + r.id + '"' + (ui.room === r.id ? ' selected' : '') + '>' + esc(r.name) + '</option>'; }).join('') + '</select><span class="text-secondary small">' + list.length + ' คน</span></div>'
            + (list.length ? '<div class="card-x p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>นักเรียน</th><th>รหัส</th><th>ห้อง</th><th>อายุ</th><th>วันนี้</th><th class="text-end">ดาว</th><th>ผู้ปกครอง</th></tr></thead><tbody>'
                + list.slice(0, ui.limit).map(function (s) { return '<tr class="row-link" data-act="student" data-id="' + s.id + '" tabindex="0"><td>' + E.avatar(s, 'xs') + ' <strong>' + esc(s.nickname) + '</strong> <small class="text-secondary">' + esc(Q.fullName(s)) + '</small></td><td>' + esc(s.code) + '</td><td>' + esc(Q.classroom(s.classroom).name) + '</td><td>' + E.age(s.dob) + '</td><td>' + E.statusBadge(Q.status(E.today(), s.id)) + '</td><td class="text-end">' + Q.starsOf(s.id) + '</td><td><small>' + esc(Q.parent(s.parent).name) + '</small></td></tr>'; }).join('')
                + '</tbody></table></div></div>' + (list.length > ui.limit ? '<div class="text-center mt-3"><button type="button" class="btn btn-outline-primary" data-act="more">แสดงเพิ่มอีก ' + Math.min(30, list.length - ui.limit) + ' คน</button></div>' : '')
                : E.empty('bi-search', 'ไม่พบนักเรียนที่ตรงกับเงื่อนไข'));
    }
    function studentModal(id) {
        var s = Q.student(id), days = range().days, c = counts([s], days);
        E.modal({
            title: E.avatar(s, 'sm') + ' ' + esc(s.nickname) + ' <small class="text-secondary">' + esc(Q.fullName(s)) + '</small>',
            body: '<dl class="kv"><dt>รหัส</dt><dd>' + esc(s.code) + '</dd><dt>ห้อง</dt><dd>' + esc(Q.classroom(s.classroom).label) + ' · ' + esc(Q.school(s.school).name) + '</dd><dt>อายุ</dt><dd>' + E.age(s.dob) + '</dd><dt>ผู้ปกครอง</dt><dd>' + esc(Q.parent(s.parent).name) + '</dd><dt>อัตรามาเรียน</dt><dd>' + (c.rate === null ? '–' : c.rate + '%') + ' <small class="text-secondary">(' + PERIODS[filter().period] + ' · ขาด ' + c.absent + ' · สาย ' + c.late + ')</small></dd><dt>ดาวสะสม</dt><dd>' + Q.starsOf(id) + ' ⭐</dd><dt>ผลงาน</dt><dd>' + E.data().portfolio.filter(function (p) { return p.studentId === id; }).length + ' ชิ้น</dd></dl>'
        });
    }

    /* Attendance stats ----------------------------------------------------------------- */
    function attendance() {
        var rg = range(), list = students(), c = counts(list, rg.days);
        var chronic = list.map(function (s) { var x = counts([s], rg.days); return { s: s, absent: x.absent, late: x.late }; }).filter(function (x) { return x.absent >= 2; }).sort(function (a, b) { return b.absent - a.absent; });
        var absentToday = list.filter(function (s) { return Q.status(E.today(), s.id) === 'absent'; });
        return head('สถิติการเข้าเรียน', scopeText() + ' · ' + rg.days.length + ' วันเรียน')
            + '<div class="stat-grid">' + E.stat('bi-percent', 'อัตราการมาเรียน', (c.rate === null ? '–' : c.rate + '%'), 'success') + E.stat('bi-clock', 'มาสาย (ครั้ง)', c.late, 'warning') + E.stat('bi-x-circle', 'ขาดเรียน (ครั้ง)', c.absent, 'danger') + E.stat('bi-envelope-paper', 'ลา (ครั้ง)', c.leave, 'info') + '</div>'
            + '<div class="row g-3"><div class="col-xl-8"><div class="card-x"><div class="card-x__head"><h3>สถิติรายวัน (จำนวนคน)</h3></div><div class="chart-box"><canvas id="ex-att-stack"></canvas></div></div></div>'
            + '<div class="col-xl-4"><div class="card-x"><div class="card-x__head"><h3>อัตรามาเรียนรายห้อง</h3></div><div class="chart-box"><canvas id="ex-att-room"></canvas></div></div></div>'
            + '<div class="col-lg-6"><div class="card-x"><div class="card-x__head"><h3>ขาดเรียนวันนี้ (' + absentToday.length + ')</h3></div>' + (absentToday.length ? '<ul class="list-plain">' + absentToday.map(function (s) { return '<li>' + E.avatar(s, 'xs') + ' ' + esc(s.nickname) + ' <small class="text-secondary">' + esc(Q.classroom(s.classroom).name) + ' · ' + esc(Q.parent(s.parent).name) + '</small></li>'; }).join('') + '</ul>' : E.empty('bi-emoji-smile', 'วันนี้ไม่มีนักเรียนขาดเรียน')) + '</div></div>'
            + '<div class="col-lg-6"><div class="card-x"><div class="card-x__head"><h3>ขาดเรียนบ่อย (≥ 2 ครั้ง)</h3></div>' + (chronic.length ? '<ul class="list-plain">' + chronic.slice(0, 10).map(function (x) { return '<li>' + E.avatar(x.s, 'xs') + ' ' + esc(x.s.nickname) + ' <small class="text-secondary">' + esc(Q.classroom(x.s.classroom).name) + '</small> <span class="badge text-bg-danger ms-auto">ขาด ' + x.absent + '</span></li>'; }).join('') + '</ul>' : E.empty('bi-emoji-smile', 'ไม่มีนักเรียนที่ขาดเรียนบ่อย')) + '</div></div></div>';
    }

    /* Activities, portfolio, development ------------------------------------------------- */
    function activitiesView() {
        var rg = range(), ids = rooms().map(function (r) { return r.id; });
        var list = E.data().activities.filter(function (a) { return ids.indexOf(a.classroom) !== -1 && a.date >= rg.from; }).sort(function (a, b) { return (b.date + b.time).localeCompare(a.date + a.time); });
        return head('สถิติกิจกรรม', scopeText() + ' · ' + list.length + ' กิจกรรม')
            + '<div class="row g-3"><div class="col-xl-6"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมตามหมวด</h3></div><div class="chart-box"><canvas id="ex-act-cat"></canvas></div></div></div>'
            + '<div class="col-xl-6"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมรายห้อง</h3></div><div class="chart-box"><canvas id="ex-act-room"></canvas></div></div></div>'
            + '<div class="col-12"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมล่าสุด</h3></div>' + (list.length ? list.slice(0, 8).map(P.activityItem).join('') : E.empty('bi-journal', 'ไม่มีกิจกรรมในช่วงนี้')) + '</div></div></div>';
    }
    function portfolioView() {
        var rg = range(), ids = students().map(function (s) { return s.id; });
        var list = E.data().portfolio.filter(function (p) { return ids.indexOf(p.studentId) !== -1 && p.date >= rg.from; }).sort(function (a, b) { return b.date.localeCompare(a.date); });
        return head('ผลงานนักเรียน', scopeText() + ' · ' + list.length + ' ผลงาน')
            + '<div class="card-x mb-3"><div class="card-x__head"><h3>ผลงานตามหมวดและห้องเรียน</h3></div><div class="chart-box"><canvas id="ex-pf"></canvas></div></div>'
            + (list.length ? '<div class="works">' + list.slice(0, 12).map(P.workCard).join('') + '</div>' : E.empty('bi-images', 'ไม่มีผลงานในช่วงนี้'));
    }
    function development() {
        var ids = students().map(function (s) { return s.id; });
        var top = students().map(function (s) { return { s: s, n: Q.starsOf(s.id) }; }).sort(function (a, b) { return b.n - a.n; }).slice(0, 10);
        var total = E.data().stars.filter(function (x) { return ids.indexOf(x.studentId) !== -1; }).reduce(function (n, x) { return n + x.count; }, 0);
        return head('พัฒนาการนักเรียน', scopeText() + ' · ดาวสะสมรวม ' + total + ' ดวง (เฉลี่ย ' + (Math.round(total / Math.max(1, ids.length) * 10) / 10) + ' ดวง/คน)')
            + '<div class="row g-3"><div class="col-xl-7"><div class="card-x"><div class="card-x__head"><h3>พัฒนาการตามหมวดรายห้อง (ดาวสะสม)</h3></div><div class="chart-box chart-box--tall"><canvas id="ex-dev"></canvas></div></div></div>'
            + '<div class="col-xl-5"><div class="card-x"><div class="card-x__head"><h3>นักเรียนดาวเด่น 10 อันดับ</h3></div><ol class="rank">' + top.map(function (t, i) { return '<li><span class="rank__no">' + (i + 1) + '</span>' + E.avatar(t.s, 'sm') + '<span class="rank__name">' + esc(t.s.nickname) + ' <small class="text-secondary">' + esc(Q.classroom(t.s.classroom).name) + '</small></span><b>' + t.n + ' ⭐</b></li>'; }).join('') + '</ol></div></div></div>';
    }

    /* Reports ---------------------------------------------------------------------------- */
    function reportRows() {
        var rg = range(), stats = rooms().map(function (r) { return roomStats(r, rg.days); });
        if (ui.report === 'activity') return { head: ['ห้อง', 'โรงเรียน', 'กิจกรรม', 'ผลงาน', 'ดาว'], rows: stats.map(function (s) { return [s.room.name, Q.school(s.room.school).short, s.activities, s.works, s.stars]; }) };
        if (ui.report === 'student') return { head: ['รหัส', 'ชื่อ', 'ชื่อเล่น', 'ห้อง', 'มาเรียน (%)', 'ขาด', 'ดาว'], rows: students().map(function (s) { var c = counts([s], rg.days); return [s.code, Q.fullName(s), s.nickname, Q.classroom(s.classroom).name, c.rate === null ? '' : c.rate, c.absent, Q.starsOf(s.id)]; }) };
        return { head: ['ห้อง', 'โรงเรียน', 'นักเรียน', 'มาเรียน', 'มาสาย', 'ขาด', 'ลา', 'อัตรามาเรียน (%)'], rows: stats.map(function (s) { return [s.room.name, Q.school(s.room.school).short, s.students, s.period.present, s.period.late, s.period.absent, s.period.leave, s.period.rate === null ? '' : s.period.rate]; }) };
    }
    function reports() {
        var data = reportRows();
        var titles = { attendance: 'รายงานสรุปการเข้าเรียน', activity: 'รายงานกิจกรรมและผลงาน', student: 'รายงานรายบุคคล' };
        return head('รายงาน', 'เลือกประเภทรายงาน ดูตัวอย่าง พิมพ์ หรือส่งออก CSV (เปิดได้ใน Excel)',
            '<button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>พิมพ์</button><button type="button" class="btn btn-primary" data-act="csv"><i class="bi bi-filetype-csv me-1"></i>Export CSV</button>')
            + '<div class="btn-group mb-3 flex-wrap" role="group" aria-label="ประเภทรายงาน">' + Object.keys(titles).map(function (k) { return '<button type="button" class="btn ' + (ui.report === k ? 'btn-primary' : 'btn-light') + '" data-act="report" data-id="' + k + '">' + titles[k] + '</button>'; }).join('') + '</div>'
            + '<div class="report-sheet card-x"><div class="report-sheet__head">' + '<strong>EduCare by TB-Solution</strong><h3>' + titles[ui.report] + '</h3><p>' + esc(scopeText()) + ' · พิมพ์วันที่ ' + E.thaiDate(E.today(), 'long') + '</p></div>'
            + '<div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr>' + data.head.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr></thead><tbody>' + data.rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody></table></div><p class="small text-secondary mb-0">ข้อมูลจำลองเพื่อการสาธิต</p></div>';
    }
    function exportCsv() {
        var data = reportRows();
        var csv = '﻿' + [data.head].concat(data.rows).map(function (r) { return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(','); }).join('\n');
        var a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        a.download = 'educare-' + ui.report + '-' + E.today() + '.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
        E.log('Executive', 'exportCsv', 'exported', { report: ui.report, rows: data.rows.length });
        E.toast('ส่งออกไฟล์ CSV แล้ว (' + data.rows.length + ' แถว)', 'success');
    }

    /* Announcements & events --------------------------------------------------------------- */
    function announcements() {
        var s = filter().school;
        var list = E.data().announcements.filter(function (a) { return s === 'all' || a.school === 'all' || a.school === s; }).sort(function (a, b) { return (b.pinned - a.pinned) || b.date.localeCompare(a.date); });
        var events = E.data().events.filter(function (ev) { return ev.date >= E.today() && (s === 'all' || ev.school === 'all' || ev.school === s); }).sort(function (a, b) { return a.date.localeCompare(b.date); });
        return head('ประกาศและกิจกรรม', scopeText())
            + '<div class="row g-3"><div class="col-xl-7"><div class="card-x mb-3"><div class="card-x__head"><h3>ประกาศ (' + list.length + ')</h3></div>' + (list.length ? list.map(P.announceCard).join('') : E.empty('bi-megaphone', 'ยังไม่มีประกาศ')) + '</div></div>'
            + '<div class="col-xl-5"><form class="card-x mb-3" data-form="announce" novalidate><h3 class="card-x__title">ประกาศถึงผู้ปกครอง</h3>'
            + '<div class="mb-2"><label class="form-label" for="ex-an-title">หัวข้อ *</label><input class="form-control" id="ex-an-title" name="title" maxlength="120" required></div>'
            + '<div class="mb-2"><label class="form-label" for="ex-an-body">รายละเอียด *</label><textarea class="form-control" id="ex-an-body" name="body" rows="3" maxlength="500" required></textarea></div>'
            + '<div class="mb-3"><label class="form-label" for="ex-an-school">โรงเรียน</label><select class="form-select" id="ex-an-school" name="school"><option value="all">ทุกโรงเรียน</option>' + E.data().schools.map(function (x) { return '<option value="' + x.id + '">' + esc(x.name) + '</option>'; }).join('') + '</select></div>'
            + '<button class="btn btn-primary w-100" type="submit"><i class="bi bi-megaphone me-1"></i>ประกาศและแจ้งเตือน</button></form>'
            + '<div class="card-x"><div class="card-x__head"><h3>กิจกรรมที่จะมาถึง</h3></div>' + (events.length ? events.slice(0, 6).map(P.eventItem).join('') : E.empty('bi-calendar', 'ไม่มีกิจกรรม')) + '</div></div></div>';
    }

    /* Charts --------------------------------------------------------------------------------- */
    function after(id) {
        var rg = range(), list = students(), days = rg.days;
        var dayLabels = days.map(function (d) { return E.thaiDate(d, 'day'); });
        if (id === 'overview') {
            E.chart('ex-att', { type: 'line', data: { labels: dayLabels, datasets: [{ label: 'อัตรามาเรียน (%)', data: days.map(function (d) { return counts(list, [d]).rate; }), borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .35, spanGaps: true }] }, options: { scales: { y: { min: 70, max: 100 } }, plugins: { legend: { display: false } } } });
            var t = counts(list, [E.today()]);
            E.chart('ex-today', { type: 'doughnut', data: { labels: ['มาเรียน', 'มาสาย', 'ขาด', 'ลา', 'ยังไม่เช็ค'], datasets: [{ data: [t.present, t.late, t.absent, t.leave, t.none], backgroundColor: ['#22C55E', '#F59E0B', '#EF4444', '#0EA5E9', '#CBD5E1'] }] }, options: { cutout: '62%', plugins: { legend: { position: 'bottom' } } } });
            var stats = rooms().map(function (r) { return roomStats(r, days.length ? days : [E.today()]); });
            E.chart('ex-acts', { type: 'bar', data: { labels: stats.map(function (s) { return s.room.name; }), datasets: [{ label: 'กิจกรรม', data: stats.map(function (s) { return s.activities; }), backgroundColor: '#2563EB', borderRadius: 6 }, { label: 'ผลงาน', data: stats.map(function (s) { return s.works; }), backgroundColor: '#0EA5E9', borderRadius: 6 }] } });
            starCatChart('ex-stars', list);
        } else if (id === 'attendance') {
            var per = days.map(function (d) { return counts(list, [d]); });
            E.chart('ex-att-stack', { type: 'bar', data: { labels: dayLabels, datasets: [['present', 'มาเรียน', '#22C55E'], ['late', 'มาสาย', '#F59E0B'], ['absent', 'ขาด', '#EF4444'], ['leave', 'ลา', '#0EA5E9']].map(function (k) { return { label: k[1], data: per.map(function (c) { return c[k[0]]; }), backgroundColor: k[2], stack: 'a' }; }) }, options: { scales: { x: { stacked: true }, y: { stacked: true } } } });
            var rs = rooms().map(function (r) { return roomStats(r, days); });
            E.chart('ex-att-room', { type: 'bar', data: { labels: rs.map(function (s) { return s.room.name; }), datasets: [{ label: 'อัตรามาเรียน (%)', data: rs.map(function (s) { return s.period.rate; }), backgroundColor: rs.map(function (s) { return s.period.rate >= 95 ? '#22C55E' : (s.period.rate >= 90 ? '#2563EB' : '#F59E0B'); }), borderRadius: 6 }] }, options: { indexAxis: 'y', scales: { x: { min: 70, max: 100 } }, plugins: { legend: { display: false } } } });
        } else if (id === 'activities') {
            var ids = rooms().map(function (r) { return r.id; });
            var acts = E.data().activities.filter(function (a) { return ids.indexOf(a.classroom) !== -1 && a.date >= rg.from; });
            var cats = {};
            acts.forEach(function (a) { cats[a.category] = (cats[a.category] || 0) + 1; });
            E.chart('ex-act-cat', { type: 'doughnut', data: { labels: Object.keys(cats), datasets: [{ data: Object.values(cats), backgroundColor: ['#2563EB', '#0EA5E9', '#14B8A6', '#F59E0B', '#EC4899', '#8B5CF6', '#22C55E', '#F97316'] }] }, options: { plugins: { legend: { position: 'right' } } } });
            E.chart('ex-act-room', { type: 'bar', data: { labels: rooms().map(function (r) { return r.name; }), datasets: [{ label: 'กิจกรรม', data: rooms().map(function (r) { return acts.filter(function (a) { return a.classroom === r.id; }).length; }), backgroundColor: '#2563EB', borderRadius: 6 }] }, options: { plugins: { legend: { display: false } } } });
        } else if (id === 'portfolio') {
            var cats2 = ['ศิลปะ', 'คณิตศาสตร์', 'วิทยาศาสตร์', 'ภาษา', 'ดนตรี', 'ทักษะชีวิต'];
            var pf = E.data().portfolio.filter(function (p) { return p.date >= rg.from; });
            E.chart('ex-pf', { type: 'bar', data: { labels: rooms().map(function (r) { return r.name; }), datasets: cats2.map(function (c, i) { return { label: c, data: rooms().map(function (r) { var ids2 = Q.studentsIn(r.id).map(function (s) { return s.id; }); return pf.filter(function (p) { return p.category === c && ids2.indexOf(p.studentId) !== -1; }).length; }), backgroundColor: ['#2563EB', '#0EA5E9', '#14B8A6', '#F59E0B', '#EC4899', '#8B5CF6'][i], stack: 's' }; }) }, options: { scales: { x: { stacked: true }, y: { stacked: true } } } });
        } else if (id === 'development') {
            E.chart('ex-dev', { type: 'bar', data: { labels: rooms().map(function (r) { return r.name; }), datasets: CATS.map(function (c, i) { return { label: c, data: rooms().map(function (r) { var ids3 = Q.studentsIn(r.id).map(function (s) { return s.id; }); return E.data().stars.filter(function (x) { return x.category === c && ids3.indexOf(x.studentId) !== -1; }).reduce(function (n, x) { return n + x.count; }, 0); }), backgroundColor: ['#2563EB', '#0EA5E9', '#F59E0B', '#EC4899', '#14B8A6'][i], borderRadius: 4 }; }) } });
        }
    }
    function starCatChart(id, list) {
        var ids = list.map(function (s) { return s.id; });
        var cats = {};
        E.data().stars.filter(function (x) { return ids.indexOf(x.studentId) !== -1; }).forEach(function (x) { cats[x.category] = (cats[x.category] || 0) + x.count; });
        E.chart(id, { type: 'polarArea', data: { labels: CATS, datasets: [{ data: CATS.map(function (c) { return cats[c] || 0; }), backgroundColor: ['rgba(37,99,235,.7)', 'rgba(14,165,233,.7)', 'rgba(245,158,11,.7)', 'rgba(236,72,153,.7)', 'rgba(20,184,166,.7)'] }] }, options: { plugins: { legend: { position: 'right' } }, scales: { r: { ticks: { display: false } } } } });
    }

    /* Wiring ---------------------------------------------------------------------------------- */
    function contextSlot(slot, label) {
        var f = filter();
        label.textContent = 'ผู้บริหาร · มุมมองเครือข่ายโรงเรียน';
        slot.innerHTML = '<label class="ctx"><i class="bi bi-building"></i><select class="form-select form-select-sm" data-ctx="school" aria-label="โรงเรียน"><option value="all">ทุกโรงเรียน</option>' + E.data().schools.map(function (s) { return '<option value="' + s.id + '"' + (f.school === s.id ? ' selected' : '') + '>' + esc(s.short) + '</option>'; }).join('') + '</select></label>'
            + '<label class="ctx"><i class="bi bi-calendar-range"></i><select class="form-select form-select-sm" data-ctx="period" aria-label="ช่วงเวลา">' + Object.keys(PERIODS).map(function (k) { return '<option value="' + k + '"' + (f.period === k ? ' selected' : '') + '>' + PERIODS[k] + '</option>'; }).join('') + '</select></label>';
        slot.querySelector('[data-ctx=school]').value = f.school;
        slot.querySelectorAll('[data-ctx]').forEach(function (sel) { sel.onchange = function () { ui.room = ''; setFilter(sel.getAttribute('data-ctx'), sel.value); }; });
    }
    function bell() {
        var list = E.data().notifications.slice(0, 6);
        document.getElementById('bell-title').textContent = 'Notification Log ล่าสุด (ทั้งระบบ)';
        document.getElementById('bell-count').hidden = true;
        document.getElementById('bell-more').setAttribute('href', '#announcements');
        document.getElementById('bell-more').textContent = 'ประกาศและกิจกรรม';
        var mark = document.querySelector('[data-action="mark-all-read"]');
        if (mark) mark.hidden = true;
        document.getElementById('bell-list').innerHTML = list.length ? list.map(P.notifRow).join('') : E.empty('bi-bell-slash', 'ยังไม่มีการแจ้งเตือน');
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-act]');
        if (!b) return;
        var act = b.getAttribute('data-act'), id = b.getAttribute('data-id');
        E.log('Executive', 'action', act, { id: id });
        if (act === 'room') roomModal(id);
        else if (act === 'student') studentModal(id);
        else if (act === 'more') { ui.limit += 30; E.app.render(); }
        else if (act === 'report') { ui.report = ['attendance', 'activity', 'student'].indexOf(id) !== -1 ? id : 'attendance'; E.app.render(); }
        else if (act === 'csv') exportCsv();
        else if (act === 'absent-list') {
            e.preventDefault();
            var abs = students().filter(function (s) { return Q.status(E.today(), s.id) === 'absent'; });
            E.modal({ title: '<i class="bi bi-x-circle text-danger me-2"></i>ขาดเรียนวันนี้ (' + abs.length + ' คน)', body: abs.length ? '<ul class="list-plain">' + abs.map(function (s) { return '<li>' + E.avatar(s, 'xs') + ' ' + esc(s.nickname) + ' · ' + esc(Q.fullName(s)) + ' <small class="text-secondary">' + esc(Q.classroom(s.classroom).name) + '</small></li>'; }).join('') + '</ul>' : E.empty('bi-emoji-smile', 'วันนี้ไม่มีนักเรียนขาดเรียน') });
        }
    });
    document.addEventListener('keydown', function (e) {
        var row = e.target.closest && e.target.closest('tr[data-act]');
        if (row && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); row.click(); }
    });
    document.addEventListener('input', function (e) {
        if (e.target.getAttribute && e.target.getAttribute('data-input') === 'q') {
            ui.query = e.target.value.slice(0, 60); ui.limit = 30;
            E.app.render();
            var el = document.querySelector('[data-input="q"]');
            if (el) { el.focus(); el.setSelectionRange(el.value.length, el.value.length); }
        }
    });
    document.addEventListener('change', function (e) {
        if (e.target.getAttribute && e.target.getAttribute('data-input') === 'room') { ui.room = e.target.value; ui.limit = 30; e.target.blur(); E.app.render(); }
    });
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-form="announce"]');
        if (!form) return;
        e.preventDefault();
        var v = E.readForm(form);
        if (!v) return;
        var school = ['all', 'SCH1', 'SCH2'].indexOf(v.school) !== -1 ? v.school : 'all';
        if (E.saved(E.store.update('announce:add', function (d) { d.announcements.push({ id: E.store.id('AN'), date: E.today(), title: v.title, body: v.body, audience: 'all', school: school, by: 'ผู้บริหาร', pinned: false }); }), 'เผยแพร่ประกาศแล้ว')) {
            var r = E.notify.emit('announcement', { school: school, title: 'ประกาศใหม่: ' + v.title, body: v.body.slice(0, 80), link: 'announcements' });
            if (r.sent) E.toast('<strong>Notification Engine</strong>: ส่งแจ้งเตือนถึงผู้ปกครอง ' + r.sent + ' คน ผ่าน ' + r.channels.map(E.notify.channelLabel).join(' · '), 'notify');
        }
    });

    E.app.register({
        overview: overview, students: studentsView, attendance: attendance, activities: activitiesView,
        portfolio: portfolioView, development: development, reports: reports, announcements: announcements
    }, { context: contextSlot, bell: bell, after: after });
})();
