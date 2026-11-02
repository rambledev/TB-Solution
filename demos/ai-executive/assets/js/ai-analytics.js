/**
 * AI Executive Dashboard — analytics & AI simulation (pure functions).
 *
 * compute(dataset, filters) derives every number the UI shows: KPIs, chart series,
 * classroom and teacher scores, student risk, AI insights, strategic recommendations
 * and forecasts. Because everything comes from one dataset, all modules agree.
 *
 * "AI" here is a rule-based simulation (Demo Analysis), not a call to an AI API.
 */
(function (root) {
    'use strict';

    var TARGET_ATT = 95;
    var GRADE_ORDER = ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'M1', 'M2', 'M3', 'M4', 'M5', 'M6'];
    var GRADE_LABEL = { P1: 'ป.1', P2: 'ป.2', P3: 'ป.3', P4: 'ป.4', P5: 'ป.5', P6: 'ป.6', M1: 'ม.1', M2: 'ม.2', M3: 'ม.3', M4: 'ม.4', M5: 'ม.5', M6: 'ม.6' };
    var MONTH_TH = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    var SUBJECT_ACTION = {
        thai: 'ควรเพิ่มกิจกรรมส่งเสริมการอ่านในระดับชั้น',
        math: 'ควรจัดคลินิกคณิตศาสตร์และแบบฝึกเสริมในระดับชั้น',
        sci: 'ควรเพิ่มกิจกรรมทดลองและโครงงานวิทยาศาสตร์ในระดับชั้น',
        eng: 'ควรจัดกิจกรรมภาษาอังกฤษเชิงสื่อสารเพิ่มเติมในระดับชั้น',
        soc: 'ควรใช้การเรียนรู้แบบโครงงานในวิชาสังคมศึกษาระดับชั้น'
    };
    var cache = { data: null, students: null, rooms: null, teachers: null };

    /* Helpers ----------------------------------------------------------- */
    function mean(list) {
        var vals = list.filter(function (v) { return v !== null && v !== undefined && !isNaN(v); });
        return vals.length ? vals.reduce(function (a, b) { return a + b; }, 0) / vals.length : null;
    }
    function round(v, d) {
        if (v === null || v === undefined || isNaN(v)) return null;
        var f = Math.pow(10, d || 0);
        return Math.round(v * f) / f;
    }
    function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }
    function pct(cur, prev) { return prev ? (cur - prev) / Math.abs(prev) * 100 : 0; }
    function monthLabel(ym) {
        var p = ym.split('-');
        return MONTH_TH[parseInt(p[1], 10) - 1] + ' ' + String((parseInt(p[0], 10) + 543) % 100);
    }
    function slope(values) {
        var n = values.length;
        var mx = (n - 1) / 2;
        var my = mean(values);
        var num = 0, den = 0;
        values.forEach(function (v, i) { num += (i - mx) * (v - my); den += (i - mx) * (i - mx); });
        return den ? num / den : 0;
    }
    function avgScore(s, t) {
        var sc = s.scores[t];
        return (sc.thai + sc.math + sc.sci + sc.eng + sc.soc) / 5;
    }
    function count(list, fn) { return list.filter(fn).length; }

    /* Student risk (support-oriented) ---------------------------------- */
    function riskOf(s, monthIdx) {
        var att = s.att[monthIdx];
        var sub = s.sub[monthIdx];
        var gpa = s.gpa[2];
        var score = 0;
        var factors = [];
        if (att < 85) { score += 3; factors.push('ขาดเรียนสูง'); } else if (att < 90) { score += 2; factors.push('ขาดเรียนสูง'); }
        if (gpa < 1.75) { score += 3; factors.push('ผลการเรียนต่ำ'); } else if (gpa < 2.0) { score += 2; factors.push('ผลการเรียนต่ำ'); }
        if (sub < 60) { score += 2; factors.push('ส่งงานต่ำ'); } else if (sub < 70) { score += 1; factors.push('ส่งงานต่ำ'); }
        if (s.dev[2] < s.dev[1] - 5) { score += 1; factors.push('พัฒนาการลดลง'); }
        if (s.behavior < 65) { score += 1; factors.push('พฤติกรรม'); }
        var level = score >= 5 ? 'high' : (score >= 3 ? 'medium' : 'low');
        return { score: score, level: level, factors: factors };
    }

    /* Per-dataset enrichment (cached) ---------------------------------- */
    function prepare(data) {
        if (cache.data === data) return cache;
        var roomById = {};
        data.classrooms.forEach(function (r) { roomById[r.id] = r; });
        var teacherById = {};
        data.teachers.forEach(function (t) { teacherById[t.id] = t; });

        var students = data.students.map(function (s) {
            var r = riskOf(s, 5);
            var rp = riskOf(s, 4);
            return Object.assign({}, s, {
                roomName: roomById[s.room] ? roomById[s.room].name : s.room,
                level: s.grade.charAt(0) === 'P' ? 'primary' : 'secondary',
                attNow: s.att[5],
                subNow: s.sub[5],
                avgScores: [avgScore(s, 0), avgScore(s, 1), avgScore(s, 2)],
                gpaDelta: round(s.gpa[2] - s.gpa[1], 2),
                devDelta: s.dev[2] - s.dev[1],
                risk: r.level, riskScore: r.score, factors: r.factors,
                riskPrev: rp.level, riskScorePrev: rp.score,
                riskLabel: r.level === 'high' ? 'เสี่ยงสูง' : (r.level === 'medium' ? 'เฝ้าระวัง' : 'ปกติ')
            });
        });

        cache = { data: data, students: students, roomById: roomById, teacherById: teacherById, teachers: null };
        return cache;
    }

    function roomStats(room, list, termIdx, teacherById) {
        var t = termIdx === null ? 2 : termIdx;
        var att = mean(list.map(function (s) { return s.att[5]; }));
        var score = mean(list.map(function (s) { return s.avgScores[t]; }));
        var sub = mean(list.map(function (s) { return s.sub[5]; }));
        var devDelta = mean(list.map(function (s) { return s.devDelta; }));
        var gpa = mean(list.map(function (s) { return s.gpa[t]; }));
        var overall = 0.35 * score + 0.25 * clamp((att - 80) * 5, 0, 100) + 0.25 * sub + 0.15 * clamp(50 + devDelta * 10, 0, 100);
        var homeroom = room.homeroom && teacherById[room.homeroom];
        return {
            id: room.id, name: room.name, grade: room.grade, level: room.level, tag: room.tag,
            students: list.length,
            att: round(att, 1), attPrev: round(mean(list.map(function (s) { return s.att[4]; })), 1),
            score: round(score, 1), gpa: round(gpa, 2), sub: round(sub, 1), devDelta: round(devDelta, 1),
            overall: round(overall, 1),
            risk: count(list, function (s) { return s.risk !== 'low'; }),
            highRisk: count(list, function (s) { return s.risk === 'high'; }),
            homeroom: homeroom ? homeroom.name : '–',
            subjects: ['thai', 'math', 'sci', 'eng', 'soc'].reduce(function (acc, k) {
                acc[k] = round(mean(list.map(function (s) { return s.scores[t][k]; })), 1);
                return acc;
            }, {})
        };
    }

    /* Teacher evaluation (10 factors, simulated) ------------------------ */
    var FACTORS = [
        ['workload', 'ภาระงาน (ความสมดุล)', 8], ['responsibility', 'จำนวนนักเรียนที่รับผิดชอบ', 6],
        ['classAtt', 'การเข้าเรียนของนักเรียนในชั้น', 10], ['achievement', 'ผลสัมฤทธิ์ของนักเรียน', 14],
        ['development', 'พัฒนาการของนักเรียน', 12], ['submission', 'อัตราการส่งงาน', 10],
        ['lessonLog', 'การบันทึกการสอน', 10], ['selfDev', 'การพัฒนาตนเอง', 10],
        ['works', 'ผลงานและความสำเร็จ', 8], ['feedback', 'Feedback นักเรียนและผู้บริหาร', 12]
    ];

    function evaluateTeachers(c) {
        if (c.teachers) return c.teachers;
        var byRoom = {};
        c.students.forEach(function (s) { (byRoom[s.room] = byRoom[s.room] || []).push(s); });
        var loads = c.data.teachers.map(function (t) { return t.classes.reduce(function (n, r) { return n + (byRoom[r] ? byRoom[r].length : 0); }, 0); });
        var avgLoadStudents = mean(loads);
        var avgHours = mean(c.data.teachers.map(function (t) { return t.hours; }));

        c.teachers = c.data.teachers.map(function (t, i) {
            var list = [];
            t.classes.forEach(function (r) { list = list.concat(byRoom[r] || []); });
            var achievement = t.subject
                ? mean(list.map(function (s) { return s.scores[2][t.subject]; }))
                : mean(list.map(function (s) { return 40 + s.gpa[2] * 15; }));
            var f = {
                workload: clamp(100 - Math.abs(t.hours - 18) * 5 - Math.max(0, t.duties - 3) * 8, 30, 100),
                responsibility: clamp(100 - Math.abs(loads[i] - avgLoadStudents) / avgLoadStudents * 60, 40, 100),
                classAtt: clamp((mean(list.map(function (s) { return s.att[5]; })) - 80) * 5, 0, 100),
                achievement: clamp(achievement, 0, 100),
                development: clamp(62 + mean(list.map(function (s) { return s.devDelta; })) * 6, 0, 100),
                submission: clamp(mean(list.map(function (s) { return s.sub[5]; })), 0, 100),
                lessonLog: t.lessonLog,
                selfDev: clamp(t.training * 2.2, 0, 100),
                works: clamp(45 + t.works * 11, 0, 100),
                feedback: (t.fbStudent + t.fbExec) / 2 / 5 * 100
            };
            var overall = FACTORS.reduce(function (sum, x) { return sum + f[x[0]] * x[2] / 100; }, 0);
            var status = overall >= 83 ? 'โดดเด่น' : (overall >= 75 ? 'ดี' : (overall >= 68 ? 'พัฒนาได้' : 'ควรได้รับการสนับสนุน'));
            var factors = FACTORS.map(function (x) { return { key: x[0], label: x[1], weight: x[2], score: round(f[x[0]], 0) }; });
            var sorted = factors.slice().sort(function (a, b) { return b.score - a.score; });
            return Object.assign({}, t, {
                studentsCount: loads[i],
                loadIndex: round(t.hours + t.duties * 1.5, 1),
                loadVsAvg: round((t.hours / avgHours - 1) * 100, 0),
                factors: factors,
                scoreAchievement: round(f.achievement, 0),
                scoreSubmission: round(f.submission, 0),
                scoreSelfDev: round(f.selfDev, 0),
                overall: round(overall, 1),
                status: status,
                strengths: sorted.slice(0, 3),
                improve: sorted.slice(-2).reverse(),
                devTrend: round(mean(list.map(function (s) { return s.devDelta; })), 1)
            });
        });
        return c.teachers;
    }

    function teacherNotes(t) {
        var notes = [];
        if (t.loadVsAvg >= 25) notes.push('ภาระงานสูงกว่าค่าเฉลี่ย ' + t.loadVsAvg + '% ควรทบทวนการกระจายงานพิเศษ');
        if (t.devTrend >= 2) notes.push('นักเรียนในความรับผิดชอบมีพัฒนาการดีขึ้นต่อเนื่อง (+' + t.devTrend + ' คะแนน)');
        if (t.status === 'โดดเด่น') notes.push('ผลการจัดการเรียนการสอนโดดเด่น เหมาะเป็นพี่เลี้ยง (Mentor) ใน PLC');
        if (t.status === 'ควรได้รับการสนับสนุน') notes.push('ควรได้รับการสนับสนุนด้านการจัดการเรียนรู้ ผ่านการนิเทศแบบกัลยาณมิตร');
        t.improve.forEach(function (x) { if (x.score < 70) notes.push('ด้าน' + x.label + ' (' + x.score + ') ควรได้รับการพัฒนาเพิ่มเติม'); });
        if (!notes.length) notes.push('ผลการปฏิบัติงานสมดุลในทุกด้าน ควรรักษามาตรฐานและแลกเปลี่ยนแนวปฏิบัติกับเพื่อนครู');
        return notes;
    }

    /* Scope ---------------------------------------------------------------- */
    function scopeOf(c, filters) {
        var room = filters.room || 'all';
        var list = c.students;
        var label = 'ทุกห้องเรียน';
        if (room.indexOf('grade:') === 0) {
            var g = room.slice(6);
            list = list.filter(function (s) { return s.grade === g; });
            label = 'ระดับชั้น ' + GRADE_LABEL[g];
        } else if (room.indexOf('room:') === 0) {
            var id = room.slice(5);
            list = list.filter(function (s) { return s.room === id; });
            label = 'ห้อง ' + (c.roomById[id] ? c.roomById[id].name : id);
        }
        return { key: room, label: label, students: list, isAll: room === 'all' };
    }

    function termIndex(data, filters) {
        var key = filters.semester + '/' + filters.year;
        var idx = data.meta.terms.indexOf(key);
        return { idx: idx === -1 ? null : idx, key: key };
    }

    /* KPIs ------------------------------------------------------------------ */
    function kpis(c, scope, term) {
        var list = scope.students;
        var n = list.length;
        var data = c.data;
        var att = mean(list.map(function (s) { return s.att[5]; }));
        var attPrev = mean(list.map(function (s) { return s.att[4]; }));
        var sub = mean(list.map(function (s) { return s.sub[5]; }));
        var subPrev = mean(list.map(function (s) { return s.sub[4]; }));
        var follow = count(list, function (s) { return s.risk !== 'low'; });
        var followPrev = count(list, function (s) { return s.riskPrev !== 'low'; });
        var ti = term.idx;
        var ach = ti === null ? null : mean(list.map(function (s) { return s.avgScores[ti]; }));
        var achPrev = ti === null || ti === 0 ? null : mean(list.map(function (s) { return s.avgScores[ti - 1]; }));
        var people = data.teachers.length + data.meta.staff;
        var peoplePrev = data.meta.teachersPrev + data.meta.staffPrev;
        if (!scope.isAll) {
            var rooms = {};
            list.forEach(function (s) { rooms[s.room] = true; });
            people = count(data.teachers, function (t) { return t.classes.some(function (r) { return rooms[r]; }); });
            peoplePrev = people;
        }
        var monthly = function (key) { return [0, 1, 2, 3, 4, 5].map(function (m) { return round(mean(list.map(function (s) { return s[key][m]; })), 1); }); };

        function card(o) {
            var diff = o.prev === null || o.value === null ? null : o.value - o.prev;
            var good = diff === null || diff === 0 ? null : (diff > 0) === o.higherBetter;
            return Object.assign(o, {
                diff: diff, pct: diff === null ? null : pct(o.value, o.prev),
                dir: diff === null || Math.abs(diff) < 1e-9 ? 'flat' : (diff > 0 ? 'up' : 'down'),
                tone: good === null ? 'neutral' : (good ? 'good' : 'bad')
            });
        }

        return [
            card({ key: 'students', label: 'นักเรียนทั้งหมด', icon: 'graduation-cap', color: 'blue', value: n, prev: scope.isAll ? data.meta.enrollment[4] : n, unit: 'คน', decimals: 0, higherBetter: true, compare: 'เทียบเดือนก่อน', spark: scope.isAll ? data.meta.enrollment : null, view: 'students' }),
            card({ key: 'people', label: 'ครูและบุคลากร', icon: 'users', color: 'indigo', value: people, prev: peoplePrev, unit: 'คน', decimals: 0, higherBetter: true, compare: 'เทียบเดือนก่อน', meta: scope.isAll ? 'ครู ' + data.teachers.length + ' · บุคลากร ' + data.meta.staff : 'ครูที่สอนในกลุ่มนี้', view: 'teachers' }),
            card({ key: 'attendance', label: 'อัตราการเข้าเรียนเฉลี่ย', icon: 'calendar-check', color: 'green', value: round(att, 1), prev: round(attPrev, 1), unit: '%', decimals: 1, higherBetter: true, compare: 'เทียบเดือนก่อน', spark: monthly('att'), view: 'attendance' }),
            card({ key: 'achievement', label: 'ผลสัมฤทธิ์ทางการเรียนเฉลี่ย', icon: 'award', color: 'violet', value: round(ach, 1), prev: round(achPrev, 1), unit: '/100', decimals: 1, higherBetter: true, compare: ti === null ? 'ยังไม่มีข้อมูลภาคเรียนนี้' : 'เทียบภาคเรียนก่อน', spark: ti === null ? null : [0, 1, 2].map(function (t) { return round(mean(list.map(function (s) { return s.avgScores[t]; })), 1); }), view: 'students' }),
            card({ key: 'submission', label: 'อัตราการส่งงาน', icon: 'clipboard-check', color: 'amber', value: round(sub, 1), prev: round(subPrev, 1), unit: '%', decimals: 1, higherBetter: true, compare: 'เทียบเดือนก่อน', spark: monthly('sub'), view: 'students' }),
            card({ key: 'follow', label: 'นักเรียนที่ต้องติดตามเป็นพิเศษ', icon: 'heart-handshake', color: 'red', value: follow, prev: followPrev, unit: 'คน', decimals: 0, higherBetter: false, compare: 'เทียบเดือนก่อน', meta: 'เสี่ยงสูง ' + count(list, function (s) { return s.risk === 'high'; }) + ' · เฝ้าระวัง ' + count(list, function (s) { return s.risk === 'medium'; }), view: 'students' })
        ];
    }

    /* Insights & AI analysis -------------------------------------------- */
    function buildInsights(c, scope, rooms, teachers) {
        var list = scope.students;
        var n = Math.max(1, list.length);
        var out = [];
        var attSeries = [0, 1, 2, 3, 4, 5].map(function (m) { return mean(list.map(function (s) { return s.att[m]; })); });
        var subSeries = [0, 1, 2, 3, 4, 5].map(function (m) { return mean(list.map(function (s) { return s.sub[m]; })); });
        var att = attSeries[5];
        var sub = subSeries[5];
        var high = count(list, function (s) { return s.risk === 'high'; });
        var follow = count(list, function (s) { return s.risk !== 'low'; });
        var followPrev = count(list, function (s) { return s.riskPrev !== 'low'; });
        var streak = list.filter(function (s) { return s.streak >= 3; });
        var lowAtt = list.filter(function (s) { return s.att[5] < 90; });
        var sortedRooms = rooms.slice().sort(function (a, b) { return a.overall - b.overall; });
        var worst = sortedRooms[0];
        var best = sortedRooms[sortedRooms.length - 1];

        function add(o) { o.id = 'ins-' + out.length; out.push(o); }

        // Attendance
        if (att < 92) {
            add({ level: 'critical', priority: 'สูง', icon: 'calendar-x', title: 'อัตราการเข้าเรียนต่ำกว่าเกณฑ์อย่างมีนัยสำคัญ', metric: round(att, 1) + '%', detail: 'อัตราการเข้าเรียนเฉลี่ยเดือนล่าสุด ' + round(att, 1) + '% ต่ำกว่าเป้าหมาย ' + TARGET_ATT + '% อยู่ ' + round(TARGET_ATT - att, 1) + ' จุด', actions: ['ตั้งทีมติดตามการมาเรียนรายวันในทุกระดับชั้น', 'ประสานผู้ปกครองนักเรียนที่ขาดเรียนเกิน 3 วันภายใน 24 ชั่วโมง'], view: 'attendance' });
        } else if (attSeries[5] < attSeries[2] - 0.4) {
            add({ level: 'warning', priority: 'กลาง', icon: 'trending-down', title: 'อัตราการเข้าเรียนมีแนวโน้มลดลง 3 เดือนต่อเนื่อง', metric: round(att, 1) + '%', detail: 'ลดลงจาก ' + round(attSeries[2], 1) + '% เป็น ' + round(att, 1) + '% ในช่วง 3 เดือนล่าสุด', actions: ['วิเคราะห์สาเหตุการขาดเรียนรายห้อง', 'ประชาสัมพันธ์ความสำคัญของการมาเรียนผ่านเครือข่ายผู้ปกครอง'], view: 'attendance' });
        }
        if (streak.length >= 10) {
            add({ level: 'critical', priority: 'สูง', icon: 'user-x', title: 'นักเรียนขาดเรียนต่อเนื่องตั้งแต่ 3 วัน ' + streak.length + ' คน', metric: streak.length + ' คน', detail: 'พบนักเรียนขาดเรียนต่อเนื่องหลายราย อาจมีปัญหาสุขภาพ ครอบครัว หรือความเสี่ยงหลุดออกจากระบบ', actions: ['ครูประจำชั้นเยี่ยมบ้านหรือโทรติดตามภายในสัปดาห์นี้', 'ส่งต่อครูแนะแนวสำหรับกรณีที่มีปัจจัยด้านครอบครัว'], view: 'attendance' });
        } else if (streak.length > 0) {
            add({ level: 'warning', priority: 'กลาง', icon: 'user-x', title: 'มีนักเรียนขาดเรียนต่อเนื่อง ' + streak.length + ' คน', metric: streak.length + ' คน', detail: 'ควรติดตามก่อนกลายเป็นการขาดเรียนเรื้อรัง', actions: ['ครูประจำชั้นติดต่อผู้ปกครองเพื่อสอบถามสาเหตุ'], view: 'attendance' });
        }

        // Risk
        if (high / n > 0.07) {
            add({ level: 'critical', priority: 'สูง', icon: 'siren', title: 'สัดส่วนนักเรียนเสี่ยงสูงเกิน 7%', metric: round(high / n * 100, 1) + '%', detail: 'นักเรียนกลุ่มเสี่ยงสูง ' + high + ' คนจาก ' + list.length + ' คน มีหลายปัจจัยร่วมกัน (การขาดเรียน ผลการเรียน การส่งงาน)', actions: ['จัดประชุมทีมดูแลช่วยเหลือรายกรณี', 'จัดทำแผนช่วยเหลือรายบุคคล (IEP) และติดตามทุก 2 สัปดาห์'], view: 'students' });
        } else if (follow > followPrev) {
            add({ level: 'warning', priority: 'กลาง', icon: 'users', title: 'นักเรียนที่ต้องติดตามเพิ่มขึ้นจากเดือนก่อน', metric: '+' + (follow - followPrev) + ' คน', detail: 'จาก ' + followPrev + ' เป็น ' + follow + ' คน', actions: ['ทบทวนรายชื่อร่วมกับครูประจำชั้นทุกสัปดาห์'], view: 'students' });
        }

        // Classroom
        if (worst && rooms.length > 1 && (worst.overall < 62 || worst.att < 90 || worst.gpa < 2.3)) {
            add({ level: 'critical', priority: 'สูง', icon: 'school', title: 'ห้อง ' + worst.name + ' มีผลการดำเนินงานต่ำที่สุด', metric: worst.overall + '/100', detail: 'คะแนนเฉลี่ย ' + worst.score + ' · เข้าเรียน ' + worst.att + '% · ส่งงาน ' + worst.sub + '% · นักเรียนต้องติดตาม ' + worst.risk + ' คน', actions: ['ควรปรับปรุงวิธีการเรียนการสอนในห้อง ' + worst.name + ' ผ่าน PLC ของครูผู้สอน', 'จัดครูพี่เลี้ยงร่วมสังเกตการสอนและสะท้อนผล'], view: 'classrooms', room: worst.id });
        }

        // Submission
        if (sub < 70) {
            add({ level: 'critical', priority: 'สูง', icon: 'clipboard-x', title: 'อัตราการส่งงานต่ำมาก', metric: round(sub, 1) + '%', detail: 'นักเรียนส่งงานเฉลี่ยเพียง ' + round(sub, 1) + '% ของงานที่มอบหมาย', actions: ['ทบทวนปริมาณงานและกำหนดส่งให้เหมาะสม', 'เปิดระบบแจ้งเตือนงานค้างถึงนักเรียนและผู้ปกครอง'], view: 'students' });
        } else if (sub < 82) {
            add({ level: 'warning', priority: 'กลาง', icon: 'clipboard-list', title: 'อัตราการส่งงานต่ำกว่าเป้าหมาย 85%', metric: round(sub, 1) + '%', detail: 'แนวโน้ม 6 เดือน ' + (slope(subSeries) >= 0 ? 'ทรงตัวหรือดีขึ้นเล็กน้อย' : 'ลดลงต่อเนื่อง'), actions: ['ติดตามงานค้างรายสัปดาห์โดยครูประจำวิชา'], view: 'students' });
        }

        // Weakest grade × subject (e.g. reading in ป.3)
        var subjects = ['thai', 'math', 'sci', 'eng', 'soc'];
        var schoolAvg = {};
        subjects.forEach(function (k) { schoolAvg[k] = mean(list.map(function (s) { return s.scores[2][k]; })); });
        var worstCell = null;
        GRADE_ORDER.forEach(function (g) {
            var gl = list.filter(function (s) { return s.grade === g; });
            if (!gl.length) return;
            subjects.forEach(function (k) {
                var diff = mean(gl.map(function (s) { return s.scores[2][k]; })) - schoolAvg[k];
                if (!worstCell || diff < worstCell.diff) worstCell = { grade: g, subject: k, diff: diff };
            });
        });
        if (worstCell && worstCell.diff < -3) {
            add({ level: 'recommendation', priority: worstCell.diff < -5 ? 'สูง' : 'กลาง', icon: 'book-open', title: SUBJECT_ACTION[worstCell.subject] + ' ' + GRADE_LABEL[worstCell.grade], metric: round(worstCell.diff, 1) + ' คะแนน', detail: 'วิชา' + c.data.subjects[worstCell.subject].name + ' ชั้น ' + GRADE_LABEL[worstCell.grade] + ' ต่ำกว่าค่าเฉลี่ยของโรงเรียน ' + Math.abs(round(worstCell.diff, 1)) + ' คะแนน', actions: ['จัดกิจกรรมเสริมสัปดาห์ละ 2 ครั้งเป็นเวลา 8 สัปดาห์', 'วัดผลก่อน–หลังเพื่อประเมินประสิทธิภาพกิจกรรม'], view: 'students' });
        }

        // Teachers
        var heavy = teachers.filter(function (t) { return t.hours >= 24; });
        var hoursSd = Math.sqrt(mean(teachers.map(function (t) { return Math.pow(t.hours - mean(teachers.map(function (x) { return x.hours; })), 2); })));
        if (heavy.length >= 2 || hoursSd > 3.2) {
            add({ level: 'warning', priority: heavy.length >= 5 ? 'สูง' : 'กลาง', icon: 'briefcase', title: 'ภาระงานครูไม่สมดุล', metric: heavy.length + ' คน', detail: 'ครู ' + heavy.length + ' คนสอนตั้งแต่ 24 ชม./สัปดาห์ (ส่วนเบี่ยงเบนมาตรฐาน ' + round(hoursSd, 1) + ' ชม.)', actions: ['ปรับการกระจายชั่วโมงสอนและงานพิเศษในภาคเรียนถัดไป', 'พิจารณาอัตรากำลังเสริมในกลุ่มสาระที่ขาดแคลน'], view: 'teachers' });
        }
        var stars = teachers.filter(function (t) { return t.status === 'โดดเด่น'; });
        if (stars.length) {
            add({ level: 'opportunity', priority: 'กลาง', icon: 'star', title: 'ควรส่งเสริมครูที่มีผลการจัดการเรียนการสอนโดดเด่น ' + stars.length + ' คน', metric: stars.length + ' คน', detail: 'ครูกลุ่มนี้มีคะแนนรวมตั้งแต่ 83 ขึ้นไป เหมาะเป็นพี่เลี้ยงและผู้นำ PLC', actions: ['มอบบทบาท Mentor ให้ครูใหม่หรือครูที่ต้องการการสนับสนุน', 'จัดเวทีแลกเปลี่ยนแนวปฏิบัติที่ดีประจำเดือน'], view: 'teachers' });
        }
        var support = teachers.filter(function (t) { return t.status === 'ควรได้รับการสนับสนุน'; });
        if (support.length) {
            add({ level: 'recommendation', priority: 'กลาง', icon: 'hand-heart', title: 'ควรสนับสนุนครู ' + support.length + ' คนด้านการจัดการเรียนรู้', metric: support.length + ' คน', detail: 'ใช้ผลวิเคราะห์ประกอบการนิเทศ ไม่ใช่การตัดสิน ควรพูดคุยถึงบริบทและอุปสรรคร่วมกัน', actions: ['นิเทศแบบกัลยาณมิตรเดือนละ 1 ครั้ง', 'จัดอบรมเทคนิคการสอนที่ตรงกับความต้องการ'], view: 'teachers' });
        }

        // Opportunities
        if (best && rooms.length > 1 && best.overall >= 75) {
            add({ level: 'opportunity', priority: 'ต่ำ', icon: 'trophy', title: 'ห้อง ' + best.name + ' มีผลการดำเนินงานโดดเด่น', metric: best.overall + '/100', detail: 'เข้าเรียน ' + best.att + '% · คะแนนเฉลี่ย ' + best.score + ' · ส่งงาน ' + best.sub + '%', actions: ['ถอดบทเรียนแนวปฏิบัติของห้องนี้และขยายผลสู่ระดับชั้น'], view: 'classrooms', room: best.id });
        }
        var gpaNow = mean(list.map(function (s) { return s.gpa[2]; }));
        var gpaPrev = mean(list.map(function (s) { return s.gpa[1]; }));
        if (gpaNow - gpaPrev >= 0.03) {
            add({ level: 'opportunity', priority: 'ต่ำ', icon: 'trending-up', title: 'ผลการเรียนเฉลี่ยดีขึ้นจากภาคเรียนก่อน', metric: '+' + round(gpaNow - gpaPrev, 2), detail: 'GPA เฉลี่ยเพิ่มจาก ' + round(gpaPrev, 2) + ' เป็น ' + round(gpaNow, 2), actions: ['รักษาโมเมนตัมด้วยการติดตามผลรายเดือน'], view: 'students' });
        }

        // Recommendations
        if (lowAtt.length) {
            add({ level: 'recommendation', priority: lowAtt.length / n > 0.1 ? 'สูง' : 'กลาง', icon: 'phone-call', title: 'ควรติดตามนักเรียนที่มีอัตราการขาดเรียนสูง ' + lowAtt.length + ' คน', metric: lowAtt.length + ' คน', detail: 'นักเรียนที่มาเรียนต่ำกว่า 90% ในเดือนล่าสุด', actions: ['มอบหมายครูประจำชั้นติดตามรายบุคคล', 'บันทึกผลการติดตามในระบบทุกสัปดาห์'], view: 'attendance' });
        }

        var order = { critical: 0, warning: 1, recommendation: 2, opportunity: 3 };
        var prio = { 'สูง': 0, 'กลาง': 1, 'ต่ำ': 2 };
        out.sort(function (a, b) { return order[a.level] - order[b.level] || prio[a.priority] - prio[b.priority]; });
        return out;
    }

    function buildAnalysis(c, scope, rooms, insights) {
        var list = scope.students;
        var n = Math.max(1, list.length);
        var attSeries = [0, 1, 2, 3, 4, 5].map(function (m) { return mean(list.map(function (s) { return s.att[m]; })); });
        var subSeries = [0, 1, 2, 3, 4, 5].map(function (m) { return mean(list.map(function (s) { return s.sub[m]; })); });
        var att = attSeries[5], sub = subSeries[5];
        var gpa = [0, 1, 2].map(function (t) { return mean(list.map(function (s) { return s.gpa[t]; })); });
        var dev = [0, 1, 2].map(function (t) { return mean(list.map(function (s) { return s.dev[t]; })); });
        var behavior = mean(list.map(function (s) { return s.behavior; }));
        var activity = c.data.activities.length ? mean(c.data.activities.map(function (a) { return a.participants; })) / c.data.students.length * 100 : 0;
        var follow = count(list, function (s) { return s.risk !== 'low'; });
        var followPrev = count(list, function (s) { return s.riskPrev !== 'low'; });

        var health = round(0.25 * clamp((att - 80) * 5, 0, 100) + 0.25 * clamp((gpa[2] - 1) / 3 * 100, 0, 100) + 0.2 * sub + 0.15 * clamp(dev[2], 0, 100) + 0.15 * clamp(100 - follow / n * 400, 0, 100), 0);
        var healthLabel = health >= 85 ? 'ดีเยี่ยม' : (health >= 72 ? 'ดี' : (health >= 60 ? 'พอใช้' : 'น่ากังวล'));

        var strengths = [], weak = [], improve = [];
        (att >= TARGET_ATT ? strengths : weak).push('อัตราการเข้าเรียน ' + round(att, 1) + '% ' + (att >= TARGET_ATT ? 'ผ่าน' : 'ต่ำกว่า') + 'เป้าหมาย ' + TARGET_ATT + '%');
        (gpa[2] >= 3.0 ? strengths : weak).push('GPA เฉลี่ย ' + round(gpa[2], 2) + (gpa[2] >= 3.0 ? ' อยู่ในระดับดี' : ' ต่ำกว่าเกณฑ์ 3.00'));
        (sub >= 85 ? strengths : weak).push('การส่งงานเฉลี่ย ' + round(sub, 1) + '%' + (sub >= 85 ? ' สูงกว่าเป้าหมาย' : ' ต่ำกว่าเป้าหมาย 85%'));
        if (behavior >= 84) strengths.push('พฤติกรรมนักเรียนโดยรวมดี (เฉลี่ย ' + round(behavior, 0) + '/100)'); else weak.push('คะแนนพฤติกรรมเฉลี่ย ' + round(behavior, 0) + '/100 ควรเสริมกิจกรรมพัฒนาคุณลักษณะ');
        if (activity >= 70) strengths.push('นักเรียนมีส่วนร่วมในกิจกรรมเฉลี่ย ' + round(activity, 0) + '% ต่อกิจกรรม'); else weak.push('การมีส่วนร่วมในกิจกรรมเฉลี่ยเพียง ' + round(activity, 0) + '%');
        if (slope(attSeries) > 0.05) improve.push('อัตราการเข้าเรียนเพิ่มขึ้นต่อเนื่อง (' + round(attSeries[0], 1) + '% → ' + round(att, 1) + '%)');
        if (slope(subSeries) > 0.1) improve.push('อัตราการส่งงานดีขึ้นใน 6 เดือน (' + round(subSeries[0], 1) + '% → ' + round(sub, 1) + '%)');
        if (gpa[2] > gpa[0] + 0.03) improve.push('GPA เฉลี่ยดีขึ้น 3 ภาคเรียนติดต่อกัน (' + round(gpa[0], 2) + ' → ' + round(gpa[2], 2) + ')');
        if (dev[2] > dev[0] + 1) improve.push('คะแนนพัฒนาการเฉลี่ยเพิ่มขึ้น ' + round(dev[2] - dev[0], 1) + ' คะแนน');
        if (follow < followPrev) improve.push('นักเรียนที่ต้องติดตามลดลงจาก ' + followPrev + ' เป็น ' + follow + ' คน');

        var groups = {};
        list.forEach(function (s) { if (s.risk !== 'low') s.factors.forEach(function (f) { groups[f] = (groups[f] || 0) + 1; }); });
        var riskGroups = Object.keys(groups).map(function (k) { return { label: k, count: groups[k], pct: round(groups[k] / n * 100, 1) }; }).sort(function (a, b) { return b.count - a.count; });

        // Forecast: linear trend for 3 months ahead.
        var s = slope(attSeries);
        var forecast = [1, 2, 3].map(function (k) { return round(clamp(att + s * k, 50, 100), 1); });
        var subS = slope(subSeries);
        var gpaNext = clamp(gpa[2] + (gpa[2] - gpa[0]) / 2, 1, 4);

        var critical = insights.filter(function (i) { return i.level === 'critical'; });
        var summary = 'สถานการณ์โดยรวมของ' + (scope.isAll ? 'โรงเรียน' : scope.label) + 'อยู่ในระดับ “' + healthLabel + '” (ดัชนีสุขภาพโรงเรียน ' + health + '/100) '
            + 'อัตราการเข้าเรียน ' + round(att, 1) + '% ' + (s >= 0 ? 'มีแนวโน้มทรงตัวหรือดีขึ้น' : 'มีแนวโน้มลดลง') + ' GPA เฉลี่ย ' + round(gpa[2], 2) + ' และการส่งงาน ' + round(sub, 1) + '% '
            + (critical.length ? 'พบประเด็นวิกฤต ' + critical.length + ' เรื่องที่ควรเร่งดำเนินการ โดยเฉพาะ' + critical[0].title : 'ไม่พบประเด็นวิกฤต ควรมุ่งรักษามาตรฐานและขยายผลแนวปฏิบัติที่ดี')
            + ' และมีนักเรียนที่ต้องติดตามเป็นพิเศษ ' + follow + ' คน';

        return {
            health: health, healthLabel: healthLabel, summary: summary,
            strengths: strengths.length ? strengths : ['ยังไม่พบจุดแข็งที่โดดเด่นชัดเจนในข้อมูลชุดนี้'],
            weaknesses: weak.length ? weak : ['ไม่พบจุดอ่อนที่มีนัยสำคัญ'],
            improving: improve.length ? improve : ['ยังไม่พบแนวโน้มที่ดีขึ้นอย่างมีนัยสำคัญ ควรติดตามผลมาตรการที่ดำเนินการอยู่'],
            urgent: critical.length ? critical.map(function (i) { return i.title + ' — ' + i.actions[0]; }) : ['ไม่มีปัญหาวิกฤตที่ต้องเร่งแก้ไขในขณะนี้'],
            riskGroups: riskGroups,
            stars: rooms.slice().sort(function (a, b) { return b.overall - a.overall; }).slice(0, 3),
            forecast: {
                labels: c.data.meta.months.map(monthLabel).concat(['พ.ย. 69', 'ธ.ค. 69', 'ม.ค. 70']),
                actual: attSeries.map(function (v) { return round(v, 1); }),
                predicted: forecast,
                notes: [
                    'คาดการณ์อัตราการเข้าเรียน ม.ค. 70: ' + forecast[2] + '% (' + (s >= 0 ? 'เพิ่มขึ้น' : 'ลดลง') + 'เฉลี่ย ' + Math.abs(round(s, 2)) + ' จุด/เดือน)',
                    'คาดการณ์อัตราการส่งงานใน 3 เดือน: ' + round(clamp(sub + subS * 3, 0, 100), 1) + '%',
                    'คาดการณ์ GPA ภาคเรียน 2/2569: ' + round(gpaNext, 2),
                    forecast[2] < TARGET_ATT ? 'หากไม่มีมาตรการเพิ่มเติม การเข้าเรียนจะยังต่ำกว่าเป้าหมาย ' + TARGET_ATT + '%' : 'การเข้าเรียนมีแนวโน้มอยู่ในเกณฑ์เป้าหมาย'
                ]
            }
        };
    }

    /* Charts data --------------------------------------------------------- */
    function charts(c, scope, term, range, rooms) {
        var list = scope.students;
        var monthIdx = [0, 1, 2, 3, 4, 5].slice(6 - range);
        var labels = monthIdx.map(function (i) { return monthLabel(c.data.meta.months[i]); });
        var grades = GRADE_ORDER.filter(function (g) { return list.some(function (s) { return s.grade === g; }); });
        var ti = term.idx;
        var byLevel = function (level, key, i) { return round(mean(list.filter(function (s) { return s.level === level; }).map(function (s) { return s[key][i]; })), 1); };
        var hasPrimary = list.some(function (s) { return s.level === 'primary'; });
        var hasSecondary = list.some(function (s) { return s.level === 'secondary'; });
        var dist = [0, 0, 0, 0];
        if (ti !== null) list.forEach(function (s) { var g = s.gpa[ti]; dist[g >= 3.5 ? 0 : (g >= 3.0 ? 1 : (g >= 2.0 ? 2 : 3))]++; });

        return {
            monthLabels: labels,
            attendance: { labels: labels, values: monthIdx.map(function (i) { return round(mean(list.map(function (s) { return s.att[i]; })), 1); }), target: TARGET_ATT },
            distribution: ti === null ? null : { labels: ['ดีเยี่ยม (3.50+)', 'ดี (3.00–3.49)', 'ปานกลาง (2.00–2.99)', 'ต้องปรับปรุง (<2.00)'], values: dist },
            academic: ti === null ? null : {
                labels: grades.map(function (g) { return GRADE_LABEL[g]; }),
                current: grades.map(function (g) { return round(mean(list.filter(function (s) { return s.grade === g; }).map(function (s) { return s.avgScores[ti]; })), 1); }),
                previous: ti === 0 ? null : grades.map(function (g) { return round(mean(list.filter(function (s) { return s.grade === g; }).map(function (s) { return s.avgScores[ti - 1]; })), 1); }),
                termKey: term.key, prevKey: ti === 0 ? null : c.data.meta.terms[ti - 1]
            },
            submission: { labels: labels, primary: hasPrimary ? monthIdx.map(function (i) { return byLevel('primary', 'sub', i); }) : null, secondary: hasSecondary ? monthIdx.map(function (i) { return byLevel('secondary', 'sub', i); }) : null },
            rooms: rooms,
            development: { labels: c.data.meta.terms.map(function (t) { return 'ภาคเรียน ' + t; }), primary: hasPrimary ? [0, 1, 2].map(function (i) { return byLevel('primary', 'dev', i); }) : null, secondary: hasSecondary ? [0, 1, 2].map(function (i) { return byLevel('secondary', 'dev', i); }) : null },
            gradeGpa: { labels: grades.map(function (g) { return GRADE_LABEL[g]; }), terms: c.data.meta.terms, values: [0, 1, 2].map(function (t) { return grades.map(function (g) { return round(mean(list.filter(function (s) { return s.grade === g; }).map(function (s) { return s.gpa[t]; })), 2); }); }) },
            subjects: { keys: ['thai', 'math', 'sci', 'eng', 'soc'], labels: ['thai', 'math', 'sci', 'eng', 'soc'].map(function (k) { return c.data.subjects[k].name; }), current: ['thai', 'math', 'sci', 'eng', 'soc'].map(function (k) { return round(mean(list.map(function (s) { return s.scores[ti === null ? 2 : ti][k]; })), 1); }), previous: ['thai', 'math', 'sci', 'eng', 'soc'].map(function (k) { return round(mean(list.map(function (s) { return s.scores[Math.max(0, (ti === null ? 2 : ti) - 1)][k]; })), 1); }) }
        };
    }

    /* Public API ----------------------------------------------------------- */
    function compute(data, filters) {
        filters = Object.assign({ range: 6, year: '2569', semester: '1', room: 'all' }, filters || {});
        var c = prepare(data);
        var scope = scopeOf(c, filters);
        var term = termIndex(data, filters);
        var byRoom = {};
        c.students.forEach(function (s) { (byRoom[s.room] = byRoom[s.room] || []).push(s); });
        var allRooms = data.classrooms.map(function (r) { return roomStats(r, byRoom[r.id] || [], term.idx, c.teacherById); });
        var scopeRoomIds = {};
        scope.students.forEach(function (s) { scopeRoomIds[s.room] = true; });
        var rooms = allRooms.filter(function (r) { return scopeRoomIds[r.id]; });
        var teachers = evaluateTeachers(c);
        var insights = buildInsights(c, scope, rooms, teachers);
        var analysis = buildAnalysis(c, scope, rooms, insights);

        return {
            filters: filters,
            meta: data.meta,
            scope: scope,
            term: term,
            kpis: kpis(c, scope, term),
            charts: charts(c, scope, term, parseInt(filters.range, 10) || 6, rooms),
            rooms: rooms,
            allRooms: allRooms,
            students: scope.students,
            allStudents: c.students,
            teachers: teachers,
            insights: insights,
            analysis: analysis,
            daily: data.daily,
            activities: data.activities,
            subjects: data.subjects
        };
    }

    var api = { compute: compute, teacherNotes: teacherNotes, monthLabel: monthLabel, mean: mean, round: round, GRADE_LABEL: GRADE_LABEL, GRADE_ORDER: GRADE_ORDER, TARGET_ATT: TARGET_ATT };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    root.EXD_AI = api;
})(typeof window !== 'undefined' ? window : globalThis);
