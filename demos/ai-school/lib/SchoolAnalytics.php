<?php

declare(strict_types=1);

/**
 * Analytics over SchoolData: KPIs, chart specs, rule-based insights, teacher
 * evaluation support and the executive summary. Every number shown in the UI
 * comes from here, so all pages stay consistent.
 *
 * Note: the "AI" in this demo is a rule-based simulation over mock data.
 */
final class SchoolAnalytics
{
    public const ATTENDANCE_TARGET = 96.0;

    /** Teacher evaluation dimensions => [label, weight, source]. Achievement is only 12%. */
    public const DIMENSIONS = [
        'workload'     => ['ภาระงานและชั่วโมงสอน', 10, 'ตารางสอนและคำสั่งมอบหมายงาน'],
        'lesson_log'   => ['บันทึกการสอน', 12, 'ระบบบันทึกหลังการสอน'],
        'achievement'  => ['ผลสัมฤทธิ์ของนักเรียน', 12, 'ผลการเรียนรายวิชา ภาคเรียน 1/2569'],
        'progress'     => ['พัฒนาการของนักเรียน', 12, 'เปรียบเทียบคะแนนกับภาคเรียนก่อน'],
        'followup'     => ['การติดตามงานและนักเรียน', 12, 'ระบบส่งงานและบันทึกการติดตาม'],
        'development'  => ['การพัฒนาตนเองและการอบรม', 10, 'ทะเบียนการอบรม (ชั่วโมง)'],
        'works'        => ['ผลงานและกิจกรรม', 8, 'ทะเบียนผลงานและกิจกรรม'],
        'student_eval' => ['แบบประเมินจากนักเรียน', 12, 'แบบสอบถามนักเรียน (ไม่ระบุตัวตน)'],
        'exec_eval'    => ['แบบประเมินจากผู้บริหาร', 12, 'การนิเทศและประเมินโดยผู้บริหาร'],
    ];

    private array $d;
    private string $periodKey;
    private array $months;
    private array $prevMonths;
    private ?array $evaluations = null;
    private ?array $insightCache = null;

    public function __construct(array $data, string $periodKey)
    {
        $this->d = $data;
        $periods = ais_config('periods', []);
        $this->periodKey = isset($periods[$periodKey]) ? $periodKey : '6m';
        $n = (int) $periods[$this->periodKey]['months'];
        $this->months = array_slice($data['months'], -$n);
        $this->prevMonths = array_slice($data['months'], -2 * $n, $n);
    }

    public static function instance(): self
    {
        static $instance = null;
        return $instance ??= new self(SchoolData::get(), ais_period());
    }

    public function data(): array
    {
        return $this->d;
    }

    public function months(): array
    {
        return $this->months;
    }

    public function periodLabel(): string
    {
        return ais_config('periods.' . $this->periodKey . '.label', '');
    }

    public function periodRange(): string
    {
        return ais_month($this->months[0]) . ' – ' . ais_month(end($this->months));
    }

    public function latestMonth(): string
    {
        return end($this->d['months']);
    }

    private static function avg(array $values, int $decimals = 1): ?float
    {
        $values = array_values(array_filter($values, static fn ($v) => $v !== null));
        return $values === [] ? null : round(array_sum($values) / count($values), $decimals);
    }

    private function pick(array $series, array $months): array
    {
        return array_map(static fn ($m) => $series[$m] ?? null, $months);
    }

    private function labels(array $months): array
    {
        return array_map(static fn ($m) => ais_month($m), $months);
    }

    /* ----------------------------------------------------------------- KPI */

    public function attendanceAvg(?array $months = null): ?float
    {
        return self::avg($this->pick($this->d['attendance']['school'], $months ?? $this->months));
    }

    public function schoolScore(string $month): float
    {
        return round(array_sum(array_map(static fn ($k) => $k['series'][$month], $this->d['kpis'])) / count($this->d['kpis']), 1);
    }

    public function riskCounts(bool $previous = false): array
    {
        $field = $previous ? 'risk_prev' : 'risk';
        $counts = ['high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($this->d['students'] as $s) {
            $counts[$s[$field]]++;
        }
        return $counts;
    }

    /**
     * The six executive KPI cards.
     */
    public function kpiCards(): array
    {
        tb_log('SchoolAnalytics', 'kpiCards', 'START', ['period' => $this->periodKey]);

        $last = end($this->months);
        $prevLast = end($this->prevMonths);
        $enroll = $this->d['enrollment']['count'];
        $people = $this->d['personnel'];
        $att = $this->attendanceAvg();
        $attPrev = $this->attendanceAvg($this->prevMonths);
        $gpa = $this->d['academics']['school_gpa'];
        $risk = $this->riskCounts();
        $riskPrev = $this->riskCounts(true);
        $follow = $risk['high'] + $risk['medium'];
        $followPrev = $riskPrev['high'] + $riskPrev['medium'];
        $score = $this->schoolScore($last);
        $scorePrev = $this->schoolScore($prevLast);
        $vs = 'เทียบ ' . ais_month($prevLast);

        $cards = [
            [
                'key' => 'students', 'label' => 'นักเรียนทั้งหมด', 'icon' => 'graduation-cap', 'tone' => 'blue',
                'value' => ais_num($enroll[$last]), 'unit' => 'คน',
                'delta' => ais_delta((float) $enroll[$last], (float) $enroll[$prevLast], 0, ' คน'), 'compare' => $vs,
                'spark' => $this->pick($enroll, $this->months), 'link' => ais_url('students'),
            ],
            [
                'key' => 'personnel', 'label' => 'ครูและบุคลากร', 'icon' => 'users', 'tone' => 'deep',
                'value' => ais_num($people[$last]['total']), 'unit' => 'คน',
                'delta' => ais_delta((float) $people[$last]['total'], (float) $people[$prevLast]['total'], 0, ' คน'), 'compare' => $vs,
                'meta' => 'ครู ' . $people[$last]['teachers'] . ' · บุคลากร ' . $people[$last]['staff'],
                'link' => ais_url('teachers'),
            ],
            [
                'key' => 'attendance', 'label' => 'อัตราการเข้าเรียนเฉลี่ย', 'icon' => 'calendar-check', 'tone' => 'green',
                'value' => ais_num($att, 1), 'unit' => '%',
                'delta' => ais_delta($att, $attPrev, 1, '%'), 'compare' => 'เทียบช่วงก่อนหน้า',
                'spark' => array_values(array_filter($this->pick($this->d['attendance']['school'], $this->months), static fn ($v) => $v !== null)),
                'link' => ais_url('students'),
            ],
            [
                'key' => 'gpa', 'label' => 'GPA เฉลี่ย', 'icon' => 'book-open-check', 'tone' => 'yellow',
                'value' => ais_num($gpa[SchoolData::CURRENT_TERM], 2), 'unit' => '',
                'delta' => ais_delta($gpa[SchoolData::CURRENT_TERM], $gpa[SchoolData::PREV_TERM], 2), 'compare' => 'เทียบภาคเรียน ' . SchoolData::PREV_TERM,
                'spark' => array_values($gpa), 'meta' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM . ' (ป.1–ม.6)',
                'link' => ais_url('academics'),
            ],
            [
                'key' => 'follow', 'label' => 'นักเรียนที่ต้องติดตาม', 'icon' => 'heart-handshake', 'tone' => 'orange',
                'value' => ais_num($follow), 'unit' => 'คน',
                'delta' => ais_delta((float) $follow, (float) $followPrev, 0, ' คน', false), 'compare' => 'เทียบภาคเรียนก่อน',
                'meta' => 'ดูแลใกล้ชิด ' . $risk['high'] . ' · ควรติดตาม ' . $risk['medium'],
                'link' => ais_url('student-risk'),
            ],
            [
                'key' => 'score', 'label' => 'คะแนนภาพรวมโรงเรียน', 'icon' => 'gauge', 'tone' => 'sky',
                'value' => ais_num($score, 1), 'unit' => '/100',
                'delta' => ais_delta($score, $scorePrev, 1), 'compare' => $vs,
                'spark' => array_map(fn ($m) => $this->schoolScore($m), $this->months),
                'link' => ais_url('performance'),
            ],
        ];

        tb_log('SchoolAnalytics', 'kpiCards', 'END', ['count' => count($cards)]);
        return $cards;
    }

    /* -------------------------------------------------------------- charts */

    public function enrollmentChart(): array
    {
        $e = $this->d['enrollment'];
        return [
            'type' => 'bar',
            'labels' => $this->labels($this->months),
            'datasets' => [
                ['label' => 'จำนวนนักเรียน', 'type' => 'line', 'data' => $this->pick($e['count'], $this->months), 'color' => 'primary', 'fill' => true, 'axis' => 'y1'],
                ['label' => 'ย้ายเข้า', 'data' => $this->pick($e['in'], $this->months), 'color' => 'green'],
                ['label' => 'ย้ายออก', 'data' => $this->pick($e['out'], $this->months), 'color' => 'yellow'],
            ],
            'options' => ['y1Suffix' => ' คน', 'suffix' => ' คน', 'legend' => true, 'y1Min' => (int) (floor(min($this->pick($e['count'], $this->months)) / 50) * 50 - 50)],
        ];
    }

    public function attendanceChart(): array
    {
        $att = $this->d['attendance'];
        $labels = $this->labels($this->months);
        $target = array_fill(0, count($this->months), self::ATTENDANCE_TARGET);
        $levelColors = ['kindergarten' => 'yellow', 'primary' => 'primary', 'secondary' => 'deep'];

        $levels = [];
        foreach (SchoolData::LEVELS as $key => $label) {
            $levels[] = ['label' => $label, 'data' => $this->pick($att['levels'][$key], $this->months), 'color' => $levelColors[$key]];
        }

        return [
            'type' => 'line',
            'labels' => $labels,
            'datasets' => [
                ['label' => 'ทั้งโรงเรียน', 'data' => $this->pick($att['school'], $this->months), 'color' => 'green', 'fill' => true],
                ['label' => 'เป้าหมาย 96%', 'data' => $target, 'color' => 'gray', 'dashed' => true],
            ],
            'views' => [
                'school' => ['label' => 'ทั้งโรงเรียน'],
                'levels' => ['label' => 'แยกช่วงชั้น', 'datasets' => $levels],
            ],
            'options' => ['suffix' => '%', 'yMin' => 88, 'yMax' => 100, 'decimals' => 1, 'legend' => true, 'note' => 'เดือนเมษายนเป็นช่วงปิดภาคเรียน'],
        ];
    }

    public function academicChart(): array
    {
        $a = $this->d['academics'];
        $grades = array_keys($a['gpa'][SchoolData::CURRENT_TERM]);
        $gradeLabels = array_map(fn ($k) => $this->d['grade_stats'][$k]['label'], $grades);
        $subjects = array_keys(SchoolData::SUBJECTS);

        return [
            'type' => 'bar',
            'labels' => $gradeLabels,
            'datasets' => [
                ['label' => 'ภาคเรียน ' . SchoolData::PREV_TERM, 'data' => array_values($a['gpa'][SchoolData::PREV_TERM]), 'color' => 'sky'],
                ['label' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM, 'data' => array_values($a['gpa'][SchoolData::CURRENT_TERM]), 'color' => 'primary'],
            ],
            'options' => ['yMin' => 2, 'yMax' => 3.6, 'decimals' => 2, 'legend' => true],
            'views' => [
                'grade' => ['label' => 'GPA รายระดับชั้น'],
                'subject' => [
                    'label' => 'คะแนนรายวิชา',
                    'labels' => array_map(static fn ($k) => SchoolData::SUBJECTS[$k]['short'], $subjects),
                    'datasets' => [
                        ['label' => 'ภาคเรียน ' . SchoolData::PREV_TERM, 'data' => array_map(static fn ($k) => $a['subjects'][SchoolData::PREV_TERM][$k]['score'], $subjects), 'color' => 'sky'],
                        ['label' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM, 'data' => array_map(static fn ($k) => $a['subjects'][SchoolData::CURRENT_TERM][$k]['score'], $subjects), 'color' => 'primary'],
                    ],
                    'options' => ['yMin' => 55, 'yMax' => 90, 'decimals' => 1, 'legend' => true],
                ],
            ],
        ];
    }

    public function teacherChart(): array
    {
        $byDept = [];
        foreach ($this->teacherEvaluations() as $t) {
            $byDept[$t['dept_label']][] = $t['overall'];
        }
        $bands = ['โดดเด่น' => 0, 'ดีมาก' => 0, 'ดี' => 0, 'ควรได้รับการสนับสนุน' => 0];
        foreach ($this->teacherEvaluations() as $t) {
            $bands[$t['band']['label']]++;
        }

        return [
            'type' => 'bar',
            'labels' => array_keys($byDept),
            'datasets' => [
                ['label' => 'คะแนนเฉลี่ยหลายมิติ', 'data' => array_map(static fn ($v) => self::avg($v), array_values($byDept)), 'color' => 'primary'],
            ],
            'options' => ['horizontal' => true, 'yMin' => 60, 'yMax' => 100, 'decimals' => 1],
            'views' => [
                'department' => ['label' => 'รายกลุ่มสาระ'],
                'band' => [
                    'label' => 'การกระจายระดับ',
                    'type' => 'doughnut',
                    'labels' => array_keys($bands),
                    'datasets' => [['label' => 'จำนวนครู', 'data' => array_values($bands), 'colors' => ['green', 'primary', 'sky', 'yellow']]],
                    'options' => ['suffix' => ' คน', 'legend' => true, 'horizontal' => false],
                ],
            ],
        ];
    }

    public function riskChart(): array
    {
        $c = $this->riskCounts();
        return [
            'type' => 'doughnut',
            'labels' => ['ต้องดูแลใกล้ชิด (High)', 'ควรติดตาม (Medium)', 'ปกติ (Low)'],
            'datasets' => [['label' => 'นักเรียน', 'data' => [$c['high'], $c['medium'], $c['low']], 'colors' => ['red', 'orange', 'green']]],
            'options' => [
                'suffix' => ' คน', 'legend' => true, 'cutout' => '68%',
                'center' => ['value' => ais_num($c['high'] + $c['medium']), 'label' => 'ต้องติดตาม'],
                'links' => [ais_url('student-risk', ['level' => 'high']), ais_url('student-risk', ['level' => 'medium']), ais_url('student-risk', ['level' => 'low'])],
            ],
        ];
    }

    public function kpiRadarChart(): array
    {
        $last = $this->latestMonth();
        return [
            'type' => 'radar',
            'labels' => array_values(array_map(static fn ($k) => $k['th'], $this->d['kpis'])),
            'datasets' => [
                ['label' => 'ผลปัจจุบัน', 'data' => array_values(array_map(static fn ($k) => $k['series'][$last], $this->d['kpis'])), 'color' => 'primary', 'fill' => true],
                ['label' => 'เป้าหมาย', 'data' => array_values(array_map(static fn ($k) => $k['target'], $this->d['kpis'])), 'color' => 'yellow', 'dashed' => true],
            ],
            'options' => ['yMin' => 60, 'yMax' => 100, 'legend' => true, 'decimals' => 1],
        ];
    }

    /* ------------------------------------------------------ student views */

    public function gradeStats(?string $level = null): array
    {
        return array_filter($this->d['grade_stats'], static fn ($g) => $level === null || $g['level'] === $level);
    }

    public function rooms(?string $level = null, ?string $grade = null): array
    {
        return array_filter($this->d['rooms'], static fn ($r) => ($level === null || $r['level'] === $level) && ($grade === null || $r['grade'] === $grade));
    }

    public function transfers(?array $months = null): array
    {
        $months ??= $this->months;
        return [
            'in'  => array_sum($this->pick($this->d['enrollment']['in'], $months)),
            'out' => array_sum($this->pick($this->d['enrollment']['out'], $months)),
        ];
    }

    /** Students that need follow-up (high + medium), most urgent first. */
    public function followUpStudents(?string $level = null, ?string $risk = null): array
    {
        $list = array_filter($this->d['students'], static fn ($s) => $s['risk'] !== 'low'
            && ($level === null || $s['level'] === $level)
            && ($risk === null || $s['risk'] === $risk));
        usort($list, static fn ($a, $b) => [$b['risk_score'], $a['attendance']] <=> [$a['risk_score'], $b['attendance']]);
        return $list;
    }

    public function riskFactorCounts(): array
    {
        $labels = ['attendance' => 'การขาดเรียน', 'academic' => 'ผลการเรียน', 'progress' => 'พัฒนาการลดลง', 'submission' => 'การส่งงาน', 'behavior' => 'บันทึกการดูแลช่วยเหลือ'];
        $counts = array_fill_keys(array_keys($labels), 0);
        foreach ($this->d['students'] as $s) {
            if ($s['risk'] === 'low') {
                continue;
            }
            foreach ($s['factors'] as $f) {
                $counts[$f['key']]++;
            }
        }
        arsort($counts);
        $result = [];
        foreach ($counts as $k => $v) {
            $result[] = ['key' => $k, 'label' => $labels[$k], 'count' => $v];
        }
        return $result;
    }

    /** Support plan derived from the factors found (support-oriented wording). */
    public static function supportPlan(array $student): array
    {
        $plan = [];
        foreach ($student['factors'] as $f) {
            $plan[$f['key']] = match ($f['key']) {
                'attendance' => 'ครูประจำชั้นโทรพูดคุยกับผู้ปกครองเพื่อเข้าใจสาเหตุการขาดเรียน และวางแผนการมาเรียนร่วมกัน',
                'academic'   => 'จัดสอนเสริมกลุ่มเล็กในรายวิชาที่คะแนนต่ำ พร้อมจับคู่เพื่อนช่วยเพื่อน',
                'progress'   => 'พูดคุยรายบุคคลเพื่อค้นหาปัจจัยที่ทำให้ผลการเรียนลดลง และติดตามทุก 2 สัปดาห์',
                'submission' => 'ใช้ระบบแจ้งเตือนงานค้างถึงนักเรียนและผู้ปกครอง และแบ่งงานเป็นขั้นตอนย่อย',
                'behavior'   => 'ส่งต่อครูแนะแนวเพื่อพูดคุยให้คำปรึกษาอย่างเป็นความลับ',
                default      => 'ติดตามอย่างใกล้ชิดร่วมกับครูประจำชั้น',
            };
        }
        if ($student['risk'] === 'high') {
            $plan['meeting'] = 'จัดประชุมทีมดูแลช่วยเหลือ (ครูประจำชั้น ครูแนะแนว ผู้ปกครอง) ภายใน 2 สัปดาห์';
        }
        return array_values($plan);
    }

    /* -------------------------------------------------- teacher evaluation */

    /**
     * Evaluation support data for every teacher (overall score, dimensions,
     * strengths, development points, suggestions, sources and limitations).
     */
    public function teacherEvaluations(): array
    {
        if ($this->evaluations !== null) {
            return $this->evaluations;
        }
        tb_log('SchoolAnalytics', 'teacherEvaluations', 'START');

        $dimAvg = [];
        foreach (array_keys(self::DIMENSIONS) as $dim) {
            $dimAvg[$dim] = self::avg(array_map(static fn ($t) => $t['dimensions'][$dim], $this->d['teachers']));
        }
        $avgLoad = self::avg(array_column($this->d['teachers'], 'load_index'));

        $result = [];
        foreach ($this->d['teachers'] as $id => $t) {
            $overall = 0.0;
            $dims = [];
            foreach (self::DIMENSIONS as $dim => [$label, $weight, $source]) {
                $overall += $t['dimensions'][$dim] * $weight / 100;
                $dims[] = ['key' => $dim, 'label' => $label, 'score' => $t['dimensions'][$dim], 'avg' => $dimAvg[$dim], 'weight' => $weight];
            }
            $overall = round($overall, 1);

            $sorted = $dims;
            usort($sorted, static fn ($a, $b) => ($b['score'] - $b['avg']) <=> ($a['score'] - $a['avg']));
            $strengths = array_slice($sorted, 0, 3);
            $develop = array_values(array_filter(array_reverse($sorted), static fn ($d) => $d['score'] < $d['avg'] || $d['score'] < 80));
            $develop = array_slice($develop !== [] ? $develop : array_reverse($sorted), 0, 2);

            $suggestions = [];
            foreach ($develop as $dim) {
                $suggestions[] = self::suggestion($dim['key'], $t);
            }
            if ($t['load_index'] > $avgLoad * 1.2) {
                $suggestions[] = sprintf('ภาระงานรวมสูงกว่าค่าเฉลี่ยโรงเรียน %d%% ควรพิจารณาปรับการมอบหมายงานพิเศษ ก่อนสรุปผลมิติอื่น', (int) round(($t['load_index'] / $avgLoad - 1) * 100));
            }
            $suggestions[] = 'นำผลวิเคราะห์นี้ไปใช้ประกอบการพูดคุยนิเทศแบบกัลยาณมิตร และให้ครูได้ชี้แจงบริบทเพิ่มเติม';

            $limits = ['ผลวิเคราะห์ใช้ข้อมูลภาคเรียน ' . SchoolData::CURRENT_TERM . ' เท่านั้น ยังไม่สะท้อนบริบทเฉพาะของแต่ละห้องเรียน'];
            if ($t['completeness'] < 80) {
                $limits[] = sprintf('ข้อมูลครบถ้วนเพียง %d%% (ขาดบันทึกการสอนบางสัปดาห์และหลักฐานการอบรม) ควรเก็บข้อมูลเพิ่มก่อนใช้ประกอบการตัดสินใจ', $t['completeness']);
            }
            if ($t['subject'] === null) {
                $limits[] = 'ระดับปฐมวัยไม่มีผลการเรียนเป็นเกรด ใช้คะแนนพัฒนาการตามวัยแทน';
            }
            if ($t['years'] < 3) {
                $limits[] = 'เป็นครูใหม่ (ประสบการณ์น้อยกว่า 3 ปี) ควรพิจารณาร่วมกับแผนพี่เลี้ยง';
            }

            $sources = [];
            foreach (self::DIMENSIONS as $dim => [$label, $weight, $source]) {
                $complete = $t['completeness'] < 80 && in_array($dim, ['lesson_log', 'development'], true) ? max(40, $t['completeness'] - 15) : min(100, $t['completeness'] + 3);
                $sources[] = ['label' => $source, 'dimension' => $label, 'complete' => $complete];
            }

            $result[$id] = [
                'id'          => $id,
                'name'        => $t['name'],
                'short'       => $t['short'],
                'gender'      => $t['gender'],
                'department'  => $t['department'],
                'dept_label'  => $t['dept_label'],
                'position'    => $t['position'],
                'years'       => $t['years'],
                'classes'     => $t['classes'],
                'homeroom'    => $t['homeroom'],
                'hours'       => $t['hours'],
                'duties'      => $t['duties'],
                'load_index'  => $t['load_index'],
                'load_vs_avg' => round(($t['load_index'] / $avgLoad - 1) * 100),
                'lesson_log'  => $t['lesson_log'],
                'training'    => $t['training_hours'],
                'works'       => $t['works'],
                'followup'    => $t['followup'],
                'survey'      => $t['student_survey'],
                'exec_eval'   => $t['exec_eval'],
                'class_score' => $t['class_score'],
                'progress'    => $t['progress'],
                'completeness' => $t['completeness'],
                'overall'     => $overall,
                'band'        => ais_band($overall),
                'dimensions'  => $dims,
                'strengths'   => array_map(static fn ($d) => $d['label'] . ' (' . $d['score'] . ')', $strengths),
                'develop'     => array_map(static fn ($d) => $d['label'] . ' (' . $d['score'] . ')', $develop),
                'suggestions' => $suggestions,
                'limitations' => $limits,
                'sources'     => $sources,
                'history'     => [
                    '1/2568' => round($overall - $t['history_drift'][0] - $t['history_drift'][1], 1),
                    '2/2568' => round($overall - $t['history_drift'][1], 1),
                    '1/2569' => $overall,
                ],
            ];
        }

        $this->evaluations = $result;
        tb_log('SchoolAnalytics', 'teacherEvaluations', 'END', ['count' => count($result)]);
        return $result;
    }

    private static function suggestion(string $dim, array $t): string
    {
        return match ($dim) {
            'workload'     => 'ทบทวนการกระจายชั่วโมงสอนและงานพิเศษให้สมดุลกับเพื่อนครูในกลุ่มสาระ',
            'lesson_log'   => sprintf('บันทึกการสอนครบ %d%% แนะนำใช้แบบฟอร์มบันทึกสั้นบนมือถือหลังคาบเรียน เพื่อลดภาระการบันทึกย้อนหลัง', $t['lesson_log']),
            'achievement'  => 'วิเคราะห์ข้อสอบรายตัวชี้วัดร่วมกับกลุ่มสาระ เพื่อหาเนื้อหาที่นักเรียนส่วนใหญ่ยังไม่เข้าใจ',
            'progress'     => 'ใช้การประเมินระหว่างเรียน (Formative) ทุก 2–3 สัปดาห์ เพื่อติดตามพัฒนาการรายบุคคล',
            'followup'     => sprintf('อัตราติดตามงาน %d%% แนะนำเปิดใช้การแจ้งเตือนงานค้างอัตโนมัติถึงนักเรียนและผู้ปกครอง', $t['followup']),
            'development'  => sprintf('ชั่วโมงอบรม %d ชม. ต่ำกว่าเป้าหมาย 40 ชม./ปี เสนอหลักสูตรอบรมออนไลน์ที่ตรงกับกลุ่มสาระ', $t['training_hours']),
            'works'        => 'สนับสนุนให้ต่อยอดกิจกรรมในห้องเรียนเป็นผลงานหรือนวัตกรรม เพื่อแลกเปลี่ยนใน PLC',
            'student_eval' => 'เปิดช่องทางรับฟังเสียงนักเรียนกลางภาคเรียน และนำข้อเสนอแนะมาปรับวิธีการสอน',
            'exec_eval'    => 'นัดหมายการนิเทศแบบเพื่อนช่วยเพื่อน (Coaching & Mentoring) อย่างน้อย 1 ครั้งต่อเดือน',
            default        => 'ติดตามข้อมูลเพิ่มเติมก่อนสรุปผล',
        };
    }

    public function teacherSummary(): array
    {
        $t = $this->d['teachers'];
        return [
            'teachers'     => count($t),
            'staff'        => count($this->d['staff']),
            'avg_hours'    => self::avg(array_column($t, 'hours')),
            'avg_load'     => self::avg(array_column($t, 'load_index')),
            'lesson_log'   => self::avg(array_column($t, 'lesson_log')),
            'training'     => self::avg(array_column($t, 'training_hours')),
            'works'        => array_sum(array_column($t, 'works')),
            'high_load'    => count(array_filter($t, fn ($x) => $x['load_index'] > self::avg(array_column($t, 'load_index')) * 1.2)),
            'overall'      => self::avg(array_column($this->teacherEvaluations(), 'overall')),
        ];
    }

    /* ------------------------------------------------------------ insights */

    /**
     * Rule-based insights. Each one carries its evidence, possible causes and
     * recommendations, all computed from the demo data.
     */
    public function insights(): array
    {
        if ($this->insightCache !== null) {
            return $this->insightCache;
        }
        tb_log('SchoolAnalytics', 'insights', 'START');

        $d = $this->d;
        $insights = [];
        $currentMonths = array_values(array_filter($d['months'], static fn ($m) => SchoolData::termOf($m) === SchoolData::CURRENT_TERM));

        // 1) Grade with the largest attendance decline.
        $grades = $d['grade_stats'];
        uasort($grades, static fn ($a, $b) => ($b['att_prev'] - $b['attendance']) <=> ($a['att_prev'] - $a['attendance']));
        $g = reset($grades);
        $rooms = $this->rooms(null, $g['key']);
        usort($rooms, static fn ($a, $b) => $a['attendance'] <=> $b['attendance']);
        $series = $this->pick($d['attendance']['grades'][$g['key']], $currentMonths);
        $insights[] = [
            'id' => 'att-decline', 'domain' => 'attendance', 'priority' => 'high', 'icon' => 'calendar-x',
            'title' => sprintf('อัตราการเข้าเรียนชั้น %s ลดลงต่อเนื่อง', $g['label']),
            'metric' => sprintf('%.1f%%', $g['attendance']), 'metric_label' => sprintf('ลดลง %.1f จุดจากภาคเรียนก่อน', $g['att_prev'] - $g['attendance']),
            'summary' => sprintf('ชั้น %s มีอัตราเข้าเรียนเฉลี่ย %.1f%% ต่ำกว่าค่าเฉลี่ยโรงเรียน (%.1f%%) และลดลงติดต่อกัน 3 เดือนล่าสุด โดยห้อง %s ต่ำที่สุด', $g['label'], $g['attendance'], $this->attendanceAvg($currentMonths), $rooms[0]['room']),
            'evidence' => [
                sprintf('ภาคเรียน %s: %.1f%% → ภาคเรียน %s: %.1f%%', SchoolData::PREV_TERM, $g['att_prev'], SchoolData::CURRENT_TERM, $g['attendance']),
                sprintf('ห้อง %s อัตราเข้าเรียน %.1f%% (ขาดเรียนรวม %s วัน-คน)', $rooms[0]['room'], $rooms[0]['attendance'], ais_num($rooms[0]['absent_days'])),
                sprintf('นักเรียนชั้น %s ที่ต้องติดตาม %d คน จาก %d คน', $g['label'], $g['high'] + $g['medium'], $g['students']),
            ],
            'causes' => ['ช่วงฤดูฝนมีนักเรียนลาป่วยเพิ่มขึ้น', 'นักเรียนบางกลุ่มเดินทางไกลและมาสายจนถูกบันทึกขาด', 'อาจมีปัจจัยด้านแรงจูงใจในช่วงรอยต่อของระดับชั้น'],
            'actions' => [sprintf('ครูประจำชั้น %s ติดต่อผู้ปกครองนักเรียนที่ขาดเรียนเกิน 8 วัน', $g['label']), 'ตั้งเป้าหมายการมาเรียนรายห้องและติดตามทุกสัปดาห์', 'ประสานงานแนะแนวเพื่อพูดคุยกับนักเรียนที่ขาดเรียนต่อเนื่อง'],
            'link' => ais_url('students', ['grade' => $g['key']]),
            'chart' => ['type' => 'line', 'labels' => $this->labels($currentMonths), 'datasets' => [
                ['label' => $g['label'], 'data' => $series, 'color' => 'red', 'fill' => true],
                ['label' => 'ทั้งโรงเรียน', 'data' => $this->pick($d['attendance']['school'], $currentMonths), 'color' => 'primary'],
            ], 'options' => ['suffix' => '%', 'decimals' => 1, 'legend' => true]],
        ];

        // 2) Room with the largest GPA decline.
        $rooms = array_filter($d['rooms'], static fn ($r) => $r['gpa'] !== null);
        uasort($rooms, static fn ($a, $b) => ($b['gpa_prev'] - $b['gpa']) <=> ($a['gpa_prev'] - $a['gpa']));
        $r = reset($rooms);
        $second = array_values($rooms)[1];
        $roomStudents = array_filter($d['students'], static fn ($s) => $s['room'] === $r['room']);
        $dropped = count(array_filter($roomStudents, static fn ($s) => $s['gpa_prev'] - $s['gpa'] >= 0.3));
        $insights[] = [
            'id' => 'room-gpa', 'domain' => 'academic', 'priority' => 'high', 'icon' => 'trending-down',
            'title' => sprintf('ผลสัมฤทธิ์ห้อง %s ลดลงมากที่สุด', $r['room']),
            'metric' => ais_num($r['gpa'], 2), 'metric_label' => sprintf('GPA ลดลง %.2f จาก %.2f', $r['gpa_prev'] - $r['gpa'], $r['gpa_prev']),
            'summary' => sprintf('ห้อง %s มี GPA เฉลี่ยลดลงจาก %.2f เป็น %.2f โดยมีนักเรียน %d คน (%d%%) ที่ GPA ลดลงตั้งแต่ 0.30 ขึ้นไป รองลงมาคือห้อง %s', $r['room'], $r['gpa_prev'], $r['gpa'], $dropped, (int) round($dropped / max(1, $r['students']) * 100), $second['room']),
            'evidence' => [
                sprintf('GPA ห้อง %s: %.2f → %.2f', $r['room'], $r['gpa_prev'], $r['gpa']),
                sprintf('ค่าเฉลี่ยชั้น %s: %.2f', $r['grade_label'], $d['grade_stats'][$r['grade']]['gpa']),
                sprintf('ห้อง %s: %.2f → %.2f', $second['room'], $second['gpa_prev'], $second['gpa']),
            ],
            'causes' => ['มีการเปลี่ยนครูผู้สอนบางรายวิชาระหว่างภาคเรียน', 'เนื้อหาคณิตศาสตร์ระดับนี้ยากขึ้นและต่อเนื่องจากพื้นฐานเดิม', 'อัตราการส่งงานในห้องลดลง'],
            'actions' => ['จัด PLC ครูผู้สอนห้องนี้เพื่อวิเคราะห์ผลการเรียนรายตัวชี้วัด', 'จัดสอนเสริมหลังเลิกเรียนสำหรับนักเรียนที่ GPA ลดลง', 'ติดตามผลอีกครั้งหลังสอบกลางภาคเรียนถัดไป'],
            'link' => ais_url('academics'),
            'chart' => ['type' => 'bar', 'labels' => array_map(static fn ($x) => $x['room'], array_slice(array_values($rooms), 0, 6)), 'datasets' => [
                ['label' => 'ภาคเรียน ' . SchoolData::PREV_TERM, 'data' => array_map(static fn ($x) => $x['gpa_prev'], array_slice(array_values($rooms), 0, 6)), 'color' => 'sky'],
                ['label' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM, 'data' => array_map(static fn ($x) => $x['gpa'], array_slice(array_values($rooms), 0, 6)), 'color' => 'primary'],
            ], 'options' => ['decimals' => 2, 'yMin' => 2, 'legend' => true]],
        ];

        // 3) Teachers with workload above average.
        $evals = $this->teacherEvaluations();
        $high = array_filter($evals, static fn ($t) => $t['load_vs_avg'] >= 20);
        usort($high, static fn ($a, $b) => $b['load_index'] <=> $a['load_index']);
        $summary = $this->teacherSummary();
        $insights[] = [
            'id' => 'teacher-load', 'domain' => 'workload', 'priority' => 'medium', 'icon' => 'briefcase',
            'title' => sprintf('ครู %d คนมีภาระงานสูงกว่าค่าเฉลี่ยมากกว่า 20%%', count($high)),
            'metric' => count($high) . ' คน', 'metric_label' => sprintf('ชั่วโมงสอนเฉลี่ยโรงเรียน %.1f ชม./สัปดาห์', $summary['avg_hours']),
            'summary' => sprintf('พบครู %d คนที่มีชั่วโมงสอนและงานพิเศษรวมสูงกว่าค่าเฉลี่ยตั้งแต่ 20%% ขึ้นไป สูงสุดคือ %s (%d ชม./สัปดาห์ + งานพิเศษ %d งาน)', count($high), $high[0]['name'], $high[0]['hours'], $high[0]['duties']),
            'evidence' => array_map(static fn ($t) => sprintf('%s (%s) สอน %d ชม./สัปดาห์ งานพิเศษ %d งาน — สูงกว่าค่าเฉลี่ย %d%%', $t['name'], $t['dept_label'], $t['hours'], $t['duties'], $t['load_vs_avg']), array_slice($high, 0, 4)),
            'causes' => ['จำนวนครูบางกลุ่มสาระไม่สมดุลกับจำนวนห้องเรียน', 'งานพิเศษ (โครงการ/กิจกรรม) กระจุกตัวที่ครูกลุ่มเดิม'],
            'actions' => ['ทบทวนการมอบหมายงานพิเศษในภาคเรียน 2/2569', 'พิจารณาจ้างครูอัตราจ้างหรือครูพี่เลี้ยงในกลุ่มสาระที่ขาดแคลน', 'ติดตามความเหนื่อยล้าผ่านแบบสอบถามความเป็นอยู่ของครู'],
            'link' => ais_url('teachers'),
            'chart' => ['type' => 'bar', 'labels' => array_map(static fn ($t) => $t['short'], array_slice($high, 0, 6)), 'datasets' => [
                ['label' => 'ดัชนีภาระงาน', 'data' => array_map(static fn ($t) => $t['load_index'], array_slice($high, 0, 6)), 'color' => 'orange'],
                ['label' => 'ค่าเฉลี่ยโรงเรียน', 'type' => 'line', 'data' => array_fill(0, min(6, count($high)), $summary['avg_load']), 'color' => 'gray', 'dashed' => true],
            ], 'options' => ['decimals' => 1, 'legend' => true]],
        ];

        // 4) Subject with the most students below the passing threshold.
        $subjects = $d['academics']['subjects'][SchoolData::CURRENT_TERM];
        uasort($subjects, static fn ($a, $b) => $b['fail_count'] <=> $a['fail_count']);
        $sk = array_key_first($subjects);
        $s = $subjects[$sk];
        $prev = $d['academics']['subjects'][SchoolData::PREV_TERM][$sk];
        $gs = $d['academics']['grade_subject'];
        $worstGrade = null;
        $worstDrop = 0.0;
        foreach ($gs[SchoolData::CURRENT_TERM] as $gk => $row) {
            $drop = $gs[SchoolData::PREV_TERM][$gk][$sk]['score'] - $row[$sk]['score'];
            if ($drop > $worstDrop) {
                $worstDrop = $drop;
                $worstGrade = $gk;
            }
        }
        $wg = $gs[SchoolData::CURRENT_TERM][$worstGrade][$sk];
        $insights[] = [
            'id' => 'subject-fail', 'domain' => 'academic', 'priority' => 'high', 'icon' => 'calculator',
            'title' => sprintf('วิชา%s มีนักเรียนไม่ผ่านเกณฑ์มากที่สุด', SchoolData::SUBJECTS[$sk]['name']),
            'metric' => ais_num($s['fail_count']) . ' คน', 'metric_label' => sprintf('อัตราผ่าน %.1f%%', $s['pass_rate']),
            'summary' => sprintf('วิชา%s มีนักเรียนไม่ผ่านเกณฑ์ %d คน (ภาคเรียนก่อน %d คน) โดยชั้น %s คะแนนเฉลี่ยลดลง %.1f คะแนน และไม่ผ่าน %d คน', SchoolData::SUBJECTS[$sk]['name'], $s['fail_count'], $prev['fail_count'], $d['grade_stats'][$worstGrade]['label'], $worstDrop, $wg['fail_count']),
            'evidence' => [
                sprintf('คะแนนเฉลี่ยทั้งโรงเรียน %.1f (ภาคเรียนก่อน %.1f)', $s['score'], $prev['score']),
                sprintf('ชั้น %s คะแนนเฉลี่ย %.1f ไม่ผ่าน %.1f%%', $d['grade_stats'][$worstGrade]['label'], $wg['score'], $wg['fail_rate']),
                sprintf('จำนวนผู้ไม่ผ่าน %d → %d คน', $prev['fail_count'], $s['fail_count']),
            ],
            'causes' => ['พื้นฐานเดิมของนักเรียนบางกลุ่มยังไม่แน่น', 'สัดส่วนคาบเรียนต่อเนื้อหาในภาคเรียนนี้ค่อนข้างแน่น', 'การส่งงานและแบบฝึกหัดลดลงในช่วงกลางภาค'],
            'actions' => [sprintf('จัดคลินิก%sหลังเลิกเรียนสัปดาห์ละ 2 ครั้ง', SchoolData::SUBJECTS[$sk]['short']), 'ใช้แบบทดสอบวินิจฉัยเพื่อแบ่งกลุ่มสอนเสริมตามระดับ', 'แจ้งผู้ปกครองนักเรียนกลุ่มเสี่ยงไม่ผ่านก่อนสอบปลายภาค'],
            'link' => ais_url('academics'),
            'chart' => ['type' => 'bar', 'labels' => array_map(fn ($k) => $d['grade_stats'][$k]['label'], array_keys($gs[SchoolData::CURRENT_TERM])), 'datasets' => [
                ['label' => 'ไม่ผ่าน (คน)', 'data' => array_values(array_map(static fn ($row) => $row[$sk]['fail_count'], $gs[SchoolData::CURRENT_TERM])), 'color' => 'orange'],
            ], 'options' => ['suffix' => ' คน']],
        ];

        // 5) Grade that most needs additional support (composite).
        $support = array_filter($d['grade_stats'], static fn ($x) => $x['gpa'] !== null);
        uasort($support, static fn ($a, $b) => (($b['high'] * 2 + $b['medium']) / $b['students']) <=> (($a['high'] * 2 + $a['medium']) / $a['students']));
        $sg = reset($support);
        $share = ($sg['high'] + $sg['medium']) / $sg['students'] * 100;
        $insights[] = [
            'id' => 'grade-support', 'domain' => 'students', 'priority' => 'medium', 'icon' => 'life-buoy',
            'title' => sprintf('ชั้น %s ควรได้รับการสนับสนุนเพิ่มเติม', $sg['label']),
            'metric' => sprintf('%.0f%%', $share), 'metric_label' => 'สัดส่วนนักเรียนที่ต้องติดตาม',
            'summary' => sprintf('ชั้น %s มีนักเรียนที่ต้องติดตาม %d คนจาก %d คน (%.0f%%) สูงที่สุดในโรงเรียน โดยปัจจัยหลักคือผลการเรียนและการส่งงาน', $sg['label'], $sg['high'] + $sg['medium'], $sg['students'], $share),
            'evidence' => [
                sprintf('ต้องดูแลใกล้ชิด %d คน · ควรติดตาม %d คน', $sg['high'], $sg['medium']),
                sprintf('GPA เฉลี่ย %.2f · อัตราเข้าเรียน %.1f%%', $sg['gpa'], $sg['attendance']),
                sprintf('ส่งงานเฉลี่ย %.1f%%', $sg['submission']),
            ],
            'causes' => ['เป็นช่วงเปลี่ยนผ่านที่ต้องเลือกแผนการเรียน', 'ภาระงานและกิจกรรมนอกห้องเรียนเพิ่มขึ้น'],
            'actions' => ['จัดกิจกรรมแนะแนวรายกลุ่มเรื่องการวางแผนการเรียน', 'มอบหมายครูที่ปรึกษาดูแลนักเรียนกลุ่มเสี่ยงรายบุคคล', 'ติดตามผลทุกเดือนผ่านหน้า Student Risk Analysis'],
            'link' => ais_url('student-risk'),
        ];

        // 6) Teacher evaluation data completeness.
        $lowData = array_filter($evals, static fn ($t) => $t['completeness'] < 80);
        $insights[] = [
            'id' => 'data-quality', 'domain' => 'teachers', 'priority' => 'medium', 'icon' => 'database-zap',
            'title' => sprintf('ข้อมูลประเมินครู %d คนยังไม่ครบถ้วน', count($lowData)),
            'metric' => count($lowData) . ' คน', 'metric_label' => 'ความครบถ้วนต่ำกว่า 80%',
            'summary' => 'ระบบจะไม่สรุปผลการประเมินของครูที่ข้อมูลไม่ครบถ้วน เพื่อป้องกันการตีความผิดพลาด ควรเก็บบันทึกการสอนและหลักฐานการอบรมเพิ่มเติม',
            'evidence' => array_values(array_map(static fn ($t) => sprintf('%s — ข้อมูลครบ %d%%', $t['name'], $t['completeness']), $lowData)),
            'causes' => ['ไฟล์ชั่วโมงอบรมแบบ PDF นำเข้าไม่สำเร็จ', 'บันทึกการสอนบางสัปดาห์ยังไม่ถูกส่งเข้าระบบ'],
            'actions' => ['นำเข้าข้อมูลอบรมผ่านเทมเพลต CSV ที่ Data Import Center', 'แจ้งครูให้บันทึกการสอนย้อนหลังให้ครบก่อนสิ้นเดือน'],
            'link' => ais_url('data-import'),
        ];

        // 7) Positive: overall attendance and follow-up trend.
        $att = $this->attendanceAvg($currentMonths);
        $attPrev = $this->attendanceAvg(array_values(array_filter($d['months'], static fn ($m) => SchoolData::termOf($m) === SchoolData::PREV_TERM)));
        $risk = $this->riskCounts();
        $riskPrev = $this->riskCounts(true);
        $insights[] = [
            'id' => 'positive', 'domain' => 'performance', 'priority' => 'low', 'icon' => 'trending-up',
            'title' => 'ภาพรวมการมาเรียนและการดูแลนักเรียนดีขึ้น',
            'metric' => sprintf('%+.1f%%', $att - $attPrev), 'metric_label' => 'อัตราเข้าเรียนเทียบภาคเรียนก่อน',
            'summary' => sprintf('อัตราเข้าเรียนเฉลี่ยภาคเรียน %s อยู่ที่ %.1f%% (ภาคเรียนก่อน %.1f%%) และนักเรียนที่ต้องติดตามลดลงจาก %d เป็น %d คน', SchoolData::CURRENT_TERM, $att, $attPrev, $riskPrev['high'] + $riskPrev['medium'], $risk['high'] + $risk['medium']),
            'evidence' => [
                sprintf('อัตราเข้าเรียน %.1f%% → %.1f%%', $attPrev, $att),
                sprintf('นักเรียนที่ต้องติดตาม %d → %d คน', $riskPrev['high'] + $riskPrev['medium'], $risk['high'] + $risk['medium']),
                sprintf('GPA เฉลี่ย %.2f → %.2f', $d['academics']['school_gpa'][SchoolData::PREV_TERM], $d['academics']['school_gpa'][SchoolData::CURRENT_TERM]),
            ],
            'causes' => ['โครงการติดตามการมาเรียนรายวันผ่านครูประจำชั้น', 'การสื่อสารกับผู้ปกครองสม่ำเสมอขึ้น'],
            'actions' => ['ขยายแนวปฏิบัติของห้องที่มีอัตราเข้าเรียนสูงไปยังห้องอื่น', 'ยกย่องห้องเรียนที่มีพัฒนาการดีในที่ประชุมครู'],
            'link' => ais_url('performance'),
        ];

        $this->insightCache = $insights;
        tb_log('SchoolAnalytics', 'insights', 'END', ['count' => count($insights)]);
        return $insights;
    }

    public function insight(string $id): ?array
    {
        foreach ($this->insights() as $insight) {
            if ($insight['id'] === $id) {
                return $insight;
            }
        }
        return null;
    }

    /** Six AI analysis domains with a status score for the AI Intelligence Center. */
    public function analysisDomains(): array
    {
        $d = $this->d;
        $risk = $this->riskCounts();
        $sum = $this->teacherSummary();
        $last = $this->latestMonth();
        $count = static fn (string $domain, array $insights) => count(array_filter($insights, static fn ($i) => $i['domain'] === $domain));
        $ins = $this->insights();

        return [
            ['key' => 'students', 'icon' => 'graduation-cap', 'label' => 'นักเรียน', 'value' => ais_num(count($d['students'])) . ' คน', 'note' => sprintf('ต้องติดตาม %d คน (%.1f%%)', $risk['high'] + $risk['medium'], ($risk['high'] + $risk['medium']) / count($d['students']) * 100), 'findings' => $count('students', $ins)],
            ['key' => 'teachers', 'icon' => 'users', 'label' => 'ครู', 'value' => $sum['teachers'] . ' คน', 'note' => sprintf('คะแนนหลายมิติเฉลี่ย %.1f', $sum['overall']), 'findings' => $count('teachers', $ins)],
            ['key' => 'academic', 'icon' => 'book-open-check', 'label' => 'ผลการเรียน', 'value' => 'GPA ' . ais_num($d['academics']['school_gpa'][SchoolData::CURRENT_TERM], 2), 'note' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM, 'findings' => $count('academic', $ins)],
            ['key' => 'attendance', 'icon' => 'calendar-check', 'label' => 'การเข้าเรียน', 'value' => ais_num($this->attendanceAvg(), 1) . '%', 'note' => $this->periodLabel(), 'findings' => $count('attendance', $ins)],
            ['key' => 'workload', 'icon' => 'briefcase', 'label' => 'ภาระงาน', 'value' => ais_num($sum['avg_hours'], 1) . ' ชม.', 'note' => 'ชั่วโมงสอนเฉลี่ย/สัปดาห์', 'findings' => $count('workload', $ins)],
            ['key' => 'performance', 'icon' => 'gauge', 'label' => 'ผลการดำเนินงาน', 'value' => ais_num($this->schoolScore($last), 1), 'note' => 'คะแนนภาพรวม/100', 'findings' => $count('performance', $ins)],
        ];
    }

    /**
     * AI-style executive summary (rule-based) with SWOT and recommended actions.
     */
    public function executiveSummary(): array
    {
        tb_log('SchoolAnalytics', 'executiveSummary', 'START');

        $d = $this->d;
        $ins = [];
        foreach ($this->insights() as $i) {
            $ins[$i['id']] = $i;
        }
        $att = $this->attendanceAvg();
        $attPrev = $this->attendanceAvg($this->prevMonths);
        $risk = $this->riskCounts();
        $kpis = $d['kpis'];
        $last = $this->latestMonth();
        $gpa = $d['academics']['school_gpa'];
        $sum = $this->teacherSummary();
        $met = array_filter($kpis, static fn ($k) => $k['series'][$last] >= $k['target']);
        $gap = $kpis;
        uasort($gap, static fn ($a, $b) => ($b['target'] - $b['series'][$last]) <=> ($a['target'] - $a['series'][$last]));
        $topGap = reset($gap);

        $headline = sprintf(
            'ภาพรวมการดำเนินงานของโรงเรียนอยู่ในเกณฑ์ดี คะแนนภาพรวม %.1f/100 อัตราการเข้าเรียนเฉลี่ย %.1f%% %s จากช่วงก่อนหน้า และ GPA เฉลี่ย %.2f อย่างไรก็ตาม พบนักเรียน %d คนที่ควรได้รับการติดตามด้านการเรียนและการเข้าเรียน โดยเฉพาะ%s',
            $this->schoolScore($last), $att, $att >= $attPrev ? 'เพิ่มขึ้น' : 'ลดลง', $gpa[SchoolData::CURRENT_TERM], $risk['high'] + $risk['medium'],
            str_replace('อัตราการเข้าเรียน', 'การเข้าเรียน', $ins['att-decline']['title'])
        );

        $result = [
            'headline' => $headline,
            'sections' => [
                'strengths' => ['label' => 'Strengths', 'th' => 'จุดแข็ง', 'icon' => 'thumbs-up', 'items' => [
                    sprintf('อัตราการเข้าเรียนเฉลี่ย %.1f%% ดีขึ้น %+.1f จุดจากช่วงก่อนหน้า', $att, $att - $attPrev),
                    sprintf('KPI ผ่านเป้าหมายแล้ว %d จาก %d ด้าน', count($met), count($kpis)),
                    sprintf('ครูมีชั่วโมงอบรมเฉลี่ย %.1f ชม./ภาคเรียน และผลงานรวม %d ชิ้น', $sum['training'], $sum['works']),
                ]],
                'weaknesses' => ['label' => 'Weaknesses', 'th' => 'จุดที่ควรพัฒนา', 'icon' => 'circle-alert', 'items' => [
                    $ins['subject-fail']['summary'],
                    $ins['room-gpa']['title'] . ' (' . $ins['room-gpa']['metric_label'] . ')',
                ]],
                'opportunities' => ['label' => 'Opportunities', 'th' => 'โอกาส', 'icon' => 'lightbulb', 'items' => [
                    'นำแนวปฏิบัติของห้องที่มีอัตราเข้าเรียนสูงไปขยายผลทั้งระดับชั้น',
                    sprintf('%s ยังห่างเป้าหมาย %.1f จุด — เป็นจุดที่ลงทุนแล้วเห็นผลเร็ว', $topGap['th'], $topGap['target'] - $topGap['series'][$last]),
                    'เชื่อมต่อระบบทะเบียนกลาง (SIS) เพื่อให้ข้อมูลเป็นปัจจุบันอัตโนมัติ',
                ]],
                'risks' => ['label' => 'Risks', 'th' => 'ความเสี่ยง', 'icon' => 'shield-alert', 'items' => [
                    $ins['att-decline']['summary'],
                    $ins['teacher-load']['title'] . ' อาจส่งผลต่อคุณภาพการสอนระยะยาว',
                    sprintf('นักเรียนกลุ่มที่ต้องดูแลใกล้ชิด %d คน หากไม่ติดตามอาจกระทบผลการเรียนปลายปี', $risk['high']),
                ]],
                'actions' => ['label' => 'Recommended Actions', 'th' => 'ข้อเสนอแนะ', 'icon' => 'list-checks', 'items' => [
                    $ins['att-decline']['actions'][0],
                    $ins['subject-fail']['actions'][0],
                    $ins['teacher-load']['actions'][0],
                    sprintf('จัดประชุมทีมดูแลช่วยเหลือสำหรับนักเรียน %d คนในกลุ่มดูแลใกล้ชิด', $risk['high']),
                ]],
            ],
        ];

        tb_log('SchoolAnalytics', 'executiveSummary', 'END');
        return $result;
    }

    /** Notification dropdown items. */
    public function notifications(): array
    {
        $items = [];
        foreach ($this->insights() as $i) {
            if ($i['priority'] === 'low') {
                continue;
            }
            $items[] = ['id' => 'n-' . $i['id'], 'icon' => $i['icon'], 'tone' => $i['priority'] === 'high' ? 'red' : 'orange', 'title' => $i['title'], 'time' => 'AI Insight', 'link' => ais_url('ai-insights', ['insight' => $i['id']])];
        }
        foreach ($this->d['imports'] as $imp) {
            if ($imp['status'] === 'imported') {
                continue;
            }
            $items[] = ['id' => 'n-' . $imp['id'], 'icon' => $imp['status'] === 'failed' ? 'file-x' : 'file-clock', 'tone' => $imp['status'] === 'failed' ? 'red' : 'yellow', 'title' => ($imp['status'] === 'failed' ? 'นำเข้าไม่สำเร็จ: ' : 'รอตรวจสอบ: ') . $imp['dataset'], 'time' => ais_thai_date($imp['date']), 'link' => ais_url('data-import')];
        }
        return array_slice($items, 0, 8);
    }

    /** Monthly KPI data for the School Performance page. */
    public function kpiPerformance(): array
    {
        $last = $this->latestMonth();
        $result = [];
        foreach ($this->d['kpis'] as $key => $k) {
            $current = $k['series'][$last];
            $prev = $k['series'][end($this->prevMonths)];
            $result[$key] = $k + [
                'key' => $key,
                'current' => $current,
                'progress' => min(100, round($current / $k['target'] * 100)),
                'met' => $current >= $k['target'],
                'delta' => ais_delta($current, $prev, 1),
                'period' => $this->pick($k['series'], $this->months),
            ];
        }
        return $result;
    }

    /** Compact facts for an external LLM (OpenAI provider). */
    public function chatContext(): array
    {
        $risk = $this->riskCounts();
        return [
            'school' => ais_config('school'),
            'period' => $this->periodLabel() . ' (' . $this->periodRange() . ')',
            'students' => count($this->d['students']),
            'personnel' => end($this->d['personnel']),
            'attendance_avg' => $this->attendanceAvg(),
            'gpa' => $this->d['academics']['school_gpa'],
            'risk' => $risk,
            'kpis' => array_map(fn ($k) => ['name' => $k['th'], 'current' => $k['series'][$this->latestMonth()], 'target' => $k['target']], $this->d['kpis']),
            'insights' => array_map(static fn ($i) => ['title' => $i['title'], 'summary' => $i['summary'], 'actions' => $i['actions']], $this->insights()),
            'rooms' => array_values(array_map(static fn ($r) => ['room' => $r['room'], 'attendance' => $r['attendance'], 'gpa' => $r['gpa'], 'follow_up' => $r['high'] + $r['medium']], $this->d['rooms'])),
        ];
    }
}
