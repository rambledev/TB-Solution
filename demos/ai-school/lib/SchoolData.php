<?php

declare(strict_types=1);

/**
 * Deterministic mock data for the demo school "TB Academy".
 *
 * Everything is generated from per-student and per-teacher records with a fixed
 * seed, and every aggregate (rooms, grades, monthly series, terms) is computed
 * from those records — so dashboards, charts, insights and the chat assistant
 * always agree with each other. All names are fictional.
 *
 * To move to MySQL later, replace SchoolData::get() with a repository that
 * returns the same array shape (see data/schema.sql).
 */
final class SchoolData
{
    public const SEED = 2569;
    public const START_MONTH = '2024-11';
    public const MONTH_COUNT = 24;
    public const CURRENT_TERM = '1/2569';
    public const PREV_TERM = '2/2568';
    public const TERMS = ['1/2568', '2/2568', '1/2569'];
    public const TERM_DAYS = 100;

    public const LEVELS = [
        'kindergarten' => 'อนุบาล',
        'primary'      => 'ประถมศึกษา',
        'secondary'    => 'มัธยมศึกษา',
    ];

    /** key => [label, level, rooms, students per room] */
    private const GRADES = [
        'K1' => ['อ.1', 'kindergarten', 2, 25], 'K2' => ['อ.2', 'kindergarten', 2, 25], 'K3' => ['อ.3', 'kindergarten', 2, 25],
        'P1' => ['ป.1', 'primary', 3, 35], 'P2' => ['ป.2', 'primary', 3, 35], 'P3' => ['ป.3', 'primary', 3, 35],
        'P4' => ['ป.4', 'primary', 3, 35], 'P5' => ['ป.5', 'primary', 3, 35], 'P6' => ['ป.6', 'primary', 3, 35],
        'M1' => ['ม.1', 'secondary', 2, 35], 'M2' => ['ม.2', 'secondary', 2, 35], 'M3' => ['ม.3', 'secondary', 2, 35],
        'M4' => ['ม.4', 'secondary', 2, 35], 'M5' => ['ม.5', 'secondary', 2, 35], 'M6' => ['ม.6', 'secondary', 2, 35],
    ];

    public const SUBJECTS = [
        'thai'    => ['name' => 'ภาษาไทย',                       'short' => 'ไทย',     'offset' => 1.5],
        'math'    => ['name' => 'คณิตศาสตร์',                    'short' => 'คณิต',    'offset' => -5.0],
        'science' => ['name' => 'วิทยาศาสตร์และเทคโนโลยี',      'short' => 'วิทย์',   'offset' => -2.5],
        'social'  => ['name' => 'สังคมศึกษา ศาสนาและวัฒนธรรม', 'short' => 'สังคม',   'offset' => 2.0],
        'english' => ['name' => 'ภาษาต่างประเทศ (อังกฤษ)',      'short' => 'อังกฤษ',  'offset' => -4.0],
        'health'  => ['name' => 'สุขศึกษาและพลศึกษา',           'short' => 'สุขศึกษา', 'offset' => 7.5],
        'arts'    => ['name' => 'ศิลปะ',                         'short' => 'ศิลปะ',   'offset' => 6.0],
        'career'  => ['name' => 'การงานอาชีพ',                   'short' => 'การงาน',  'offset' => 5.0],
    ];

    /** department => [label, subject key|null, teacher count] */
    private const DEPARTMENTS = [
        'early'   => ['ปฐมวัย', null, 6],
        'thai'    => ['ภาษาไทย', 'thai', 6],
        'math'    => ['คณิตศาสตร์', 'math', 7],
        'science' => ['วิทยาศาสตร์และเทคโนโลยี', 'science', 7],
        'social'  => ['สังคมศึกษาฯ', 'social', 6],
        'english' => ['ภาษาต่างประเทศ', 'english', 7],
        'health'  => ['สุขศึกษาและพลศึกษา', 'health', 4],
        'arts'    => ['ศิลปะ', 'arts', 4],
        'career'  => ['การงานอาชีพ', 'career', 5],
    ];

    /* Story injections — the patterns the AI insights are expected to find. */
    private const GRADE_ATT_OFFSET = ['K1' => -1.4, 'K2' => -0.8, 'K3' => -0.4, 'P1' => 0.2, 'P2' => 0.4, 'P3' => 0.5, 'P4' => 0.3, 'P5' => 0.0, 'P6' => 0.2, 'M1' => -0.2, 'M2' => -2.1, 'M3' => -0.9, 'M4' => -0.5, 'M5' => -1.1, 'M6' => -0.6];
    private const GRADE_GPA_OFFSET = ['P1' => 0.25, 'P2' => 0.2, 'P3' => 0.12, 'P4' => 0.05, 'P5' => -0.02, 'P6' => 0.0, 'M1' => -0.05, 'M2' => -0.18, 'M3' => -0.12, 'M4' => -0.04, 'M5' => -0.08, 'M6' => 0.03];
    private const ROOM_ATT_OFFSET = ['ม.2/2' => -1.7, 'ป.5/3' => -0.7];
    private const ROOM_GPA_DROP = ['ม.3/2' => 0.34, 'ป.5/3' => 0.16];
    /** Previous-term attendance was higher for these grades (= attendance is now falling). */
    private const GRADE_ATT_DECLINE = ['M2' => 1.9, 'M5' => 0.6];
    /** Subject score change in the current term: [grade][subject] => points */
    private const SUBJECT_SHIFT = ['M2' => ['math' => -7.0, 'science' => -2.0], 'M1' => ['science' => -3.6], 'P5' => ['english' => -3.2], 'M3' => ['math' => -2.6], 'P3' => ['thai' => 2.4], 'M6' => ['english' => 2.8]];
    /** Monthly seasonal attendance effect (month number => points). April = school break. */
    private const SEASON = [1 => 0.1, 2 => -0.4, 3 => 0.3, 4 => null, 5 => 0.4, 6 => -0.6, 7 => -0.9, 8 => -0.2, 9 => 0.3, 10 => 0.2, 11 => 0.5, 12 => -0.3];
    /** Extra monthly drift for the current term (sums to zero, keeps the term average). */
    private const CURRENT_TERM_TREND = ['M2' => [1.3, 1.0, 0.5, -0.4, -1.0, -1.4], 'M5' => [0.4, 0.3, 0.1, -0.2, -0.3, -0.3]];

    private const MALE_FIRST = ['ธนกร', 'ภูริ', 'กิตติพัฒน์', 'ณัฐวุฒิ', 'ปัณณวิชญ์', 'ธีรภัทร', 'วรเมธ', 'ชยพล', 'ศุภกร', 'พีรพัฒน์', 'อนุชิต', 'ภาคิน', 'ธนวัฒน์', 'กฤตภาส', 'ปุณณวิช', 'รัชชานนท์', 'ธนพนธ์', 'ณัฐภัทร', 'สิรวิชญ์', 'พงศกร', 'ตะวัน', 'ปกรณ์', 'นันทพงศ์', 'ภูมิพัฒน์', 'จิรายุ', 'วชิรวิทย์', 'คุณานนต์', 'ศิวกร', 'อชิระ', 'กันตภณ', 'ภัทรพล', 'เตชินท์'];
    private const FEMALE_FIRST = ['ณัฐธิดา', 'ปุณยวีร์', 'พิมพ์ชนก', 'กัญญาภัค', 'ธัญชนก', 'ชนิดาภา', 'อรณิชา', 'ภัทรธิดา', 'ศศิกานต์', 'ปภาวรินท์', 'วรัญญา', 'ณิชาภัทร', 'กมลชนก', 'สุพิชญา', 'ขวัญข้าว', 'ปาลิตา', 'ธนัชพร', 'พิชชาภา', 'อภิชญา', 'นภัสสร', 'ชญานิศ', 'รินรดา', 'มัณฑนา', 'กชกร', 'ญาดา', 'ปิยธิดา', 'วริศรา', 'อัญชิสา', 'ณัฐนรี', 'ภิญญดา', 'เพียงขวัญ', 'ธมลวรรณ'];
    private const TEACHER_MALE = ['ประพันธ์', 'วีระพงษ์', 'สุรเชษฐ์', 'อนุวัฒน์', 'ธีรวัฒน์', 'กิตติศักดิ์', 'สมพงษ์', 'ณรงค์ศักดิ์', 'วิทยา', 'ปริญญา', 'เอกชัย', 'ชาญวิทย์', 'ไพโรจน์', 'อดิศักดิ์', 'ศุภชัย', 'พงษ์ศักดิ์', 'ธนพล', 'ภานุวัฒน์'];
    private const TEACHER_FEMALE = ['สุนันทา', 'วิไลวรรณ', 'กาญจนา', 'ศิริพร', 'พรทิพย์', 'อรุณี', 'จิราภรณ์', 'รัตนา', 'นภาพร', 'ปิยะนุช', 'สุภาวดี', 'วรรณา', 'ดวงใจ', 'อัมพร', 'ลักขณา', 'พัชรินทร์', 'ศศิธร', 'เบญจวรรณ', 'มาลัยพร', 'ชุติมา', 'กนกวรรณ', 'ณัฐพร', 'อรอุมา', 'ธิดารัตน์', 'จันทร์เพ็ญ', 'สายสุนีย์'];
    private const LAST_NAMES = ['ใจดีงาม', 'ศรีสวัสดิ์ดี', 'แสงทองคำ', 'รุ่งเรืองสุข', 'วงศ์ประเสริฐกุล', 'บุญมาเจริญ', 'ทองประเสริฐ', 'สุขสมบูรณ์ดี', 'พึ่งพาวงศ์', 'ศักดิ์ไพศาลดี', 'จันทร์เพ็ญศรี', 'มั่นคงวัฒนา', 'เพชรรัตนกุล', 'อินทร์แก้วดี', 'ชัยมงคลศักดิ์', 'กิตติวงศ์ไทย', 'ธรรมรักษ์ดี', 'สายสุวรรณ', 'นิลวงศ์ทอง', 'ปัญญาวุฒิกุล', 'ประเสริฐสุขใจ', 'รัตนพงษ์ไพบูลย์', 'ศรีอรุณรุ่ง', 'พรหมประเสริฐ', 'บัวทองงาม', 'วิเศษสมบัติ', 'เจริญผลดี', 'ดวงแก้วงาม', 'อุดมสินทรัพย์', 'แก้วประกายดี', 'สมบัติทวีสุข', 'ภูมิปัญญาดี', 'เรืองศรีสกุล', 'ทวีศักดิ์ดี', 'คงสวัสดิ์วงศ์', 'ศรีวิไลสุข', 'มณีรัตนโชติ', 'ทิพย์สุวรรณ', 'วัฒนกิจไพศาล', 'ชื่นชมบุญ'];
    private const STAFF_ROLES = ['งานธุรการ', 'งานธุรการ', 'งานการเงินและบัญชี', 'งานการเงินและบัญชี', 'งานพัสดุ', 'งานทะเบียนและวัดผล', 'งานทะเบียนและวัดผล', 'งานเทคโนโลยีสารสนเทศ', 'งานห้องสมุด', 'งานพยาบาล', 'งานอาคารสถานที่', 'งานอาคารสถานที่', 'งานโภชนาการ'];

    private static ?array $cache = null;

    /** @var array<string, true> */
    private array $usedNames = [];

    /**
     * Return the full demo dataset (generated once per request).
     */
    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        tb_log('SchoolData', 'get', 'START generate', ['seed' => self::SEED]);
        $started = microtime(true);

        mt_srand(self::SEED);
        self::$cache = (new self())->build();

        tb_log('SchoolData', 'get', 'END', [
            'ms'       => round((microtime(true) - $started) * 1000, 1),
            'students' => count(self::$cache['students']),
            'teachers' => count(self::$cache['teachers']),
        ]);
        return self::$cache;
    }

    private function build(): array
    {
        $months = $this->months();
        $grades = $this->grades();
        $teachers = $this->teachers($grades);
        $students = $this->students($grades, $teachers);

        $data = [
            'months'   => $months,
            'levels'   => self::LEVELS,
            'subjects' => self::SUBJECTS,
            'terms'    => self::TERMS,
            'grades'   => $grades,
            'teachers' => $teachers,
            'staff'    => $this->staff(),
            'students' => $students,
        ];

        $data['rooms'] = $this->roomStats($grades, $students, $teachers);
        $data['grade_stats'] = $this->gradeStats($grades, $students);
        $data['enrollment'] = $this->enrollment($months);
        $data['personnel'] = $this->personnel($months, count($teachers), count($data['staff']));
        $data['attendance'] = $this->attendance($months, $grades, $data['grade_stats']);
        $data['academics'] = $this->academics($grades, $data['grade_stats']);
        $data['teachers'] = $this->teacherMetrics($data['teachers'], $data['academics'], $data['rooms']);
        $data['kpis'] = $this->kpis($months, $data['attendance']['school']);
        $data['imports'] = $this->imports();

        return $data;
    }

    /* ---------------------------------------------------------------- utils */

    private function rnd(float $min, float $max): float
    {
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

    private function gauss(float $mean = 0.0, float $sd = 1.0): float
    {
        $u = max(1e-9, mt_rand() / mt_getrandmax());
        $v = mt_rand() / mt_getrandmax();
        return $mean + $sd * sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    }

    private static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function uniqueName(array $firstNames): array
    {
        for ($i = 0; $i < 50; $i++) {
            $first = $this->pick($firstNames);
            $last = $this->pick(self::LAST_NAMES);
            if (!isset($this->usedNames[$first . $last])) {
                $this->usedNames[$first . $last] = true;
                return [$first, $last];
            }
        }
        return [$this->pick($firstNames), $this->pick(self::LAST_NAMES)];
    }

    /** Term label for a YYYY-MM month, or null during the April break. */
    public static function termOf(string $month): ?string
    {
        [$y, $m] = array_map('intval', explode('-', $month));
        if ($m === 4) {
            return null;
        }
        if ($m >= 5 && $m <= 10) {
            return '1/' . ($y + 543);
        }
        return '2/' . ($m >= 11 ? $y + 543 : $y + 542);
    }

    /* ------------------------------------------------------------ structure */

    private function months(): array
    {
        $months = [];
        $date = new DateTimeImmutable(self::START_MONTH . '-01');
        for ($i = 0; $i < self::MONTH_COUNT; $i++) {
            $months[] = $date->modify("+{$i} month")->format('Y-m');
        }
        return $months;
    }

    private function grades(): array
    {
        $grades = [];
        foreach (self::GRADES as $key => [$label, $level, $roomCount, $perRoom]) {
            $rooms = [];
            for ($r = 1; $r <= $roomCount; $r++) {
                $rooms[$label . '/' . $r] = $perRoom + mt_rand(-2, 2);
            }
            $grades[$key] = ['key' => $key, 'label' => $label, 'level' => $level, 'rooms' => $rooms];
        }

        // Make the school total exactly 1,200 students.
        $total = array_sum(array_map(static fn ($g) => array_sum($g['rooms']), $grades));
        $keys = array_keys($grades);
        $i = 0;
        while ($total !== 1200) {
            $grade = &$grades[$keys[$i % count($keys)]];
            $room = array_key_first(array_slice($grade['rooms'], $i % count($grade['rooms']), 1, true));
            $grade['rooms'][$room] += $total < 1200 ? 1 : -1;
            $total += $total < 1200 ? 1 : -1;
            unset($grade);
            $i++;
        }

        return $grades;
    }

    private function teachers(array $grades): array
    {
        $rooms = [];
        foreach ($grades as $grade) {
            foreach (array_keys($grade['rooms']) as $room) {
                $rooms[] = ['room' => $room, 'grade' => $grade['key'], 'level' => $grade['level']];
            }
        }

        $positions = ['ครูผู้ช่วย', 'ครู', 'ครูชำนาญการ', 'ครูชำนาญการ', 'ครูชำนาญการพิเศษ'];
        $teachers = [];
        $n = 0;
        foreach (self::DEPARTMENTS as $deptKey => [$deptLabel, $subject, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $n++;
                $female = mt_rand(1, 100) <= 68;
                [$first, $last] = $this->uniqueName($female ? self::TEACHER_FEMALE : self::TEACHER_MALE);
                $prefix = $female ? $this->pick(['นาง', 'นางสาว']) : 'นาย';
                $years = mt_rand(1, 28);

                // Classes taught: early-years teachers stay in kindergarten rooms.
                $pool = array_values(array_filter($rooms, static fn ($r) => $deptKey === 'early' ? $r['level'] === 'kindergarten' : $r['level'] !== 'kindergarten'));
                shuffle($pool);
                $classes = array_column(array_slice($pool, 0, $deptKey === 'early' ? 1 : mt_rand(3, 6)), 'room');

                $teachers['T' . str_pad((string) $n, 3, '0', STR_PAD_LEFT)] = [
                    'id'         => 'T' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                    'name'       => $prefix . $first . ' ' . $last,
                    'short'      => 'ครู' . $first,
                    'gender'     => $female ? 'F' : 'M',
                    'department' => $deptKey,
                    'dept_label' => $deptLabel,
                    'subject'    => $subject,
                    'position'   => $years < 3 ? 'ครูผู้ช่วย' : $positions[min(4, intdiv($years, 6) + 1)],
                    'years'      => $years,
                    'classes'    => $classes,
                    'homeroom'   => null,
                ];
            }
        }

        // Homeroom assignment: early-years teachers first for kindergarten rooms, then round-robin.
        $kRooms = array_values(array_filter($rooms, static fn ($r) => $r['level'] === 'kindergarten'));
        $others = array_values(array_filter($rooms, static fn ($r) => $r['level'] !== 'kindergarten'));
        $early = array_keys(array_filter($teachers, static fn ($t) => $t['department'] === 'early'));
        $rest = array_keys(array_filter($teachers, static fn ($t) => $t['department'] !== 'early'));
        foreach ($kRooms as $i => $room) {
            $teachers[$early[$i % count($early)]]['homeroom'] = $room['room'];
            $teachers[$early[$i % count($early)]]['classes'] = [$room['room']];
        }
        foreach ($others as $i => $room) {
            $teachers[$rest[$i]]['homeroom'] = $room['room'];
        }

        return $teachers;
    }

    private function staff(): array
    {
        $staff = [];
        foreach (self::STAFF_ROLES as $i => $role) {
            $female = mt_rand(1, 100) <= 60;
            [$first, $last] = $this->uniqueName($female ? self::TEACHER_FEMALE : self::TEACHER_MALE);
            $staff[] = [
                'id'   => 'E' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'name' => ($female ? 'นางสาว' : 'นาย') . $first . ' ' . $last,
                'role' => $role,
            ];
        }
        return $staff;
    }

    /* ------------------------------------------------------------- students */

    private function students(array $grades, array $teachers): array
    {
        $homeroomOf = [];
        foreach ($teachers as $t) {
            if ($t['homeroom'] !== null) {
                $homeroomOf[$t['homeroom']] = $t['id'];
            }
        }

        $students = [];
        $n = 0;
        foreach ($grades as $grade) {
            $isK = $grade['level'] === 'kindergarten';
            $gradeNo = (int) substr($grade['key'], 1);
            $adult = $grade['level'] === 'secondary' && $gradeNo >= 4;

            foreach ($grade['rooms'] as $room => $size) {
                for ($i = 1; $i <= $size; $i++) {
                    $n++;
                    $female = mt_rand(0, 1) === 1;
                    [$first, $last] = $this->uniqueName($female ? self::FEMALE_FIRST : self::MALE_FIRST);
                    $prefix = $adult ? ($female ? 'นางสาว' : 'นาย') : ($female ? 'เด็กหญิง' : 'เด็กชาย');

                    $ability = $this->gauss();
                    $engage = 0.55 * $ability + 0.83 * $this->gauss();

                    $att = 95.4 + self::GRADE_ATT_OFFSET[$grade['key']] + (self::ROOM_ATT_OFFSET[$room] ?? 0) + 1.5 * $engage;
                    if (mt_rand(1, 1000) <= 55) {
                        $att -= $this->rnd(6, 13); // chronic absence
                    }
                    $att = round(self::clamp($att, 72, 100), 1);
                    $attPrev = round(self::clamp($att + (self::GRADE_ATT_DECLINE[$grade['key']] ?? 0) + $this->gauss(-0.35, 1.0), 72, 100), 1);

                    $gpa = $gpaPrev = $dev = null;
                    if ($isK) {
                        $dev = (int) round(self::clamp(84 + 6 * $ability + ($att - 95) * 0.6 + $this->gauss(0, 3), 55, 100));
                    } else {
                        $gpa = self::clamp(2.95 + (self::GRADE_GPA_OFFSET[$grade['key']] ?? 0) + 0.5 * $ability + ($att - 95) * 0.045, 1.0, 4.0);
                        $drop = (self::ROOM_GPA_DROP[$room] ?? 0) + $this->gauss(-0.07, 0.13);
                        if (mt_rand(1, 1000) <= 45) {
                            $drop += $this->rnd(0.45, 0.85); // sudden decline
                        }
                        $gpaPrev = round(self::clamp($gpa + $drop, 1.0, 4.0), 2);
                        $gpa = round($gpa, 2);
                    }

                    $sub = (int) round(self::clamp(88 + 7 * $ability + ($att - 95) * 0.9 + $this->gauss(0, 4), 35, 100));
                    $subPrev = (int) round(self::clamp($sub + $this->gauss(1.5, 4), 35, 100));
                    $notes = 0;
                    if (mt_rand(1, 100) <= ($att < 88 ? 45 : 8)) {
                        $notes = mt_rand(1, 3);
                    }

                    $id = 'S' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
                    $student = [
                        'id'          => $id,
                        'code'        => '69' . str_pad((string) (1000 + $n), 5, '0', STR_PAD_LEFT),
                        'name'        => $prefix . $first . ' ' . $last,
                        'first'       => $first,
                        'gender'      => $female ? 'F' : 'M',
                        'grade'       => $grade['key'],
                        'grade_label' => $grade['label'],
                        'level'       => $grade['level'],
                        'room'        => $room,
                        'homeroom'    => $homeroomOf[$room] ?? null,
                        'attendance'  => $att,
                        'att_prev'    => $attPrev,
                        'absent_days' => (int) round((100 - $att) / 100 * self::TERM_DAYS),
                        'gpa'         => $gpa,
                        'gpa_prev'    => $gpaPrev,
                        'development' => $dev,
                        'submission'  => $sub,
                        'sub_prev'    => $subPrev,
                        'notes'       => $notes,
                    ];
                    $student += self::assessRisk($student);
                    $student += ['risk_prev' => self::assessRisk($student, true)['risk']];
                    $students[$id] = $student;
                }
            }
        }

        return $students;
    }

    /**
     * Rule-based support-need assessment. Returns risk level, score and the
     * factors found (used by the risk page, insights and chat).
     *
     * @return array{risk: string, risk_score: int, factors: list<array{key: string, label: string}>}
     */
    public static function assessRisk(array $s, bool $previous = false): array
    {
        $att = $previous ? $s['att_prev'] : $s['attendance'];
        $sub = $previous ? $s['sub_prev'] : $s['submission'];
        $gpa = $previous ? $s['gpa_prev'] : $s['gpa'];
        $score = 0;
        $factors = [];

        if ($att < 88) {
            $score += 3;
            $factors[] = ['key' => 'attendance', 'label' => sprintf('อัตราเข้าเรียน %.1f%% (ขาดเรียนประมาณ %d วัน)', $att, (int) round((100 - $att) / 100 * self::TERM_DAYS))];
        } elseif ($att < 92) {
            $score += 1;
            $factors[] = ['key' => 'attendance', 'label' => sprintf('อัตราเข้าเรียน %.1f%% ต่ำกว่าเป้าหมาย 96%%', $att)];
        }

        if ($gpa !== null) {
            if ($gpa < 2.0) {
                $score += 3;
                $factors[] = ['key' => 'academic', 'label' => sprintf('GPA %.2f ต่ำกว่า 2.00', $gpa)];
            } elseif ($gpa < 2.4) {
                $score += 1;
                $factors[] = ['key' => 'academic', 'label' => sprintf('GPA %.2f อยู่ในช่วงที่ควรเฝ้าระวัง', $gpa)];
            }
            if (!$previous && $s['gpa_prev'] - $gpa >= 0.4) {
                $score += 2;
                $factors[] = ['key' => 'progress', 'label' => sprintf('GPA ลดลง %.2f จากภาคเรียนก่อน', $s['gpa_prev'] - $gpa)];
            }
        } elseif (($s['development'] ?? 100) < 72) {
            $score += 2;
            $factors[] = ['key' => 'progress', 'label' => sprintf('คะแนนพัฒนาการ %d/100 ต่ำกว่าเกณฑ์ตามวัย', $s['development'])];
        }

        if ($sub < 70) {
            $score += 2;
            $factors[] = ['key' => 'submission', 'label' => sprintf('ส่งงานครบ %d%% ของงานที่มอบหมาย', $sub)];
        } elseif ($sub < 78) {
            $score += 1;
            $factors[] = ['key' => 'submission', 'label' => sprintf('ส่งงานครบ %d%% ต่ำกว่าค่าเฉลี่ยห้อง', $sub)];
        }

        if (!$previous && $s['notes'] >= 2) {
            $score += 1;
            $factors[] = ['key' => 'behavior', 'label' => sprintf('มีบันทึกการดูแลช่วยเหลือ %d ครั้ง', $s['notes'])];
        }

        $risk = $score >= 5 ? 'high' : ($score >= 3 ? 'medium' : 'low');
        return ['risk' => $risk, 'risk_score' => $score, 'factors' => $factors];
    }

    /* ----------------------------------------------------------- aggregates */

    private static function mean(array $values): float
    {
        $values = array_values(array_filter($values, static fn ($v) => $v !== null));
        return $values === [] ? 0.0 : array_sum($values) / count($values);
    }

    private function roomStats(array $grades, array $students, array $teachers): array
    {
        $byRoom = [];
        foreach ($students as $s) {
            $byRoom[$s['room']][] = $s;
        }

        $rooms = [];
        foreach ($grades as $grade) {
            foreach (array_keys($grade['rooms']) as $room) {
                $list = $byRoom[$room] ?? [];
                $homeroom = $list[0]['homeroom'] ?? null;
                $rooms[$room] = [
                    'room'        => $room,
                    'grade'       => $grade['key'],
                    'grade_label' => $grade['label'],
                    'level'       => $grade['level'],
                    'students'    => count($list),
                    'male'        => count(array_filter($list, static fn ($s) => $s['gender'] === 'M')),
                    'female'      => count(array_filter($list, static fn ($s) => $s['gender'] === 'F')),
                    'homeroom'    => $homeroom,
                    'homeroom_name' => $homeroom !== null ? $teachers[$homeroom]['name'] : '-',
                    'attendance'  => round(self::mean(array_column($list, 'attendance')), 1),
                    'att_prev'    => round(self::mean(array_column($list, 'att_prev')), 1),
                    'absent_days' => array_sum(array_column($list, 'absent_days')),
                    'gpa'         => $grade['level'] === 'kindergarten' ? null : round(self::mean(array_column($list, 'gpa')), 2),
                    'gpa_prev'    => $grade['level'] === 'kindergarten' ? null : round(self::mean(array_column($list, 'gpa_prev')), 2),
                    'submission'  => round(self::mean(array_column($list, 'submission')), 1),
                    'high'        => count(array_filter($list, static fn ($s) => $s['risk'] === 'high')),
                    'medium'      => count(array_filter($list, static fn ($s) => $s['risk'] === 'medium')),
                ];
            }
        }
        return $rooms;
    }

    private function gradeStats(array $grades, array $students): array
    {
        $byGrade = [];
        foreach ($students as $s) {
            $byGrade[$s['grade']][] = $s;
        }

        $stats = [];
        foreach ($grades as $key => $grade) {
            $list = $byGrade[$key];
            $isK = $grade['level'] === 'kindergarten';
            $stats[$key] = [
                'key'        => $key,
                'label'      => $grade['label'],
                'level'      => $grade['level'],
                'rooms'      => count($grade['rooms']),
                'students'   => count($list),
                'male'       => count(array_filter($list, static fn ($s) => $s['gender'] === 'M')),
                'female'     => count(array_filter($list, static fn ($s) => $s['gender'] === 'F')),
                'attendance' => round(self::mean(array_column($list, 'attendance')), 2),
                'att_prev'   => round(self::mean(array_column($list, 'att_prev')), 2),
                'gpa'        => $isK ? null : round(self::mean(array_column($list, 'gpa')), 2),
                'gpa_prev'   => $isK ? null : round(self::mean(array_column($list, 'gpa_prev')), 2),
                'submission' => round(self::mean(array_column($list, 'submission')), 1),
                'high'       => count(array_filter($list, static fn ($s) => $s['risk'] === 'high')),
                'medium'     => count(array_filter($list, static fn ($s) => $s['risk'] === 'medium')),
                'low'        => count(array_filter($list, static fn ($s) => $s['risk'] === 'low')),
            ];
        }
        return $stats;
    }

    /** Monthly head count with transfers; the latest month equals the 1,200 students generated. */
    private function enrollment(array $months): array
    {
        $in = $out = [];
        foreach ($months as $month) {
            $m = (int) substr($month, 5, 2);
            $in[$month] = match ($m) { 5 => mt_rand(48, 58), 4 => mt_rand(2, 5), default => mt_rand(1, 6) };
            $out[$month] = match ($m) { 5 => mt_rand(30, 38), 4 => mt_rand(8, 14), default => mt_rand(0, 4) };
        }

        $count = [];
        $current = 1200;
        foreach (array_reverse($months) as $month) {
            $count[$month] = $current;
            $current = $current - $in[$month] + $out[$month];
        }

        return ['count' => array_reverse($count, true), 'in' => $in, 'out' => $out];
    }

    private function personnel(array $months, int $teachers, int $staff): array
    {
        $series = [];
        foreach ($months as $month) {
            $t = $teachers - ($month < '2026-05' ? 1 : 0) - ($month < '2025-05' ? 1 : 0);
            $s = $staff - ($month < '2025-11' ? 1 : 0);
            $series[$month] = ['teachers' => $t, 'staff' => $s, 'total' => $t + $s];
        }
        return $series;
    }

    /**
     * Monthly attendance per grade. Months of the current and previous term are
     * calibrated so their averages equal the student-level averages.
     */
    private function attendance(array $months, array $grades, array $stats): array
    {
        $currentMonths = array_values(array_filter($months, static fn ($m) => self::termOf($m) === self::CURRENT_TERM));
        $prevMonths = array_values(array_filter($months, static fn ($m) => self::termOf($m) === self::PREV_TERM));
        $seasonMean = static fn (array $list) => self::mean(array_map(static fn ($m) => self::SEASON[(int) substr($m, 5, 2)], $list));
        $curSeason = $seasonMean($currentMonths);
        $prevSeason = $seasonMean($prevMonths);

        $byGrade = [];
        foreach ($grades as $key => $grade) {
            foreach ($months as $month) {
                $season = self::SEASON[(int) substr($month, 5, 2)];
                $term = self::termOf($month);
                if ($season === null || $term === null) {
                    $byGrade[$key][$month] = null;
                    continue;
                }
                if ($term === self::CURRENT_TERM) {
                    $idx = array_search($month, $currentMonths, true);
                    $value = $stats[$key]['attendance'] + $season - $curSeason + (self::CURRENT_TERM_TREND[$key][$idx] ?? 0);
                } elseif ($term === self::PREV_TERM) {
                    $value = $stats[$key]['att_prev'] + $season - $prevSeason;
                } else {
                    $value = $stats[$key]['att_prev'] - 0.45 + $season + $this->gauss(0, 0.15);
                }
                $byGrade[$key][$month] = round(self::clamp($value, 70, 99.6), 1);
            }
        }

        $school = $byLevel = [];
        foreach ($months as $month) {
            $sum = $weight = 0.0;
            $levelSum = $levelWeight = [];
            foreach ($stats as $key => $st) {
                if ($byGrade[$key][$month] === null) {
                    continue;
                }
                $sum += $byGrade[$key][$month] * $st['students'];
                $weight += $st['students'];
                $levelSum[$st['level']] = ($levelSum[$st['level']] ?? 0) + $byGrade[$key][$month] * $st['students'];
                $levelWeight[$st['level']] = ($levelWeight[$st['level']] ?? 0) + $st['students'];
            }
            $school[$month] = $weight > 0 ? round($sum / $weight, 1) : null;
            foreach (array_keys(self::LEVELS) as $level) {
                $byLevel[$level][$month] = isset($levelWeight[$level]) ? round($levelSum[$level] / $levelWeight[$level], 1) : null;
            }
        }

        return ['school' => $school, 'levels' => $byLevel, 'grades' => $byGrade];
    }

    private function academics(array $grades, array $stats): array
    {
        $gpa = $subjects = $gradeSubject = [];
        foreach ($grades as $key => $grade) {
            if ($grade['level'] === 'kindergarten') {
                continue;
            }
            $gpa['1/2569'][$key] = $stats[$key]['gpa'];
            $gpa['2/2568'][$key] = $stats[$key]['gpa_prev'];
            $gpa['1/2568'][$key] = round($stats[$key]['gpa_prev'] - 0.05 + $this->gauss(0, 0.03), 2);
        }

        foreach (self::TERMS as $term) {
            foreach ($gpa[$term] as $key => $g) {
                foreach (self::SUBJECTS as $sk => $subject) {
                    $score = 52 + $g * 7.4 + $subject['offset'] + $this->gauss(0, 0.9);
                    if ($term === self::CURRENT_TERM) {
                        $score += self::SUBJECT_SHIFT[$key][$sk] ?? 0;
                    }
                    $score = round(self::clamp($score, 40, 95), 1);
                    $failRate = round(self::clamp(12 * exp((68 - $score) / 9) + $this->gauss(0, 0.4), 1.0, 40), 1);
                    $gradeSubject[$term][$key][$sk] = [
                        'score'      => $score,
                        'fail_rate'  => $failRate,
                        'fail_count' => (int) round($stats[$key]['students'] * $failRate / 100),
                        'students'   => $stats[$key]['students'],
                    ];
                }
            }

            foreach (array_keys(self::SUBJECTS) as $sk) {
                $students = $scoreSum = $fails = 0;
                foreach ($gradeSubject[$term] as $row) {
                    $students += $row[$sk]['students'];
                    $scoreSum += $row[$sk]['score'] * $row[$sk]['students'];
                    $fails += $row[$sk]['fail_count'];
                }
                $subjects[$term][$sk] = [
                    'score'      => round($scoreSum / $students, 1),
                    'fail_count' => $fails,
                    'students'   => $students,
                    'pass_rate'  => round(100 - $fails / $students * 100, 1),
                ];
            }
        }

        $schoolGpa = [];
        foreach (self::TERMS as $term) {
            $sum = $w = 0;
            foreach ($gpa[$term] as $key => $g) {
                $sum += $g * $stats[$key]['students'];
                $w += $stats[$key]['students'];
            }
            $schoolGpa[$term] = round($sum / $w, 2);
        }

        return ['gpa' => $gpa, 'school_gpa' => $schoolGpa, 'subjects' => $subjects, 'grade_subject' => $gradeSubject];
    }

    /** Teacher workload, records and the 9 evaluation dimensions (0–100). */
    private function teacherMetrics(array $teachers, array $academics, array $rooms): array
    {
        $highLoad = ['T012', 'T019', 'T033', 'T040'];
        $lowData = ['T027', 'T046'];

        foreach ($teachers as $id => &$t) {
            $hours = (int) round(self::clamp($this->gauss(18, 2.2), 13, 22));
            $duties = mt_rand(1, 3);
            if (in_array($id, $highLoad, true)) {
                $hours = mt_rand(24, 26);
                $duties = 4;
            }
            $loadIndex = round($hours + $duties * 1.5, 1);

            // Achievement: average current-term score of the subject in the grades this teacher teaches.
            $scores = $progress = [];
            foreach ($t['classes'] as $room) {
                $grade = $rooms[$room]['grade'];
                if ($t['subject'] !== null && isset($academics['grade_subject']['1/2569'][$grade])) {
                    $scores[] = $academics['grade_subject']['1/2569'][$grade][$t['subject']]['score'];
                    $progress[] = $academics['grade_subject']['1/2569'][$grade][$t['subject']]['score'] - $academics['grade_subject']['2/2568'][$grade][$t['subject']]['score'];
                }
            }
            $classScore = $scores !== [] ? self::mean($scores) : self::clamp($this->gauss(84, 3), 70, 95);
            $delta = $progress !== [] ? self::mean($progress) : $this->gauss(1.2, 1.2);

            $t['hours'] = $hours;
            $t['duties'] = $duties;
            $t['load_index'] = $loadIndex;
            $t['lesson_log'] = (int) round(self::clamp($this->gauss(91, 6), 62, 100));
            $t['training_hours'] = (int) round(self::clamp($this->gauss(28, 9), 6, 52));
            $t['works'] = mt_rand(0, 6);
            $t['followup'] = (int) round(self::clamp($this->gauss(86, 6), 64, 99));
            $t['student_survey'] = round(self::clamp($this->gauss(4.25, 0.25), 3.4, 4.9), 2);
            $t['exec_eval'] = round(self::clamp($this->gauss(4.2, 0.28), 3.3, 4.9), 2);
            $t['class_score'] = round($classScore, 1);
            $t['progress'] = round($delta, 1);
            $t['completeness'] = in_array($id, $lowData, true) ? mt_rand(62, 72) : mt_rand(86, 100);
            $t['homeroom_students'] = $t['homeroom'] !== null ? $rooms[$t['homeroom']]['students'] : 0;

            $t['dimensions'] = [
                'workload'     => (int) round(self::clamp(70 + ($hours - 14) * 2 + $duties * 3 + $this->gauss(0, 3), 60, 99)),
                'lesson_log'   => $t['lesson_log'],
                'achievement'  => (int) round(self::clamp(($classScore - 50) * 2.2, 40, 99)),
                'progress'     => (int) round(self::clamp(78 + $delta * 4 + $this->gauss(0, 2), 45, 99)),
                'followup'     => $t['followup'],
                'development'  => (int) round(self::clamp(45 + $t['training_hours'] * 1.4, 40, 99)),
                'works'        => (int) round(self::clamp(60 + $t['works'] * 6.5, 50, 99)),
                'student_eval' => (int) round($t['student_survey'] / 5 * 100),
                'exec_eval'    => (int) round($t['exec_eval'] / 5 * 100),
            ];

            $prevOverall = $this->gauss(0, 1);
            $t['history_drift'] = [round($this->rnd(-1.5, 4.5) + $prevOverall, 1), round($this->rnd(-1.0, 3.0), 1)];
        }
        unset($t);

        return $teachers;
    }

    private function kpis(array $months, array $attendance): array
    {
        $defs = [
            'academic'    => ['label' => 'Academic Performance', 'th' => 'ผลสัมฤทธิ์ทางการเรียน', 'icon' => 'book-open-check', 'target' => 80, 'start' => 73.5, 'end' => 77.8],
            'attendance'  => ['label' => 'Attendance',           'th' => 'การมาเรียน',           'icon' => 'calendar-check', 'target' => 96, 'start' => 0, 'end' => 0],
            'student_dev' => ['label' => 'Student Development',  'th' => 'พัฒนาการผู้เรียน',      'icon' => 'sprout',          'target' => 85, 'start' => 76.0, 'end' => 82.4],
            'teacher_dev' => ['label' => 'Teacher Development',  'th' => 'การพัฒนาครู',          'icon' => 'award',           'target' => 85, 'start' => 71.0, 'end' => 84.1],
            'operations'  => ['label' => 'Operational Efficiency', 'th' => 'ประสิทธิภาพการบริหาร', 'icon' => 'settings-2',      'target' => 85, 'start' => 77.5, 'end' => 85.6],
            'parent_sat'  => ['label' => 'Parent Satisfaction',  'th' => 'ความพึงพอใจผู้ปกครอง', 'icon' => 'heart',           'target' => 88, 'start' => 80.2, 'end' => 87.3],
        ];

        $last = count($months) - 1;
        $kpis = [];
        foreach ($defs as $key => $def) {
            $series = [];
            $carry = null;
            foreach ($months as $i => $month) {
                if ($key === 'attendance') {
                    $carry = $attendance[$month] ?? $carry;
                    $series[$month] = $carry;
                    continue;
                }
                $value = $def['start'] + ($def['end'] - $def['start']) * ($i / $last) + ($i === $last ? 0 : $this->gauss(0, 0.6));
                $series[$month] = round($value, 1);
            }
            unset($def['start'], $def['end']);
            $kpis[$key] = $def + ['series' => $series];
        }
        return $kpis;
    }

    private function imports(): array
    {
        return [
            ['id' => 'IMP-0112', 'file' => 'students_term1_2569.xlsx',   'source' => 'excel',  'dataset' => 'ข้อมูลนักเรียน',          'records' => 1200, 'status' => 'imported', 'date' => '2026-10-28 09:14', 'by' => 'งานทะเบียนและวัดผล', 'note' => 'ตรวจสอบครบ 1,200 รายการ'],
            ['id' => 'IMP-0111', 'file' => 'attendance_oct_2569.csv',     'source' => 'csv',    'dataset' => 'การเข้าเรียนรายวัน',      'records' => 11760, 'status' => 'imported', 'date' => '2026-10-27 16:40', 'by' => 'งานทะเบียนและวัดผล', 'note' => 'นำเข้าสำเร็จ'],
            ['id' => 'IMP-0110', 'file' => 'grades_term1_2569.xlsx',     'source' => 'excel',  'dataset' => 'ผลการเรียนรายวิชา',      'records' => 8640, 'status' => 'pending',  'date' => '2026-10-27 11:05', 'by' => 'งานทะเบียนและวัดผล', 'note' => 'รอครูประจำวิชายืนยัน 3 รายวิชา'],
            ['id' => 'IMP-0109', 'file' => 'lesson_logs_sep_2569.csv',   'source' => 'csv',    'dataset' => 'บันทึกการสอน',            'records' => 2184, 'status' => 'imported', 'date' => '2026-10-05 10:22', 'by' => 'งานวิชาการ',        'note' => 'นำเข้าสำเร็จ'],
            ['id' => 'IMP-0108', 'file' => 'training_records_2569.pdf',  'source' => 'pdf',    'dataset' => 'ชั่วโมงอบรมครู',          'records' => 52,   'status' => 'failed',   'date' => '2026-10-03 14:51', 'by' => 'งานบุคคล',          'note' => 'อ่านตารางในไฟล์ PDF ไม่ครบ กรุณาใช้เทมเพลต CSV'],
            ['id' => 'IMP-0107', 'file' => 'teacher_survey_term1.csv',   'source' => 'csv',    'dataset' => 'แบบประเมินจากนักเรียน',    'records' => 3910, 'status' => 'imported', 'date' => '2026-09-30 08:30', 'by' => 'งานบุคคล',          'note' => 'นำเข้าสำเร็จ'],
            ['id' => 'IMP-0106', 'file' => 'manual-entry',               'source' => 'manual', 'dataset' => 'บันทึกการดูแลช่วยเหลือ',  'records' => 46,   'status' => 'imported', 'date' => '2026-09-26 15:12', 'by' => 'ครูประจำชั้น',       'note' => 'บันทึกผ่านแบบฟอร์ม'],
            ['id' => 'IMP-0105', 'file' => 'parent_survey_2569.xlsx',    'source' => 'excel',  'dataset' => 'ความพึงพอใจผู้ปกครอง',    'records' => 812,  'status' => 'imported', 'date' => '2026-09-12 13:02', 'by' => 'งานประกันคุณภาพ',   'note' => 'นำเข้าสำเร็จ'],
            ['id' => 'IMP-0104', 'file' => 'transfer_records_aug.csv',   'source' => 'csv',    'dataset' => 'การย้ายเข้า-ย้ายออก',      'records' => 9,    'status' => 'failed',   'date' => '2026-09-02 09:47', 'by' => 'งานทะเบียนและวัดผล', 'note' => 'รูปแบบวันที่ไม่ตรงเทมเพลต 2 แถว'],
            ['id' => 'IMP-0103', 'file' => 'sis-api-sync',               'source' => 'api',    'dataset' => 'ระบบทะเบียนกลาง (SIS)',   'records' => 0,    'status' => 'pending',  'date' => '2026-08-30 00:00', 'by' => 'ระบบ',              'note' => 'รอเชื่อมต่อในเฟสถัดไป'],
        ];
    }
}
