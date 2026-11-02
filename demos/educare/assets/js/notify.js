/**
 * EduCare — Notification Engine (simulated).
 *
 * emit(event, payload) looks up the enabled rule for the event, resolves the recipient
 * parents and writes one Notification Log per parent with a status for every channel.
 * In-App is delivered immediately; Push and LINE move "sending" → "delivered" after a short
 * delay. Nothing is sent to real Push or LINE services. Parents without LINE get "skipped".
 */
(function () {
    'use strict';

    var STORE = window.EDU_STORE;
    var IS_DEV = Boolean((window.EDU_CONFIG || {}).debug);
    var MAX_LOG = 400;
    var CHANNELS = { inapp: 'In-App', push: 'Push', line: 'LINE' };

    function log(fn, message, data) {
        if (IS_DEV) console.log('[Notify][' + fn + '] ' + message, data === undefined ? '' : data);
    }

    function recipients(data, payload) {
        var studentIds = payload.studentIds || [];
        if (!studentIds.length && payload.classroom) {
            studentIds = data.students.filter(function (s) { return s.classroom === payload.classroom; }).map(function (s) { return s.id; });
        }
        if (!studentIds.length && payload.school) {
            studentIds = data.students.filter(function (s) { return payload.school === 'all' || s.school === payload.school; }).map(function (s) { return s.id; });
        }
        // One notification per parent (first matching child) to avoid duplicates for siblings.
        var seen = {};
        var out = [];
        studentIds.forEach(function (id) {
            var s = data.students.filter(function (x) { return x.id === id; })[0];
            if (!s || seen[s.parent]) return;
            seen[s.parent] = true;
            out.push({ parent: s.parent, student: s });
        });
        return out;
    }

    /**
     * @returns {{sent: number, channels: string[], rule: object|null}}
     */
    function emit(event, payload) {
        log('emit', 'START', { event: event, payload: payload });
        var data = STORE.get();
        var rule = data.rules.filter(function (r) { return r.event === event; })[0];
        if (!rule || !rule.enabled) {
            log('emit', 'END no enabled rule', { event: event });
            return { sent: 0, channels: [], rule: rule || null };
        }
        var list = recipients(data, payload);
        var created = [];
        var ts = new Date();
        var stamp = ts.getFullYear() + '-' + String(ts.getMonth() + 1).padStart(2, '0') + '-' + String(ts.getDate()).padStart(2, '0') + 'T' + ts.toTimeString().slice(0, 8);

        STORE.update('notify:' + event, function (d) {
            list.forEach(function (r) {
                var parent = d.parents.filter(function (p) { return p.id === r.parent; })[0];
                var channels = {};
                rule.channels.forEach(function (c) {
                    channels[c] = c === 'inapp' ? 'delivered' : (c === 'line' && parent && !parent.line ? 'skipped' : 'sending');
                });
                var title = typeof payload.title === 'function' ? payload.title(r.student) : payload.title;
                var body = typeof payload.body === 'function' ? payload.body(r.student) : payload.body;
                var item = { id: STORE.id('NT'), ts: stamp, event: event, parent: r.parent, studentId: r.student.id, title: title, body: body || '', link: payload.link || '', channels: channels, read: false };
                d.notifications.unshift(item);
                created.push(item.id);
            });
            if (d.notifications.length > MAX_LOG) d.notifications.length = MAX_LOG;
        }, { event: event, ids: created });

        // Simulated delivery for Push / LINE.
        [['push', 900], ['line', 1700]].forEach(function (pair) {
            if (rule.channels.indexOf(pair[0]) === -1 || !created.length) return;
            setTimeout(function () {
                STORE.update('notify:delivered', function (d) {
                    d.notifications.forEach(function (n) {
                        if (created.indexOf(n.id) !== -1 && n.channels[pair[0]] === 'sending') n.channels[pair[0]] = 'delivered';
                    });
                });
            }, pair[1]);
        });

        log('emit', 'END', { event: event, sent: created.length, channels: rule.channels });
        return { sent: created.length, channels: rule.channels, rule: rule };
    }

    function channelLabel(key) { return CHANNELS[key] || key; }

    window.EDU_NOTIFY = { emit: emit, channelLabel: channelLabel, CHANNELS: CHANNELS };
})();
