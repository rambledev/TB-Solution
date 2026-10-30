<?php

declare(strict_types=1);

/**
 * Product detail page for the AI School demo ("ดูรายละเอียด" on the landing page).
 */
require __DIR__ . '/bootstrap.php';

tb_log('AiSchool', 'about', 'START');

$a = SchoolAnalytics::instance();
$d = $a->data();
$risk = $a->riskCounts();
$modules = [];
foreach (ais_config('nav') as $group) {
    foreach ($group['items'] as $item) {
        $modules[] = $item;
    }
}
$moduleDesc = [
    'overview'           => 'KPI 6 ด้าน กราฟ 6 ชุด และ AI Executive Summary ในหน้าเดียว',
    'students'           => 'จำนวนนักเรียนรายชั้น/ห้อง การเข้าเรียน การย้ายเข้า–ออก พร้อมตัวกรอง',
    'academics'          => 'GPA ผลสัมฤทธิ์รายวิชา อัตราผ่าน/ไม่ผ่าน และ Heatmap รายระดับชั้น',
    'teachers'           => 'ภาระงาน ชั่วโมงสอน บันทึกการสอน การอบรม และผลงาน',
    'teacher-evaluation' => 'วิเคราะห์ 9 มิติ พร้อมแหล่งข้อมูล ความครบถ้วน และข้อจำกัด',
    'student-risk'       => 'จัดกลุ่มความเร่งด่วน สาเหตุที่พบ และแผนช่วยเหลือรายบุคคล',
    'ai-insights'        => 'Insight Cards และ AI Chat Assistant ถามตอบข้อมูลโรงเรียน',
    'performance'        => 'KPI รายเดือนเทียบเป้าหมายโรงเรียน',
    'reports'            => 'รายงาน 6 ชุด Preview, Print และบันทึกเป็น PDF',
    'data-import'        => 'นำเข้า Excel, CSV, PDF, Manual พร้อมตรวจสอบรูปแบบไฟล์',
    'settings'           => 'ตั้งค่าการแสดงผล โหมด AI และ Reset Demo Data',
];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>AI School Management · TB-Solution</title>
    <meta name="description" content="AI School Management & Intelligence Platform — ระบบบริหารจัดการและวิเคราะห์ข้อมูลโรงเรียนด้วย AI สำหรับผู้บริหาร โดย TB-Solution">
    <meta name="theme-color" content="#2878E8">
    <link rel="icon" type="image/svg+xml" href="../../assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(ais_asset('assets/css/app.css')) ?>">
    <style>
        .about { max-width: 1200px; margin: 0 auto; padding: 24px 24px 60px; }
        .about-nav { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 36px; }
        .about-hero { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr); gap: 40px; align-items: center; }
        .about-hero h1 { font-size: clamp(1.9rem, 1.3rem + 2.4vw, 3rem); font-weight: 700; line-height: 1.25; color: var(--c-deep); }
        .about-hero h1 span { background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .about-hero p.lead { margin: 16px 0 24px; font-size: 1.05rem; color: var(--c-muted); }
        .about-hero img { width: 100%; border-radius: 24px; box-shadow: var(--shadow-lg); background: #fff; }
        .about-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .about-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 44px 0; }
        .about h2 { margin: 48px 0 16px; font-size: 1.5rem; font-weight: 700; color: var(--c-deep); }
        .module-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .module { display: flex; gap: 12px; padding: 16px; border-radius: var(--radius-lg); background: #fff; border: 1px solid var(--c-border-soft); box-shadow: var(--shadow-xs); transition: transform .2s; }
        .module:hover { transform: translateY(-2px); border-color: #BCD5F5; }
        .module strong { display: block; color: var(--c-deep); font-size: .95rem; }
        .module small { color: var(--c-muted); font-size: .8rem; line-height: 1.5; }
        .principles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .about-cta { margin-top: 48px; padding: 32px; border-radius: 28px; background: var(--grad-deep); color: #fff; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px; box-shadow: var(--shadow-lg); }
        .about-cta h3 { font-size: 1.4rem; font-weight: 700; }
        .about-cta p { color: rgba(255, 255, 255, .75); }
        @media (max-width: 900px) { .about-hero, .principles { grid-template-columns: minmax(0, 1fr); } .module-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .about-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .about { padding: 16px 16px 40px; } .module-grid { grid-template-columns: minmax(0, 1fr); } .about-actions .btn { flex: 1; } .about-nav .brand__text small { display: none; } }
    </style>
</head>
<body>
<div class="about">
    <nav class="about-nav" aria-label="นำทาง">
        <a class="brand" href="../../"><?= tb_logo_mark('about', 'brand__mark') ?><span class="brand__text"><strong>TB-Solution</strong><small>AI School Intelligence</small></span></a>
        <a class="btn btn--ghost btn--sm" href="../../#demos"><?= ais_icon('arrow-left') ?>กลับหน้าหลัก</a>
    </nav>

    <section class="about-hero">
        <div>
            <span class="eyebrow"><?= ais_icon('sparkles', 'icon-xs') ?> AI Powered · Education</span>
            <h1>AI School Management<br><span>&amp; Intelligence Platform</span></h1>
            <p class="lead">ระบบบริหารจัดการและวิเคราะห์ข้อมูลโรงเรียนด้วย AI สำหรับผู้บริหาร ช่วยติดตามผลการดำเนินงาน วิเคราะห์ข้อมูลนักเรียน ครู และผลสัมฤทธิ์ทางการศึกษา เพื่อสนับสนุนการตัดสินใจได้เร็วและแม่นยำขึ้น</p>
            <div class="about-actions">
                <a class="btn btn--primary" href="./"><?= ais_icon('play') ?>ทดลองใช้งาน Demo</a>
                <a class="btn btn--ghost" href="./?page=ai-insights"><?= ais_icon('bot') ?>ลองถาม AI Assistant</a>
            </div>
            <p class="muted" style="margin-top:14px;font-size:.85rem"><?= ais_icon('flask-conical', 'icon-xs') ?> ไม่ต้อง Login · ใช้ข้อมูลจำลองทั้งหมด</p>
        </div>
        <img src="assets/images/preview.svg" alt="ตัวอย่างหน้าจอ Executive Dashboard" width="1280" height="800">
    </section>

    <div class="about-stats">
        <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('graduation-cap') ?></span><div><p class="mini-stat__label">นักเรียนตัวอย่าง</p><p class="mini-stat__value"><?= ais_num(count($d['students'])) ?></p></div></div>
        <div class="mini-stat tone-deep"><span class="mini-stat__icon"><?= ais_icon('users') ?></span><div><p class="mini-stat__label">ครูและบุคลากร</p><p class="mini-stat__value"><?= count($d['teachers']) + count($d['staff']) ?></p></div></div>
        <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('lightbulb') ?></span><div><p class="mini-stat__label">AI Insights ที่ตรวจพบ</p><p class="mini-stat__value"><?= count($a->insights()) ?></p></div></div>
        <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('heart-handshake') ?></span><div><p class="mini-stat__label">นักเรียนที่ควรติดตาม</p><p class="mini-stat__value"><?= $risk['high'] + $risk['medium'] ?></p></div></div>
    </div>

    <h2>โมดูลในระบบ</h2>
    <div class="module-grid">
<?php foreach ($modules as $m): ?>
        <a class="module" href="<?= e(ais_url($m['page'])) ?>">
            <span class="mini-stat__icon" style="width:40px;height:40px"><?= ais_icon($m['icon']) ?></span>
            <span><strong><?= e($m['label']) ?></strong><small><?= e($moduleDesc[$m['page']] ?? $m['th']) ?></small></span>
        </a>
<?php endforeach; ?>
    </div>

    <h2>หลักการออกแบบ AI อย่างรับผิดชอบ</h2>
    <div class="principles">
        <div class="card"><h3 class="card__title"><?= ais_icon('scale', 'icon-xs') ?> สนับสนุน ไม่ตัดสิน</h3><p class="muted">ผลวิเคราะห์ครูใช้ข้อมูล 9 มิติ แสดงแหล่งข้อมูลและข้อจำกัดเสมอ ไม่ใช้ผลการเรียนของนักเรียนเพียงอย่างเดียว</p></div>
        <div class="card"><h3 class="card__title"><?= ais_icon('hand-heart', 'icon-xs') ?> ช่วยเหลือ ไม่ตีตรา</h3><p class="muted">การวิเคราะห์นักเรียนเน้นแผนช่วยเหลือรายบุคคล และมีโหมดปกปิดชื่อสำหรับการนำเสนอ</p></div>
        <div class="card"><h3 class="card__title"><?= ais_icon('shield-check', 'icon-xs') ?> โปร่งใสเรื่องข้อมูล</h3><p class="muted">ระบุชัดเจนเมื่อเป็นการจำลอง (Rule-based) และส่งเฉพาะข้อมูลสรุปให้โมเดลภาษาเมื่อเชื่อมต่อ OpenAI</p></div>
    </div>

    <div class="about-cta">
        <div><h3>พร้อมเห็นภาพการใช้งานจริงแล้วหรือยัง?</h3><p>เข้าสู่ Dashboard ผู้บริหารได้ทันที โดยไม่ต้องสมัครหรือเข้าสู่ระบบ</p></div>
        <a class="btn btn--yellow" href="./"><?= ais_icon('play') ?>เริ่มทดลองใช้งาน</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/lucide@0.460.0/dist/umd/lucide.min.js"></script>
<script>if (window.lucide) window.lucide.createIcons();</script>
</body>
</html>
<?php
tb_log('AiSchool', 'about', 'END');
