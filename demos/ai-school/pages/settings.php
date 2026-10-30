<?php

declare(strict_types=1);

/**
 * Settings — school profile, AI mode, demo controls and display preferences.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'settings', 'START');

$school = ais_config('school');
$mode = AiAssistant::mode();
$d = $a->data();

ais_component('page-head', [
    'eyebrow' => ais_icon('settings', 'icon-xs') . ' Settings',
    'title'   => 'ตั้งค่าระบบ',
    'desc'    => 'ข้อมูลโรงเรียน การเชื่อมต่อ AI และการควบคุมโหมดสาธิต',
]);
?>
<section class="bento">
    <article class="span-6 card">
        <header class="card__head"><div><h3 class="card__title">ข้อมูลโรงเรียน</h3><p class="card__sub">ข้อมูลจำลองสำหรับการสาธิต</p></div><span class="badge badge--demo">DEMO DATA</span></header>
        <dl class="kv">
            <dt>ชื่อโรงเรียน</dt><dd><?= e($school['name']) ?></dd>
            <dt>ปีการศึกษา / ภาคเรียน</dt><dd><?= e($school['academic_year']) ?> / <?= e($school['current_term']) ?></dd>
            <dt>ระดับที่เปิดสอน</dt><dd><?= e($school['levels']) ?></dd>
            <dt>นักเรียน / ห้องเรียน</dt><dd><?= ais_num(count($d['students'])) ?> คน / <?= count($d['rooms']) ?> ห้อง</dd>
            <dt>ครูและบุคลากร</dt><dd><?= count($d['teachers']) + count($d['staff']) ?> คน (ครู <?= count($d['teachers']) ?> · บุคลากร <?= count($d['staff']) ?>)</dd>
            <dt>ข้อมูลย้อนหลัง</dt><dd><?= e(ais_month($d['months'][0])) ?> – <?= e(ais_month(end($d['months']))) ?> (<?= count($d['months']) ?> เดือน, 3 ภาคเรียน)</dd>
        </dl>
        <button type="button" class="btn btn--ghost btn--sm" style="margin-top:16px" data-coming-soon="การแก้ไขข้อมูลโรงเรียนจะเปิดใช้งานพร้อมระบบ Login (Coming Soon)"><?= ais_icon('pencil') ?>แก้ไขข้อมูล <span class="badge badge--soon">Coming Soon</span></button>
    </article>

    <article class="span-6 card">
        <header class="card__head"><div><h3 class="card__title">AI Engine</h3><p class="card__sub">ผู้ให้บริการสำหรับ AI Chat Assistant</p></div><span class="badge badge--<?= $mode['live'] ? 'good' : 'warn' ?>"><?= $mode['live'] ? 'Live' : 'Simulation' ?></span></header>
        <ul class="settings-list">
            <li><div><strong>โหมดปัจจุบัน</strong><small><?= e($mode['label']) ?></small></div><?= ais_icon($mode['live'] ? 'circle-check' : 'flask-conical') ?></li>
            <li><div><strong>เชื่อมต่อ OpenAI API</strong><small>ตั้งค่า AI_PROVIDER=openai และ OPENAI_API_KEY บนเซิร์ฟเวอร์ ระบบจะสลับให้อัตโนมัติ และกลับมาใช้โหมดจำลองหากเชื่อมต่อไม่ได้</small></div></li>
            <li><div><strong>ความเป็นส่วนตัวของข้อมูล</strong><small>ส่งเฉพาะข้อมูลสรุประดับโรงเรียน/ห้องเรียนให้โมเดล ไม่ส่งข้อมูลรายบุคคล</small></div><?= ais_icon('shield-check') ?></li>
        </ul>
    </article>

    <article class="span-6 card">
        <header class="card__head"><div><h3 class="card__title">การแสดงผล</h3><p class="card__sub">บันทึกในเบราว์เซอร์นี้เท่านั้น</p></div></header>
        <ul class="settings-list">
            <li><div><strong>ย่อเมนูด้านข้าง</strong><small>แสดงเฉพาะไอคอนบนหน้าจอขนาดใหญ่</small></div><label class="switch"><input type="checkbox" data-pref="sidebar"><span class="switch__track"></span><span class="sr-only">ย่อเมนู</span></label></li>
            <li><div><strong>ปกปิดชื่อนักเรียน</strong><small>เบลอชื่อในหน้า Student Risk Analysis (เหมาะกับการนำเสนอ)</small></div><label class="switch"><input type="checkbox" data-pref="privacy"><span class="switch__track"></span><span class="sr-only">ปกปิดชื่อ</span></label></li>
            <li><div><strong>ช่วงเวลาเริ่มต้น</strong><small>ใช้กับ Dashboard และกราฟทั้งหมด</small></div>
                <select class="select select--pill" data-pref="period" aria-label="ช่วงเวลาเริ่มต้น">
<?php foreach (ais_config('periods') as $key => $p): ?>
                    <option value="<?= e($key) ?>"<?= $key === ais_period() ? ' selected' : '' ?>><?= e($p['label']) ?></option>
<?php endforeach; ?>
                </select>
            </li>
        </ul>
    </article>

    <article class="span-6 card">
        <header class="card__head"><div><h3 class="card__title">Demo Mode</h3><p class="card__sub">เข้าชมได้โดยไม่ต้อง Login · ไม่มีการแก้ไขข้อมูลถาวร</p></div></header>
        <ul class="settings-list">
            <li><div><strong>Reset Demo Data</strong><small>ล้างประวัติแชต รายการนำเข้าที่ทดลอง การแจ้งเตือนที่อ่านแล้ว และการตั้งค่าในเบราว์เซอร์นี้</small></div><button type="button" class="btn btn--danger btn--sm" data-reset-demo><?= ais_icon('rotate-ccw') ?>Reset</button></li>
            <li><div><strong>ระบบ Login และสิทธิ์ผู้ใช้</strong><small>รองรับ 5 บทบาท: ผู้บริหาร ครู บุคลากร นักเรียน ผู้ปกครอง</small></div><span class="badge badge--soon">Coming Soon</span></li>
            <li><div><strong>ฐานข้อมูล MySQL / MariaDB</strong><small>โครงสร้างตารางเตรียมไว้ที่ data/schema.sql</small></div><span class="badge badge--soon">Coming Soon</span></li>
        </ul>
    </article>
</section>
<?php
tb_log('Page', 'settings', 'END');
