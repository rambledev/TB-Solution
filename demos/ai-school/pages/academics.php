<?php

declare(strict_types=1);

/**
 * Academic Analytics.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'academics', 'START');

$d = $a->data();
$ac = $d['academics'];
$cur = SchoolData::CURRENT_TERM;
$prev = SchoolData::PREV_TERM;
$subjects = SchoolData::SUBJECTS;
$gradeKeys = array_keys($ac['gpa'][$cur]);
$students = array_sum(array_map(static fn ($k) => $d['grade_stats'][$k]['students'], $gradeKeys));
$fails = array_sum(array_column($ac['subjects'][$cur], 'fail_count'));
$failsPrev = array_sum(array_column($ac['subjects'][$prev], 'fail_count'));
$enrolments = array_sum(array_column($ac['subjects'][$cur], 'students'));
$passRate = 100 - $fails / $enrolments * 100;
$passRatePrev = 100 - $failsPrev / $enrolments * 100;

// Subjects that need improvement: score dropped or pass rate below 93%.
$improve = [];
foreach ($subjects as $k => $s) {
    $diff = $ac['subjects'][$cur][$k]['score'] - $ac['subjects'][$prev][$k]['score'];
    if ($diff < -0.5 || $ac['subjects'][$cur][$k]['pass_rate'] < 93) {
        $improve[$k] = ['name' => $s['name'], 'diff' => $diff] + $ac['subjects'][$cur][$k];
    }
}
uasort($improve, static fn ($x, $y) => $x['pass_rate'] <=> $y['pass_rate']);

$gpaTrend = [
    'type' => 'line',
    'labels' => array_map(static fn ($t) => 'ภาคเรียน ' . $t, SchoolData::TERMS),
    'datasets' => [
        ['label' => 'ทั้งโรงเรียน', 'data' => array_values($ac['school_gpa']), 'color' => 'primary', 'fill' => true],
    ],
    'options' => ['decimals' => 2, 'yMin' => 2.5, 'yMax' => 3.3, 'legend' => true],
];
foreach (['primary' => ['P1', 'P6', 'sky'], 'secondary' => ['M1', 'M6', 'deep']] as $level => [$from, $to, $color]) {
    $keys = array_filter($gradeKeys, static fn ($k) => $d['grade_stats'][$k]['level'] === $level);
    $series = [];
    foreach (SchoolData::TERMS as $t) {
        $w = $sum = 0;
        foreach ($keys as $k) {
            $sum += $ac['gpa'][$t][$k] * $d['grade_stats'][$k]['students'];
            $w += $d['grade_stats'][$k]['students'];
        }
        $series[] = round($sum / $w, 2);
    }
    $gpaTrend['datasets'][] = ['label' => SchoolData::LEVELS[$level], 'data' => $series, 'color' => $color];
}

$passChart = [
    'type' => 'bar',
    'labels' => array_values(array_map(static fn ($s) => $s['short'], $subjects)),
    'datasets' => [
        ['label' => 'ผ่าน', 'data' => array_values(array_map(static fn ($k) => $ac['subjects'][$cur][$k]['students'] - $ac['subjects'][$cur][$k]['fail_count'], array_keys($subjects))), 'color' => 'green', 'stack' => 'p'],
        ['label' => 'ไม่ผ่าน', 'data' => array_values(array_map(static fn ($k) => $ac['subjects'][$cur][$k]['fail_count'], array_keys($subjects))), 'color' => 'orange', 'stack' => 'p'],
    ],
    'options' => ['stacked' => true, 'suffix' => ' คน', 'legend' => true],
];

$heat = static function (float $score): string {
    $t = max(0, min(1, ($score - 55) / 35));
    $r = (int) round(255 + (234 - 255) * $t);
    $g = (int) round(236 + (244 - 236) * $t);
    $b = (int) round(220 + (255 - 220) * $t);
    return $score < 62 ? '#FDE2E2' : sprintf('#%02X%02X%02X', $r, $g, $b);
};

ais_component('page-head', [
    'eyebrow' => ais_icon('book-open-check', 'icon-xs') . ' Academic Analytics',
    'title'   => 'ผลสัมฤทธิ์ทางการเรียน',
    'desc'    => 'GPA ผลสัมฤทธิ์รายวิชาและระดับชั้น อัตราผ่าน/ไม่ผ่าน และแนวโน้ม 3 ภาคเรียน (ระดับ ป.1–ม.6)',
]);
?>
<div class="mini-stats">
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('award') ?></span><div><p class="mini-stat__label">GPA เฉลี่ย ภาคเรียน <?= e($cur) ?></p><p class="mini-stat__value"><?= ais_num($ac['school_gpa'][$cur], 2) ?><small>(<?= e(sprintf('%+.2f', $ac['school_gpa'][$cur] - $ac['school_gpa'][$prev])) ?>)</small></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('circle-check') ?></span><div><p class="mini-stat__label">อัตราผ่านเฉลี่ยทุกวิชา</p><p class="mini-stat__value"><?= ais_num($passRate, 1) ?><small>% (<?= e(sprintf('%+.1f', $passRate - $passRatePrev)) ?>)</small></p></div></div>
    <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('circle-x') ?></span><div><p class="mini-stat__label">ผลการเรียนไม่ผ่าน (รายวิชา-คน)</p><p class="mini-stat__value"><?= ais_num($fails) ?><small>จาก <?= ais_num($enrolments) ?></small></p></div></div>
    <div class="mini-stat tone-red"><span class="mini-stat__icon"><?= ais_icon('triangle-alert') ?></span><div><p class="mini-stat__label">วิชาที่ต้องปรับปรุง</p><p class="mini-stat__value"><?= count($improve) ?><small>วิชา</small></p></div></div>
</div>

<section class="bento">
    <article class="span-8 card">
        <header class="card__head">
            <div><h3 class="card__title">เปรียบเทียบผลการเรียนระหว่างภาคเรียน</h3><p class="card__sub"><?= e($prev) ?> เทียบ <?= e($cur) ?></p></div>
            <div class="seg" data-chart-views="chart-academic">
                <button type="button" class="is-active" data-view="grade">GPA รายระดับชั้น</button>
                <button type="button" data-view="subject">คะแนนรายวิชา</button>
            </div>
        </header>
        <?= ais_chart('chart-academic', $a->academicChart(), 300, 'กราฟเปรียบเทียบผลการเรียน') ?>
    </article>
    <article class="span-4 card">
        <header class="card__head"><div><h3 class="card__title">แนวโน้ม GPA ย้อนหลัง</h3><p class="card__sub">3 ภาคเรียนล่าสุด</p></div></header>
        <?= ais_chart('chart-gpa-trend', $gpaTrend, 300, 'กราฟแนวโน้ม GPA') ?>
    </article>

    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">อัตราผ่านและไม่ผ่านรายวิชา</h3><p class="card__sub">ภาคเรียน <?= e($cur) ?> (จำนวนนักเรียน)</p></div></header>
        <?= ais_chart('chart-pass', $passChart, 300, 'กราฟอัตราผ่านไม่ผ่าน') ?>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">วิชาที่ต้องปรับปรุง</h3><p class="card__sub">คะแนนลดลงหรืออัตราผ่านต่ำกว่า 93%</p></div></header>
        <ul class="list-rows">
<?php foreach ($improve as $k => $s): ?>
            <li>
                <span class="initials" style="background:var(--c-orange-soft);color:#B86800"><?= ais_icon('book-marked') ?></span>
                <div class="grow"><strong><?= e($s['name']) ?></strong><small>คะแนนเฉลี่ย <?= ais_num($s['score'], 1) ?> (<?= e(sprintf('%+.1f', $s['diff'])) ?>) · ไม่ผ่าน <?= $s['fail_count'] ?> คน</small></div>
                <span class="badge badge--<?= $s['pass_rate'] < 90 ? 'high' : 'medium' ?>">ผ่าน <?= ais_num($s['pass_rate'], 1) ?>%</span>
            </li>
<?php endforeach; ?>
        </ul>
    </article>

    <article class="span-12 card">
        <header class="card__head">
            <div><h3 class="card__title">ผลสัมฤทธิ์แยกตามระดับชั้นและรายวิชา</h3><p class="card__sub">คะแนนเฉลี่ย ภาคเรียน <?= e($cur) ?> · สีแดง = ต่ำกว่า 62 · ตัวเลขเล็ก = เปลี่ยนแปลงจากภาคเรียนก่อน</p></div>
        </header>
        <div class="table-wrap">
            <table class="table heatmap" id="heat-table" data-table>
                <thead><tr>
                    <th data-sort="text">ระดับชั้น</th>
                    <th data-sort="num" class="num">GPA</th>
<?php foreach ($subjects as $s): ?>
                    <th data-sort="num" class="num"><?= e($s['short']) ?></th>
<?php endforeach; ?>
                </tr></thead>
                <tbody>
<?php foreach ($gradeKeys as $gk): ?>
                    <tr>
                        <td><strong><?= e($d['grade_stats'][$gk]['label']) ?></strong></td>
                        <td class="num"><?= ais_num($ac['gpa'][$cur][$gk], 2) ?></td>
<?php foreach (array_keys($subjects) as $sk):
    $c = $ac['grade_subject'][$cur][$gk][$sk];
    $diff = $c['score'] - $ac['grade_subject'][$prev][$gk][$sk]['score']; ?>
                        <td class="heat num" data-value="<?= $c['score'] ?>" style="background:<?= $heat($c['score']) ?>" title="ไม่ผ่าน <?= $c['fail_count'] ?> คน (<?= $c['fail_rate'] ?>%)"><?= ais_num($c['score'], 1) ?><br><small style="color:<?= $diff < -2 ? '#C2353A' : 'var(--c-subtle)' ?>"><?= e(sprintf('%+.1f', $diff)) ?></small></td>
<?php endforeach; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php
tb_log('Page', 'academics', 'END');
