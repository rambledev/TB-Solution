<?php

declare(strict_types=1);

/**
 * Teacher Intelligence.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'teachers', 'START');

$d = $a->data();
$sum = $a->teacherSummary();
$evals = $a->teacherEvaluations();
$depts = [];
foreach ($evals as $t) {
    $depts[$t['dept_label']][] = $t;
}
$avg = static fn (array $list, string $key) => round(array_sum(array_column($list, $key)) / max(1, count($list)), 1);

$deptChart = [
    'type' => 'bar',
    'labels' => array_keys($depts),
    'datasets' => [
        ['label' => 'ชั่วโมงสอนเฉลี่ย/สัปดาห์', 'data' => array_values(array_map(static fn ($l) => $avg($l, 'hours'), $depts)), 'color' => 'primary'],
        ['label' => 'ดัชนีภาระงานรวม', 'data' => array_values(array_map(static fn ($l) => $avg($l, 'load_index'), $depts)), 'color' => 'yellow'],
    ],
    'options' => ['decimals' => 1, 'legend' => true, 'horizontal' => true],
];

$buckets = ['≤ 15' => 0, '16–18' => 0, '19–21' => 0, '22–23' => 0, '≥ 24' => 0];
foreach ($evals as $t) {
    $h = $t['hours'];
    $key = $h <= 15 ? '≤ 15' : ($h <= 18 ? '16–18' : ($h <= 21 ? '19–21' : ($h <= 23 ? '22–23' : '≥ 24')));
    $buckets[$key]++;
}
$hoursChart = [
    'type' => 'bar',
    'labels' => array_map(static fn ($k) => $k . ' ชม.', array_keys($buckets)),
    'datasets' => [['label' => 'จำนวนครู', 'data' => array_values($buckets), 'colors' => ['sky', 'primary', 'primary', 'orange', 'red']]],
    'options' => ['suffix' => ' คน'],
];
$devChart = [
    'type' => 'bar',
    'labels' => array_keys($depts),
    'datasets' => [
        ['label' => 'บันทึกการสอน (%)', 'data' => array_values(array_map(static fn ($l) => $avg($l, 'lesson_log'), $depts)), 'color' => 'green'],
        ['label' => 'ชั่วโมงอบรมเฉลี่ย', 'data' => array_values(array_map(static fn ($l) => $avg($l, 'training'), $depts)), 'color' => 'deep'],
    ],
    'options' => ['decimals' => 1, 'legend' => true],
];

ais_component('page-head', [
    'eyebrow' => ais_icon('users', 'icon-xs') . ' Teacher Intelligence',
    'title'   => 'ข้อมูลครูและบุคลากร',
    'desc'    => 'ภาระงาน ชั่วโมงสอน การบันทึกการสอน การพัฒนาตนเอง และผลงาน เพื่อวางแผนกำลังคนอย่างสมดุล',
    'actions' => '<a class="btn btn--primary" href="' . e(ais_url('teacher-evaluation')) . '">' . ais_icon('clipboard-check') . 'AI Teacher Evaluation</a>',
]);
?>
<div class="mini-stats">
    <div class="mini-stat tone-deep"><span class="mini-stat__icon"><?= ais_icon('users') ?></span><div><p class="mini-stat__label">ครู / บุคลากรสนับสนุน</p><p class="mini-stat__value"><?= $sum['teachers'] ?> / <?= $sum['staff'] ?><small>คน</small></p></div></div>
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('clock') ?></span><div><p class="mini-stat__label">ชั่วโมงสอนเฉลี่ย</p><p class="mini-stat__value"><?= ais_num($sum['avg_hours'], 1) ?><small>ชม./สัปดาห์</small></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('notebook-pen') ?></span><div><p class="mini-stat__label">บันทึกการสอนครบถ้วน</p><p class="mini-stat__value"><?= ais_num($sum['lesson_log'], 1) ?><small>%</small></p></div></div>
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('award') ?></span><div><p class="mini-stat__label">ชั่วโมงอบรมเฉลี่ย / ผลงานรวม</p><p class="mini-stat__value"><?= ais_num($sum['training'], 1) ?><small>ชม. · <?= $sum['works'] ?> ผลงาน</small></p></div></div>
</div>

<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">ภาระงานเฉลี่ยรายกลุ่มสาระ</h3><p class="card__sub">ดัชนีภาระงาน = ชั่วโมงสอน + (งานพิเศษ × 1.5)</p></div></header>
        <?= ais_chart('chart-dept', $deptChart, 320, 'กราฟภาระงานรายกลุ่มสาระ') ?>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">การกระจายชั่วโมงสอน</h3><p class="card__sub">ครู <?= $sum['high_load'] ?> คนภาระงานสูงกว่าค่าเฉลี่ยเกิน 20%</p></div></header>
        <?= ais_chart('chart-hours', $hoursChart, 320, 'กราฟการกระจายชั่วโมงสอน') ?>
    </article>
    <article class="span-12 card">
        <header class="card__head"><div><h3 class="card__title">การบันทึกการสอนและการพัฒนาตนเอง</h3><p class="card__sub">ภาคเรียน <?= e(SchoolData::CURRENT_TERM) ?> · เป้าหมายอบรม 40 ชม./ปี</p></div></header>
        <?= ais_chart('chart-dev', $devChart, 260, 'กราฟการบันทึกการสอนและชั่วโมงอบรม') ?>
    </article>

    <article class="span-12 card">
        <header class="card__head">
            <div><h3 class="card__title">ภาพรวมการปฏิบัติงานรายบุคคล</h3><p class="card__sub">แสดง <span data-table-count="teacher-table"><?= count($evals) ?></span> คน · คลิกแถวเพื่อดูการวิเคราะห์รายบุคคล</p></div>
        </header>
        <div class="toolbar">
            <label class="search"><?= ais_icon('search') ?><input type="search" placeholder="ค้นหาชื่อครู หรือกลุ่มสาระ" data-table-search="teacher-table" aria-label="ค้นหาครู"></label>
            <select class="select select--pill" data-table-filter="teacher-table" data-filter-key="dept" aria-label="กลุ่มสาระ">
                <option value="*">ทุกกลุ่มสาระ</option>
<?php foreach (array_keys($depts) as $dept): ?>
                <option value="<?= e($dept) ?>"><?= e($dept) ?></option>
<?php endforeach; ?>
            </select>
        </div>
        <div class="table-wrap">
            <table class="table" id="teacher-table" data-table>
                <thead><tr>
                    <th data-sort="text">ครู</th>
                    <th data-sort="text">กลุ่มสาระ</th>
                    <th data-sort="num" class="num">ชั่วโมงสอน</th>
                    <th data-sort="num" class="num">งานพิเศษ</th>
                    <th data-sort="num">ภาระงานเทียบค่าเฉลี่ย</th>
                    <th data-sort="num" class="num">บันทึกการสอน</th>
                    <th data-sort="num" class="num">อบรม (ชม.)</th>
                    <th data-sort="num" class="num">ผลงาน</th>
                </tr></thead>
                <tbody>
<?php foreach ($evals as $t):
    $tone = $t['load_vs_avg'] >= 20 ? 'red' : ($t['load_vs_avg'] >= 8 ? 'orange' : 'green'); ?>
                    <tr data-row-click data-href="<?= e(ais_url('teacher-evaluation') . '#' . $t['id']) ?>" data-dept="<?= e($t['dept_label']) ?>">
                        <td><div class="cell-main"><span class="initials"><?= e(mb_substr(str_replace('ครู', '', $t['short']), 0, 1)) ?></span><div><strong><?= e($t['name']) ?></strong><small><?= e($t['position']) ?> · <?= $t['years'] ?> ปี</small></div></div></td>
                        <td><?= e($t['dept_label']) ?></td>
                        <td class="num"><?= $t['hours'] ?></td>
                        <td class="num"><?= $t['duties'] ?></td>
                        <td data-value="<?= $t['load_vs_avg'] ?>"><span class="badge badge--<?= $tone ?>"><?= e(sprintf('%+d%%', $t['load_vs_avg'])) ?></span></td>
                        <td class="num" data-value="<?= $t['lesson_log'] ?>"><?= $t['lesson_log'] ?>%</td>
                        <td class="num"><?= $t['training'] ?></td>
                        <td class="num"><?= $t['works'] ?></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php
tb_log('Page', 'teachers', 'END');
