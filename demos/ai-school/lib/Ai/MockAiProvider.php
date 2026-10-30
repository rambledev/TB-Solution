<?php

declare(strict_types=1);

/**
 * Rule-based assistant for the demo. Detects the question type by keywords and
 * answers from SchoolAnalytics, so every number matches the dashboards.
 * It does not use a language model.
 */
final class MockAiProvider implements AiProvider
{
    /** intent => keywords (Thai/English, lower-case) */
    private const INTENTS = [
        'greeting'     => ['สวัสดี', 'hello', 'hi ', 'ทำอะไรได้', 'ช่วยอะไร', 'วิธีใช้'],
        'priority'     => ['ปรับปรุง', 'ควรทำ', 'อะไรก่อน', 'เรื่องใดก่อน', 'เรื่องไหนก่อน', 'เร่งด่วน', 'ลำดับความสำคัญ', 'แนะนำ'],
        'absence'      => ['ขาดเรียน', 'ขาด', 'มาเรียน', 'เข้าเรียน', 'attendance'],
        'subject'      => ['วิชา', 'ไม่ผ่าน', 'คะแนน', 'ผลการเรียนลด', 'ผลสัมฤทธิ์'],
        'teacher_load' => ['ภาระงาน', 'ชั่วโมงสอน', 'งานเยอะ', 'workload', 'ภาระ'],
        'teacher_eval' => ['ประเมินครู', 'ผลการประเมิน', 'ครูคนใด', 'ครูคนไหน', 'พัฒนาครู'],
        'risk'         => ['เสี่ยง', 'ติดตาม', 'ช่วยเหลือ', 'ดูแล', 'risk'],
        'gpa'          => ['gpa', 'เกรด', 'เกรดเฉลี่ย'],
        'enrollment'   => ['จำนวนนักเรียน', 'ย้ายเข้า', 'ย้ายออก', 'รับนักเรียน', 'นักเรียนทั้งหมด'],
        'kpi'          => ['kpi', 'เป้าหมาย', 'ผลการดำเนินงาน', 'ตัวชี้วัด'],
        'overview'     => ['ภาพรวม', 'สรุป', 'สถานการณ์', 'เดือนนี้', 'overview'],
    ];

    private const SUGGESTIONS = [
        'สรุปภาพรวมโรงเรียนในเดือนนี้',
        'ห้องเรียนใดมีอัตราการขาดเรียนสูงที่สุด',
        'วิชาใดมีผลการเรียนลดลง',
        'ครูคนใดมีภาระงานสูง',
        'โรงเรียนควรปรับปรุงเรื่องใดก่อน',
        'มีนักเรียนที่ควรติดตามกี่คน',
    ];

    public function __construct(private readonly SchoolAnalytics $a)
    {
    }

    public function id(): string
    {
        return 'mock';
    }

    public function detectIntent(string $message): string
    {
        $text = mb_strtolower($message . ' ');
        $best = 'fallback';
        $bestScore = 0;
        foreach (self::INTENTS as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (mb_strpos($text, $kw) !== false) {
                    $score += mb_strlen($kw);
                }
            }
            // Questions about a specific teacher's workload outrank generic teacher questions.
            if ($intent === 'teacher_load' && mb_strpos($text, 'ครู') !== false && $score > 0) {
                $score += 5;
            }
            if ($score > $bestScore) {
                $best = $intent;
                $bestScore = $score;
            }
        }
        return $best;
    }

    public function reply(string $message, array $history = []): array
    {
        tb_log('MockAiProvider', 'reply', 'START', ['message' => mb_substr($message, 0, 80)]);

        $intent = $this->detectIntent($message);
        [$text, $sources] = match ($intent) {
            'greeting'     => $this->greeting(),
            'overview'     => $this->overview(),
            'absence'      => $this->absence($message),
            'subject'      => $this->subject(),
            'teacher_load' => $this->teacherLoad(),
            'teacher_eval' => $this->teacherEval(),
            'priority'     => $this->priority(),
            'risk'         => $this->risk(),
            'gpa'          => $this->gpa(),
            'enrollment'   => $this->enrollment(),
            'kpi'          => $this->kpi(),
            default        => $this->fallback(),
        };

        $suggestions = array_values(array_filter(self::SUGGESTIONS, static fn ($s) => $s !== trim($message)));
        shuffle($suggestions);

        tb_log('MockAiProvider', 'reply', 'END', ['intent' => $intent]);
        return [
            'text' => $text,
            'sources' => $sources,
            'suggestions' => array_slice($suggestions, 0, 3),
            'provider' => $this->id(),
            'intent' => $intent,
        ];
    }

    /* ----------------------------------------------------------- answers */

    private function greeting(): array
    {
        return ["สวัสดีครับ ผมคือผู้ช่วยวิเคราะห์ข้อมูลสำหรับผู้บริหาร (โหมดสาธิต)\n\nสามารถถามได้ เช่น\n- ภาพรวมโรงเรียน และ KPI\n- การเข้าเรียนรายห้อง/รายชั้น\n- ผลการเรียนรายวิชา\n- ภาระงานครู\n- นักเรียนที่ควรติดตาม", []];
    }

    private function overview(): array
    {
        $s = $this->a->executiveSummary();
        $kpi = $this->a->kpiCards();
        $lines = [];
        foreach ($kpi as $k) {
            $lines[] = sprintf('- %s: **%s%s** (%s %s)', $k['label'], $k['value'], $k['unit'] !== '' ? ' ' . $k['unit'] : '', $k['delta']['text'], $k['compare']);
        }
        $text = "**สรุปภาพรวม — " . $this->a->periodLabel() . "**\n\n" . $s['headline'] . "\n\n**ตัวชี้วัดหลัก**\n" . implode("\n", $lines)
            . "\n\n**สิ่งที่ควรดำเนินการ**\n- " . implode("\n- ", array_slice($s['sections']['actions']['items'], 0, 3));
        return [$text, ['ข้อมูลนักเรียน', 'การเข้าเรียนรายเดือน', 'ผลการเรียนภาคเรียน ' . SchoolData::CURRENT_TERM, 'KPI โรงเรียน']];
    }

    private function absence(string $message): array
    {
        $byGrade = mb_strpos($message, 'ชั้น') !== false && mb_strpos($message, 'ห้อง') === false;
        $d = $this->a->data();

        if ($byGrade) {
            $grades = $d['grade_stats'];
            uasort($grades, static fn ($x, $y) => $x['attendance'] <=> $y['attendance']);
            $lines = [];
            foreach (array_slice($grades, 0, 5) as $g) {
                $lines[] = sprintf('- ชั้น %s: **%.1f%%** (ภาคเรียนก่อน %.1f%%)', $g['label'], $g['attendance'], $g['att_prev']);
            }
            $text = "**ระดับชั้นที่มีอัตราเข้าเรียนต่ำที่สุด** (ภาคเรียน " . SchoolData::CURRENT_TERM . ")\n" . implode("\n", $lines);
        } else {
            $rooms = $d['rooms'];
            uasort($rooms, static fn ($x, $y) => $x['attendance'] <=> $y['attendance']);
            $top = reset($rooms);
            $lines = [];
            foreach (array_slice($rooms, 0, 5) as $r) {
                $lines[] = sprintf('- ห้อง %s: เข้าเรียน **%.1f%%** · ขาดรวม %s วัน-คน · ต้องติดตาม %d คน', $r['room'], $r['attendance'], ais_num($r['absent_days']), $r['high'] + $r['medium']);
            }
            $text = sprintf("ห้องที่มีอัตราการขาดเรียนสูงที่สุดคือ **ห้อง %s** อัตราเข้าเรียน %.1f%% (ขาดเรียน %.1f%%) ครูประจำชั้นคือ %s\n\n**5 ห้องที่ควรติดตาม**\n%s",
                $top['room'], $top['attendance'], 100 - $top['attendance'], $top['homeroom_name'], implode("\n", $lines));
        }

        $insight = $this->a->insight('att-decline');
        $text .= "\n\n**ข้อเสนอแนะ**\n- " . implode("\n- ", $insight['actions']);
        return [$text, ['บันทึกการเข้าเรียนรายวัน ภาคเรียน ' . SchoolData::CURRENT_TERM]];
    }

    private function subject(): array
    {
        $d = $this->a->data();
        $cur = $d['academics']['subjects'][SchoolData::CURRENT_TERM];
        $prev = $d['academics']['subjects'][SchoolData::PREV_TERM];
        $changes = [];
        foreach ($cur as $k => $s) {
            $changes[$k] = $s['score'] - $prev[$k]['score'];
        }
        asort($changes);
        $lines = [];
        foreach (array_slice($changes, 0, 4, true) as $k => $diff) {
            $lines[] = sprintf('- %s: **%.1f** (%+.1f) · ไม่ผ่าน %d คน', SchoolData::SUBJECTS[$k]['name'], $cur[$k]['score'], $diff, $cur[$k]['fail_count']);
        }
        $insight = $this->a->insight('subject-fail');
        $text = "**รายวิชาที่คะแนนเฉลี่ยลดลงมากที่สุด** (ภาคเรียน " . SchoolData::PREV_TERM . ' → ' . SchoolData::CURRENT_TERM . ")\n" . implode("\n", $lines)
            . "\n\n" . $insight['summary'] . "\n\n**ข้อเสนอแนะ**\n- " . implode("\n- ", $insight['actions']);
        return [$text, ['ผลการเรียนรายวิชา 3 ภาคเรียน']];
    }

    private function teacherLoad(): array
    {
        $insight = $this->a->insight('teacher-load');
        $sum = $this->a->teacherSummary();
        $text = sprintf("ชั่วโมงสอนเฉลี่ยของครูอยู่ที่ **%.1f ชม./สัปดาห์** พบครู **%d คน** ที่มีภาระงานรวมสูงกว่าค่าเฉลี่ยตั้งแต่ 20%%\n\n- %s\n\n**ข้อเสนอแนะ**\n- %s\n\n_หมายเหตุ: ภาระงานที่สูงไม่ได้หมายถึงผลการปฏิบัติงานที่ไม่ดี แต่เป็นสัญญาณให้พิจารณาความสมดุลของงาน_",
            $sum['avg_hours'], $sum['high_load'], implode("\n- ", $insight['evidence']), implode("\n- ", $insight['actions']));
        return [$text, ['ตารางสอน', 'คำสั่งมอบหมายงานพิเศษ']];
    }

    private function teacherEval(): array
    {
        $evals = $this->a->teacherEvaluations();
        $sum = $this->a->teacherSummary();
        $bands = array_count_values(array_map(static fn ($t) => $t['band']['label'], $evals));
        $lowData = array_filter($evals, static fn ($t) => $t['completeness'] < 80);
        $lines = [];
        foreach ($bands as $label => $n) {
            $lines[] = "- {$label}: {$n} คน";
        }
        $text = sprintf("ผลวิเคราะห์หลายมิติของครู %d คน คะแนนเฉลี่ย **%.1f/100**\n\n%s\n\nมีครู %d คนที่ข้อมูลยังไม่ครบถ้วน ระบบจึงแสดงข้อจำกัดกำกับไว้\n\n_ผลนี้เป็นเครื่องมือสนับสนุนการนิเทศและพัฒนาครู ไม่ใช่การตัดสินคุณภาพครูโดยอัตโนมัติ และไม่ได้ใช้ผลการเรียนของนักเรียนเพียงอย่างเดียว (น้ำหนักผลสัมฤทธิ์ 12%%)_",
            count($evals), $sum['overall'], implode("\n", $lines), count($lowData));
        return [$text, ['ข้อมูล 9 มิติจากหน้า AI Teacher Evaluation']];
    }

    private function priority(): array
    {
        $high = array_filter($this->a->insights(), static fn ($i) => $i['priority'] === 'high');
        $lines = [];
        $n = 1;
        foreach ($high as $i) {
            $lines[] = sprintf("%d. **%s** — %s", $n++, $i['title'], $i['actions'][0]);
        }
        $medium = array_filter($this->a->insights(), static fn ($i) => $i['priority'] === 'medium');
        foreach ($medium as $i) {
            $lines[] = sprintf("%d. %s — %s", $n++, $i['title'], $i['actions'][0]);
        }
        $text = "จากข้อมูลปัจจุบัน แนะนำลำดับความสำคัญดังนี้\n\n" . implode("\n", $lines)
            . "\n\nเรื่องลำดับ 1–3 ส่งผลโดยตรงต่อผลการเรียนปลายปี จึงควรเริ่มภายในเดือนนี้";
        return [$text, ['AI Insights ทั้งหมด ' . count($this->a->insights()) . ' รายการ']];
    }

    private function risk(): array
    {
        $c = $this->a->riskCounts();
        $p = $this->a->riskCounts(true);
        $factors = $this->a->riskFactorCounts();
        $lines = array_map(static fn ($f) => sprintf('- %s: %d คน', $f['label'], $f['count']), array_slice($factors, 0, 4));
        $text = sprintf("ปัจจุบันมีนักเรียนที่ควรได้รับการติดตาม **%d คน** (ภาคเรียนก่อน %d คน)\n\n- ต้องดูแลใกล้ชิด: **%d คน**\n- ควรติดตาม: **%d คน**\n- ปกติ: %s คน\n\n**ปัจจัยที่พบบ่อย**\n%s\n\nแนะนำให้ครูประจำชั้นใช้แผนช่วยเหลือรายบุคคลในหน้า Student Risk Analysis โดยเน้นการพูดคุยและสนับสนุน ไม่ใช่การตีตรานักเรียน",
            $c['high'] + $c['medium'], $p['high'] + $p['medium'], $c['high'], $c['medium'], ais_num($c['low']), implode("\n", $lines));
        return [$text, ['การเข้าเรียน', 'ผลการเรียน', 'การส่งงาน', 'บันทึกการดูแลช่วยเหลือ']];
    }

    private function gpa(): array
    {
        $d = $this->a->data();
        $g = $d['academics']['school_gpa'];
        $grades = array_filter($d['grade_stats'], static fn ($x) => $x['gpa'] !== null);
        uasort($grades, static fn ($x, $y) => $y['gpa'] <=> $x['gpa']);
        $best = reset($grades);
        $worst = end($grades);
        $text = sprintf("GPA เฉลี่ยของโรงเรียน (ป.1–ม.6)\n\n- ภาคเรียน 1/2568: %.2f\n- ภาคเรียน 2/2568: %.2f\n- ภาคเรียน 1/2569: **%.2f**\n\nระดับชั้นที่ GPA สูงสุดคือ %s (%.2f) และต่ำสุดคือ %s (%.2f)",
            $g['1/2568'], $g['2/2568'], $g['1/2569'], $best['label'], $best['gpa'], $worst['label'], $worst['gpa']);
        return [$text, ['ผลการเรียน 3 ภาคเรียน']];
    }

    private function enrollment(): array
    {
        $d = $this->a->data();
        $t = $this->a->transfers();
        $months = $this->a->months();
        $first = $d['enrollment']['count'][$months[0]];
        $text = sprintf("ปัจจุบันมีนักเรียน **%s คน** ใน %d ห้องเรียน\n\nช่วง%s (%s)\n- ย้ายเข้า %d คน\n- ย้ายออก %d คน\n- จำนวนนักเรียนเปลี่ยนจาก %s เป็น %s คน",
            ais_num(count($d['students'])), count($d['rooms']), $this->a->periodLabel(), $this->a->periodRange(), $t['in'], $t['out'], ais_num($first), ais_num(end($d['enrollment']['count'])));
        return [$text, ['ทะเบียนนักเรียน', 'บันทึกการย้ายเข้า-ย้ายออก']];
    }

    private function kpi(): array
    {
        $lines = [];
        foreach ($this->a->kpiPerformance() as $k) {
            $lines[] = sprintf('- %s: **%.1f** / เป้าหมาย %d %s', $k['th'], $k['current'], $k['target'], $k['met'] ? '✓ ผ่านเป้าหมาย' : sprintf('(ขาดอีก %.1f)', $k['target'] - $k['current']));
        }
        return ["**KPI โรงเรียนเดือนล่าสุด**\n" . implode("\n", $lines), ['KPI โรงเรียนรายเดือน']];
    }

    private function fallback(): array
    {
        return ["ขออภัยครับ ในโหมดสาธิตผมตอบได้เฉพาะคำถามเกี่ยวกับข้อมูลในระบบ เช่น ภาพรวมโรงเรียน การเข้าเรียน ผลการเรียน ภาระงานครู และนักเรียนที่ควรติดตาม ลองเลือกคำถามตัวอย่างด้านล่างได้เลยครับ", []];
    }
}
