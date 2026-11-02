/**
 * EduCare — shared UI core used by every role: helpers, Thai dates, data lookups,
 * toast/modal/confirm, photo picker, hash router, bell menu and Reset Demo Data.
 * Role modules (teacher.js, parent.js, executive.js) register their views with EDU.app.register().
 */
(function () {
    'use strict';

    var CONFIG = window.EDU_CONFIG || {};
    var IS_DEV = Boolean(CONFIG.debug);
    var STORE = window.EDU_STORE;
    var MONTHS = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    var MONTHS_SHORT = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    var DAYS = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    var STATUS = {
        present: { label: 'มาเรียน', tone: 'success', icon: 'bi-check-circle-fill' },
        late: { label: 'มาสาย', tone: 'warning', icon: 'bi-clock-fill' },
        absent: { label: 'ขาดเรียน', tone: 'danger', icon: 'bi-x-circle-fill' },
        leave: { label: 'ลา', tone: 'info', icon: 'bi-envelope-paper-fill' },
        none: { label: 'ยังไม่เช็คชื่อ', tone: 'secondary', icon: 'bi-dash-circle' }
    };
    var DEMO_PHOTOS = ['art', 'music', 'sport', 'science', 'reading', 'garden', 'cooking', 'blocks'];

    /* Logging ------------------------------------------------------------- */
    function log(module, fn, message, data) {
        if (!IS_DEV) return;
        if (data === undefined) console.log('[' + module + '][' + fn + '] ' + message);
        else console.log('[' + module + '][' + fn + '] ' + message, data);
    }
    function logError(module, fn, error, context) {
        console.error('[' + module + '][' + fn + '] ERROR', { error: error, context: context });
    }

    /* Text & dates -------------------------------------------------------------- */
    function esc(v) {
        return String(v === null || v === undefined ? '' : v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function pad(n) { return String(n).padStart(2, '0'); }
    function iso(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function parse(s) { var p = String(s).slice(0, 10).split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function today() { return iso(new Date()); }
    function addDays(s, n) { var d = parse(s); d.setDate(d.getDate() + n); return iso(d); }
    function nowTime() { var d = new Date(); return pad(d.getHours()) + ':' + pad(d.getMinutes()); }
    function isWeekday(s) { var w = parse(s).getDay(); return w > 0 && w < 6; }
    /** "2 พ.ย. 2569" / long: "วันจันทร์ที่ 2 พฤศจิกายน 2569" */
    function thaiDate(s, style) {
        if (!s) return '–';
        var d = parse(s);
        if (style === 'long') return 'วัน' + DAYS[d.getDay()] + 'ที่ ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + (d.getFullYear() + 543);
        if (style === 'day') return d.getDate() + ' ' + MONTHS_SHORT[d.getMonth()];
        if (style === 'month') return MONTHS[d.getMonth()] + ' ' + (d.getFullYear() + 543);
        return d.getDate() + ' ' + MONTHS_SHORT[d.getMonth()] + ' ' + (d.getFullYear() + 543);
    }
    function thaiTime(t) { return t ? t.slice(0, 5) + ' น.' : ''; }
    function ago(ts) {
        var diff = (Date.now() - new Date(ts).getTime()) / 1000;
        if (diff < 60) return 'เมื่อสักครู่';
        if (diff < 3600) return Math.floor(diff / 60) + ' นาทีที่แล้ว';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ชั่วโมงที่แล้ว';
        return thaiDate(ts.slice(0, 10)) + ' ' + thaiTime(ts.slice(11, 16));
    }
    function age(dob) {
        var d = parse(dob), n = new Date();
        var y = n.getFullYear() - d.getFullYear() - ((n.getMonth() < d.getMonth() || (n.getMonth() === d.getMonth() && n.getDate() < d.getDate())) ? 1 : 0);
        return y + ' ปี';
    }

    /* Data lookups -------------------------------------------------------------- */
    function data() { return STORE.get(); }
    function byId(list, id) { for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i]; return null; }
    var Q = {
        student: function (id) { return byId(data().students, id); },
        classroom: function (id) { return byId(data().classrooms, id); },
        school: function (id) { return byId(data().schools, id); },
        teacher: function (id) { return byId(data().teachers, id); },
        parent: function (id) { return byId(data().parents, id); },
        studentsIn: function (room) { return data().students.filter(function (s) { return s.classroom === room; }); },
        fullName: function (s) { return s.title + s.first + ' ' + s.last; },
        att: function (date, sid) { var day = data().attendance[date]; return day && day[sid] ? day[sid] : null; },
        status: function (date, sid) { var a = Q.att(date, sid); return a ? a.status : 'none'; },
        starsOf: function (sid) { return data().stars.filter(function (x) { return x.studentId === sid; }).reduce(function (n, x) { return n + x.count; }, 0); },
        schoolDays: function (from, to) {
            return Object.keys(data().attendance).filter(function (d) { return d >= from && d <= to; }).sort();
        },
        authorName: function (by) {
            var t = Q.teacher(by);
            return t ? t.name : (by || 'โรงเรียน');
        }
    };

    /* UI atoms ---------------------------------------------------------------------- */
    var COLORS = ['#2563EB', '#0EA5E9', '#14B8A6', '#F59E0B', '#EC4899', '#8B5CF6', '#22C55E', '#F97316'];
    function avatar(s, size) {
        var n = s.nickname.replace('น้อง', '');
        return '<span class="avatar avatar--' + (size || 'md') + '" style="--av:' + COLORS[s.color % 8] + '" aria-hidden="true">' + esc(n.slice(0, 2)) + '</span>';
    }
    function statusBadge(status) {
        var m = STATUS[status] || STATUS.none;
        return '<span class="status status--' + m.tone + '"><i class="bi ' + m.icon + '"></i>' + m.label + '</span>';
    }
    function photoSrc(p) { return /^data:image\//.test(p) ? p : 'assets/images/demo/' + encodeURIComponent(p) + '.svg'; }
    function photo(p, cls) { return '<img class="' + (cls || 'photo') + '" src="' + esc(photoSrc(p)) + '" alt="ภาพกิจกรรม" loading="lazy">'; }
    function empty(icon, text, action) {
        return '<div class="empty"><i class="bi ' + icon + '"></i><p>' + esc(text) + '</p>' + (action || '') + '</div>';
    }
    function stat(icon, label, value, tone, sub) {
        return '<div class="stat stat--' + (tone || 'primary') + '"><span class="stat__icon"><i class="bi ' + icon + '"></i></span><div><p class="stat__label">' + esc(label) + '</p><p class="stat__value">' + value + '</p>' + (sub ? '<p class="stat__sub">' + sub + '</p>' : '') + '</div></div>';
    }
    function channelBadges(channels) {
        return Object.keys(channels).map(function (c) {
            var st = channels[c];
            var tone = st === 'delivered' ? 'success' : (st === 'sending' ? 'warning' : 'secondary');
            var label = st === 'delivered' ? 'ส่งแล้ว' : (st === 'sending' ? 'กำลังส่ง' : 'ข้าม (ยังไม่เชื่อม LINE)');
            var icon = c === 'line' ? 'bi-line' : (c === 'push' ? 'bi-phone-vibrate' : 'bi-app-indicator');
            return '<span class="chan chan--' + tone + '" title="' + esc(window.EDU_NOTIFY.channelLabel(c) + ': ' + label) + '"><i class="bi ' + icon + '"></i>' + window.EDU_NOTIFY.channelLabel(c) + (st === 'sending' ? ' <span class="spinner-grow spinner-grow-sm"></span>' : (st === 'delivered' ? ' ✓' : '')) + '</span>';
        }).join('');
    }

    /* Toast, modal, confirm ------------------------------------------------------- */
    function toast(message, type) {
        var box = document.getElementById('toasts');
        if (!box) return;
        var el = document.createElement('div');
        var tone = { success: 'bi-check-circle-fill text-success', error: 'bi-x-circle-fill text-danger', warning: 'bi-exclamation-triangle-fill text-warning', notify: 'bi-bell-fill text-primary' }[type] || 'bi-info-circle-fill text-primary';
        el.className = 'toast align-items-center border-0 shadow';
        el.setAttribute('role', 'status');
        el.innerHTML = '<div class="d-flex"><div class="toast-body d-flex gap-2 align-items-start"><i class="bi ' + tone + '"></i><span>' + message + '</span></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="ปิด"></button></div>';
        box.appendChild(el);
        var t = new bootstrap.Toast(el, { delay: 4200 });
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        t.show();
    }

    var modalApi = null;
    function modal(o) {
        log('UI', 'modal', 'open', { title: o.title });
        var el = document.getElementById('edu-modal');
        document.getElementById('edu-modal-dialog').className = 'modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down' + (o.size ? ' modal-' + o.size : '');
        document.getElementById('edu-modal-title').innerHTML = o.title || '';
        document.getElementById('edu-modal-body').innerHTML = o.body || '';
        var foot = document.getElementById('edu-modal-foot');
        foot.innerHTML = o.foot || '<button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>';
        modalApi = bootstrap.Modal.getOrCreateInstance(el);
        if (o.onShow) {
            var handler = function () { el.removeEventListener('shown.bs.modal', handler); o.onShow(el); };
            el.addEventListener('shown.bs.modal', handler);
        }
        modalApi.show();
        return el;
    }
    function closeModal() { if (modalApi) modalApi.hide(); }
    function confirmBox(o) {
        return new Promise(function (resolve) {
            var done = false;
            var el = modal({
                title: o.title,
                body: '<p class="mb-0">' + o.body + '</p>',
                foot: '<button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button><button type="button" class="btn btn-' + (o.tone || 'primary') + '" id="confirm-ok">' + o.ok + '</button>'
            });
            el.querySelector('#confirm-ok').addEventListener('click', function () { done = true; closeModal(); resolve(true); }, { once: true });
            el.addEventListener('hidden.bs.modal', function () { if (!done) resolve(false); }, { once: true });
        });
    }

    /* Photo picker: demo images + optional upload (resized, kept small for LocalStorage) */
    function photoPicker(name, selected) {
        selected = selected || [];
        return '<div class="photo-picker" data-photo-picker="' + esc(name) + '">'
            + '<div class="photo-picker__grid">' + DEMO_PHOTOS.map(function (p) {
                return '<label class="photo-opt"><input type="checkbox" value="' + p + '"' + (selected.indexOf(p) !== -1 ? ' checked' : '') + '><img src="' + photoSrc(p) + '" alt="ภาพ Demo ' + p + '"><i class="bi bi-check-circle-fill"></i></label>';
            }).join('') + '</div>'
            + '<label class="btn btn-outline-primary btn-sm mt-2"><i class="bi bi-upload me-1"></i>อัปโหลดรูปจากเครื่อง<input type="file" accept="image/*" hidden data-photo-upload></label>'
            + '<div class="photo-picker__uploads" data-photo-uploads></div>'
            + '<p class="form-text">เลือกภาพ Demo หรืออัปโหลดรูป (ระบบย่อขนาดอัตโนมัติ เก็บในเบราว์เซอร์นี้เท่านั้น)</p></div>';
    }
    function bindPhotoPicker(root) {
        root.querySelectorAll('[data-photo-upload]').forEach(function (input) {
            input.addEventListener('change', function () {
                var file = input.files[0];
                if (!file) return;
                if (!/^image\//.test(file.type) || file.size > 8 * 1024 * 1024) { toast('รองรับเฉพาะไฟล์รูปภาพขนาดไม่เกิน 8 MB', 'warning'); return; }
                var reader = new FileReader();
                reader.onload = function () {
                    var img = new Image();
                    img.onload = function () {
                        var scale = Math.min(1, 480 / Math.max(img.width, img.height));
                        var c = document.createElement('canvas');
                        c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
                        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                        var url = c.toDataURL('image/jpeg', 0.7);
                        var box = input.closest('[data-photo-picker]').querySelector('[data-photo-uploads]');
                        var tile = document.createElement('label');
                        tile.className = 'photo-opt';
                        tile.innerHTML = '<input type="checkbox" checked><img alt="รูปที่อัปโหลด"><i class="bi bi-check-circle-fill"></i>';
                        tile.querySelector('input').value = url;
                        tile.querySelector('img').src = url;
                        box.appendChild(tile);
                        log('UI', 'photoUpload', 'resized', { w: c.width, h: c.height, kb: Math.round(url.length / 1024) });
                    };
                    img.onerror = function () { toast('ไม่สามารถอ่านไฟล์รูปนี้ได้ กรุณาลองไฟล์อื่น', 'warning'); };
                    img.src = reader.result;
                };
                reader.readAsDataURL(file);
                input.value = '';
            });
        });
    }
    function pickedPhotos(root, name) {
        var box = root.querySelector('[data-photo-picker="' + name + '"]');
        return box ? Array.prototype.map.call(box.querySelectorAll('input[type=checkbox]:checked'), function (i) { return i.value; }) : [];
    }

    /** Read a form into an object with trimmed strings; returns null and marks invalid fields when required ones are empty. */
    function readForm(form) {
        var out = {};
        var ok = true;
        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name || el.type === 'file' || (el.type === 'checkbox' && !el.checked && !el.hasAttribute('data-bool'))) return;
            var v = el.type === 'checkbox' && el.hasAttribute('data-bool') ? el.checked : String(el.value).trim().slice(0, 500);
            out[el.name] = v;
            var bad = el.required && v === '';
            el.classList.toggle('is-invalid', bad);
            if (bad) ok = false;
        });
        if (!ok) toast('กรุณากรอกข้อมูลที่จำเป็นให้ครบ', 'warning');
        return ok ? out : null;
    }

    function saved(result, message) {
        if (result.ok) { toast(message, 'success'); return true; }
        toast(result.quota ? 'พื้นที่จัดเก็บในเบราว์เซอร์เต็ม กรุณาลดจำนวนรูปที่อัปโหลด หรือ Reset Demo Data' : 'ไม่สามารถบันทึกได้ กรุณาลองใหม่อีกครั้ง', 'error');
        return false;
    }

    /* Charts ---------------------------------------------------------------------- */
    var charts = {};
    function chart(id, config) {
        var canvas = document.getElementById(id);
        if (!canvas) return null;
        if (!window.Chart) { canvas.parentNode.innerHTML = empty('bi-bar-chart', 'ไม่สามารถโหลดกราฟได้ในขณะนี้'); return null; }
        if (charts[id]) charts[id].destroy();
        Chart.defaults.font.family = "'Inter', 'Sarabun', sans-serif";
        Chart.defaults.color = '#64748B';
        config.options = Object.assign({ responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { usePointStyle: true, boxWidth: 8 } } } }, config.options || {});
        try { charts[id] = new Chart(canvas, config); } catch (error) { logError('UI', 'chart', error, { id: id }); }
        return charts[id];
    }

    /* Reset demo -------------------------------------------------------------------- */
    function resetDemo() {
        confirmBox({ title: '<i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Reset Demo Data', body: 'ระบบจะล้างข้อมูลที่ทดลองบันทึกทั้งหมด (การเช็คชื่อ กิจกรรม ผลงาน ดาว ประกาศ และการแจ้งเตือน) แล้วกลับสู่ข้อมูลตั้งต้น<br><small class="text-secondary">การดำเนินการนี้ย้อนกลับไม่ได้</small>', ok: 'ยืนยัน Reset', tone: 'danger' })
            .then(function (yes) {
                if (!yes) return;
                log('UI', 'resetDemo', 'confirmed');
                STORE.reset();
                try { sessionStorage.setItem('educare.flash', 'รีเซ็ตข้อมูล Demo เรียบร้อยแล้ว'); } catch (e) { /* ignore */ }
                location.reload();
            });
    }

    /* App router ------------------------------------------------------------------- */
    var app = {
        role: CONFIG.role, views: {}, opts: {}, current: null, pending: false,
        register: function (views, opts) { app.views = views; app.opts = opts || {}; },
        render: function () {
            var view = document.getElementById('view');
            if (!view || !STORE.get()) return;
            var id = (location.hash || '').slice(1) || CONFIG.menu[0].id;
            var item = CONFIG.menu.filter(function (m) { return m.id === id; })[0];
            if (!item || !app.views[id]) { id = CONFIG.menu[0].id; item = CONFIG.menu[0]; }
            app.current = id;
            log('Router', 'render', 'view', { role: app.role, view: id });
            document.getElementById('page-title').textContent = item.label;
            document.title = item.label + ' · EduCare Demo';
            document.querySelectorAll('[data-nav]').forEach(function (a) {
                var on = a.getAttribute('data-nav') === id;
                a.classList.toggle('active', on);
                if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
            });
            try {
                if (app.opts.context) app.opts.context(document.getElementById('context-slot'), document.getElementById('context-label'));
                view.innerHTML = app.views[id]() || '';
                if (app.opts.after) app.opts.after(id, view);
                if (app.opts.bell) app.opts.bell();
                if (app.opts.badges) app.opts.badges();
            } catch (error) {
                logError('Router', 'render', error, { view: id });
                view.innerHTML = empty('bi-cloud-slash', 'ไม่สามารถแสดงข้อมูลส่วนนี้ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง', '<button class="btn btn-primary btn-sm" data-action="reset-demo">Reset Demo Data</button>');
            }
            app.pending = false;
        },
        /** Re-render unless the user is typing in a form inside the view (then wait for blur). */
        refresh: function () {
            var active = document.activeElement;
            var view = document.getElementById('view');
            if (view && active && view.contains(active) && /INPUT|TEXTAREA|SELECT/.test(active.tagName)) { app.pending = true; return; }
            var y = window.scrollY;
            app.render();
            window.scrollTo(0, y);
        }
    };

    function initApp() {
        log('App', 'init', 'START', { role: CONFIG.role });
        if (!STORE.load()) {
            document.getElementById('view').innerHTML = empty('bi-cloud-slash', 'ไม่สามารถโหลดข้อมูล Demo ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
            return;
        }
        STORE.onChange(function (reason) {
            if (reason === 'external-reset') { location.reload(); return; }
            app.refresh();
        });
        window.addEventListener('hashchange', function () {
            app.render();
            window.scrollTo(0, 0);
            var side = document.getElementById('sidebar');
            var oc = side && bootstrap.Offcanvas.getInstance(side);
            if (oc) oc.hide();
        });
        document.getElementById('view').addEventListener('focusout', function () {
            setTimeout(function () { if (app.pending) app.refresh(); }, 250);
        });
        app.render();
        log('App', 'init', 'END');
    }

    function initLanding() {
        var status = document.getElementById('demo-status');
        if (status) {
            var n = STORE.changes();
            status.innerHTML = STORE.hasStored() ? '<i class="bi bi-database-check"></i> ข้อมูล Demo ที่ทดลองไว้: ' + n + ' การเปลี่ยนแปลง (เก็บในเบราว์เซอร์นี้)' : '<i class="bi bi-database"></i> ข้อมูล Demo: ชุดเริ่มต้น';
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action="reset-demo"]');
        if (btn) { e.preventDefault(); resetDemo(); }
    });

    document.addEventListener('DOMContentLoaded', function () {
        try {
            var flash = sessionStorage.getItem('educare.flash');
            if (flash) { sessionStorage.removeItem('educare.flash'); setTimeout(function () { toast(flash, 'success'); }, 300); }
        } catch (e) { /* ignore */ }
        if (CONFIG.page === 'app') initApp(); else initLanding();
    });

    window.EDU = {
        log: log, logError: logError, esc: esc, iso: iso, parse: parse, today: today, addDays: addDays, nowTime: nowTime, isWeekday: isWeekday,
        thaiDate: thaiDate, thaiTime: thaiTime, ago: ago, age: age, MONTHS: MONTHS, DAYS: DAYS, STATUS: STATUS, DEMO_PHOTOS: DEMO_PHOTOS,
        data: data, Q: Q, avatar: avatar, statusBadge: statusBadge, photo: photo, photoSrc: photoSrc, empty: empty, stat: stat, channelBadges: channelBadges,
        toast: toast, modal: modal, closeModal: closeModal, confirm: confirmBox, photoPicker: photoPicker, bindPhotoPicker: bindPhotoPicker, pickedPhotos: pickedPhotos,
        readForm: readForm, saved: saved, chart: chart, app: app, store: STORE, notify: window.EDU_NOTIFY
    };
})();
