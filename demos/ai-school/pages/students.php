<?php

declare(strict_types=1);

/**
 * Student Intelligence.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'students', 'START', ['level' => $_GET['level'] ?? null, 'grade' => $_GET['grade'] ?? null]);

$d = $a->data();
$levels = SchoolData::LEVELS;
$level = isset($levels[$_GET['level'] ?? '']) ? (string) $_GET['level'] : null;
$grade = isset($d['grade_stats'][$_GET['grade'] ?? '']) ? (string) $_GET['grade'] : null;
if ($grade !== null) {
    $level = $d['grade_stats'][$grade]['level'];
}

$grades = $a->gradeStats($level);
if ($grade !== null) {
    $grades = [$grade => $grades[$grade]];
}
$rooms = $a->rooms($level, $grade);
$students = array_sum(array_column($grades, 'students'));
$male = array_sum(array_column($grades, 'male'));
$female = array_sum(array_column($grades, 'female'));
$att = $students > 0 ? array_sum(array_map(static fn ($g) => $g['attendance'] * $g['students'], $grades)) / $students : 0;
$attPrev = $students > 0 ? array_sum(array_map(static fn ($g) => $g['att_prev'] * $g['students'], $grades)) / $students : 0;
$follow = array_sum(array_column($grades, 'high')) + array_sum(array_column($grades, 'medium'));
$transfers = $a->transfers();
$months = $a->months();
$scopeLabel = $grade !== null ? 'ชั้น ' . $d['grade_stats'][$grade]['label'] : ($level !== null ? 'ระดับ' . $levels[$level] : 'ทั้งโรงเรียน');

// Attendance trend for the selected scope (weighted by grade size).
$attSeries = [];
foreach ($months as $m) {
    $sum = $w = 0;
    foreach ($grades as $key => $g) {
        $v = $d['attendance']['grades'][$key][$m];
        if ($v !== null) {
            $sum += $v * $g['students'];
            $w += $g['students'];
        }
    }
    $attSeries[] = $w > 0 ? round($sum / $w, 1) : null;
}

$byGradeChart = [
    'type' => 'bar',
    'labels' => array_values(array_map(static fn ($g) => $g['label'], $grades)),
    'datasets' => [
        ['label' => 'ชาย', 'data' => array_values(array_column($grades, 'male')), 'color' => 'primary', 'stack' => 's'],
        ['label' => 'หญิง', 'data' => array_values(array_column($grades, 'female')), 'color' => 'yellow', 'stack' => 's'],
    ],
    'options' => ['stacked' => true, 'suffix' => ' คน', 'legend' => true],
];
$attGradeChart = [
    'type' => 'bar',
    'labels' => array_values(array_map(static fn ($g) => $g['label'], $grades)),
    'datasets' => [
        ['label' => 'ภาคเรียน ' . SchoolData::PREV_TERM, 'data' => array_values(array_column($grades, 'att_prev')), 'color' => 'sky'],
        ['label' => 'ภาคเรียน ' . SchoolData::CURRENT_TERM, 'data' => array_values(array_column($grades, 'attendance')), 'color' => 'green'],
    ],
    'options' => ['yMin' => 88, 'yMax' => 100, 'suffix' => '%', 'decimals' => 1, 'legend' => true],
];
$trendChart = [
    'type' => 'line',
    'labels' => array_map(static fn ($m) => ais_month($m), $months),
    'datasets' => [
        ['label' => 'อัตราเข้าเรียน ' . $scopeLabel, 'data' => $attSeries, 'color' => 'green', 'fill' => true],
        ['label' => 'เป้าหมาย 96%', 'data' => array_fill(0, count($months), SchoolAnalytics::ATTENDANCE_TARGET), 'color' => 'gray', 'dashed' => true],
    ],
    'options' => ['yMin' => 86, 'yMax' => 100, 'suffix' => '%', 'decimals' => 1, 'legend' => true],
];

$followList = array_slice(array_values(array_filter($a->followUpStudents($level), static fn ($s) => $grade === null || $s['grade'] === $grade)), 0, 6);

ais_component('page-head', [
    'eyebrow' => ais_icon('graduation-cap', 'icon-xs') . ' Student Intelligence',
    'title'   => 'ข้อมูลนักเรียน — ' . $scopeLabel,
    'desc'    => 'จำนวนนักเรียน การเข้าเรียน การย้ายเข้า–ย้ายออก และนักเรียนที่ควรติดตาม (' . $a->periodLabel() . ')',
]);
?>
<form class="filters" method="get" action="./" data-autosubmit>
    <input type="hidden" name="page" value="students">
    <span class="filters__label"><?= ais_icon('filter', 'icon-xs') ?> ตัวกรอง</span>
    <select class="select select--pill" name="level" aria-label="ระดับการศึกษา">
        <option value="">ทุกระดับ</option>
<?php foreach ($levels as $key => $label): ?>
        <option value="<?= e($key) ?>"<?= $key === $level ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
    </select>
    <select class="select select--pill" name="grade" aria-label="ระดับชั้น">
        <option value="">ทุกระดับชั้น</option>
<?php foreach ($d['grade_stats'] as $key => $g): if ($level !== null && $g['level'] !== $level) { continue; } ?>
        <option value="<?= e($key) ?>"<?= $key === $grade ? ' selected' : '' ?>><?= e($g['label']) ?></option>
<?php endforeach; ?>
    </select>
    <span class="badge badge--primary"><?= ais_icon('calendar-days') ?><?= e($a->periodLabel()) ?></span>
<?php if ($level !== null || $grade !== null): ?>
    <a class="btn btn--ghost btn--sm" href="<?= e(ais_url('students')) ?>"><?= ais_icon('x') ?>ล้างตัวกรอง</a>
<?php endif; ?>
</form>

<div class="mini-stats">
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('users') ?></span><div><p class="mini-stat__label">นักเรียน (<?= e($scopeLabel) ?>)</p><p class="mini-stat__value"><?= ais_num($students) ?><small>คน</small></p></div></div>
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('users-round') ?></span><div><p class="mini-stat__label">ชาย / หญิง</p><p class="mini-stat__value"><?= ais_num($male) ?> / <?= ais_num($female) ?></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('calendar-check') ?></span><div><p class="mini-stat__label">อัตราเข้าเรียน (ภาคเรียน <?= e(SchoolData::CURRENT_TERM) ?>)</p><p class="mini-stat__value"><?= ais_num($att, 1) ?><small>% (<?= e(sprintf('%+.1f', $att - $attPrev)) ?>)</small></p></div></div>
    <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('heart-handshake') ?></span><div><p class="mini-stat__label">นักเรียนที่ต้องติดตาม</p><p class="mini-stat__value"><?= ais_num($follow) ?><small>คน</small></p></div></div>
</div>

<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">จำนวนนักเรียนตามระดับชั้น</h3><p class="card__sub">แยกชาย–หญิง ปีการศึกษา <?= e(ais_config('school.academic_year')) ?></p></div></header>
        <?= ais_chart('chart-by-grade', $byGradeChart, 280, 'กราฟจำนวนนักเรียนตามระดับชั้น') ?>
    </article>
    <article class="span-5 card">
        <header class="card__head">
            <div><h3 class="card__title">แนวโน้มจำนวนนักเรียน</h3><p class="card__sub">ทั้งโรงเรียน · ย้ายเข้า <?= $transfers['in'] ?> คน · ย้ายออก <?= $transfers['out'] ?> คน</p></div>
        </header>
        <?= ais_chart('chart-enrollment', $a->enrollmentChart(), 280, 'กราฟแนวโน้มจำนวนนักเรียน') ?>
    </article>
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">สถิติการเข้าเรียนรายเดือน</h3><p class="card__sub"><?= e($scopeLabel) ?> · เดือนเมษายนเป็นช่วงปิดภาคเรียน</p></div></header>
        <?= ais_chart('chart-att-trend', $trendChart, 260, 'กราฟอัตราการเข้าเรียนรายเดือน') ?>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">การเข้าเรียนรายระดับชั้น</h3><p class="card__sub">เทียบภาคเรียนก่อนหน้า</p></div></header>
        <?= ais_chart('chart-att-grade', $attGradeChart, 260, 'กราฟการเข้าเรียนรายระดับชั้น') ?>
    </article>

    <article class="span-8 card">
        <header class="card__head">
            <div><h3 class="card__title">จำนวนนักเรียนตามห้องเรียน</h3><p class="card__sub">แสดง <span data-table-count="room-table"><?= count($rooms) ?></span> ห้อง · คลิกหัวตารางเพื่อเรียงลำดับ</p></div>
        </header>
        <div class="toolbar">
            <label class="search"><?= ais_icon('search') ?><input type="search" placeholder="ค้นหาห้อง หรือครูประจำชั้น" data-table-search="room-table" aria-label="ค้นหาห้องเรียน"></label>
        </div>
        <div class="table-wrap">
            <table class="table" id="room-table" data-table>
                <thead><tr>
                    <th data-sort="text">ห้อง</th>
                    <th data-sort="text">ครูประจำชั้น</th>
                    <th data-sort="num" class="num">นักเรียน</th>
                    <th data-sort="num" class="num">ชาย/หญิง</th>
                    <th data-sort="num">อัตราเข้าเรียน</th>
                    <th data-sort="num" class="num">GPA</th>
                    <th data-sort="num" class="num">ต้องติดตาม</th>
                </tr></thead>
                <tbody>
<?php foreach ($rooms as $r):
    $tone = $r['attendance'] < 92.5 ? 'orange' : ($r['attendance'] >= 95 ? 'green' : ''); ?>
                    <tr>
                        <td><strong><?= e($r['room']) ?></strong></td>
                        <td><?= e($r['homeroom_name']) ?></td>
                        <td class="num"><?= $r['students'] ?></td>
                        <td class="num" data-value="<?= $r['male'] ?>"><?= $r['male'] ?> / <?= $r['female'] ?></td>
                        <td data-value="<?= $r['attendance'] ?>"><div class="bar-cell"><div class="bar bar--<?= e($tone ?: 'blue') ?>"><span style="width:<?= max(0, ($r['attendance'] - 85) / 15 * 100) ?>%"></span></div><span class="num"><?= ais_num($r['attendance'], 1) ?>%</span></div></td>
                        <td class="num" data-value="<?= $r['gpa'] ?? 0 ?>"><?= $r['gpa'] !== null ? ais_num($r['gpa'], 2) : '<span class="muted">พัฒนาการ</span>' ?></td>
                        <td class="num" data-value="<?= $r['high'] + $r['medium'] ?>"><?php if ($r['high'] + $r['medium'] > 0): ?><span class="badge badge--<?= $r['high'] > 2 ? 'high' : 'medium' ?>"><?= $r['high'] + $r['medium'] ?> คน</span><?php else: ?>–<?php endif; ?></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="span-4 card">
        <header class="card__head">
            <div><h3 class="card__title">นักเรียนที่ต้องติดตาม</h3><p class="card__sub"><?= e($scopeLabel) ?> · เรียงตามความเร่งด่วน</p></div>
            <a class="btn-icon" href="<?= e(ais_url('student-risk')) ?>" aria-label="ไปหน้าวิเคราะห์นักเรียน"><?= ais_icon('arrow-up-right') ?></a>
        </header>
<?php if ($followList === []): ?>
        <p class="muted">ไม่พบนักเรียนที่ต้องติดตามในกลุ่มนี้</p>
<?php else: ?>
        <ul class="list-rows">
<?php foreach ($followList as $s): ?>
            <li>
                <span class="initials"><?= e(mb_substr($s['first'], 0, 1)) ?></span>
                <div class="grow"><strong data-private><?= e($s['name']) ?></strong><small><?= e($s['room']) ?> · <?= e($s['factors'][0]['label'] ?? '') ?></small></div>
                <span class="badge badge--<?= e($s['risk']) ?>"><?= e(ais_risk_label($s['risk'])) ?></span>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <a class="link-arrow" style="margin-top:12px" href="<?= e(ais_url('student-risk')) ?>">ดูแผนช่วยเหลือทั้งหมด<?= ais_icon('arrow-right', 'icon-xs') ?></a>
    </article>
</section>
<?php
tb_log('Page', 'students', 'END', ['rooms' => count($rooms)]);
