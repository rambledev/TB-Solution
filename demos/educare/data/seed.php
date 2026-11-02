<?php

declare(strict_types=1);

/**
 * EduCare — shared seed data (one data set for every role).
 *
 * Generated deterministically (fixed seed) and dated relative to "today", so the demo
 * always has recent history. All names are fictional. The browser copies this seed
 * into LocalStorage once; from then on every role reads and writes that same store.
 */
function educare_seed(string $today): array
{
    educare_log('Seed', 'educare_seed', 'START', ['today' => $today]);
    mt_srand(2569);

    $rand = static fn (int $min, int $max): int => mt_rand($min, $max);
    $pick = static fn (array $items): mixed => $items[mt_rand(0, count($items) - 1)];
    $chance = static fn (float $p): bool => mt_rand() / mt_getrandmax() < $p;

    $schools = [
        ['id' => 'SCH1', 'name' => 'โรงเรียนอนุบาลโพนสูง', 'short' => 'อนุบาลโพนสูง'],
        ['id' => 'SCH2', 'name' => 'โรงเรียนสายรุ้ง',      'short' => 'สายรุ้ง'],
    ];
    $classrooms = [
        ['id' => 'K1-1', 'name' => 'K1/1', 'label' => 'อนุบาล 1/1', 'school' => 'SCH1', 'age' => 3],
        ['id' => 'K1-2', 'name' => 'K1/2', 'label' => 'อนุบาล 1/2', 'school' => 'SCH1', 'age' => 3],
        ['id' => 'K2-1', 'name' => 'K2/1', 'label' => 'อนุบาล 2/1', 'school' => 'SCH1', 'age' => 4],
        ['id' => 'K2-2', 'name' => 'K2/2', 'label' => 'อนุบาล 2/2', 'school' => 'SCH2', 'age' => 4],
        ['id' => 'P1-1', 'name' => 'P1/1', 'label' => 'ประถม 1/1',  'school' => 'SCH2', 'age' => 6],
        ['id' => 'P2-1', 'name' => 'P2/1', 'label' => 'ประถม 2/1',  'school' => 'SCH2', 'age' => 7],
    ];

    $teacherNames = ['วิไลวรรณ แสงทองคำ', 'สุนันทา ใจดีงาม', 'กาญจนา ศรีสวัสดิ์', 'ศิริพร บุญมาเจริญ', 'ปิยะนุช ทองประเสริฐ', 'อรุณี สุขสมบูรณ์', 'จิราภรณ์ มั่นคงวัฒนา', 'ชุติมา เพชรรัตน์', 'ธีรวัฒน์ อินทร์แก้ว', 'ณัฐพร ธรรมรักษ์'];
    $teachers = [];
    foreach ($teacherNames as $i => $name) {
        $teachers[] = [
            'id'         => sprintf('T%02d', $i + 1),
            'name'       => 'ครู' . explode(' ', $name)[0],
            'fullName'   => ($i === 8 ? 'นาย' : 'นางสาว') . $name,
            'school'     => $i < 5 ? 'SCH1' : 'SCH2',
            'classrooms' => [],
            'role'       => $i < 6 ? 'ครูประจำชั้น' : 'ครูผู้ช่วย',
        ];
    }
    // Homeroom teachers T01–T06, assistants T07–T10. The demo teacher (T01) also covers K1-2 and K2-1.
    foreach ($classrooms as $i => &$room) {
        $room['homeroom'] = $teachers[$i]['id'];
        $teachers[$i]['classrooms'][] = $room['id'];
    }
    unset($room);
    $teachers[0]['classrooms'] = ['K1-1', 'K1-2', 'K2-1'];
    $teachers[6]['classrooms'] = ['K1-1', 'K1-2'];
    $teachers[7]['classrooms'] = ['K2-1'];
    $teachers[8]['classrooms'] = ['K2-2', 'P1-1'];
    $teachers[9]['classrooms'] = ['P2-1'];

    // Students (20 per room) and parents.
    $boys = ['ภูริ', 'ธนกร', 'ปัณณวิชญ์', 'ธีรภัทร', 'ชยพล', 'ศุภกร', 'ภาคิน', 'กฤตภาส', 'ปุณณวิช', 'ตะวัน', 'ปกรณ์', 'จิรายุ', 'คุณานนต์', 'อชิระ', 'กันตภณ', 'เตชินท์', 'นภัทร', 'วีรภัทร', 'ธนภูมิ', 'ภัทรพล'];
    $girls = ['ณัฐธิดา', 'ปุณยวีร์', 'พิมพ์ชนก', 'กัญญาภัค', 'ธัญชนก', 'อรณิชา', 'ภัทรธิดา', 'ศศิกานต์', 'วรัญญา', 'กมลชนก', 'สุพิชญา', 'ขวัญข้าว', 'ปาลิตา', 'พิชชาภา', 'นภัสสร', 'ชญานิศ', 'รินรดา', 'กชกร', 'ญาดา', 'ปิยธิดา'];
    $nickBoy = ['ภูมิ', 'ต้นกล้า', 'ปั้น', 'ไทม์', 'ชิน', 'ซัน', 'คิน', 'ภู', 'ปุณณ์', 'ตะวัน', 'กร', 'จิ', 'คุณ', 'อชิ', 'กัน', 'เต้', 'นะ', 'วี', 'ภูมิใจ', 'พล'];
    $nickGirl = ['ใบเตย', 'ปุยฝ้าย', 'พิม', 'แก้ม', 'ข้าวหอม', 'อิงอิง', 'มะลิ', 'ซีซี', 'วาวา', 'กะทิ', 'น้ำใส', 'ขวัญ', 'ปาล์ม', 'พิชชา', 'นับดาว', 'ชมพู่', 'ริน', 'ไข่มุก', 'ญาญ่า', 'ปิ่น'];
    $surnames = ['ใจดีงาม', 'ศรีสวัสดิ์ดี', 'แสงทองคำ', 'รุ่งเรืองสุข', 'บุญมาเจริญ', 'สุขสมบูรณ์ดี', 'จันทร์เพ็ญศรี', 'มั่นคงวัฒนา', 'เพชรรัตนกุล', 'อินทร์แก้วดี', 'ชัยมงคลศักดิ์', 'ธรรมรักษ์ดี', 'สายสุวรรณ', 'ปัญญาวุฒิกุล', 'ศรีอรุณรุ่ง', 'บัวทองงาม', 'เจริญผลดี', 'ดวงแก้วงาม', 'แก้วประกายดี', 'เรืองศรีสกุล'];
    $parentFirst = ['สมศรี', 'วรรณา', 'ประภา', 'อรทัย', 'จันทร์เพ็ญ', 'สมชาย', 'วิทยา', 'ประเสริฐ', 'สุรชัย', 'มาลี', 'นงลักษณ์', 'บุญมี'];
    $relations = ['คุณแม่', 'คุณแม่', 'คุณพ่อ', 'คุณยาย', 'คุณแม่', 'คุณพ่อ'];
    $allergies = ['', '', '', '', '', '', 'แพ้นมวัว', 'แพ้ไข่', 'แพ้กุ้ง', 'แพ้ถั่วลิสง'];
    $year = (int) substr($today, 0, 4);

    $students = [];
    $parents = [];
    $n = 0;
    foreach ($classrooms as $room) {
        for ($i = 0; $i < 20; $i++) {
            $n++;
            $boy = $i % 2 === 0;
            $k = ($n * 7 + $i) % 20;
            $surname = $surnames[($n * 3) % 20];
            $id = sprintf('S%03d', $n);
            $parentId = sprintf('PA%03d', $n);
            $students[] = [
                'id'        => $id,
                'code'      => sprintf('%s%03d', $room['school'] === 'SCH1' ? '69' : '79', $n),
                'title'     => $boy ? 'ด.ช.' : 'ด.ญ.',
                'first'     => $boy ? $boys[$k] : $girls[$k],
                'last'      => $surname,
                'nickname'  => 'น้อง' . ($boy ? $nickBoy[$k] : $nickGirl[$k]),
                'gender'    => $boy ? 'M' : 'F',
                'classroom' => $room['id'],
                'school'    => $room['school'],
                'dob'       => sprintf('%04d-%02d-%02d', $year - $room['age'] - 1, $rand(1, 12), $rand(1, 28)),
                'color'     => $n % 8,
                'parent'    => $parentId,
                'allergy'   => $pick($allergies),
                'blood'     => $pick(['A', 'B', 'O', 'AB']),
            ];
            $parents[$parentId] = [
                'id'       => $parentId,
                'name'     => $pick($relations) . $parentFirst[$n % 12] . ' ' . $surname,
                'phone'    => sprintf('08%d-XXX-%04d', $rand(1, 9), $rand(1000, 9999)),
                'line'     => $chance(0.85),
                'children' => [$id],
            ];
        }
    }
    // Demo parent PA001 has two children: S001 (K1-1) and S041 (K2-1), both taught by the demo teacher.
    $sibling = &$students[40];
    unset($parents[$sibling['parent']]);
    $sibling['parent'] = 'PA001';
    $sibling['last'] = $students[0]['last'];
    $sibling['first'] = 'ปัณณวิชญ์';
    $sibling['nickname'] = 'น้องปั้น';
    unset($sibling);
    $parents['PA001']['children'][] = 'S041';
    $parents['PA001']['name'] = 'คุณแม่วรรณา ' . $students[0]['last'];
    $parents['PA001']['line'] = true;

    // School days: 20 weekdays before today.
    $days = [];
    $d = new DateTimeImmutable($today);
    while (count($days) < 20) {
        $d = $d->modify('-1 day');
        if ((int) $d->format('N') <= 5) {
            $days[] = $d->format('Y-m-d');
        }
    }
    $days = array_reverse($days);
    $isSchoolDay = (int) (new DateTimeImmutable($today))->format('N') <= 5;

    // Attendance history; today is left open for the demo teacher's rooms (K1-1, K1-2) to fill in.
    $attendance = [];
    foreach ($students as $s) {
        $absentRate = $s['classroom'] === 'K1-2' ? 0.08 : 0.04;
        foreach ($days as $day) {
            $r = mt_rand() / mt_getrandmax();
            $status = $r < $absentRate ? 'absent' : ($r < $absentRate + 0.06 ? 'late' : ($r < $absentRate + 0.08 ? 'leave' : 'present'));
            $attendance[$day][$s['id']] = ['status' => $status, 'time' => $status === 'late' ? sprintf('08:%02d', $rand(31, 55)) : ($status === 'present' ? sprintf('07:%02d', $rand(15, 58)) : ''), 'by' => $s['classroom']];
        }
        if ($isSchoolDay && !in_array($s['classroom'], ['K1-1', 'K1-2'], true)) {
            $r = mt_rand() / mt_getrandmax();
            $status = $r < 0.05 ? 'absent' : ($r < 0.1 ? 'late' : 'present');
            $attendance[$today][$s['id']] = ['status' => $status, 'time' => $status === 'late' ? sprintf('08:%02d', $rand(31, 50)) : ($status === 'present' ? sprintf('07:%02d', $rand(20, 58)) : ''), 'by' => $s['classroom']];
        }
    }

    // Daily activities with demo photos.
    $activityPool = [
        ['ระบายสีนิ้วมือเป็นรูปต้นไม้', 'ศิลปะ', ['art', 'garden']], ['ร้องเพลงและเคลื่อนไหวประกอบจังหวะ', 'ดนตรี', ['music']],
        ['เกมวิ่งเก็บลูกบอลสี', 'กีฬา', ['sport']], ['ทดลองจมลอยของสิ่งต่าง ๆ', 'วิทยาศาสตร์', ['science']],
        ['นิทานก่อนนอน “กระต่ายน้อยใจดี”', 'ภาษา', ['reading']], ['ปลูกผักบุ้งในกระถาง', 'นอกห้องเรียน', ['garden']],
        ['ทำแซนด์วิชผลไม้', 'ทักษะชีวิต', ['cooking']], ['ต่อบล็อกเป็นบ้านในฝัน', 'คณิตศาสตร์', ['blocks']],
        ['ปั้นดินน้ำมันรูปสัตว์', 'ศิลปะ', ['art']], ['เรียนรู้ตัวอักษร ก–ฮ ผ่านเกม', 'ภาษา', ['reading', 'blocks']],
        ['ออกกำลังกายยามเช้า', 'กีฬา', ['sport']], ['ฝึกล้างมือ 7 ขั้นตอน', 'ทักษะชีวิต', ['cooking']],
    ];
    $activities = [];
    $a = 0;
    foreach ($classrooms as $room) {
        foreach (array_slice($days, -10) as $day) {
            $p = $pick($activityPool);
            $a++;
            $activities[] = [
                'id' => sprintf('AC%04d', $a), 'date' => $day, 'time' => sprintf('%02d:%02d', $rand(9, 14), $pick([0, 15, 30, 45])),
                'classroom' => $room['id'], 'title' => $p[0], 'category' => $p[1],
                'description' => 'นักเรียนให้ความสนใจและร่วมกิจกรรมอย่างสนุกสนาน ฝึกทักษะการสังเกต การสื่อสาร และการทำงานร่วมกับเพื่อน',
                'photos' => $p[2], 'by' => $room['homeroom'],
            ];
        }
    }

    // Meals for this week (Mon–Fri) per school.
    $monday = (new DateTimeImmutable($today))->modify('monday this week');
    $menus = [
        'morning' => ['นมจืด + ขนมปังโฮลวีต', 'นมจืด + กล้วยน้ำว้า', 'นมจืด + ขนมกล้วย', 'นมถั่วเหลือง + ซาลาเปา', 'นมจืด + ข้าวโพดต้ม'],
        'lunch'   => ['ข้าวผัดไก่ + แกงจืดผักกาดขาว', 'ข้าวมันไก่ + ซุปฟัก', 'ก๋วยเตี๋ยวหมูน้ำใส', 'ข้าวสวย + ไข่พะโล้ + ผัดผักรวม', 'ข้าวหมูกระเทียม + ต้มจืดเต้าหู้'],
        'snack'   => ['แตงโม', 'มะละกอสุก', 'วุ้นมะพร้าว', 'สับปะรด', 'บัวลอยเผือก'],
    ];
    $meals = [];
    $m = 0;
    foreach ($schools as $si => $school) {
        for ($i = 0; $i < 5; $i++) {
            $day = $monday->modify("+{$i} day")->format('Y-m-d');
            foreach ($menus as $type => $list) {
                $m++;
                $meals[] = ['id' => sprintf('ML%04d', $m), 'date' => $day, 'school' => $school['id'], 'type' => $type, 'menu' => $list[($i + $si) % 5], 'note' => $type === 'lunch' && $i === 2 ? 'มีถั่ว — แยกจานสำหรับเด็กแพ้ถั่ว' : ''];
            }
        }
    }

    // Portfolio and stars.
    $works = [['ภาพวาดครอบครัวของฉัน', 'ศิลปะ', 'art'], ['บ้านจากบล็อกไม้', 'คณิตศาสตร์', 'blocks'], ['ต้นถั่วงอกของฉัน', 'วิทยาศาสตร์', 'garden'], ['เล่านิทานหน้าชั้น', 'ภาษา', 'reading'], ['ร้องเพลงช้าง', 'ดนตรี', 'music'], ['ผลไม้แสนอร่อย', 'ทักษะชีวิต', 'cooking'], ['ทดลองสีผสม', 'วิทยาศาสตร์', 'science']];
    $starReasons = [['ช่วยเพื่อนเก็บของเล่น', 'น้ำใจ'], ['ตั้งใจฟังครูเล่านิทาน', 'การเรียนรู้'], ['กินข้าวหมดจาน', 'ความรับผิดชอบ'], ['กล้ายกมือตอบคำถาม', 'ความกล้าแสดงออก'], ['เข้าแถวเรียบร้อย', 'ระเบียบวินัย'], ['ล้างมือก่อนทานอาหาร', 'ความรับผิดชอบ'], ['แบ่งปันขนมให้เพื่อน', 'น้ำใจ'], ['ทำงานศิลปะเสร็จตามเวลา', 'การเรียนรู้']];
    $portfolio = [];
    $stars = [];
    $pf = $st = 0;
    foreach ($students as $s) {
        $homeroom = $classrooms[array_search($s['classroom'], array_column($classrooms, 'id'), true)]['homeroom'];
        for ($i = 0, $c = $rand(1, 2); $i < $c; $i++) {
            $w = $pick($works);
            $pf++;
            $portfolio[] = ['id' => sprintf('PF%04d', $pf), 'studentId' => $s['id'], 'date' => $pick(array_slice($days, -15)), 'title' => $w[0], 'category' => $w[1], 'photo' => $w[2], 'description' => 'ผลงานแสดงถึงความคิดสร้างสรรค์และความตั้งใจของนักเรียน', 'by' => $homeroom];
        }
        for ($i = 0, $c = $rand(1, 6); $i < $c; $i++) {
            $r = $pick($starReasons);
            $st++;
            $stars[] = ['id' => sprintf('ST%04d', $st), 'studentId' => $s['id'], 'date' => $pick($days), 'count' => $chance(0.2) ? 2 : 1, 'reason' => $r[0], 'category' => $r[1], 'by' => $homeroom];
        }
    }

    $announcements = [
        ['id' => 'AN001', 'date' => $days[19], 'title' => 'ประชุมผู้ปกครองภาคเรียนที่ 2', 'body' => 'ขอเชิญผู้ปกครองทุกท่านร่วมประชุมเพื่อรับทราบแนวทางการจัดการเรียนรู้และกิจกรรมตลอดภาคเรียน ณ หอประชุมโรงเรียน เวลา 09.00 น.', 'audience' => 'all', 'school' => 'SCH1', 'by' => 'ผู้บริหาร', 'pinned' => true],
        ['id' => 'AN002', 'date' => $days[17], 'title' => 'กำหนดการฉีดวัคซีนไข้หวัดใหญ่', 'body' => 'โรงพยาบาลส่งเสริมสุขภาพตำบลจะเข้ามาให้บริการวัคซีนแก่นักเรียนที่ได้รับความยินยอมจากผู้ปกครอง', 'audience' => 'all', 'school' => 'all', 'by' => 'ผู้บริหาร', 'pinned' => false],
        ['id' => 'AN003', 'date' => $days[15], 'title' => 'ขอความร่วมมือเตรียมชุดพละทุกวันพุธ', 'body' => 'นักเรียนห้อง K1/1 มีกิจกรรมพลศึกษาทุกวันพุธ ขอให้ผู้ปกครองเตรียมชุดพละและขวดน้ำส่วนตัว', 'audience' => 'K1-1', 'school' => 'SCH1', 'by' => 'T01', 'pinned' => false],
        ['id' => 'AN004', 'date' => $days[12], 'title' => 'งดรับ–ส่งนักเรียนบริเวณหน้าประตูหลัก', 'body' => 'เนื่องจากมีการปรับปรุงถนน ขอให้ผู้ปกครองใช้ประตูทางเข้าด้านข้างอาคารเรียนชั่วคราว', 'audience' => 'all', 'school' => 'SCH1', 'by' => 'ผู้บริหาร', 'pinned' => false],
        ['id' => 'AN005', 'date' => $days[8], 'title' => 'สัปดาห์ส่งเสริมการอ่าน', 'body' => 'ชวนผู้ปกครองอ่านนิทานกับบุตรหลานวันละ 15 นาที และบันทึกลงสมุดรักการอ่าน', 'audience' => 'all', 'school' => 'SCH2', 'by' => 'ผู้บริหาร', 'pinned' => false],
    ];

    $eventTypes = [['กีฬาสีอนุบาล', 'กิจกรรม'], ['วันลอยกระทง', 'ประเพณี'], ['ทัศนศึกษาสวนสัตว์', 'นอกสถานที่'], ['ตรวจสุขภาพประจำปี', 'สุขภาพ'], ['วันพ่อแห่งชาติ', 'ประเพณี'], ['งานวันเด็ก', 'กิจกรรม'], ['ประชุมผู้ปกครอง', 'ประชุม'], ['วันหยุดปีใหม่', 'วันหยุด']];
    $events = [];
    $base = new DateTimeImmutable(substr($today, 0, 7) . '-01');
    foreach ([[5, 0, 'all'], [12, 1, 'all'], [19, 2, 'K2-1'], [24, 3, 'all'], [35, 4, 'all'], [41, 0, 'P1-1'], [48, 6, 'all'], [62, 5, 'all'], [66, 7, 'all'], [-6, 6, 'all']] as $i => [$offset, $t, $aud]) {
        $events[] = ['id' => sprintf('EV%03d', $i + 1), 'date' => $base->modify("+{$offset} day")->format('Y-m-d'), 'title' => $eventTypes[$t][0], 'type' => $eventTypes[$t][1], 'audience' => $aud, 'school' => $i % 3 === 2 ? 'SCH2' : 'all', 'time' => '09:00'];
    }

    // A few recent notifications for the demo parent so the inbox is not empty.
    $notifications = [
        ['id' => 'NT0001', 'ts' => $days[19] . 'T15:10:00', 'event' => 'star_awarded', 'parent' => 'PA001', 'studentId' => 'S001', 'title' => $students[0]['nickname'] . ' ได้รับดาว ⭐', 'body' => 'ตั้งใจฟังครูเล่านิทาน', 'channels' => ['inapp' => 'delivered', 'push' => 'delivered'], 'read' => false],
        ['id' => 'NT0002', 'ts' => $days[19] . 'T14:20:00', 'event' => 'daily_activity', 'parent' => 'PA001', 'studentId' => 'S041', 'title' => 'กิจกรรมใหม่ห้อง K2/1', 'body' => 'ครูบันทึกกิจกรรมประจำวันพร้อมรูปภาพ', 'channels' => ['inapp' => 'delivered', 'push' => 'delivered'], 'read' => false],
        ['id' => 'NT0003', 'ts' => $days[19] . 'T08:05:00', 'event' => 'announcement', 'parent' => 'PA001', 'studentId' => 'S001', 'title' => 'ประกาศใหม่: ประชุมผู้ปกครองภาคเรียนที่ 2', 'body' => 'ขอเชิญผู้ปกครองร่วมประชุม เวลา 09.00 น.', 'channels' => ['inapp' => 'delivered', 'push' => 'delivered', 'line' => 'delivered'], 'read' => true],
    ];

    $seed = [
        'meta'          => ['version' => 1, 'seededAt' => $today, 'demoTeacher' => 'T01', 'demoParent' => 'PA001'],
        'schools'       => $schools,
        'classrooms'    => $classrooms,
        'teachers'      => $teachers,
        'students'      => $students,
        'parents'       => array_values($parents),
        'attendance'    => $attendance,
        'activities'    => $activities,
        'meals'         => $meals,
        'portfolio'     => $portfolio,
        'stars'         => $stars,
        'announcements' => $announcements,
        'events'        => $events,
        'notifications' => $notifications,
        'rules'         => educare_config('rules'),
    ];

    educare_log('Seed', 'educare_seed', 'END', ['students' => count($students), 'parents' => count($seed['parents']), 'activities' => count($activities), 'stars' => count($stars)]);
    return $seed;
}
