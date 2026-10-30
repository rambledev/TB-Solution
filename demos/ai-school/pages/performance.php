<?php

declare(strict_types=1);

/**
 * School Performance — 6 KPIs vs targets, monthly comparison.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'performance', 'START');

$kpis = $a->kpiPerformance();
$months = $a->months();
$labels = array_map(static fn ($m) => ais_month($m), $months);
$last = $a->latestMonth();
$met = count(array_filter($kpis, static fn ($k) => $k['met']));
$colors = ['academic' => 'primary', 'attendance' => 'green', 'student_dev' => 'yellow', 'teacher_dev' => 'deep', 'operations' => 'sky', 'parent_sat' => 'orange'];

// One view per KPI for the monthly chart; the first is the default.
$views = [];
foreach ($kpis as $key => $k) {
    $views[$key] = [
        'label' => $k['th'],
        'type' => 'line',
        'labels' => $labels,
        'datasets' => [
            ['label' => $k['th'], 'data' => $k['period'], 'color' => $colors[$key], 'fill' => true],
            ['label' => 'เป้าหมาย ' . $k['target'], 'data' => array_fill(0, count($months), $k['target']), 'color' => 'gray', 'dashed' => true],
        ],
        'options' => ['decimals' => 1, 'legend' => true, 'yMin' => floor(min(array_filter($k['period'])) - 3), 'yMax' => min(100, ceil(max($k['target'], max($k['period'])) + 2))],
    ];
}
$firstKey = array_key_first($kpis);
$monthly = $views[$firstKey] + ['views' => $views];

$compare = [
    'type' => 'bar',
    'labels' => array_values(array_map(static fn ($k) => $k['th'], $kpis)),
    'datasets' => [
        ['label' => 'ผลเดือนล่าสุด', 'data' => array_values(array_column($kpis, 'current')), 'colors' => array_values(array_map(static fn ($k) => $k['met'] ? 'green' : 'primary', $kpis))],
        ['label' => 'เป้าหมาย', 'type' => 'line', 'data' => array_values(array_column($kpis, 'target')), 'color' => 'yellow', 'dashed' => true],
    ],
    'options' => ['yMin' => 60, 'yMax' => 100, 'decimals' => 1, 'legend' => true],
];
$overall = [
    'type' => 'line',
    'labels' => $labels,
    'datasets' => [['label' => 'คะแนนภาพรวมโรงเรียน', 'data' => array_map(fn ($m) => $a->schoolScore($m), $months), 'color' => 'deep', 'fill' => true]],
    'options' => ['decimals' => 1, 'yMin' => 70, 'yMax' => 95],
];

ais_component('page-head', [
    'eyebrow' => ais_icon('gauge', 'icon-xs') . ' School Performance',
    'title'   => 'ผลการดำเนินงานของโรงเรียน',
    'desc'    => sprintf('KPI 6 ด้านเทียบเป้าหมาย · ผ่านเป้าหมายแล้ว %d จาก %d ด้าน (ข้อมูลเดือน %s)', $met, count($kpis), ais_month($last)),
]);
?>
<div class="gauge-grid" role="group" aria-label="เลือก KPI">
<?php foreach ($kpis as $key => $k):
    $pct = $k['progress'];
    $c = 2 * M_PI * 30; ?>
    <button type="button" class="gauge<?= $key === $firstKey ? ' is-active' : '' ?>" data-kpi-select="<?= e($key) ?>">
        <svg width="76" height="76" viewBox="0 0 76 76" aria-hidden="true">
            <circle cx="38" cy="38" r="30" fill="none" stroke="#EAF1FB" stroke-width="8"/>
            <circle cx="38" cy="38" r="30" fill="none" stroke="<?= $k['met'] ? '#22A06B' : '#2878E8' ?>" stroke-width="8" stroke-linecap="round" stroke-dasharray="<?= round($c * $pct / 100, 1) ?> <?= round($c, 1) ?>" transform="rotate(-90 38 38)"/>
            <text x="38" y="43" text-anchor="middle" font-size="15" font-weight="700" fill="#173B75"><?= ais_num($k['current'], 1) ?></text>
        </svg>
        <span class="gauge__text">
            <span class="gauge__label"><?= e($k['label']) ?></span>
            <span class="gauge__title"><?= e($k['th']) ?></span>
            <span class="gauge__meta">
                <span class="badge badge--<?= $k['met'] ? 'good' : 'warn' ?>"><?= $k['met'] ? 'ผ่านเป้าหมาย' : 'เป้าหมาย ' . $k['target'] ?></span>
                <span class="trend trend--<?= e($k['delta']['tone']) ?>"><?= e($k['delta']['text']) ?></span>
            </span>
        </span>
    </button>
<?php endforeach; ?>
</div>

<section class="bento" style="margin-top:var(--gap)">
    <article class="span-8 card">
        <header class="card__head">
            <div><h3 class="card__title">เปรียบเทียบรายเดือนกับเป้าหมาย</h3><p class="card__sub" data-kpi-caption><?= e($kpis[$firstKey]['th']) ?> · <?= e($a->periodLabel()) ?></p></div>
        </header>
        <?= ais_chart('chart-kpi-monthly', $monthly, 300, 'กราฟ KPI รายเดือน') ?>
    </article>
    <article class="span-4 card">
        <header class="card__head"><div><h3 class="card__title">คะแนนภาพรวมโรงเรียน</h3><p class="card__sub">ค่าเฉลี่ย KPI 6 ด้าน</p></div></header>
        <?= ais_chart('chart-overall', $overall, 300, 'กราฟคะแนนภาพรวม') ?>
    </article>
    <article class="span-12 card">
        <header class="card__head"><div><h3 class="card__title">ผลเดือนล่าสุดเทียบเป้าหมาย</h3><p class="card__sub">แท่งสีเขียว = ผ่านเป้าหมาย</p></div></header>
        <?= ais_chart('chart-kpi-compare', $compare, 260, 'กราฟ KPI เทียบเป้าหมาย') ?>
    </article>
    <article class="span-12 card">
        <header class="card__head"><div><h3 class="card__title">ตารางข้อมูล KPI รายเดือน</h3><p class="card__sub"><?= e($a->periodRange()) ?></p></div></header>
        <div class="table-wrap">
            <table class="table" id="kpi-table" data-table>
                <thead><tr>
                    <th data-sort="text">KPI</th>
                    <th class="num">เป้าหมาย</th>
<?php foreach ($months as $m): ?>
                    <th class="num"><?= e(ais_month($m)) ?></th>
<?php endforeach; ?>
                </tr></thead>
                <tbody>
<?php foreach ($kpis as $k): ?>
                    <tr>
                        <td><strong><?= e($k['th']) ?></strong></td>
                        <td class="num"><?= $k['target'] ?></td>
<?php foreach ($k['period'] as $v): ?>
                        <td class="num" style="<?= $v !== null && $v >= $k['target'] ? 'color:#17784F;font-weight:600' : '' ?>"><?= ais_num($v, 1) ?></td>
<?php endforeach; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php
tb_log('Page', 'performance', 'END');
