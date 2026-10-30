<?php

declare(strict_types=1);

/**
 * Executive dashboard (Overview).
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'overview', 'START');

$summary = $a->executiveSummary();
$kpis = $a->kpiCards();
$insights = array_slice($a->insights(), 0, 3);
$school = ais_config('school');

ais_component('page-head', [
    'eyebrow' => ais_icon('sparkles', 'icon-xs') . ' Executive Dashboard · ' . e($a->periodLabel()) . ' (' . e($a->periodRange()) . ')',
    'title'   => 'สวัสดีครับ ผู้บริหาร ' . $school['name'],
    'desc'    => 'ภาพรวมการดำเนินงานของโรงเรียน พร้อมสรุปประเด็นสำคัญจาก AI เพื่อสนับสนุนการตัดสินใจ',
    'actions' => '<a class="btn btn--ghost" href="' . e(ais_url('reports')) . '">' . ais_icon('file-text') . 'รายงานผู้บริหาร</a>'
        . '<a class="btn btn--primary" href="' . e(ais_url('ai-insights')) . '">' . ais_icon('bot') . 'ถาม AI Assistant</a>',
]);
?>
<section class="bento">
    <div class="span-8 kpi-grid" aria-label="ตัวชี้วัดหลัก">
<?php foreach ($kpis as $kpi) {
    ais_component('kpi-card', ['kpi' => $kpi]);
} ?>
    </div>

    <article class="span-4 card card--ai" data-tabs>
        <header class="card__head">
            <div class="ai-title">
                <span class="ai-orb"><?= ais_icon('sparkles') ?></span>
                <div>
                    <h3 class="card__title">AI Executive Summary</h3>
                    <p class="card__sub">สรุปจากข้อมูล<?= e($a->periodLabel()) ?> · Rule-based simulation</p>
                </div>
            </div>
        </header>
        <p class="ai-headline"><?= e($summary['headline']) ?></p>
        <div class="tabs" role="tablist" aria-label="หมวดสรุป">
<?php $first = true; foreach ($summary['sections'] as $key => $section): ?>
            <button type="button" role="tab" class="tab<?= $first ? ' is-active' : '' ?>" aria-selected="<?= $first ? 'true' : 'false' ?>" data-tab="<?= e($key) ?>"><?= e($section['th']) ?></button>
<?php $first = false; endforeach; ?>
        </div>
<?php $first = true; foreach ($summary['sections'] as $key => $section): ?>
        <div class="tab-panel" role="tabpanel" data-tab-panel="<?= e($key) ?>"<?= $first ? '' : ' hidden' ?>>
            <p class="tab-panel__label"><?= ais_icon($section['icon'], 'icon-xs') ?> <?= e($section['label']) ?></p>
            <ul class="check-list">
<?php foreach ($section['items'] as $item): ?>
                <li><?= e($item) ?></li>
<?php endforeach; ?>
            </ul>
        </div>
<?php $first = false; endforeach; ?>
        <a class="link-arrow link-arrow--light" href="<?= e(ais_url('ai-insights')) ?>">เปิด AI Intelligence Center<?= ais_icon('arrow-right', 'icon-xs') ?></a>
    </article>

    <article class="span-6 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">Student Enrollment Trend</h3>
                <p class="card__sub">จำนวนนักเรียน ย้ายเข้า และย้ายออกรายเดือน</p>
            </div>
            <a class="btn-icon" href="<?= e(ais_url('students')) ?>" aria-label="ดูข้อมูลนักเรียน"><?= ais_icon('arrow-up-right') ?></a>
        </header>
        <?= ais_chart('chart-enrollment', $a->enrollmentChart(), 260, 'กราฟแนวโน้มจำนวนนักเรียน') ?>
    </article>

    <article class="span-6 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">Attendance Trend</h3>
                <p class="card__sub">อัตราการเข้าเรียนรายเดือนเทียบเป้าหมาย 96%</p>
            </div>
            <div class="seg" data-chart-views="chart-attendance">
                <button type="button" class="is-active" data-view="school">ทั้งโรงเรียน</button>
                <button type="button" data-view="levels">แยกช่วงชั้น</button>
            </div>
        </header>
        <?= ais_chart('chart-attendance', $a->attendanceChart(), 260, 'กราฟอัตราการเข้าเรียน') ?>
    </article>

    <article class="span-8 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">Academic Performance</h3>
                <p class="card__sub">เปรียบเทียบผลการเรียนภาคเรียน <?= e(SchoolData::PREV_TERM) ?> และ <?= e(SchoolData::CURRENT_TERM) ?></p>
            </div>
            <div class="seg" data-chart-views="chart-academic">
                <button type="button" class="is-active" data-view="grade">รายระดับชั้น</button>
                <button type="button" data-view="subject">รายวิชา</button>
            </div>
        </header>
        <?= ais_chart('chart-academic', $a->academicChart(), 280, 'กราฟผลสัมฤทธิ์ทางการเรียน') ?>
    </article>

    <article class="span-4 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">Student Risk Distribution</h3>
                <p class="card__sub">คลิกที่กราฟเพื่อดูรายชื่อในแต่ละกลุ่ม</p>
            </div>
        </header>
        <?= ais_chart('chart-risk', $a->riskChart(), 280, 'กราฟสัดส่วนนักเรียนตามระดับการติดตาม') ?>
    </article>

    <article class="span-6 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">Teacher Performance</h3>
                <p class="card__sub">คะแนนวิเคราะห์หลายมิติ (9 มิติ) — ใช้ประกอบการพัฒนาครู</p>
            </div>
            <div class="seg" data-chart-views="chart-teacher">
                <button type="button" class="is-active" data-view="department">รายกลุ่มสาระ</button>
                <button type="button" data-view="band">การกระจาย</button>
            </div>
        </header>
        <?= ais_chart('chart-teacher', $a->teacherChart(), 300, 'กราฟผลการปฏิบัติงานครู') ?>
    </article>

    <article class="span-6 card">
        <header class="card__head">
            <div>
                <h3 class="card__title">School KPI Performance</h3>
                <p class="card__sub">ผลเดือนล่าสุดเทียบเป้าหมาย 6 ด้าน</p>
            </div>
            <a class="btn-icon" href="<?= e(ais_url('performance')) ?>" aria-label="ดูผลการดำเนินงาน"><?= ais_icon('arrow-up-right') ?></a>
        </header>
        <?= ais_chart('chart-kpi', $a->kpiRadarChart(), 300, 'กราฟ KPI เทียบเป้าหมาย') ?>
    </article>
</section>

<section class="section-block">
    <div class="section-block__head">
        <div>
            <h3 class="section-title"><?= ais_icon('sparkles') ?> AI Insights ที่ควรให้ความสำคัญ</h3>
            <p class="muted">ประเด็นที่ระบบตรวจพบจากข้อมูลล่าสุด เรียงตามระดับความสำคัญ</p>
        </div>
        <a class="btn btn--ghost btn--sm" href="<?= e(ais_url('ai-insights')) ?>">ดูทั้งหมด <?= ais_icon('arrow-right') ?></a>
    </div>
    <div class="insight-grid">
<?php foreach ($insights as $insight) {
    ais_component('insight-card', ['insight' => $insight, 'compact' => true]);
} ?>
    </div>
</section>
<?php
tb_log('Page', 'overview', 'END');
