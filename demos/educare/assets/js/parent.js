/**
 * EduCare — Parent dashboard (10 menus). Read-only view of the shared store for the
 * demo parent's children; new notifications from the teacher appear immediately
 * (same tab or another tab via the storage event).
 */
(function () {
    'use strict';

    var E = window.EDU, Q = E.Q, esc = E.esc, P = window.EDU_PARTS;
    var CHILD_KEY = 'educare.parent.child';
    var ui = { attMonth: null, notifFilter: 'all', seen: null };
    var MEAL_TYPES = { morning: 'อาหารว่างเช้า', lunch: 'อาหารกลางวัน', snack: 'อาหารว่างบ่าย' };

    function me() { return Q.parent(E.data().meta.demoParent); }
    function children() { return me().children.map(Q.student); }
    function child() {
        var id = null;
        try { id = localStorage.getItem(CHILD_KEY); } catch (e) { /* ignore */ }
        return Q.student(me().children.indexOf(id) !== -1 ? id : me().children[0]);
    }
    function setChild(id) {
        try { localStorage.setItem(CHILD_KEY, id); } catch (e) { /* ignore */ }
        E.log('Parent', 'setChild', 'child changed', { child: id });
        E.app.render();
    }
    function myNotifs() { return E.data().notifications.filter(function (n) { return n.parent === me().id; }); }
    function head(title, sub, actions) {
        return '<div class="page-head"><div><h2>' + title + '</h2>' + (sub ? '<p>' + sub + '</p>' : '') + '</div>' + (actions ? '<div class="page-head__actions">' + actions + '</div>' : '') + '</div>';
    }
    function roomActs(c) { return E.data().activities.filter(function (a) { return a.classroom === c.classroom; }).sort(function (a, b) { return (b.date + b.time).localeCompare(a.date + a.time); }); }
    function attRate(c, days) {
        var n = 0, ok = 0;
        days.forEach(function (d) { var s = Q.status(d, c.id); if (s !== 'none') { n++; if (s === 'present' || s === 'late') ok++; } });
        return n ? Math.round(ok / n * 1000) / 10 : null;
    }
    function notifItem(n) {
        var s = Q.student(n.studentId);
        var icon = { student_absent: 'bi-x-circle-fill text-danger', student_late: 'bi-clock-fill text-warning', student_present: 'bi-check-circle-fill text-success', daily_activity: 'bi-camera-fill text-primary', portfolio_added: 'bi-images text-info', star_awarded: 'bi-star-fill text-warning', announcement: 'bi-megaphone-fill text-primary', school_event: 'bi-calendar-event-fill text-info' }[n.event] || 'bi-bell-fill';
        return '<div class="notif-item' + (n.read ? '' : ' is-unread') + '"><i class="bi ' + icon + '"></i><div class="notif-item__body"><strong>' + esc(n.title) + '</strong><p>' + esc(n.body) + '</p><small>' + esc(s ? s.nickname : '') + ' · ' + E.ago(n.ts) + '</small><div class="chans">' + E.channelBadges(n.channels) + '</div></div>'
            + (n.read ? '' : '<button type="button" class="btn btn-sm btn-link" data-act="read" data-id="' + n.id + '">อ่านแล้ว</button>') + '</div>';
    }

    /* Overview ------------------------------------------------------------------ */
    function overview() {
        var c = child(), r = Q.classroom(c.classroom), today = E.today(), a = Q.att(today, c.id), d = E.data();
        var act = roomActs(c)[0];
        var month = today.slice(0, 7);
        var starsMonth = d.stars.filter(function (x) { return x.studentId === c.id && x.date.slice(0, 7) === month; }).reduce(function (n, x) { return n + x.count; }, 0);
        var works = d.portfolio.filter(function (x) { return x.studentId === c.id; }).sort(function (x, y) { return y.date.localeCompare(x.date); });
        var unread = myNotifs().filter(function (n) { return !n.read; });
        var mealsToday = d.meals.filter(function (m) { return m.school === c.school && m.date === today; });
        var teacher = Q.teacher(r.homeroom);
        var days = Q.schoolDays(E.addDays(today, -30), today);
        var status = a ? a.status : 'none';

        return '<div class="child-hero"><div class="child-hero__who">' + E.avatar(c, 'xl') + '<div><p class="child-hero__hi">สวัสดี ' + esc(me().name) + '</p><h2>' + esc(c.nickname) + '</h2><p>' + esc(Q.fullName(c)) + ' · ' + esc(r.label) + '<br>' + esc(Q.school(c.school).name) + ' · ครูประจำชั้น ' + esc(teacher.name) + '</p></div></div>'
            + '<div class="child-hero__today"><small>สถานะวันนี้ · ' + E.thaiDate(today) + '</small>' + E.statusBadge(status) + (a && a.time ? '<span class="child-hero__time">เข้าเรียน ' + E.thaiTime(a.time) + '</span>' : (status === 'none' ? '<span class="child-hero__time">รอครูเช็คชื่อ</span>' : '')) + '</div></div>'
            + (status === 'absent' ? '<div class="alert alert-danger d-flex gap-2"><i class="bi bi-exclamation-octagon"></i><div><strong>' + esc(c.nickname) + ' ถูกบันทึกว่าขาดเรียนวันนี้</strong><br><small>หากมีเหตุจำเป็น กรุณาติดต่อ' + esc(teacher.name) + '</small></div></div>' : '')
            + '<div class="stat-grid">'
            + E.stat('bi-calendar-check', 'อัตราการมาเรียน (30 วัน)', (attRate(c, days) === null ? '–' : attRate(c, days) + '%'), 'success')
            + E.stat('bi-star-fill', 'ดาวสะสมทั้งหมด', Q.starsOf(c.id) + ' <small>ดวง</small>', 'warning', 'เดือนนี้ ' + starsMonth + ' ดวง')
            + E.stat('bi-images', 'ผลงาน', works.length + ' <small>ชิ้น</small>', 'info')
            + E.stat('bi-bell', 'แจ้งเตือนที่ยังไม่อ่าน', unread.length + ' <small>รายการ</small>', unread.length ? 'danger' : 'secondary', '<a href="#notifications">ดูทั้งหมด →</a>')
            + '</div><div class="row g-3"><div class="col-lg-7">'
            + '<div class="card-x mb-3"><div class="card-x__head"><h3>กิจกรรมล่าสุดของห้อง</h3><a href="#activities">ทั้งหมด</a></div>' + (act ? P.activityItem(act) : E.empty('bi-journal', 'ยังไม่มีกิจกรรม')) + '</div>'
            + '<div class="card-x"><div class="card-x__head"><h3>ผลงานล่าสุด</h3><a href="#portfolio">ทั้งหมด</a></div>' + (works.length ? '<div class="works works--sm">' + works.slice(0, 3).map(P.workCard).join('') + '</div>' : E.empty('bi-images', 'ยังไม่มีผลงาน')) + '</div>'
            + '</div><div class="col-lg-5">'
            + '<div class="card-x mb-3"><div class="card-x__head"><h3>แจ้งเตือนล่าสุด</h3><a href="#notifications">ทั้งหมด</a></div>' + (myNotifs().length ? myNotifs().slice(0, 3).map(notifItem).join('') : E.empty('bi-bell-slash', 'ไม่มีการแจ้งเตือน')) + '</div>'
            + '<div class="card-x"><div class="card-x__head"><h3>อาหารวันนี้</h3><a href="#meals">ทั้งสัปดาห์</a></div>' + (mealsToday.length ? mealsToday.map(function (m) { return '<div class="meal-item meal-item--' + m.type + '"><small>' + MEAL_TYPES[m.type] + '</small><strong>' + esc(m.menu) + '</strong></div>'; }).join('') : E.empty('bi-egg-fried', 'ไม่มีเมนูอาหารวันนี้')) + '</div>'
            + '</div></div>';
    }

    /* Activities ------------------------------------------------------------- */
    function activities() {
        var c = child(), list = roomActs(c);
        return head('กิจกรรมประจำวัน', 'ห้อง ' + esc(Q.classroom(c.classroom).name) + ' · คลิกที่รูปเพื่อดูภาพใหญ่')
            + (list.length ? '<div class="feed">' + list.map(function (a) {
                return '<article class="feed-card"><header><span class="chip">' + esc(a.category) + '</span><small>' + E.thaiDate(a.date, 'long') + ' · ' + E.thaiTime(a.time) + '</small></header><h3>' + esc(a.title) + '</h3><p>' + esc(a.description) + '</p>'
                    + '<div class="feed-card__photos">' + a.photos.map(function (p) { return '<button type="button" class="feed-photo" data-act="photo" data-src="' + esc(p) + '" data-title="' + esc(a.title) + '">' + E.photo(p) + '</button>'; }).join('') + '</div><footer><i class="bi bi-person-badge"></i> ' + esc(Q.authorName(a.by)) + '</footer></article>';
            }).join('') + '</div>' : E.empty('bi-journal', 'ยังไม่มีกิจกรรม'));
    }

    /* Attendance -------------------------------------------------------------- */
    function attendance() {
        var c = child(), month = ui.attMonth || E.today().slice(0, 7);
        var first = E.parse(month + '-01');
        var start = new Date(first); start.setDate(1 - ((first.getDay() + 6) % 7));
        var counts = { present: 0, late: 0, absent: 0, leave: 0 };
        var cells = '';
        for (var i = 0; i < 42; i++) {
            var d = new Date(start); d.setDate(start.getDate() + i);
            var day = E.iso(d), st = Q.status(day, c.id), inMonth = day.slice(0, 7) === month;
            if (inMonth && counts[st] !== undefined) counts[st]++;
            var a = Q.att(day, c.id);
            cells += '<div class="cal__cell' + (inMonth ? '' : ' is-out') + (day === E.today() ? ' is-today' : '') + '"><span class="cal__num">' + d.getDate() + '</span>' + (st !== 'none' && inMonth ? '<span class="cal__status cal__status--' + E.STATUS[st].tone + '"><i class="bi ' + E.STATUS[st].icon + '"></i><span>' + E.STATUS[st].label + '</span>' + (a.time ? '<small>' + a.time + '</small>' : '') + '</span>' : '') + '</div>';
        }
        var total = counts.present + counts.late + counts.absent + counts.leave;
        return head('การเข้าเรียน', esc(c.nickname) + ' · ห้อง ' + esc(Q.classroom(c.classroom).name))
            + '<div class="stat-grid">' + E.stat('bi-check-circle', 'มาเรียน', counts.present + ' วัน', 'success') + E.stat('bi-clock', 'มาสาย', counts.late + ' วัน', 'warning') + E.stat('bi-x-circle', 'ขาดเรียน', counts.absent + ' วัน', 'danger') + E.stat('bi-percent', 'อัตราการมาเรียน', total ? Math.round((counts.present + counts.late) / total * 1000) / 10 + '%' : '–', 'primary', E.thaiDate(month + '-01', 'month')) + '</div>'
            + '<div class="card-x"><div class="cal__nav"><button type="button" class="btn btn-light btn-sm" data-act="att-month" data-step="-1" aria-label="เดือนก่อน"><i class="bi bi-chevron-left"></i></button><h3>' + E.thaiDate(month + '-01', 'month') + '</h3><button type="button" class="btn btn-light btn-sm" data-act="att-month" data-step="1" aria-label="เดือนถัดไป"><i class="bi bi-chevron-right"></i></button></div>'
            + '<div class="cal cal--att"><div class="cal__dow">' + ['จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส', 'อา'].map(function (x) { return '<span>' + x + '</span>'; }).join('') + '</div><div class="cal__grid">' + cells + '</div></div></div>';
    }

    /* Meals ------------------------------------------------------------------------ */
    function meals() {
        var c = child(), today = E.today();
        var monday = E.parse(today); monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7));
        var days = [0, 1, 2, 3, 4].map(function (i) { return E.addDays(E.iso(monday), i); });
        var list = E.data().meals.filter(function (m) { return m.school === c.school; });
        var allergyWord = c.allergy ? c.allergy.replace('แพ้', '').replace('นมวัว', 'นม') : '';
        return head('เมนูอาหาร', esc(Q.school(c.school).name) + ' · สัปดาห์นี้')
            + (c.allergy ? '<div class="alert alert-warning d-flex gap-2"><i class="bi bi-exclamation-triangle"></i><div>ข้อมูลสุขภาพของ' + esc(c.nickname) + ': <strong>' + esc(c.allergy) + '</strong> — ระบบจะไฮไลต์เมนูที่อาจมีส่วนผสมนี้</div></div>' : '')
            + '<div class="meal-grid">' + days.map(function (day) {
                var items = list.filter(function (m) { return m.date === day; });
                return '<div class="meal-day' + (day === today ? ' is-today' : '') + '"><h4>' + E.DAYS[E.parse(day).getDay()] + '<small>' + E.thaiDate(day, 'day') + '</small></h4>'
                    + (items.length ? ['morning', 'lunch', 'snack'].map(function (t) { return items.filter(function (m) { return m.type === t; }).map(function (m) { var warn = allergyWord && (m.menu + m.note).indexOf(allergyWord) !== -1; return '<div class="meal-item meal-item--' + t + (warn ? ' is-warn' : '') + '"><small>' + MEAL_TYPES[t] + '</small><strong>' + esc(m.menu) + '</strong>' + (warn ? '<em><i class="bi bi-exclamation-triangle-fill"></i> ระวัง: ' + esc(c.allergy) + '</em>' : (m.note ? '<em>' + esc(m.note) + '</em>' : '')) + '</div>'; }).join(''); }).join('') : '<p class="text-secondary small">ยังไม่มีเมนู</p>') + '</div>';
            }).join('') + '</div>';
    }

    /* Portfolio ---------------------------------------------------------------------- */
    function portfolio() {
        var c = child(), list = E.data().portfolio.filter(function (p) { return p.studentId === c.id; }).sort(function (a, b) { return b.date.localeCompare(a.date); });
        return head('Student Portfolio', 'ผลงานของ' + esc(c.nickname) + ' · ' + list.length + ' ชิ้น')
            + (list.length ? '<div class="works">' + list.map(function (w) { return '<button type="button" class="work-btn" data-act="work" data-id="' + w.id + '">' + P.workCard(w) + '</button>'; }).join('') + '</div>' : E.empty('bi-images', 'ยังไม่มีผลงาน'));
    }

    /* Stars --------------------------------------------------------------------------- */
    function stars() {
        var c = child(), list = E.data().stars.filter(function (x) { return x.studentId === c.id; }).sort(function (a, b) { return (b.date + b.id).localeCompare(a.date + a.id); });
        var cats = {};
        list.forEach(function (x) { cats[x.category] = (cats[x.category] || 0) + x.count; });
        var total = Q.starsOf(c.id);
        return head('ดาวสะสม', esc(c.nickname) + ' · ห้อง ' + esc(Q.classroom(c.classroom).name))
            + '<div class="row g-3"><div class="col-lg-4"><div class="star-total"><span>⭐</span><strong>' + total + '</strong><p>ดาวสะสมทั้งหมด</p><small>เป้าหมายรับเกียรติบัตร 20 ดวง</small><div class="progress mt-2" role="progressbar" aria-valuenow="' + Math.min(100, total * 5) + '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-warning" style="width:' + Math.min(100, total * 5) + '%"></div></div></div>'
            + '<div class="card-x mt-3"><h3 class="card-x__title">ดาวตามหมวด</h3><div class="chart-box" style="height:220px"><canvas id="pc-star-cat"></canvas></div></div></div>'
            + '<div class="col-lg-8"><div class="card-x"><div class="card-x__head"><h3>ประวัติการได้รับดาว</h3></div>' + (list.length ? '<ul class="timeline">' + list.map(function (x) { return '<li><span class="timeline__dot">⭐</span><div><strong>' + '⭐'.repeat(x.count) + ' ' + esc(x.reason) + '</strong><small>' + esc(x.category) + ' · ' + E.thaiDate(x.date) + ' · ' + esc(Q.authorName(x.by)) + '</small></div></li>'; }).join('') + '</ul>' : E.empty('bi-star', 'ยังไม่มีดาว')) + '</div></div></div>';
    }

    /* Announcements, calendar --------------------------------------------------------- */
    function announcements() {
        var c = child();
        var list = E.data().announcements.filter(function (a) { return (a.school === 'all' || a.school === c.school) && (a.audience === 'all' || a.audience === c.classroom); }).sort(function (a, b) { return (b.pinned - a.pinned) || b.date.localeCompare(a.date); });
        return head('ประกาศโรงเรียน', esc(Q.school(c.school).name) + ' · ห้อง ' + esc(Q.classroom(c.classroom).name)) + (list.length ? list.map(P.announceCard).join('') : E.empty('bi-megaphone', 'ยังไม่มีประกาศ'));
    }
    function calendar() {
        var c = child();
        return head('ปฏิทินกิจกรรม', esc(Q.school(c.school).name)) + P.calendarView(function (ev) { return (ev.school === 'all' || ev.school === c.school) && (ev.audience === 'all' || ev.audience === c.classroom); }, false);
    }

    /* Development report ---------------------------------------------------------------- */
    function development() {
        var c = child(), d = E.data(), today = E.today();
        var days = Q.schoolDays(E.addDays(today, -30), today);
        var rate = attRate(c, days);
        var stars = d.stars.filter(function (x) { return x.studentId === c.id; });
        var cats = {};
        stars.forEach(function (x) { cats[x.category] = (cats[x.category] || 0) + x.count; });
        var works = d.portfolio.filter(function (x) { return x.studentId === c.id; });
        var acts = roomActs(c).filter(function (a) { return a.date >= E.addDays(today, -30); });
        var top = Object.keys(cats).sort(function (a, b) { return cats[b] - cats[a]; })[0];
        var comment = esc(c.nickname) + (rate !== null && rate >= 95 ? 'มาเรียนสม่ำเสมอ ' : 'ควรส่งเสริมให้มาเรียนสม่ำเสมอมากขึ้น ') + (top ? 'มีความโดดเด่นด้าน' + esc(top) + ' ' : '') + 'ร่วมกิจกรรมของห้อง ' + acts.length + ' กิจกรรมในเดือนที่ผ่านมา และมีผลงาน ' + works.length + ' ชิ้น ครูแนะนำให้ผู้ปกครองชวนพูดคุยเรื่องกิจกรรมในแต่ละวันเพื่อส่งเสริมทักษะการสื่อสาร';
        return head('รายงานพัฒนาการ', esc(Q.fullName(c)) + ' · ข้อมูล 30 วันล่าสุด', '<button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>พิมพ์รายงาน</button>')
            + '<div class="report-sheet"><div class="stat-grid">' + E.stat('bi-calendar-check', 'อัตราการมาเรียน', rate === null ? '–' : rate + '%', 'success') + E.stat('bi-star-fill', 'ดาวสะสม', Q.starsOf(c.id) + ' ดวง', 'warning') + E.stat('bi-images', 'ผลงาน', works.length + ' ชิ้น', 'info') + E.stat('bi-journal-text', 'กิจกรรมที่ร่วม', acts.length + ' กิจกรรม', 'primary') + '</div>'
            + '<div class="row g-3"><div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">พัฒนาการตามหมวด (จากดาวสะสม)</h3><div class="chart-box" style="height:260px"><canvas id="pc-dev"></canvas></div></div></div>'
            + '<div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">ความเห็นจากครูประจำชั้น</h3><p class="lead-sm">' + comment + '</p><p class="text-secondary small mb-0">— ' + esc(Q.teacher(Q.classroom(c.classroom).homeroom).name) + ' (สร้างจากข้อมูลในระบบเพื่อการสาธิต)</p></div>'
            + '<div class="card-x mt-3"><h3 class="card-x__title">ผลงานตามหมวด</h3><ul class="list-plain">' + (works.length ? Object.entries(works.reduce(function (o, w) { o[w.category] = (o[w.category] || 0) + 1; return o; }, {})).map(function (kv) { return '<li>' + esc(kv[0]) + ' <b>' + kv[1] + '</b> ชิ้น</li>'; }).join('') : '<li class="text-secondary">ยังไม่มีผลงาน</li>') + '</ul></div></div></div></div>';
    }

    /* Notifications ------------------------------------------------------------------------ */
    function notifications() {
        var all = myNotifs();
        var list = ui.notifFilter === 'unread' ? all.filter(function (n) { return !n.read; }) : all;
        var rules = E.data().rules;
        return head('การแจ้งเตือน', 'จาก Notification Engine · สถานะการส่งแยกตามช่องทาง (จำลอง)', '<button type="button" class="btn btn-outline-primary btn-sm" data-act="read-all"><i class="bi bi-check2-all me-1"></i>อ่านทั้งหมด</button>')
            + '<div class="row g-3"><div class="col-xl-8"><div class="card-x"><div class="card-x__head"><div class="btn-group btn-group-sm" role="group"><button type="button" class="btn ' + (ui.notifFilter === 'all' ? 'btn-primary' : 'btn-light') + '" data-act="nfilter" data-id="all">ทั้งหมด (' + all.length + ')</button><button type="button" class="btn ' + (ui.notifFilter === 'unread' ? 'btn-primary' : 'btn-light') + '" data-act="nfilter" data-id="unread">ยังไม่อ่าน (' + all.filter(function (n) { return !n.read; }).length + ')</button></div></div>'
            + (list.length ? list.slice(0, 60).map(notifItem).join('') : E.empty('bi-bell-slash', 'ไม่มีการแจ้งเตือน')) + '</div></div>'
            + '<div class="col-xl-4"><div class="card-x mb-3"><h3 class="card-x__title">ช่องทางของฉัน</h3><ul class="list-plain"><li><i class="bi bi-app-indicator text-primary"></i> In-App — เปิดใช้งาน</li><li><i class="bi bi-phone-vibrate text-primary"></i> Push Notification — เปิดใช้งาน</li><li><i class="bi bi-line text-success"></i> LINE — ' + (me().line ? 'เชื่อมต่อแล้ว' : 'ยังไม่เชื่อมต่อ') + '</li></ul><p class="small text-secondary mb-0">Demo จำลองสถานะการส่ง ไม่มีการส่ง Push หรือ LINE จริง</p></div>'
            + '<div class="card-x"><h3 class="card-x__title">กฎการแจ้งเตือนของโรงเรียน</h3><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>เหตุการณ์</th><th>ช่องทาง</th><th>สถานะ</th></tr></thead><tbody>' + rules.map(function (r) { return '<tr><td><small class="d-block text-secondary"><code>' + esc(r.event) + '</code></small>' + esc(r.label) + '</td><td>' + r.channels.map(function (ch) { return '<span class="chan chan--secondary">' + E.notify.channelLabel(ch) + '</span>'; }).join(' ') + '</td><td>' + (r.enabled ? '<span class="text-success small">เปิด · ทันที</span>' : '<span class="text-secondary small">ปิด</span>') + '</td></tr>'; }).join('') + '</tbody></table></div></div></div></div>';
    }

    /* Wiring --------------------------------------------------------------------------------- */
    function markRead(ids) {
        E.store.update('notif:read', function (d) { d.notifications.forEach(function (n) { if (n.parent === me().id && (!ids || ids.indexOf(n.id) !== -1)) n.read = true; }); });
    }

    function contextSlot(slot, label) {
        label.textContent = 'ผู้ปกครอง · ' + me().name;
        var kids = children();
        slot.innerHTML = '<div class="child-switch" role="group" aria-label="เลือกบุตรหลาน">' + kids.map(function (k) { return '<button type="button" class="child-switch__btn' + (k.id === child().id ? ' is-on' : '') + '" data-act="child" data-id="' + k.id + '" aria-pressed="' + (k.id === child().id) + '">' + E.avatar(k, 'xs') + '<span>' + esc(k.nickname) + '<small>' + esc(Q.classroom(k.classroom).name) + '</small></span></button>'; }).join('') + '</div>';
    }

    function bell() {
        if (ui.seen === null) ui.seen = myNotifs().map(function (n) { return n.id; });
        var list = myNotifs(), unread = list.filter(function (n) { return !n.read; }).length;
        var badge = document.getElementById('bell-count');
        badge.hidden = !unread;
        badge.textContent = unread > 99 ? '99+' : unread;
        document.getElementById('bell-title').textContent = 'การแจ้งเตือน';
        document.getElementById('bell-list').innerHTML = list.length ? list.slice(0, 6).map(notifItem).join('') : E.empty('bi-bell-slash', 'ไม่มีการแจ้งเตือน');
        var nb = document.querySelector('[data-badge="notifications"]');
        if (nb) nb.textContent = unread || '';
    }

    function after(id) {
        if (id === 'stars' || id === 'development') {
            var c = child(), cats = {};
            E.data().stars.filter(function (x) { return x.studentId === c.id; }).forEach(function (x) { cats[x.category] = (cats[x.category] || 0) + x.count; });
            var labels = ['น้ำใจ', 'การเรียนรู้', 'ความรับผิดชอบ', 'ความกล้าแสดงออก', 'ระเบียบวินัย'];
            if (id === 'stars') {
                E.chart('pc-star-cat', { type: 'doughnut', data: { labels: labels, datasets: [{ data: labels.map(function (l) { return cats[l] || 0; }), backgroundColor: ['#2563EB', '#0EA5E9', '#F59E0B', '#EC4899', '#14B8A6'], borderWidth: 2 }] }, options: { cutout: '60%', plugins: { legend: { position: 'bottom' } } } });
            } else {
                E.chart('pc-dev', { type: 'radar', data: { labels: labels, datasets: [{ label: c.nickname, data: labels.map(function (l) { return cats[l] || 0; }), backgroundColor: 'rgba(37,99,235,.18)', borderColor: '#2563EB', pointBackgroundColor: '#2563EB' }] }, options: { scales: { r: { beginAtZero: true, ticks: { stepSize: 1, display: false } } }, plugins: { legend: { display: false } } } });
            }
        }
    }

    // Toast for notifications that arrive while the parent page is open (e.g. teacher in another tab).
    E.store.onChange(function () {
        var ids = myNotifs().map(function (n) { return n.id; });
        if (ui.seen) {
            myNotifs().filter(function (n) { return ui.seen.indexOf(n.id) === -1; }).slice(0, 3).forEach(function (n) {
                E.toast('<strong>แจ้งเตือนใหม่</strong><br>' + esc(n.title), 'notify');
            });
        }
        ui.seen = ids;
    });

    document.addEventListener('click', function (e) {
        var mark = e.target.closest('[data-action="mark-all-read"]');
        if (mark) { markRead(null); E.toast('อ่านการแจ้งเตือนทั้งหมดแล้ว', 'success'); return; }
        var b = e.target.closest('[data-act]');
        if (!b) return;
        var act = b.getAttribute('data-act'), id = b.getAttribute('data-id');
        E.log('Parent', 'action', act, { id: id });
        if (act === 'child') { setChild(id); E.toast('กำลังดูข้อมูลของ ' + esc(Q.student(id).nickname), 'success'); }
        else if (act === 'read') markRead([id]);
        else if (act === 'read-all') { markRead(null); E.toast('อ่านการแจ้งเตือนทั้งหมดแล้ว', 'success'); }
        else if (act === 'nfilter') { ui.notifFilter = id === 'unread' ? 'unread' : 'all'; E.app.render(); }
        else if (act === 'att-month') { var m = E.parse((ui.attMonth || E.today().slice(0, 7)) + '-01'); m.setMonth(m.getMonth() + +b.getAttribute('data-step')); ui.attMonth = E.iso(m).slice(0, 7); E.app.render(); }
        else if (act === 'photo') E.modal({ size: 'lg', title: esc(b.getAttribute('data-title')), body: '<img class="photo-full" src="' + esc(E.photoSrc(b.getAttribute('data-src'))) + '" alt="ภาพกิจกรรม">' });
        else if (act === 'work') {
            var w = E.data().portfolio.filter(function (x) { return x.id === id; })[0];
            if (w) E.modal({ title: esc(w.title), body: '<img class="photo-full mb-3" src="' + esc(E.photoSrc(w.photo)) + '" alt="ผลงาน"><dl class="kv"><dt>หมวด</dt><dd>' + esc(w.category) + '</dd><dt>วันที่</dt><dd>' + E.thaiDate(w.date, 'long') + '</dd><dt>ครู</dt><dd>' + esc(Q.authorName(w.by)) + '</dd><dt>คำอธิบาย</dt><dd>' + esc(w.description || '–') + '</dd></dl>' });
        }
    });

    E.app.register({
        overview: overview, activities: activities, attendance: attendance, meals: meals, portfolio: portfolio, stars: stars,
        announcements: announcements, calendar: calendar, development: development, notifications: notifications
    }, { context: contextSlot, bell: bell, after: after });
})();
