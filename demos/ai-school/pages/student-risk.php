<?php

declare(strict_types=1);

/**
 * Student Risk Analysis — support-oriented follow-up list.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'student-risk', 'START', ['level' => $_GET['level'] ?? null]);

$d = $a->data();
$counts = $a->riskCounts();
$prev = $a->riskCounts(true);
$focus = in_array($_GET['level'] ?? '', ['high', 'medium', 'low'], true) ? (string) $_GET['level'] : '*';
$list = $a->followUpStudents();
$factors = $a->riskFactorCounts();
$total = count($d['students']);

// Low-risk students are not listed individually; show a sample so the filter still works.
$lowSample = array_slice(array_values(array_filter($d['students'], static fn ($s) => $s['risk'] === 'low' && $s['risk_score'] > 0)), 0, 12);
$rows = array_merge($list, $lowSample);

$byGrade = [
    'type' => 'bar',
    'labels' => array_values(array_map(static fn ($g) => $g['label'], $d['grade_stats'])),
    'datasets' => [
        ['label' => 'ต้องดูแลใกล้ชิด', 'data' => array_values(array_column($d['grade_stats'], 'high')), 'color' => 'red', 'stack' => 'r'],
        ['label' => 'ควรติดตาม', 'data' => array_values(array_column($d['grade_stats'], 'medium')), 'color' => 'orange', 'stack' => 'r'],
    ],
    'options' => ['stacked' => true, 'suffix' => ' คน', 'legend' => true],
];
$factorChart = [
    'type' => 'bar',
    'labels' => array_column($factors, 'label'),
    'datasets' => [['label' => 'จำนวนนักเรียน', 'data' => array_column($factors, 'count'), 'color' => 'primary']],
    'options' => ['horizontal' => true, 'suffix' => ' คน'],
];

$studentJson = [];
foreach ($rows as $s) {
    $studentJson[$s['id']] = [
        'id' => $s['id'], 'code' => $s['code'], 'name' => $s['name'], 'room' => $s['room'], 'grade' => $s['grade_label'],
        'risk' => $s['risk'], 'risk_label' => ais_risk_label($s['risk']), 'score' => $s['risk_score'],
        'attendance' => $s['attendance'], 'att_prev' => $s['att_prev'], 'absent' => $s['absent_days'],
        'gpa' => $s['gpa'], 'gpa_prev' => $s['gpa_prev'], 'development' => $s['development'],
        'submission' => $s['submission'], 'notes' => $s['notes'],
        'factors' => array_column($s['factors'], 'label'),
        'plan' => SchoolAnalytics::supportPlan($s),
        'homeroom' => $s['homeroom'] !== null ? $d['teachers'][$s['homeroom']]['name'] : '-',
    ];
}

$cards = [
    ['key' => '*', 'tone' => 'primary', 'icon' => 'users', 'label' => 'นักเรียนทั้งหมด', 'value' => $total, 'note' => 'ปีการศึกษา ' . ais_config('school.academic_year')],
    ['key' => 'high', 'tone' => 'high', 'icon' => 'siren', 'label' => 'ต้องดูแลใกล้ชิด (High)', 'value' => $counts['high'], 'note' => sprintf('ภาคเรียนก่อน %d คน', $prev['high'])],
    ['key' => 'medium', 'tone' => 'medium', 'icon' => 'eye', 'label' => 'ควรติดตาม (Medium)', 'value' => $counts['medium'], 'note' => sprintf('ภาคเรียนก่อน %d คน', $prev['medium'])],
    ['key' => 'low', 'tone' => 'low', 'icon' => 'smile', 'label' => 'ปกติ (Low)', 'value' => $counts['low'], 'note' => sprintf('%.1f%% ของนักเรียนทั้งหมด', $counts['low'] / $total * 100)],
];

ais_component('page-head', [
    'eyebrow' => ais_icon('heart-handshake', 'icon-xs') . ' Student Risk Analysis',
    'title'   => 'นักเรียนที่ควรได้รับการติดตามและช่วยเหลือ',
    'desc'    => 'วิเคราะห์จากการขาดเรียน ผลการเรียน การส่งงาน พัฒนาการ และบันทึกการดูแลช่วยเหลือ เพื่อวางแผนสนับสนุนรายบุคคล',
]);
?>
<div class="support-note">
    <?= ais_icon('hand-heart') ?>
    <div><strong>ระบบนี้มีไว้เพื่อการดูแลช่วยเหลือ ไม่ใช่การตีตรานักเรียน</strong> — ระดับ High/Medium/Low หมายถึงระดับความเร่งด่วนในการให้การสนับสนุน ข้อมูลควรเปิดเผยเฉพาะครูประจำชั้น ครูแนะแนว และผู้บริหารที่เกี่ยวข้อง ชื่อทั้งหมดในเดโมเป็นชื่อสมมติ</div>
</div>

<div class="risk-cards" role="group" aria-label="กรองตามระดับ">
<?php foreach ($cards as $c): ?>
    <button type="button" class="risk-card risk-card--<?= e($c['tone']) ?><?= $focus === $c['key'] ? ' is-active' : '' ?>" data-table-filter="risk-table" data-filter-key="risk" data-filter-value="<?= e($c['key']) ?>" aria-pressed="<?= $focus === $c['key'] ? 'true' : 'false' ?>">
        <span class="risk-card__top"><span class="badge badge--<?= e($c['tone']) ?>"><?= ais_icon($c['icon']) ?><?= e($c['key'] === '*' ? 'ทั้งหมด' : strtoupper($c['key'])) ?></span></span>
        <span class="risk-card__value"><?= ais_num($c['value']) ?></span>
        <span class="risk-card__label"><?= e($c['label']) ?></span>
        <small class="muted"><?= e($c['note']) ?></small>
    </button>
<?php endforeach; ?>
</div>

<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">จำนวนนักเรียนที่ต้องติดตามตามระดับชั้น</h3><p class="card__sub">ภาคเรียน <?= e(SchoolData::CURRENT_TERM) ?></p></div></header>
        <?= ais_chart('chart-risk-grade', $byGrade, 280, 'กราฟนักเรียนที่ต้องติดตามตามระดับชั้น') ?>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">สาเหตุที่ตรวจพบ</h3><p class="card__sub">นักเรียน 1 คนอาจมีมากกว่า 1 ปัจจัย</p></div></header>
        <?= ais_chart('chart-factors', $factorChart, 280, 'กราฟสาเหตุที่ตรวจพบ') ?>
    </article>

    <article class="span-12 card" id="risk-list">
        <header class="card__head">
            <div><h3 class="card__title">รายชื่อนักเรียนและแนวทางช่วยเหลือ</h3><p class="card__sub">แสดง <span data-table-count="risk-table"><?= count($rows) ?></span> คน · คลิกที่แถวเพื่อดูแผนช่วยเหลือรายบุคคล<?= $focus === 'low' || $focus === '*' ? ' · กลุ่มปกติแสดงเฉพาะตัวอย่าง' : '' ?></p></div>
            <label class="switch"><input type="checkbox" data-privacy-toggle="#risk-list"><span class="switch__track"></span>ปกปิดชื่อ</label>
        </header>
        <div class="toolbar">
            <label class="search"><?= ais_icon('search') ?><input type="search" placeholder="ค้นหาชื่อ รหัส หรือห้อง" data-table-search="risk-table" aria-label="ค้นหานักเรียน"></label>
            <select class="select select--pill" data-table-filter="risk-table" data-filter-key="level" aria-label="ระดับการศึกษา">
                <option value="*">ทุกระดับ</option>
<?php foreach (SchoolData::LEVELS as $k => $label): ?>
                <option value="<?= e($k) ?>"><?= e($label) ?></option>
<?php endforeach; ?>
            </select>
        </div>
        <div class="table-wrap">
            <table class="table" id="risk-table" data-table>
                <thead><tr>
                    <th data-sort="text">นักเรียน</th>
                    <th data-sort="text">ห้อง</th>
                    <th data-sort="num">ระดับ</th>
                    <th data-sort="num" class="num">เข้าเรียน</th>
                    <th data-sort="num" class="num">GPA</th>
                    <th data-sort="num" class="num">ส่งงาน</th>
                    <th>สาเหตุที่ตรวจพบ</th>
                    <th></th>
                </tr></thead>
                <tbody>
<?php foreach ($rows as $s): ?>
                    <tr data-row-click data-student="<?= e($s['id']) ?>" data-risk="<?= e($s['risk']) ?>" data-level="<?= e($s['level']) ?>" data-search="<?= e(mb_strtolower($s['name'] . ' ' . $s['code'] . ' ' . $s['room'])) ?>">
                        <td><div class="cell-main"><span class="initials"><?= e(mb_substr($s['first'], 0, 1)) ?></span><div><strong data-private><?= e($s['name']) ?></strong><small><?= e($s['code']) ?></small></div></div></td>
                        <td><?= e($s['room']) ?></td>
                        <td data-value="<?= $s['risk_score'] ?>"><span class="badge badge--<?= e($s['risk']) ?>"><?= e(ais_risk_label($s['risk'])) ?></span></td>
                        <td class="num" data-value="<?= $s['attendance'] ?>"><?= ais_num($s['attendance'], 1) ?>%</td>
                        <td class="num" data-value="<?= $s['gpa'] ?? $s['development'] / 25 ?>"><?= $s['gpa'] !== null ? ais_num($s['gpa'], 2) : '<small class="muted">พัฒนาการ ' . $s['development'] . '</small>' ?></td>
                        <td class="num" data-value="<?= $s['submission'] ?>"><?= $s['submission'] ?>%</td>
                        <td><div class="factor-tags"><?php foreach ($s['factors'] as $f): ?><span><?= e(['attendance' => 'ขาดเรียน', 'academic' => 'ผลการเรียน', 'progress' => 'พัฒนาการลดลง', 'submission' => 'ส่งงาน', 'behavior' => 'การดูแล'][$f['key']]) ?></span><?php endforeach; ?><?php if ($s['factors'] === []): ?><span>–</span><?php endif; ?></div></td>
                        <td><button type="button" class="btn btn--soft btn--xs" data-student-open="<?= e($s['id']) ?>"><?= ais_icon('clipboard-list') ?>แผนช่วยเหลือ</button></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<script type="application/json" id="student-data"><?= ais_json($studentJson) ?></script>
<?php
tb_log('Page', 'student-risk', 'END', ['rows' => count($rows)]);
