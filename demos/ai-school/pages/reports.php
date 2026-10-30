<?php

declare(strict_types=1);

/**
 * Executive Reports — preview, print and save as PDF (via the browser print dialog).
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'reports', 'START');

$reports = require AIS_ROOT . '/config/reports.php';

ais_component('page-head', [
    'eyebrow' => ais_icon('file-text', 'icon-xs') . ' Executive Reports',
    'title'   => 'รายงานสำหรับผู้บริหาร',
    'desc'    => 'สร้างรายงานจากข้อมูลล่าสุดได้ทันที ดูตัวอย่างก่อนพิมพ์ หรือบันทึกเป็น PDF',
]);
?>
<div class="ai-disclaimer" style="margin-bottom:var(--gap)">
    <?= ais_icon('printer') ?>
    <div><strong>Export PDF:</strong> ในเฟสนี้ใช้หน้าต่างพิมพ์ของเบราว์เซอร์ — เลือกปลายทางเป็น <strong>“บันทึกเป็น PDF / Save as PDF”</strong> ได้ทันที รายงานจัดรูปแบบสำหรับกระดาษ A4 แล้ว</div>
</div>

<div class="report-grid">
<?php foreach ($reports as $key => $r): ?>
    <article class="report-card tone-<?= e($r['tone']) ?>" id="<?= e($key) ?>">
        <span class="report-card__icon"><?= ais_icon($r['icon']) ?></span>
        <h3><?= e($r['title']) ?></h3>
        <p><?= e($r['desc']) ?></p>
        <div class="report-card__meta">
            <span class="badge badge--neutral"><?= ais_icon('calendar-days') ?><?= e($a->periodLabel()) ?></span>
            <span class="badge badge--primary"><?= ais_icon('file') ?>A4 · <?= e($r['pages']) ?></span>
        </div>
        <div class="report-card__actions">
            <button type="button" class="btn btn--soft btn--sm" data-report-preview="<?= e($key) ?>" data-report-title="<?= e($r['title']) ?>"><?= ais_icon('eye') ?>Preview</button>
            <button type="button" class="btn btn--ghost btn--sm" data-report-print="<?= e($key) ?>"><?= ais_icon('printer') ?>Print</button>
            <a class="btn btn--primary btn--sm" href="report.php?type=<?= e($key) ?>&amp;print=1" target="_blank" rel="noopener"><?= ais_icon('download') ?>PDF</a>
        </div>
    </article>
<?php endforeach; ?>
</div>

<section class="section-block">
    <div class="section-block__head">
        <div>
            <h3 class="section-title"><?= ais_icon('calendar-clock') ?> รายงานอัตโนมัติ</h3>
            <p class="muted">ตั้งเวลาส่งรายงานทางอีเมลหรือ LINE ให้ผู้บริหารทุกเดือน</p>
        </div>
    </div>
    <div class="soon-card">
        <?= ais_icon('mail') ?>
        <div><strong>ส่งรายงานอัตโนมัติรายเดือน</strong><small>กำหนดผู้รับ รูปแบบไฟล์ และรอบการส่ง</small></div>
        <button type="button" class="btn btn--ghost btn--sm ml-auto" data-coming-soon="การส่งรายงานอัตโนมัติจะเปิดให้ใช้งานในเฟสถัดไป (Coming Soon)"><span class="badge badge--soon">Coming Soon</span></button>
    </div>
</section>
<?php
tb_log('Page', 'reports', 'END');
