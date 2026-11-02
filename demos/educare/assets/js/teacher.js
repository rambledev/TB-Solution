/**
 * EduCare — Teacher dashboard (11 menus). Every save goes to the shared store and,
 * where a rule exists, through the Notification Engine to the parents.
 */
(function () {
    'use strict';

    var E = window.EDU, Q = E.Q, esc = E.esc, P = window.EDU_PARTS;
    var ROOM_KEY = 'educare.teacher.room';
    var ui = { date: null, studentQuery: '', pfStudent: '', starStudent: '', mealWeek: 0, reportDate: null };
    var ACT_CATEGORIES = ['ศิลปะ', 'ดนตรี', 'กีฬา', 'วิทยาศาสตร์', 'ภาษา', 'คณิตศาสตร์', 'ทักษะชีวิต', 'นอกห้องเรียน'];
    var STAR_REASONS = [['ช่วยเพื่อนเก็บของเล่น', 'น้ำใจ'], ['ตั้งใจฟังครู', 'การเรียนรู้'], ['กินข้าวหมดจาน', 'ความรับผิดชอบ'], ['กล้ายกมือตอบคำถาม', 'ความกล้าแสดงออก'], ['เข้าแถวเรียบร้อย', 'ระเบียบวินัย'], ['แบ่งปันให้เพื่อน', 'น้ำใจ']];
    var MEAL_TYPES = { morning: 'อาหารว่างเช้า', lunch: 'อาหารกลางวัน', snack: 'อาหารว่างบ่าย' };

    function me() { return Q.teacher(E.data().meta.demoTeacher); }
    function rooms() { return me().classrooms.map(Q.classroom); }
    function room() {
        var id = null;
        try { id = localStorage.getItem(ROOM_KEY); } catch (e) { /* ignore */ }
        return Q.classroom(me().classrooms.indexOf(id) !== -1 ? id : me().classrooms[0]);
    }
    function setRoom(id) {
        try { localStorage.setItem(ROOM_KEY, id); } catch (e) { /* ignore */ }
        E.log('Teacher', 'setRoom', 'room changed', { room: id });
        E.app.render();
        E.toast('สลับเป็นห้อง ' + esc(Q.classroom(id).name), 'success');
    }
    function students() { return Q.studentsIn(room().id); }
    function head(title, sub, actions) {
        return '<div class="page-head"><div><h2>' + title + '</h2>' + (sub ? '<p>' + sub + '</p>' : '') + '</div>' + (actions ? '<div class="page-head__actions">' + actions + '</div>' : '') + '</div>';
    }
    function attSummary(date, list) {
        var s = { present: 0, late: 0, absent: 0, leave: 0, none: 0 };
        list.forEach(function (st) { s[Q.status(date, st.id)]++; });
        return s;
    }
    function notifySummary(result, who) {
        if (!result.sent) return;
        E.toast('<strong>Notification Engine</strong>: ส่งแจ้งเตือนถึง' + esc(who) + ' ผ่าน ' + result.channels.map(E.notify.channelLabel).join(' · '), 'notify');
    }

    /* Dashboard ------------------------------------------------------------- */
    function dashboard() {
        var r = room(), list = students(), today = E.today(), s = attSummary(today, list);
        var d = E.data();
        var acts = d.activities.filter(function (a) { return a.classroom === r.id; }).sort(function (a, b) { return (b.date + b.time).localeCompare(a.date + a.time); }).slice(0, 3);
        var since = E.addDays(today, -30);
        var top = list.map(function (st) { return { st: st, n: d.stars.filter(function (x) { return x.studentId === st.id && x.date >= since; }).reduce(function (a, x) { return a + x.count; }, 0) }; }).sort(function (a, b) { return b.n - a.n; }).slice(0, 5);
        var events = d.events.filter(function (ev) { return ev.date >= today && (ev.audience === 'all' || ev.audience === r.id); }).sort(function (a, b) { return a.date.localeCompare(b.date); }).slice(0, 3);
        var sent = d.notifications.filter(function (n) { var st = Q.student(n.studentId); return st && st.classroom === r.id; }).slice(0, 4);

        return head('สวัสดี ' + esc(me().name) + ' 👋', E.thaiDate(today, 'long') + ' · ห้อง ' + esc(r.name) + ' (' + esc(Q.school(r.school).name) + ')',
            '<a class="btn btn-primary rounded-pill" href="#attendance"><i class="bi bi-clipboard-check me-1"></i>เช็คชื่อวันนี้</a>')
            + (!E.isWeekday(today) ? '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>วันนี้เป็นวันหยุด ทดลองเช็คชื่อได้ตามปกติในโหมด Demo</div>' : '')
            + '<div class="stat-grid">'
            + E.stat('bi-people', 'นักเรียนในห้อง', list.length + ' <small>คน</small>', 'primary')
            + E.stat('bi-check-circle', 'มาเรียน', (s.present + s.late) + ' <small>คน</small>', 'success', 'มาสาย ' + s.late + ' คน')
            + E.stat('bi-x-circle', 'ขาด / ลา', (s.absent + s.leave) + ' <small>คน</small>', 'danger', 'ขาด ' + s.absent + ' · ลา ' + s.leave)
            + E.stat('bi-hourglass-split', 'ยังไม่เช็คชื่อ', s.none + ' <small>คน</small>', s.none ? 'warning' : 'secondary', s.none ? '<a href="#attendance">เช็คชื่อเลย →</a>' : 'เช็คครบแล้ว')
            + '</div>'
            + '<div class="quick-actions">'
            + '<a href="#attendance" class="qa"><i class="bi bi-clipboard-check"></i>เช็คชื่อ</a>'
            + '<a href="#activities" class="qa"><i class="bi bi-camera"></i>บันทึกกิจกรรม</a>'
            + '<button type="button" class="qa" data-act="star-pick"><i class="bi bi-star"></i>มอบดาว</button>'
            + '<button type="button" class="qa" data-act="portfolio-add"><i class="bi bi-images"></i>เพิ่มผลงาน</button>'
            + '<a href="#announcements" class="qa"><i class="bi bi-megaphone"></i>ประกาศ</a>'
            + '<a href="#reports" class="qa"><i class="bi bi-file-earmark-text"></i>รายงานวันนี้</a>'
            + '</div>'
            + '<div class="row g-3">'
            + '<div class="col-lg-7"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมล่าสุด</h3><a href="#activities">ทั้งหมด</a></div>'
            + (acts.length ? acts.map(P.activityItem).join('') : E.empty('bi-journal', 'ยังไม่มีกิจกรรม')) + '</div></div>'
            + '<div class="col-lg-5">'
            + '<div class="card-x mb-3"><div class="card-x__head"><h3>⭐ ดาวสะสม 30 วันล่าสุด</h3><a href="#stars">ระบบดาว</a></div><ol class="rank">' + top.map(function (t, i) { return '<li><span class="rank__no">' + (i + 1) + '</span>' + E.avatar(t.st, 'sm') + '<span class="rank__name">' + esc(t.st.nickname) + '</span><b>' + t.n + ' ⭐</b></li>'; }).join('') + '</ol></div>'
            + '<div class="card-x mb-3"><div class="card-x__head"><h3>กิจกรรมที่จะมาถึง</h3><a href="#calendar">ปฏิทิน</a></div>' + (events.length ? events.map(P.eventItem).join('') : E.empty('bi-calendar', 'ไม่มีกิจกรรมที่จะมาถึง')) + '</div>'
            + '<div class="card-x"><div class="card-x__head"><h3>การแจ้งเตือนที่ส่งล่าสุด</h3></div>' + (sent.length ? sent.map(P.notifRow).join('') : E.empty('bi-bell-slash', 'ยังไม่มีการแจ้งเตือน')) + '</div>'
            + '</div></div>';
    }


    /* Classrooms ------------------------------------------------------------------ */
    function classrooms() {
        var cur = room().id, today = E.today();
        return head('ห้องเรียนของฉัน', 'เลือกห้องที่ต้องการจัดการ · ข้อมูลทุกเมนูจะเปลี่ยนตามห้องที่เลือก')
            + '<div class="row g-3">' + rooms().map(function (r) {
                var list = Q.studentsIn(r.id), s = attSummary(today, list);
                var co = E.data().teachers.filter(function (t) { return t.id !== me().id && t.classrooms.indexOf(r.id) !== -1; });
                return '<div class="col-md-6 col-xl-4"><div class="room-card' + (r.id === cur ? ' is-active' : '') + '">'
                    + '<div class="room-card__head"><span class="room-card__name">' + esc(r.name) + '</span>' + (r.id === cur ? '<span class="badge text-bg-primary">ห้องที่เลือก</span>' : '') + '</div>'
                    + '<p class="room-card__label">' + esc(r.label) + ' · ' + esc(Q.school(r.school).short) + '</p>'
                    + '<div class="room-card__stats"><span><b>' + list.length + '</b>นักเรียน</span><span><b>' + (s.present + s.late) + '</b>มาเรียนวันนี้</span><span><b>' + s.absent + '</b>ขาด</span><span><b>' + s.none + '</b>ยังไม่เช็ค</span></div>'
                    + '<p class="small text-secondary mb-3"><i class="bi bi-person-badge"></i> ' + (r.homeroom === me().id ? 'ครูประจำชั้น' : 'ครูร่วมสอน') + (co.length ? ' · ร่วมกับ ' + co.map(function (t) { return esc(t.name); }).join(', ') : '') + '</p>'
                    + (r.id === cur ? '<a class="btn btn-outline-primary w-100" href="#students">ดูรายชื่อนักเรียน</a>' : '<button type="button" class="btn btn-primary w-100" data-act="set-room" data-id="' + r.id + '">เลือกห้องนี้</button>')
                    + '</div></div>';
            }).join('') + '</div>';
    }

    /* Students ---------------------------------------------------------------------- */
    function studentsView() {
        var q = ui.studentQuery.toLowerCase();
        var list = students().filter(function (s) { return !q || (Q.fullName(s) + ' ' + s.nickname + ' ' + s.code).toLowerCase().indexOf(q) !== -1; });
        return head('ข้อมูลนักเรียน', 'ห้อง ' + esc(room().name) + ' · ' + students().length + ' คน · คลิกที่การ์ดเพื่อดูข้อมูลรายบุคคล')
            + '<div class="toolbar"><div class="search"><i class="bi bi-search"></i><input type="search" class="form-control" placeholder="ค้นหาชื่อ ชื่อเล่น หรือรหัสนักเรียน" value="' + esc(ui.studentQuery) + '" data-input="student-search" aria-label="ค้นหานักเรียน"></div><span class="text-secondary small">พบ ' + list.length + ' คน</span></div>'
            + (list.length ? '<div class="student-grid">' + list.map(function (s) {
                return '<button type="button" class="student-card" data-act="student" data-id="' + s.id + '">' + E.avatar(s, 'lg') + '<strong>' + esc(s.nickname) + '</strong><small>' + esc(Q.fullName(s)) + '</small><span class="student-card__meta">' + E.statusBadge(Q.status(E.today(), s.id)) + '<span class="stars-mini">⭐ ' + Q.starsOf(s.id) + '</span></span>' + (s.allergy ? '<span class="allergy"><i class="bi bi-exclamation-triangle"></i> ' + esc(s.allergy) + '</span>' : '') + '</button>';
            }).join('') + '</div>' : E.empty('bi-search', 'ไม่พบนักเรียนที่ตรงกับคำค้นหา'));
    }

    function studentModal(id) {
        var s = Q.student(id), p = Q.parent(s.parent), d = E.data();
        var days = Q.schoolDays(E.addDays(E.today(), -30), E.today()).slice(-10);
        var stars = d.stars.filter(function (x) { return x.studentId === id; }).sort(function (a, b) { return b.date.localeCompare(a.date); });
        var works = d.portfolio.filter(function (x) { return x.studentId === id; });
        E.modal({
            size: 'lg',
            title: E.avatar(s, 'sm') + ' ' + esc(s.nickname) + ' <small class="text-secondary">' + esc(Q.fullName(s)) + '</small>',
            body: '<div class="row g-3"><div class="col-md-6"><div class="info-box"><h4>ข้อมูลนักเรียน</h4><dl class="kv">'
                + '<dt>รหัสนักเรียน</dt><dd>' + esc(s.code) + '</dd><dt>ห้องเรียน</dt><dd>' + esc(Q.classroom(s.classroom).label) + '</dd><dt>อายุ</dt><dd>' + E.age(s.dob) + '</dd><dt>หมู่เลือด</dt><dd>' + esc(s.blood) + '</dd><dt>ข้อควรระวัง</dt><dd>' + (s.allergy ? '<span class="text-danger">' + esc(s.allergy) + '</span>' : 'ไม่มี') + '</dd></dl></div></div>'
                + '<div class="col-md-6"><div class="info-box"><h4>ผู้ปกครอง</h4><dl class="kv"><dt>ชื่อ</dt><dd>' + esc(p.name) + '</dd><dt>โทรศัพท์</dt><dd>' + esc(p.phone) + '</dd><dt>LINE</dt><dd>' + (p.line ? '<span class="text-success"><i class="bi bi-check-circle"></i> เชื่อมต่อแล้ว</span>' : '<span class="text-secondary">ยังไม่เชื่อมต่อ</span>') + '</dd></dl></div></div></div>'
                + '<h4 class="mini-title mt-3">การเข้าเรียน 10 วันล่าสุด</h4><div class="att-strip">' + days.map(function (day) { var st = Q.status(day, id); return '<span class="att-dot att-dot--' + E.STATUS[st].tone + '" title="' + E.thaiDate(day) + ': ' + E.STATUS[st].label + '">' + E.parse(day).getDate() + '</span>'; }).join('') + '</div>'
                + '<h4 class="mini-title mt-3">ดาวสะสม ' + Q.starsOf(id) + ' ⭐</h4>' + (stars.length ? '<ul class="list-plain">' + stars.slice(0, 5).map(function (x) { return '<li>⭐×' + x.count + ' ' + esc(x.reason) + ' <small class="text-secondary">· ' + esc(x.category) + ' · ' + E.thaiDate(x.date) + '</small></li>'; }).join('') + '</ul>' : '<p class="text-secondary">ยังไม่มีดาว</p>')
                + '<h4 class="mini-title mt-3">ผลงาน (' + works.length + ')</h4><div class="thumb-row">' + works.map(function (w) { return '<figure>' + E.photo(w.photo) + '<figcaption>' + esc(w.title) + '</figcaption></figure>'; }).join('') + '</div>',
            foot: '<button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button><button type="button" class="btn btn-warning" data-act="star-award" data-id="' + id + '"><i class="bi bi-star-fill me-1"></i>มอบดาว</button>'
        });
    }

    /* Activities ------------------------------------------------------------------- */
    function activities() {
        var r = room();
        var list = E.data().activities.filter(function (a) { return a.classroom === r.id; }).sort(function (a, b) { return (b.date + b.time).localeCompare(a.date + a.time); });
        return head('บันทึกกิจกรรมประจำวัน', 'ห้อง ' + esc(r.name) + ' · ผู้ปกครองจะเห็นกิจกรรมทันทีหลังบันทึก')
            + '<div class="row g-3"><div class="col-xl-5"><form class="card-x" data-form="activity" novalidate><h3 class="card-x__title">เพิ่มกิจกรรม</h3>'
            + '<div class="mb-3"><label class="form-label" for="ac-title">ชื่อกิจกรรม *</label><input class="form-control" id="ac-title" name="title" maxlength="120" required placeholder="เช่น ระบายสีภาพครอบครัว"></div>'
            + '<div class="row g-2 mb-3"><div class="col-6"><label class="form-label" for="ac-cat">หมวด</label><select class="form-select" id="ac-cat" name="category">' + ACT_CATEGORIES.map(function (c) { return '<option>' + c + '</option>'; }).join('') + '</select></div>'
            + '<div class="col-6"><label class="form-label" for="ac-date">วันที่</label><input class="form-control" type="date" id="ac-date" name="date" value="' + E.today() + '" max="' + E.today() + '" required></div></div>'
            + '<div class="mb-3"><label class="form-label" for="ac-desc">รายละเอียด</label><textarea class="form-control" id="ac-desc" name="description" rows="3" maxlength="500" placeholder="นักเรียนได้เรียนรู้อะไร"></textarea></div>'
            + '<label class="form-label">รูปภาพกิจกรรม</label>' + E.photoPicker('ac-photos', ['art'])
            + '<button class="btn btn-primary w-100 mt-3" type="submit"><i class="bi bi-send me-1"></i>บันทึกและแจ้งผู้ปกครอง</button></form></div>'
            + '<div class="col-xl-7"><div class="card-x"><div class="card-x__head"><h3>กิจกรรมห้อง ' + esc(r.name) + ' (' + list.length + ')</h3></div>' + (list.length ? list.slice(0, 15).map(P.activityItem).join('') : E.empty('bi-journal', 'ยังไม่มีกิจกรรม')) + '</div></div></div>';
    }
    function saveActivity(form) {
        var v = E.readForm(form);
        if (!v) return;
        var photos = E.pickedPhotos(form, 'ac-photos');
        if (!photos.length) photos = ['blocks'];
        var r = room();
        var res = E.store.update('activity:add', function (d) {
            d.activities.push({ id: E.store.id('AC'), date: v.date, time: E.nowTime(), classroom: r.id, title: v.title, category: v.category, description: v.description || '—', photos: photos, by: me().id });
        });
        if (E.saved(res, 'บันทึกกิจกรรมเรียบร้อย')) {
            notifySummary(E.notify.emit('daily_activity', { classroom: r.id, title: 'กิจกรรมใหม่ห้อง ' + r.name + ': ' + v.title, body: v.category + ' · ' + photos.length + ' รูป', link: 'activities' }), 'ผู้ปกครองห้อง ' + r.name);
        }
    }

    /* Attendance ----------------------------------------------------------------------- */
    function attendance() {
        var date = ui.date || E.today(), list = students(), s = attSummary(date, list), r = room();
        var log = E.data().notifications.filter(function (n) { var st = Q.student(n.studentId); return st && st.classroom === r.id && n.ts.slice(0, 10) === E.today() && /^student_/.test(n.event); }).slice(0, 8);
        return head('การเข้าเรียน', 'ห้อง ' + esc(r.name) + ' · เลือกสถานะของนักเรียนแต่ละคน ระบบจะแจ้งผู้ปกครองตามกฎการแจ้งเตือน',
            '<input type="date" class="form-control" value="' + date + '" max="' + E.today() + '" data-input="att-date" aria-label="วันที่">')
            + '<div class="att-summary">'
            + ['present', 'late', 'absent', 'leave', 'none'].map(function (k) { return '<span class="att-summary__item att-summary__item--' + E.STATUS[k].tone + '"><i class="bi ' + E.STATUS[k].icon + '"></i>' + E.STATUS[k].label + ' <b>' + s[k] + '</b></span>'; }).join('')
            + '<button type="button" class="btn btn-success btn-sm ms-auto" data-act="all-present"' + (s.none ? '' : ' disabled') + '><i class="bi bi-check2-all me-1"></i>เช็ค “มาเรียน” ที่เหลือทั้งหมด (' + s.none + ')</button></div>'
            + '<div class="alert alert-primary small d-flex gap-2"><i class="bi bi-bell"></i><div>กฎ <code>student_absent</code> → ผู้ปกครอง · In-App, Push, LINE · ส่งทันที · <b>เปิดใช้งาน</b></div></div>'
            + '<div class="roster">' + list.map(function (st) {
                var a = Q.att(date, st.id), cur = a ? a.status : 'none';
                return '<div class="roster__row"><div class="roster__who">' + E.avatar(st, 'sm') + '<div><strong>' + esc(st.nickname) + '</strong><small>' + esc(Q.fullName(st)) + (a && a.time ? ' · ' + E.thaiTime(a.time) : '') + '</small></div></div>'
                    + '<div class="seg-status" role="group" aria-label="สถานะของ' + esc(st.nickname) + '">' + ['present', 'late', 'absent', 'leave'].map(function (k) {
                        return '<button type="button" class="seg-status__btn seg-status__btn--' + E.STATUS[k].tone + (cur === k ? ' is-on' : '') + '" data-act="att" data-id="' + st.id + '" data-status="' + k + '" aria-pressed="' + (cur === k) + '">' + E.STATUS[k].label + '</button>';
                    }).join('') + '</div></div>';
            }).join('') + '</div>'
            + '<div class="card-x mt-3"><div class="card-x__head"><h3><i class="bi bi-broadcast me-1"></i>Notification Log วันนี้ (ห้อง ' + esc(r.name) + ')</h3></div>' + (log.length ? log.map(P.notifRow).join('') : E.empty('bi-bell-slash', 'ยังไม่มีการแจ้งเตือนวันนี้ ลองกด “ขาดเรียน” ให้นักเรียนสักคน')) + '</div>';
    }
    function setAttendance(id, status, silent) {
        var date = ui.date || E.today(), st = Q.student(id);
        var prev = Q.status(date, id);
        if (prev === status) return null;
        var time = status === 'present' || status === 'late' ? E.nowTime() : '';
        var res = E.store.update('attendance:set', function (d) {
            d.attendance[date] = d.attendance[date] || {};
            d.attendance[date][id] = { status: status, time: time, by: st.classroom };
        });
        if (!res.ok) { E.saved(res, ''); return null; }
        if (date !== E.today()) return { sent: 0 };
        var map = { present: ['student_present', st.nickname + ' มาถึงโรงเรียนแล้ว', 'เข้าเรียนเวลา ' + E.thaiTime(time)], late: ['student_late', st.nickname + ' มาสายวันนี้', 'เข้าเรียนเวลา ' + E.thaiTime(time)], absent: ['student_absent', st.nickname + ' ขาดเรียนวันนี้', 'ครูบันทึกว่าขาดเรียน หากมีเหตุจำเป็นกรุณาแจ้งครูประจำชั้น'] };
        if (!map[status]) return { sent: 0 };
        var result = E.notify.emit(map[status][0], { studentIds: [id], title: map[status][1], body: map[status][2], link: 'attendance' });
        if (!silent && (status === 'absent' || status === 'late')) notifySummary(result, Q.parent(st.parent).name);
        return result;
    }

    /* Meals ---------------------------------------------------------------------------- */
    function meals() {
        var school = room().school;
        var monday = E.parse(E.today());
        monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7) + ui.mealWeek * 7);
        var days = [0, 1, 2, 3, 4].map(function (i) { return E.addDays(E.iso(monday), i); });
        var list = E.data().meals.filter(function (m) { return m.school === school; });
        return head('เมนูอาหาร', esc(Q.school(school).name) + ' · สัปดาห์ ' + E.thaiDate(days[0], 'day') + ' – ' + E.thaiDate(days[4]),
            '<div class="btn-group"><button type="button" class="btn btn-light" data-act="meal-week" data-step="-1" aria-label="สัปดาห์ก่อน"><i class="bi bi-chevron-left"></i></button><button type="button" class="btn btn-light" data-act="meal-week" data-step="0">สัปดาห์นี้</button><button type="button" class="btn btn-light" data-act="meal-week" data-step="1" aria-label="สัปดาห์ถัดไป"><i class="bi bi-chevron-right"></i></button></div>')
            + '<div class="row g-3"><div class="col-xl-8"><div class="meal-grid">' + days.map(function (day) {
                var items = list.filter(function (m) { return m.date === day; });
                return '<div class="meal-day' + (day === E.today() ? ' is-today' : '') + '"><h4>' + E.DAYS[E.parse(day).getDay()] + '<small>' + E.thaiDate(day, 'day') + '</small></h4>'
                    + (items.length ? Object.keys(MEAL_TYPES).map(function (t) { return items.filter(function (m) { return m.type === t; }).map(function (m) { return '<div class="meal-item meal-item--' + t + '"><small>' + MEAL_TYPES[t] + '</small><strong>' + esc(m.menu) + '</strong>' + (m.note ? '<em><i class="bi bi-exclamation-triangle"></i> ' + esc(m.note) + '</em>' : '') + '</div>'; }).join(''); }).join('') : '<p class="text-secondary small">ยังไม่มีเมนู</p>') + '</div>';
            }).join('') + '</div></div>'
            + '<div class="col-xl-4"><form class="card-x" data-form="meal" novalidate><h3 class="card-x__title">เพิ่มรายการอาหาร</h3>'
            + '<div class="mb-3"><label class="form-label" for="ml-date">วันที่ *</label><input class="form-control" type="date" id="ml-date" name="date" value="' + (days.indexOf(E.today()) !== -1 ? E.today() : days[0]) + '" required></div>'
            + '<div class="mb-3"><label class="form-label" for="ml-type">มื้อ</label><select class="form-select" id="ml-type" name="type">' + Object.keys(MEAL_TYPES).map(function (k) { return '<option value="' + k + '">' + MEAL_TYPES[k] + '</option>'; }).join('') + '</select></div>'
            + '<div class="mb-3"><label class="form-label" for="ml-menu">เมนู *</label><input class="form-control" id="ml-menu" name="menu" maxlength="120" required placeholder="เช่น ข้าวต้มปลา + ผลไม้"></div>'
            + '<div class="mb-3"><label class="form-label" for="ml-note">หมายเหตุ / สารก่อภูมิแพ้</label><input class="form-control" id="ml-note" name="note" maxlength="120" placeholder="เช่น มีไข่"></div>'
            + '<button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg me-1"></i>เพิ่มเมนู</button></form></div></div>';
    }

    /* Portfolio ------------------------------------------------------------------------ */
    function portfolio() {
        var ids = students().map(function (s) { return s.id; });
        var list = E.data().portfolio.filter(function (p) { return ids.indexOf(p.studentId) !== -1 && (!ui.pfStudent || p.studentId === ui.pfStudent); }).sort(function (a, b) { return b.date.localeCompare(a.date); });
        return head('Student Portfolio', 'ผลงานนักเรียนห้อง ' + esc(room().name) + ' · ผู้ปกครองเห็นผลงานของบุตรหลานทันที',
            '<button type="button" class="btn btn-primary rounded-pill" data-act="portfolio-add"><i class="bi bi-plus-lg me-1"></i>เพิ่มผลงาน</button>')
            + '<div class="toolbar"><select class="form-select" data-input="pf-student" aria-label="กรองตามนักเรียน"><option value="">นักเรียนทุกคน</option>' + students().map(function (s) { return '<option value="' + s.id + '"' + (ui.pfStudent === s.id ? ' selected' : '') + '>' + esc(s.nickname + ' · ' + s.first) + '</option>'; }).join('') + '</select><span class="text-secondary small">' + list.length + ' ผลงาน</span></div>'
            + (list.length ? '<div class="works">' + list.map(P.workCard).join('') + '</div>' : E.empty('bi-images', 'ยังไม่มีผลงาน', '<button class="btn btn-primary btn-sm" data-act="portfolio-add">เพิ่มผลงานแรก</button>'));
    }
    function portfolioModal(studentId) {
        var el = E.modal({
            title: '<i class="bi bi-images text-primary me-2"></i>เพิ่มผลงานนักเรียน',
            body: '<form data-form="portfolio" id="pf-form" novalidate>'
                + '<div class="mb-3"><label class="form-label" for="pf-student">นักเรียน *</label><select class="form-select" id="pf-student" name="studentId" required><option value="">เลือกนักเรียน</option>' + students().map(function (s) { return '<option value="' + s.id + '"' + (studentId === s.id ? ' selected' : '') + '>' + esc(s.nickname + ' · ' + Q.fullName(s)) + '</option>'; }).join('') + '</select></div>'
                + '<div class="mb-3"><label class="form-label" for="pf-title">ชื่อผลงาน *</label><input class="form-control" id="pf-title" name="title" maxlength="120" required></div>'
                + '<div class="mb-3"><label class="form-label" for="pf-cat">หมวด</label><select class="form-select" id="pf-cat" name="category">' + ACT_CATEGORIES.map(function (c) { return '<option>' + c + '</option>'; }).join('') + '</select></div>'
                + '<div class="mb-3"><label class="form-label" for="pf-desc">คำอธิบายจากครู</label><textarea class="form-control" id="pf-desc" name="description" rows="2" maxlength="400"></textarea></div>'
                + '<label class="form-label">รูปผลงาน (เลือก 1 รูป)</label>' + E.photoPicker('pf-photo', ['art']) + '</form>',
            foot: '<button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button><button type="submit" form="pf-form" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกผลงาน</button>',
            onShow: function (m) { E.bindPhotoPicker(m); }
        });
        el.querySelector('#pf-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var v = E.readForm(e.target);
            if (!v) return;
            var photos = E.pickedPhotos(e.target, 'pf-photo');
            var s = Q.student(v.studentId);
            var res = E.store.update('portfolio:add', function (d) {
                d.portfolio.push({ id: E.store.id('PF'), studentId: s.id, date: E.today(), title: v.title, category: v.category, photo: photos[photos.length - 1] || 'art', description: v.description || '', by: me().id });
            });
            if (E.saved(res, 'บันทึกผลงานของ ' + esc(s.nickname) + ' แล้ว')) {
                E.closeModal();
                notifySummary(E.notify.emit('portfolio_added', { studentIds: [s.id], title: 'ผลงานใหม่ของ' + s.nickname + ': ' + v.title, body: v.category, link: 'portfolio' }), Q.parent(s.parent).name);
            }
        });
    }

    /* Stars ------------------------------------------------------------------------------ */
    function stars() {
        var list = students(), ids = list.map(function (s) { return s.id; });
        var history = E.data().stars.filter(function (x) { return ids.indexOf(x.studentId) !== -1 && (!ui.starStudent || x.studentId === ui.starStudent); }).sort(function (a, b) { return (b.date + b.id).localeCompare(a.date + a.id); });
        return head('ระบบสะสมดาว', 'คลิกที่นักเรียนเพื่อมอบดาว · ผู้ปกครองได้รับแจ้งเตือนทันที')
            + '<div class="star-grid">' + list.map(function (s) { return '<button type="button" class="star-card" data-act="star-award" data-id="' + s.id + '">' + E.avatar(s, 'md') + '<strong>' + esc(s.nickname) + '</strong><span class="star-card__count">⭐ ' + Q.starsOf(s.id) + '</span></button>'; }).join('') + '</div>'
            + '<div class="card-x mt-3"><div class="card-x__head"><h3>ประวัติการได้รับดาว</h3><select class="form-select form-select-sm w-auto" data-input="star-student" aria-label="กรองตามนักเรียน"><option value="">ทุกคน</option>' + list.map(function (s) { return '<option value="' + s.id + '"' + (ui.starStudent === s.id ? ' selected' : '') + '>' + esc(s.nickname) + '</option>'; }).join('') + '</select></div>'
            + (history.length ? '<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>วันที่</th><th>นักเรียน</th><th>เหตุผล</th><th>หมวด</th><th class="text-end">ดาว</th></tr></thead><tbody>'
                + history.slice(0, 25).map(function (x) { var s = Q.student(x.studentId); return '<tr><td>' + E.thaiDate(x.date) + '</td><td>' + E.avatar(s, 'xs') + ' ' + esc(s.nickname) + '</td><td>' + esc(x.reason) + '</td><td><span class="chip">' + esc(x.category) + '</span></td><td class="text-end">' + '⭐'.repeat(x.count) + '</td></tr>'; }).join('') + '</tbody></table></div>' : E.empty('bi-star', 'ยังไม่มีประวัติ')) + '</div>';
    }
    function starModal(id) {
        var s = Q.student(id);
        var el = E.modal({
            title: '<i class="bi bi-star-fill text-warning me-2"></i>มอบดาวให้ ' + esc(s.nickname),
            body: '<form id="star-form" novalidate><div class="text-center mb-3">' + E.avatar(s, 'lg') + '<p class="mt-2 mb-0">มีดาวสะสม ' + Q.starsOf(id) + ' ดวง</p></div>'
                + '<label class="form-label">จำนวนดาว</label><div class="btn-group w-100 mb-3" role="group">' + [1, 2, 3].map(function (n) { return '<input type="radio" class="btn-check" name="count" id="sc' + n + '" value="' + n + '"' + (n === 1 ? ' checked' : '') + '><label class="btn btn-outline-warning" for="sc' + n + '">' + '⭐'.repeat(n) + '</label>'; }).join('') + '</div>'
                + '<label class="form-label">เหตุผล</label><div class="chips mb-2">' + STAR_REASONS.map(function (r, i) { return '<button type="button" class="chip chip--btn" data-reason="' + i + '">' + r[0] + '</button>'; }).join('') + '</div>'
                + '<input class="form-control mb-2" name="reason" id="star-reason" maxlength="120" required placeholder="หรือพิมพ์เหตุผลเอง">'
                + '<select class="form-select" name="category" id="star-cat">' + ['น้ำใจ', 'การเรียนรู้', 'ความรับผิดชอบ', 'ความกล้าแสดงออก', 'ระเบียบวินัย'].map(function (c) { return '<option>' + c + '</option>'; }).join('') + '</select></form>',
            foot: '<button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button><button type="submit" form="star-form" class="btn btn-warning"><i class="bi bi-star-fill me-1"></i>มอบดาว</button>'
        });
        el.querySelectorAll('[data-reason]').forEach(function (b) {
            b.addEventListener('click', function () {
                var r = STAR_REASONS[+b.getAttribute('data-reason')];
                el.querySelector('#star-reason').value = r[0];
                el.querySelector('#star-cat').value = r[1];
                el.querySelectorAll('[data-reason]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
            });
        });
        el.querySelector('#star-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var form = e.target;
            var reason = form.querySelector('#star-reason').value.trim().slice(0, 120);
            if (!reason) { form.querySelector('#star-reason').classList.add('is-invalid'); E.toast('กรุณาระบุเหตุผลในการมอบดาว', 'warning'); return; }
            var count = Math.min(3, Math.max(1, parseInt(form.querySelector('input[name=count]:checked').value, 10) || 1));
            var category = form.querySelector('#star-cat').value;
            var res = E.store.update('star:add', function (d) {
                d.stars.push({ id: E.store.id('ST'), studentId: id, date: E.today(), count: count, reason: reason, category: category, by: me().id });
            });
            if (E.saved(res, 'มอบดาว ' + count + ' ดวงให้ ' + esc(s.nickname) + ' แล้ว')) {
                E.closeModal();
                notifySummary(E.notify.emit('star_awarded', { studentIds: [id], title: s.nickname + ' ได้รับดาว ' + '⭐'.repeat(count), body: reason, link: 'stars' }), Q.parent(s.parent).name);
            }
        });
    }
    function pickStudentForStar() {
        E.modal({
            title: '<i class="bi bi-star text-warning me-2"></i>เลือกนักเรียนที่จะมอบดาว',
            body: '<div class="star-grid star-grid--modal">' + students().map(function (s) { return '<button type="button" class="star-card" data-act="star-award" data-id="' + s.id + '">' + E.avatar(s, 'md') + '<strong>' + esc(s.nickname) + '</strong><span class="star-card__count">⭐ ' + Q.starsOf(s.id) + '</span></button>'; }).join('') + '</div>'
        });
    }

    /* Announcements ------------------------------------------------------------------- */
    function announcements() {
        var r = room();
        var list = E.data().announcements.filter(function (a) { return (a.school === 'all' || a.school === r.school) && (a.audience === 'all' || a.audience === r.id); }).sort(function (a, b) { return (b.pinned - a.pinned) || b.date.localeCompare(a.date); });
        return head('ประกาศ', 'ประกาศถึงผู้ปกครองห้อง ' + esc(r.name) + ' หรือทั้งโรงเรียน')
            + '<div class="row g-3"><div class="col-xl-7">' + (list.length ? list.map(P.announceCard).join('') : E.empty('bi-megaphone', 'ยังไม่มีประกาศ')) + '</div>'
            + '<div class="col-xl-5"><form class="card-x" data-form="announce" novalidate><h3 class="card-x__title">สร้างประกาศใหม่</h3>'
            + '<div class="mb-3"><label class="form-label" for="an-title">หัวข้อ *</label><input class="form-control" id="an-title" name="title" maxlength="120" required></div>'
            + '<div class="mb-3"><label class="form-label" for="an-body">รายละเอียด *</label><textarea class="form-control" id="an-body" name="body" rows="4" maxlength="500" required></textarea></div>'
            + '<div class="mb-3"><label class="form-label" for="an-aud">ส่งถึง</label><select class="form-select" id="an-aud" name="audience"><option value="' + r.id + '">ผู้ปกครองห้อง ' + esc(r.name) + '</option><option value="all">ผู้ปกครองทั้งโรงเรียน (' + esc(Q.school(r.school).short) + ')</option></select></div>'
            + '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="an-pin" name="pinned" data-bool><label class="form-check-label" for="an-pin">ปักหมุดประกาศ</label></div>'
            + '<button class="btn btn-primary w-100" type="submit"><i class="bi bi-megaphone me-1"></i>ประกาศและแจ้งเตือน</button></form></div></div>';
    }

    /* Calendar --------------------------------------------------------------------------- */
    function calendar() {
        var r = room();
        return head('ปฏิทินกิจกรรม', 'กิจกรรมของโรงเรียนและห้อง ' + esc(r.name)) + P.calendarView(function (ev) { return (ev.school === 'all' || ev.school === r.school) && (ev.audience === 'all' || ev.audience === r.id); }, true);
    }

    /* Reports ----------------------------------------------------------------------------- */
    function reports() {
        var r = room(), date = ui.reportDate || E.today(), list = students(), s = attSummary(date, list), d = E.data();
        var acts = d.activities.filter(function (a) { return a.classroom === r.id && a.date === date; });
        var ids = list.map(function (x) { return x.id; });
        var starsToday = d.stars.filter(function (x) { return x.date === date && ids.indexOf(x.studentId) !== -1; });
        var mealsToday = d.meals.filter(function (m) { return m.school === r.school && m.date === date; });
        var notifs = d.notifications.filter(function (n) { return n.ts.slice(0, 10) === date && ids.indexOf(n.studentId) !== -1; });
        var missing = list.filter(function (x) { var st = Q.status(date, x.id); return st === 'absent' || st === 'leave' || st === 'late'; });
        return head('รายงานประจำวัน', 'ห้อง ' + esc(r.name) + ' · ' + E.thaiDate(date, 'long'),
            '<input type="date" class="form-control" value="' + date + '" max="' + E.today() + '" data-input="report-date" aria-label="วันที่รายงาน"><button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>พิมพ์</button>')
            + '<div class="report-sheet"><div class="stat-grid">'
            + E.stat('bi-check-circle', 'มาเรียน', s.present + ' คน', 'success') + E.stat('bi-clock', 'มาสาย', s.late + ' คน', 'warning') + E.stat('bi-x-circle', 'ขาด', s.absent + ' คน', 'danger') + E.stat('bi-envelope-paper', 'ลา', s.leave + ' คน', 'info')
            + '</div><div class="row g-3"><div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">นักเรียนที่ขาด / ลา / มาสาย</h3>' + (missing.length ? '<ul class="list-plain">' + missing.map(function (x) { return '<li>' + E.avatar(x, 'xs') + ' ' + esc(x.nickname) + ' ' + E.statusBadge(Q.status(date, x.id)) + '</li>'; }).join('') + '</ul>' : '<p class="text-secondary">ไม่มี</p>') + (s.none ? '<p class="text-warning small mb-0"><i class="bi bi-exclamation-circle"></i> ยังไม่เช็คชื่อ ' + s.none + ' คน</p>' : '') + '</div></div>'
            + '<div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">เมนูอาหาร</h3>' + (mealsToday.length ? '<ul class="list-plain">' + mealsToday.map(function (m) { return '<li><b>' + MEAL_TYPES[m.type] + ':</b> ' + esc(m.menu) + '</li>'; }).join('') + '</ul>' : '<p class="text-secondary">ไม่มีข้อมูลเมนู</p>') + '</div></div>'
            + '<div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">กิจกรรม (' + acts.length + ')</h3>' + (acts.length ? '<ul class="list-plain">' + acts.map(function (a) { return '<li>' + E.thaiTime(a.time) + ' ' + esc(a.title) + ' <span class="chip">' + esc(a.category) + '</span></li>'; }).join('') + '</ul>' : '<p class="text-secondary">ไม่มีกิจกรรม</p>') + '</div></div>'
            + '<div class="col-lg-6"><div class="card-x"><h3 class="card-x__title">ดาวที่มอบ (' + starsToday.reduce(function (n, x) { return n + x.count; }, 0) + ' ดวง) · แจ้งเตือนที่ส่ง (' + notifs.length + ')</h3>' + (starsToday.length ? '<ul class="list-plain">' + starsToday.map(function (x) { return '<li>' + esc(Q.student(x.studentId).nickname) + ' ⭐×' + x.count + ' — ' + esc(x.reason) + '</li>'; }).join('') + '</ul>' : '<p class="text-secondary">ยังไม่มีการมอบดาว</p>') + '</div></div>'
            + '</div></div>';
    }

    /* Wiring ------------------------------------------------------------------------------ */
    function contextSlot(slot, label) {
        label.textContent = 'ครู · ' + me().name;
        slot.innerHTML = '<label class="ctx"><i class="bi bi-door-open"></i><select class="form-select form-select-sm" data-input="room" aria-label="เลือกห้องเรียน">' + rooms().map(function (r) { return '<option value="' + r.id + '"' + (r.id === room().id ? ' selected' : '') + '>ห้อง ' + esc(r.name) + '</option>'; }).join('') + '</select></label>';
        slot.querySelector('select').onchange = function (e) { setRoom(e.target.value); };
    }

    function bell() {
        var r = room();
        var list = E.data().notifications.filter(function (n) { var st = Q.student(n.studentId); return st && st.classroom === r.id; }).slice(0, 8);
        document.getElementById('bell-title').textContent = 'แจ้งเตือนที่ส่งถึงผู้ปกครอง';
        document.getElementById('bell-more').setAttribute('href', '#attendance');
        document.getElementById('bell-more').textContent = 'ดู Notification Log';
        document.getElementById('bell-count').hidden = true;
        var mark = document.querySelector('[data-action="mark-all-read"]');
        if (mark) mark.hidden = true;
        document.getElementById('bell-list').innerHTML = list.length ? list.map(P.notifRow).join('') : E.empty('bi-bell-slash', 'ยังไม่มีการแจ้งเตือน');
    }

    function after(id, view) {
        E.bindPhotoPicker(view);
        if (ui.focus) {
            var el = view.querySelector(ui.focus);
            if (el) { el.focus(); var v = el.value; el.value = ''; el.value = v; }
            ui.focus = null;
        }
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-act]');
        if (!b) return;
        var act = b.getAttribute('data-act'), id = b.getAttribute('data-id');
        E.log('Teacher', 'action', act, { id: id });
        if (act === 'set-room') setRoom(id);
        else if (act === 'student') studentModal(id);
        else if (act === 'star-award') { E.closeModal(); setTimeout(function () { starModal(id); }, 200); }
        else if (act === 'star-pick') pickStudentForStar();
        else if (act === 'portfolio-add') portfolioModal(ui.pfStudent);
        else if (act === 'att') setAttendance(id, b.getAttribute('data-status'));
        else if (act === 'all-present') {
            var date = ui.date || E.today(), n = 0;
            students().forEach(function (s) { if (Q.status(date, s.id) === 'none') { setAttendance(s.id, 'present', true); n++; } });
            E.toast('เช็คชื่อ “มาเรียน” ' + n + ' คน และแจ้งผู้ปกครองผ่าน In-App แล้ว', 'success');
        } else if (act === 'meal-week') { var st = +b.getAttribute('data-step'); ui.mealWeek = st === 0 ? 0 : ui.mealWeek + st; E.app.render(); }
    });

    document.addEventListener('input', function (e) {
        var key = e.target.getAttribute && e.target.getAttribute('data-input');
        if (key === 'student-search') { ui.studentQuery = e.target.value.slice(0, 60); ui.focus = '[data-input="student-search"]'; E.app.render(); }
    });
    document.addEventListener('change', function (e) {
        var key = e.target.getAttribute && e.target.getAttribute('data-input');
        if (!key || key === 'room' || key === 'student-search') return;
        var v = e.target.value;
        if (key === 'att-date') ui.date = v && v <= E.today() ? v : E.today();
        if (key === 'pf-student') ui.pfStudent = v;
        if (key === 'star-student') ui.starStudent = v;
        if (key === 'report-date') ui.reportDate = v && v <= E.today() ? v : E.today();
        e.target.blur();
        E.app.render();
    });
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-form]');
        if (!form || !document.getElementById('view').contains(form)) return;
        e.preventDefault();
        var kind = form.getAttribute('data-form');
        E.log('Teacher', 'submit', kind);
        var r = room();
        if (kind === 'activity') { saveActivity(form); return; }
        var v = E.readForm(form);
        if (!v) return;
        if (kind === 'meal') {
            E.saved(E.store.update('meal:add', function (d) { d.meals.push({ id: E.store.id('ML'), date: v.date, school: r.school, type: MEAL_TYPES[v.type] ? v.type : 'lunch', menu: v.menu, note: v.note || '' }); }), 'เพิ่มเมนู “' + esc(v.menu) + '” แล้ว');
        } else if (kind === 'announce') {
            var audience = v.audience === 'all' ? 'all' : r.id;
            if (E.saved(E.store.update('announce:add', function (d) { d.announcements.push({ id: E.store.id('AN'), date: E.today(), title: v.title, body: v.body, audience: audience, school: r.school, by: me().id, pinned: v.pinned === true }); }), 'เผยแพร่ประกาศแล้ว')) {
                notifySummary(E.notify.emit('announcement', audience === 'all' ? { school: r.school, title: 'ประกาศใหม่: ' + v.title, body: v.body.slice(0, 80), link: 'announcements' } : { classroom: r.id, title: 'ประกาศใหม่: ' + v.title, body: v.body.slice(0, 80), link: 'announcements' }), audience === 'all' ? 'ผู้ปกครองทั้งโรงเรียน' : 'ผู้ปกครองห้อง ' + r.name);
            }
        } else if (kind === 'event') {
            if (E.saved(E.store.update('event:add', function (d) { d.events.push({ id: E.store.id('EV'), date: v.date, title: v.title, type: v.type, audience: r.id, school: r.school, time: '09:00' }); }), 'เพิ่ม “' + esc(v.title) + '” ในปฏิทินแล้ว')) {
                P.state.calMonth = v.date.slice(0, 7);
                notifySummary(E.notify.emit('school_event', { classroom: r.id, title: 'กิจกรรมโรงเรียน: ' + v.title, body: E.thaiDate(v.date, 'long'), link: 'calendar' }), 'ผู้ปกครองห้อง ' + r.name);
            }
        }
    });

    E.app.register({
        dashboard: dashboard, classrooms: classrooms, students: studentsView, activities: activities, attendance: attendance,
        meals: meals, portfolio: portfolio, stars: stars, announcements: announcements, calendar: calendar, reports: reports
    }, { context: contextSlot, bell: bell, after: after });
})();
