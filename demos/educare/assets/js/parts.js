/**
 * EduCare — shared view parts used by every role (activity cards, calendar, announcements,
 * portfolio cards and notification rows), plus the generic calendar/event click handlers.
 */
(function () {
    'use strict';

    var E = window.EDU, Q = E.Q, esc = E.esc;
    var state = { calMonth: null };

    function activityItem(a) {
        return '<article class="activity"><div class="activity__photos">' + a.photos.slice(0, 3).map(function (p) { return E.photo(p); }).join('') + '</div>'
            + '<div class="activity__body"><span class="chip">' + esc(a.category) + '</span><h4>' + esc(a.title) + '</h4><p>' + esc(a.description) + '</p>'
            + '<small><i class="bi bi-clock"></i> ' + E.thaiDate(a.date) + ' ' + E.thaiTime(a.time) + ' · ' + esc(Q.authorName(a.by)) + ' · ห้อง ' + esc(Q.classroom(a.classroom).name) + '</small></div></article>';
    }

    function eventItem(ev) {
        return '<div class="event-row"><span class="event-row__date"><b>' + E.parse(ev.date).getDate() + '</b>' + E.thaiDate(ev.date, 'day').split(' ')[1] + '</span><div><strong>' + esc(ev.title) + '</strong><small>' + esc(ev.type) + ' · ' + (ev.audience === 'all' ? 'ทุกห้อง' : 'ห้อง ' + esc(Q.classroom(ev.audience).name)) + '</small></div></div>';
    }

    function notifRow(n) {
        var st = Q.student(n.studentId), p = Q.parent(n.parent);
        return '<div class="notif-row"><div><strong>' + esc(n.title) + '</strong><small>ถึง ' + esc(p ? p.name : '-') + ' (ผู้ปกครอง' + esc(st ? st.nickname : '') + ') · ' + E.ago(n.ts) + '</small></div><div class="chans">' + E.channelBadges(n.channels) + '</div></div>';
    }

    function workCard(w) {
        var s = Q.student(w.studentId);
        return '<figure class="work">' + E.photo(w.photo) + '<figcaption><span class="chip">' + esc(w.category) + '</span><strong>' + esc(w.title) + '</strong><small>' + E.avatar(s, 'xs') + ' ' + esc(s.nickname) + ' · ' + E.thaiDate(w.date) + '</small></figcaption></figure>';
    }

    function announceCard(a) {
        return '<article class="announce' + (a.pinned ? ' is-pinned' : '') + '"><div class="announce__head">' + (a.pinned ? '<span class="badge text-bg-warning"><i class="bi bi-pin-angle-fill"></i> ปักหมุด</span>' : '') + '<span class="chip">' + (a.audience === 'all' ? 'ทั้งโรงเรียน' : 'ห้อง ' + esc(Q.classroom(a.audience).name)) + '</span><small>' + E.thaiDate(a.date) + ' · ' + esc(Q.authorName(a.by)) + '</small></div><h4>' + esc(a.title) + '</h4><p>' + esc(a.body) + '</p></article>';
    }

    function calendarView(audienceFilter, canAdd) {
        var month = state.calMonth || E.today().slice(0, 7);
        var first = E.parse(month + '-01');
        var start = new Date(first); start.setDate(1 - ((first.getDay() + 6) % 7));
        var events = E.data().events.filter(audienceFilter);
        var cells = '';
        for (var i = 0; i < 42; i++) {
            var d = new Date(start); d.setDate(start.getDate() + i);
            var day = E.iso(d);
            var evs = events.filter(function (ev) { return ev.date === day; });
            cells += '<div class="cal__cell' + (day.slice(0, 7) !== month ? ' is-out' : '') + (day === E.today() ? ' is-today' : '') + '"><span class="cal__num">' + d.getDate() + '</span>' + evs.map(function (ev) { return '<button type="button" class="cal__ev cal__ev--' + esc(ev.type) + '" data-act="event" data-id="' + ev.id + '">' + esc(ev.title) + '</button>'; }).join('') + '</div>';
        }
        var upcoming = events.filter(function (ev) { return ev.date.slice(0, 7) === month; }).sort(function (a, b) { return a.date.localeCompare(b.date); });
        return '<div class="row g-3"><div class="col-xl-8"><div class="card-x"><div class="cal__nav"><button type="button" class="btn btn-light btn-sm" data-act="cal" data-step="-1" aria-label="เดือนก่อน"><i class="bi bi-chevron-left"></i></button><h3>' + E.thaiDate(month + '-01', 'month') + '</h3><button type="button" class="btn btn-light btn-sm" data-act="cal" data-step="1" aria-label="เดือนถัดไป"><i class="bi bi-chevron-right"></i></button></div>'
            + '<div class="cal"><div class="cal__dow">' + ['จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส', 'อา'].map(function (x) { return '<span>' + x + '</span>'; }).join('') + '</div><div class="cal__grid">' + cells + '</div></div></div></div>'
            + '<div class="col-xl-4"><div class="card-x mb-3"><div class="card-x__head"><h3>กิจกรรมเดือนนี้</h3></div>' + (upcoming.length ? upcoming.map(eventItem).join('') : E.empty('bi-calendar', 'ไม่มีกิจกรรมในเดือนนี้')) + '</div>'
            + (canAdd ? '<form class="card-x" data-form="event" novalidate><h3 class="card-x__title">เพิ่มกิจกรรมในปฏิทิน</h3><div class="mb-2"><label class="form-label" for="ev-title">ชื่อกิจกรรม *</label><input class="form-control" id="ev-title" name="title" maxlength="100" required></div><div class="row g-2 mb-2"><div class="col-6"><label class="form-label" for="ev-date">วันที่ *</label><input class="form-control" type="date" id="ev-date" name="date" required value="' + E.today() + '"></div><div class="col-6"><label class="form-label" for="ev-type">ประเภท</label><select class="form-select" id="ev-type" name="type"><option>กิจกรรม</option><option>ประเพณี</option><option>นอกสถานที่</option><option>สุขภาพ</option><option>ประชุม</option><option>วันหยุด</option></select></div></div><button class="btn btn-primary w-100 mt-2" type="submit"><i class="bi bi-calendar-plus me-1"></i>เพิ่มและแจ้งผู้ปกครอง</button></form>' : '')
            + '</div></div>';
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-act="cal"], [data-act="event"]');
        if (!b) return;
        if (b.getAttribute('data-act') === 'cal') {
            var m = E.parse((state.calMonth || E.today().slice(0, 7)) + '-01');
            m.setMonth(m.getMonth() + +b.getAttribute('data-step'));
            state.calMonth = E.iso(m).slice(0, 7);
            E.log('Parts', 'calendar', 'month changed', { month: state.calMonth });
            E.app.render();
            return;
        }
        var ev = E.data().events.filter(function (x) { return x.id === b.getAttribute('data-id'); })[0];
        if (!ev) return;
        E.modal({ title: '<i class="bi bi-calendar-event text-primary me-2"></i>' + esc(ev.title), body: '<dl class="kv"><dt>วันที่</dt><dd>' + E.thaiDate(ev.date, 'long') + '</dd><dt>เวลา</dt><dd>' + E.thaiTime(ev.time) + '</dd><dt>ประเภท</dt><dd>' + esc(ev.type) + '</dd><dt>ผู้เข้าร่วม</dt><dd>' + (ev.audience === 'all' ? 'นักเรียนทุกห้อง' : 'ห้อง ' + esc(Q.classroom(ev.audience).name)) + '</dd><dt>โรงเรียน</dt><dd>' + (ev.school === 'all' ? 'ทุกโรงเรียน' : esc(Q.school(ev.school).name)) + '</dd></dl>' });
    });

    window.EDU_PARTS = { state: state, activityItem: activityItem, eventItem: eventItem, notifRow: notifRow, workCard: workCard, announceCard: announceCard, calendarView: calendarView };
})();
