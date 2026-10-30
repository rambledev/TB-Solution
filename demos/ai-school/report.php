<?php

declare(strict_types=1);

/**
 * Printable A4 executive report: report.php?type=<key>[&print=1]
 * print=1 opens the browser print dialog (choose "Save as PDF" to export).
 */
require __DIR__ . '/bootstrap.php';

$reports = require AIS_ROOT . '/config/reports.php';
$type = isset($reports[$_GET['type'] ?? '']) ? (string) $_GET['type'] : 'overview';
$autoPrint = ($_GET['print'] ?? '') === '1';
tb_log('Report', 'render', 'START', ['type' => $type, 'print' => $autoPrint]);

$a = SchoolAnalytics::instance();
$d = $a->data();
$school = ais_config('school');
$report = $reports[$type];
$cur = SchoolData::CURRENT_TERM;
$prev = SchoolData::PREV_TERM;

$bar = static fn (float $value, float $max, string $color = '#2878E8'): string =>
    '<span class="rbar"><i style="width:' . max(2, min(100, $value / $max * 100)) . '%;background:' . $color . '"></i></span>';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($report['title']) ?> · <?= e($school['name']) ?></title>
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/svg+xml" href="../../assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap">
    <style>
        :root { --primary: #2878E8; --deep: #173B75; --muted: #5B7396; --border: #DDE9F8; --light: #EAF4FF; --yellow: #FFE9A8; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEF2F8; font-family: 'IBM Plex Sans Thai', system-ui, sans-serif; color: #18345B; font-size: 12.5px; line-height: 1.6; }
        .sheet { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 16mm 15mm; background: #fff; box-shadow: 0 10px 30px rgba(23,59,117,.12); }
        .rhead { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-bottom: 12px; border-bottom: 3px solid var(--primary); }
        .rhead__brand { display: flex; align-items: center; gap: 10px; }
        .rhead__brand svg { width: 38px; height: 38px; }
        .rhead__brand strong { display: block; font-size: 14px; color: var(--deep); }
        .rhead__brand small { color: var(--muted); }
        .rhead__meta { text-align: right; color: var(--muted); font-size: 11px; }
        .demo { display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 99px; background: var(--yellow); color: var(--deep); font-weight: 700; font-size: 10px; letter-spacing: .05em; }
        h1 { margin: 18px 0 2px; font-size: 22px; color: var(--deep); }
        h2 { margin: 20px 0 8px; font-size: 14px; color: var(--deep); border-left: 4px solid var(--primary); padding-left: 8px; }
        .sub { color: var(--muted); margin: 0; }
        .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 14px; }
        .kpis div { padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; background: #F7FAFF; }
        .kpis small { display: block; color: var(--muted); font-size: 10.5px; }
        .kpis strong { font-size: 18px; color: var(--deep); }
        .kpis em { font-style: normal; font-size: 10.5px; color: var(--muted); margin-left: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
        th { background: var(--light); color: var(--deep); font-weight: 600; font-size: 11px; }
        td.n, th.n { text-align: right; font-variant-numeric: tabular-nums; }
        .rbar { display: inline-block; width: 90px; height: 7px; border-radius: 4px; background: #EAF1FB; vertical-align: middle; margin-right: 6px; overflow: hidden; }
        .rbar i { display: block; height: 100%; border-radius: 4px; }
        .box { padding: 10px 12px; border-radius: 10px; background: #F7FAFF; border: 1px solid var(--border); }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        ul { margin: 4px 0 0; padding-left: 18px; }
        li { margin: 2px 0; }
        .tag { display: inline-block; padding: 0 7px; border-radius: 99px; font-size: 10.5px; font-weight: 600; }
        .tag.high { background: #FDECEC; color: #C2353A; } .tag.medium { background: #FFF1DD; color: #B86800; } .tag.low { background: #E3F6EC; color: #17784F; }
        .insight { padding: 10px 12px; margin-top: 8px; border: 1px solid var(--border); border-radius: 10px; page-break-inside: avoid; }
        .insight h3 { margin: 0 0 4px; font-size: 13px; color: var(--deep); }
        .rfoot { margin-top: 24px; padding-top: 10px; border-top: 1px solid var(--border); color: var(--muted); font-size: 10.5px; display: flex; justify-content: space-between; gap: 10px; }
        .toolbar { position: sticky; top: 0; z-index: 2; display: flex; justify-content: center; gap: 8px; padding: 10px; background: rgba(238,242,248,.92); backdrop-filter: blur(6px); }
        .toolbar button, .toolbar a { padding: 8px 16px; border: 0; border-radius: 99px; background: var(--primary); color: #fff; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
        .toolbar a { background: #fff; color: var(--deep); border: 1px solid var(--border); }
        .embed .toolbar { display: none; }
        .embed .sheet { margin: 0 auto; }
        @media (max-width: 820px) { .sheet { width: 100%; min-height: 0; padding: 18px; margin: 0; } .kpis { grid-template-columns: 1fr 1fr; } .grid2 { grid-template-columns: 1fr; } }
        @media print {
            @page { size: A4; margin: 12mm; }
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            h2 { page-break-after: avoid; }
            tr { page-break-inside: avoid; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="<?= isset($_GET['embed']) ? 'embed' : '' ?>">
<div class="toolbar">
    <button type="button" onclick="window.print()">พิมพ์ / บันทึกเป็น PDF</button>
    <a href="./?page=reports">กลับหน้ารายงาน</a>
</div>
<div class="sheet">
    <header class="rhead">
        <div class="rhead__brand">
            <?= tb_logo_mark('report', '') ?>
            <div><strong><?= e($school['name']) ?></strong><small>AI School Intelligence · TB-Solution</small></div>
        </div>
        <div class="rhead__meta">
            ปีการศึกษา <?= e($school['academic_year']) ?> · ภาคเรียน <?= e($cur) ?><br>
            ข้อมูล <?= e($a->periodLabel()) ?> (<?= e($a->periodRange()) ?>)<br>
            <span class="demo">DEMO DATA · ข้อมูลจำลอง</span>
        </div>
    </header>

    <h1><?= e($report['title']) ?></h1>
    <p class="sub"><?= e($report['desc']) ?> · จัดทำวันที่ <?= e(ais_thai_date('2026-10-30')) ?></p>

<?php if ($type === 'overview'):
    $summary = $a->executiveSummary(); ?>
    <div class="kpis">
<?php foreach ($a->kpiCards() as $k): ?>
        <div><small><?= e($k['label']) ?></small><strong><?= e($k['value']) ?></strong><em><?= e($k['unit']) ?> (<?= e($k['delta']['text']) ?>)</em></div>
<?php endforeach; ?>
    </div>
    <h2>บทสรุปผู้บริหาร (AI Executive Summary)</h2>
    <p class="box"><?= e($summary['headline']) ?></p>
    <div class="grid2" style="margin-top:10px">
<?php foreach ($summary['sections'] as $s): ?>
        <div class="box"><strong><?= e($s['th']) ?> (<?= e($s['label']) ?>)</strong><ul><?php foreach ($s['items'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></div>
<?php endforeach; ?>
    </div>
    <h2>KPI โรงเรียนเทียบเป้าหมาย</h2>
    <table><thead><tr><th>ตัวชี้วัด</th><th class="n">ปัจจุบัน</th><th class="n">เป้าหมาย</th><th>ความคืบหน้า</th></tr></thead><tbody>
<?php foreach ($a->kpiPerformance() as $k): ?>
        <tr><td><?= e($k['th']) ?></td><td class="n"><?= ais_num($k['current'], 1) ?></td><td class="n"><?= $k['target'] ?></td><td><?= $bar((float) $k['progress'], 100, $k['met'] ? '#22A06B' : '#2878E8') ?><?= $k['progress'] ?>%</td></tr>
<?php endforeach; ?>
    </tbody></table>

<?php elseif ($type === 'students'):
    $t = $a->transfers(); ?>
    <div class="kpis">
        <div><small>นักเรียนทั้งหมด</small><strong><?= ais_num(count($d['students'])) ?></strong><em>คน · <?= count($d['rooms']) ?> ห้อง</em></div>
        <div><small>อัตราเข้าเรียนเฉลี่ย</small><strong><?= ais_num($a->attendanceAvg(), 1) ?>%</strong><em><?= e($a->periodLabel()) ?></em></div>
        <div><small>ย้ายเข้า / ย้ายออก</small><strong><?= $t['in'] ?> / <?= $t['out'] ?></strong><em>คน</em></div>
    </div>
    <h2>จำนวนนักเรียนและการเข้าเรียนรายระดับชั้น</h2>
    <table><thead><tr><th>ระดับชั้น</th><th class="n">ห้อง</th><th class="n">ชาย</th><th class="n">หญิง</th><th class="n">รวม</th><th>อัตราเข้าเรียน</th><th class="n">ต้องติดตาม</th></tr></thead><tbody>
<?php foreach ($d['grade_stats'] as $g): ?>
        <tr><td><?= e($g['label']) ?></td><td class="n"><?= $g['rooms'] ?></td><td class="n"><?= $g['male'] ?></td><td class="n"><?= $g['female'] ?></td><td class="n"><strong><?= $g['students'] ?></strong></td><td><?= $bar($g['attendance'] - 85, 15, '#22A06B') ?><?= ais_num($g['attendance'], 1) ?>%</td><td class="n"><?= $g['high'] + $g['medium'] ?></td></tr>
<?php endforeach; ?>
    </tbody></table>
    <h2>การเปลี่ยนแปลงจำนวนนักเรียนรายเดือน</h2>
    <table><thead><tr><th>เดือน</th><th class="n">ย้ายเข้า</th><th class="n">ย้ายออก</th><th class="n">จำนวนสิ้นเดือน</th></tr></thead><tbody>
<?php foreach ($a->months() as $m): ?>
        <tr><td><?= e(ais_month($m)) ?></td><td class="n"><?= $d['enrollment']['in'][$m] ?></td><td class="n"><?= $d['enrollment']['out'][$m] ?></td><td class="n"><?= ais_num($d['enrollment']['count'][$m]) ?></td></tr>
<?php endforeach; ?>
    </tbody></table>

<?php elseif ($type === 'academics'):
    $ac = $d['academics']; ?>
    <div class="kpis">
<?php foreach (SchoolData::TERMS as $term): ?>
        <div><small>GPA เฉลี่ย ภาคเรียน <?= e($term) ?></small><strong><?= ais_num($ac['school_gpa'][$term], 2) ?></strong></div>
<?php endforeach; ?>
    </div>
    <h2>ผลสัมฤทธิ์รายวิชา ภาคเรียน <?= e($cur) ?></h2>
    <table><thead><tr><th>รายวิชา</th><th class="n">คะแนนเฉลี่ย</th><th class="n">เปลี่ยนแปลง</th><th class="n">ไม่ผ่าน (คน)</th><th>อัตราผ่าน</th></tr></thead><tbody>
<?php foreach (SchoolData::SUBJECTS as $k => $s):
    $c = $ac['subjects'][$cur][$k]; ?>
        <tr><td><?= e($s['name']) ?></td><td class="n"><?= ais_num($c['score'], 1) ?></td><td class="n"><?= e(sprintf('%+.1f', $c['score'] - $ac['subjects'][$prev][$k]['score'])) ?></td><td class="n"><?= $c['fail_count'] ?></td><td><?= $bar($c['pass_rate'] - 80, 20, '#22A06B') ?><?= ais_num($c['pass_rate'], 1) ?>%</td></tr>
<?php endforeach; ?>
    </tbody></table>
    <h2>GPA รายระดับชั้น</h2>
    <table><thead><tr><th>ระดับชั้น</th><?php foreach (SchoolData::TERMS as $term): ?><th class="n"><?= e($term) ?></th><?php endforeach; ?><th>ภาคเรียนปัจจุบัน</th></tr></thead><tbody>
<?php foreach ($ac['gpa'][$cur] as $gk => $g): ?>
        <tr><td><?= e($d['grade_stats'][$gk]['label']) ?></td><?php foreach (SchoolData::TERMS as $term): ?><td class="n"><?= ais_num($ac['gpa'][$term][$gk], 2) ?></td><?php endforeach; ?><td><?= $bar($g, 4) ?></td></tr>
<?php endforeach; ?>
    </tbody></table>

<?php elseif ($type === 'teachers'):
    $sum = $a->teacherSummary();
    $evals = $a->teacherEvaluations();
    $depts = [];
    foreach ($evals as $t) {
        $depts[$t['dept_label']][] = $t;
    } ?>
    <div class="kpis">
        <div><small>ครู / บุคลากร</small><strong><?= $sum['teachers'] ?> / <?= $sum['staff'] ?></strong><em>คน</em></div>
        <div><small>ชั่วโมงสอนเฉลี่ย</small><strong><?= ais_num($sum['avg_hours'], 1) ?></strong><em>ชม./สัปดาห์</em></div>
        <div><small>คะแนนหลายมิติเฉลี่ย</small><strong><?= ais_num($sum['overall'], 1) ?></strong><em>/100</em></div>
    </div>
    <h2>สรุปรายกลุ่มสาระ</h2>
    <table><thead><tr><th>กลุ่มสาระ</th><th class="n">ครู</th><th class="n">ชม.สอนเฉลี่ย</th><th class="n">บันทึกการสอน</th><th class="n">อบรมเฉลี่ย</th><th class="n">ผลงาน</th><th class="n">คะแนนหลายมิติ</th></tr></thead><tbody>
<?php foreach ($depts as $name => $list):
    $avg = static fn (string $k) => array_sum(array_column($list, $k)) / count($list); ?>
        <tr><td><?= e($name) ?></td><td class="n"><?= count($list) ?></td><td class="n"><?= ais_num($avg('hours'), 1) ?></td><td class="n"><?= ais_num($avg('lesson_log'), 1) ?>%</td><td class="n"><?= ais_num($avg('training'), 1) ?></td><td class="n"><?= array_sum(array_column($list, 'works')) ?></td><td class="n"><?= ais_num($avg('overall'), 1) ?></td></tr>
<?php endforeach; ?>
    </tbody></table>
    <h2>หมายเหตุการใช้ผลวิเคราะห์</h2>
    <p class="box">ผลวิเคราะห์ครูเป็นข้อมูลประกอบการนิเทศและพัฒนา ไม่ใช่ผลการตัดสินคุณภาพครู ใช้ข้อมูล 9 มิติ โดยผลสัมฤทธิ์ของนักเรียนมีน้ำหนัก 12% ครูที่ข้อมูลไม่ครบถ้วนจะมีข้อจำกัดกำกับไว้ในรายบุคคล</p>

<?php elseif ($type === 'insights'): ?>
<?php foreach ($a->insights() as $i): ?>
    <div class="insight">
        <h3><span class="tag <?= e($i['priority']) ?>"><?= e(ais_priority_label($i['priority'])) ?></span> <?= e($i['title']) ?></h3>
        <p style="margin:0"><?= e($i['summary']) ?></p>
        <div class="grid2" style="margin-top:6px">
            <div><strong>ข้อมูลสนับสนุน</strong><ul><?php foreach ($i['evidence'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
            <div><strong>ข้อเสนอแนะ</strong><ul><?php foreach ($i['actions'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
        </div>
    </div>
<?php endforeach; ?>
    <p class="sub" style="margin-top:10px">ประเด็นข้างต้นสร้างจากกฎการวิเคราะห์ (Rule-based Simulation) บนข้อมูลจำลอง</p>

<?php else:
    $c = $a->riskCounts(); ?>
    <div class="kpis">
        <div><small>ต้องดูแลใกล้ชิด (High)</small><strong><?= $c['high'] ?></strong><em>คน</em></div>
        <div><small>ควรติดตาม (Medium)</small><strong><?= $c['medium'] ?></strong><em>คน</em></div>
        <div><small>ปกติ (Low)</small><strong><?= ais_num($c['low']) ?></strong><em>คน</em></div>
    </div>
    <h2>สาเหตุที่ตรวจพบ</h2>
    <table><thead><tr><th>ปัจจัย</th><th class="n">จำนวน</th><th></th></tr></thead><tbody>
<?php foreach ($a->riskFactorCounts() as $f): ?>
        <tr><td><?= e($f['label']) ?></td><td class="n"><?= $f['count'] ?></td><td><?= $bar((float) $f['count'], (float) ($c['high'] + $c['medium']), '#E8890C') ?></td></tr>
<?php endforeach; ?>
    </tbody></table>
    <h2>รายชื่อกลุ่มดูแลใกล้ชิด (ปกปิดชื่อ)</h2>
    <table><thead><tr><th>รหัส</th><th>ห้อง</th><th class="n">เข้าเรียน</th><th class="n">GPA</th><th>แนวทางช่วยเหลือหลัก</th></tr></thead><tbody>
<?php foreach ($a->followUpStudents(null, 'high') as $s): ?>
        <tr><td><?= e($s['code']) ?></td><td><?= e($s['room']) ?></td><td class="n"><?= ais_num($s['attendance'], 1) ?>%</td><td class="n"><?= $s['gpa'] !== null ? ais_num($s['gpa'], 2) : '–' ?></td><td><?= e(SchoolAnalytics::supportPlan($s)[0] ?? '-') ?></td></tr>
<?php endforeach; ?>
    </tbody></table>
<?php endif; ?>

    <footer class="rfoot">
        <span>รายงานนี้สร้างจากข้อมูลจำลองเพื่อการสาธิต ไม่ใช่ข้อมูลจริงของโรงเรียนหรือบุคคลใด</span>
        <span><?= e(ais_config('product.full_name')) ?> · TB-Solution</span>
    </footer>
</div>
<?php if ($autoPrint): ?>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 500); });</script>
<?php endif; ?>
</body>
</html>
<?php
tb_log('Report', 'render', 'END', ['type' => $type]);
