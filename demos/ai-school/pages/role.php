<?php

declare(strict_types=1);

/**
 * Role preview (Teacher / Staff / Student / Parent) for the role selector demo.
 * The executive role always uses the full dashboard.
 *
 * @var SchoolAnalytics $a
 * @var string          $role
 */
tb_log('Page', 'role', 'START', ['role' => $role]);

$d = $a->data();
$roles = ais_config('roles');
$info = $roles[$role];
$focusRoom = 'ม.2/2';
$room = $d['rooms'][$focusRoom];
$teacher = $a->teacherEvaluations()[$room['homeroom']];
$roomStudents = array_values(array_filter($d['students'], static fn ($s) => $s['room'] === $focusRoom));
$child = array_values(array_filter($roomStudents, static fn ($s) => $s['risk'] === 'medium'))[0] ?? $roomStudents[0];
$soon = static fn (string $title, string $desc, string $icon) => '<div class="soon-card">' . ais_icon($icon) . '<div><strong>' . e($title) . '</strong><small>' . e($desc) . '</small></div><span class="badge badge--soon ml-auto">Coming Soon</span></div>';
?>
<div class="role-hero">
    <span class="avatar avatar--<?= e($role) ?>"><?= ais_icon($info['icon']) ?></span>
    <div>
        <h2>มุมมอง<?= e($info['label']) ?> (<?= e($info['en']) ?>)</h2>
        <p>
<?php if ($role === 'teacher'): ?>
            <?= e($teacher['name']) ?> · ครูประจำชั้น <?= e($focusRoom) ?> · <?= e($teacher['dept_label']) ?>
<?php elseif ($role === 'student' || $role === 'parent'): ?>
            <?= $role === 'parent' ? 'ผู้ปกครองของ ' : '' ?><?= e($child['name']) ?> · ห้อง <?= e($child['room']) ?> · รหัส <?= e($child['code']) ?>
<?php else: ?>
            เจ้าหน้าที่งานทะเบียนและวัดผล · ดูแลการนำเข้าข้อมูลและทะเบียนนักเรียน
<?php endif; ?>
        </p>
    </div>
</div>

<?php if ($role === 'teacher'):
    $follow = array_values(array_filter($roomStudents, static fn ($s) => $s['risk'] !== 'low')); ?>
<div class="mini-stats">
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('users') ?></span><div><p class="mini-stat__label">นักเรียนในห้อง</p><p class="mini-stat__value"><?= $room['students'] ?><small>คน</small></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('calendar-check') ?></span><div><p class="mini-stat__label">อัตราเข้าเรียนของห้อง</p><p class="mini-stat__value"><?= ais_num($room['attendance'], 1) ?><small>%</small></p></div></div>
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('clock') ?></span><div><p class="mini-stat__label">ชั่วโมงสอนของฉัน</p><p class="mini-stat__value"><?= $teacher['hours'] ?><small>ชม./สัปดาห์</small></p></div></div>
    <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('heart-handshake') ?></span><div><p class="mini-stat__label">นักเรียนที่ควรดูแล</p><p class="mini-stat__value"><?= count($follow) ?><small>คน</small></p></div></div>
</div>
<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">นักเรียนในห้องที่ควรได้รับการดูแล</h3><p class="card__sub">พร้อมแนวทางช่วยเหลือแรกที่แนะนำ</p></div></header>
        <ul class="list-rows">
<?php foreach ($follow as $s): ?>
            <li><span class="initials"><?= e(mb_substr($s['first'], 0, 1)) ?></span><div class="grow"><strong><?= e($s['name']) ?></strong><small><?= e(SchoolAnalytics::supportPlan($s)[0] ?? '') ?></small></div><span class="badge badge--<?= e($s['risk']) ?>"><?= e(ais_risk_label($s['risk'])) ?></span></li>
<?php endforeach; ?>
        </ul>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">ข้อเสนอแนะเพื่อการพัฒนาตนเอง</h3><p class="card__sub">จาก AI Teacher Evaluation (เห็นเฉพาะตนเองและผู้บริหาร)</p></div></header>
        <ul class="check-list">
<?php foreach (array_slice($teacher['suggestions'], 0, 3) as $s): ?>
            <li><?= e($s) ?></li>
<?php endforeach; ?>
        </ul>
        <div style="margin-top:14px"><?= $soon('บันทึกหลังการสอน', 'บันทึกผ่านมือถือได้ภายใน 1 นาที', 'notebook-pen') ?></div>
    </article>
</section>

<?php elseif ($role === 'staff'):
    $imports = $d['imports']; ?>
<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">งานนำเข้าข้อมูลที่ต้องดำเนินการ</h3><p class="card__sub">จาก Data Import Center</p></div><a class="btn btn--soft btn--sm" href="<?= e(ais_url('data-import')) ?>">เปิด Data Import</a></header>
        <ul class="list-rows">
<?php foreach (array_filter($imports, static fn ($i) => $i['status'] !== 'imported') as $i): ?>
            <li><span class="initials"><?= ais_icon($i['status'] === 'failed' ? 'circle-x' : 'clock') ?></span><div class="grow"><strong><?= e($i['dataset']) ?></strong><small><?= e($i['file']) ?> · <?= e($i['note']) ?></small></div><span class="badge badge--<?= e($i['status']) ?>"><?= e(ucfirst($i['status'])) ?></span></li>
<?php endforeach; ?>
        </ul>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">ทะเบียนนักเรียน</h3><p class="card__sub"><?= e($a->periodLabel()) ?></p></div></header>
<?php $t = $a->transfers(); ?>
        <div class="stat-row" style="grid-template-columns:1fr 1fr">
            <div><small>นักเรียนปัจจุบัน</small><strong><?= ais_num(count($d['students'])) ?></strong></div>
            <div><small>ห้องเรียน</small><strong><?= count($d['rooms']) ?></strong></div>
            <div><small>ย้ายเข้า</small><strong><?= $t['in'] ?></strong></div>
            <div><small>ย้ายออก</small><strong><?= $t['out'] ?></strong></div>
        </div>
        <?= $soon('ออกเอกสาร ปพ. และใบรับรอง', 'สร้างเอกสารทะเบียนอัตโนมัติ', 'file-badge') ?>
    </article>
</section>

<?php else:
    $subjects = SchoolData::SUBJECTS;
    $gs = $d['academics']['grade_subject'][SchoolData::CURRENT_TERM][$child['grade']]; ?>
<div class="mini-stats">
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('award') ?></span><div><p class="mini-stat__label">GPA ภาคเรียน <?= e(SchoolData::CURRENT_TERM) ?></p><p class="mini-stat__value"><?= ais_num($child['gpa'], 2) ?><small>(ก่อนหน้า <?= ais_num($child['gpa_prev'], 2) ?>)</small></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('calendar-check') ?></span><div><p class="mini-stat__label">การมาเรียน</p><p class="mini-stat__value"><?= ais_num($child['attendance'], 1) ?><small>% · ขาด <?= $child['absent_days'] ?> วัน</small></p></div></div>
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('clipboard-check') ?></span><div><p class="mini-stat__label">ส่งงานครบ</p><p class="mini-stat__value"><?= $child['submission'] ?><small>%</small></p></div></div>
    <div class="mini-stat tone-deep"><span class="mini-stat__icon"><?= ais_icon('presentation') ?></span><div><p class="mini-stat__label">ครูประจำชั้น</p><p class="mini-stat__value" style="font-size:.95rem"><?= e($teacher['name']) ?></p></div></div>
</div>
<section class="bento">
    <article class="span-7 card">
        <header class="card__head"><div><h3 class="card__title">ค่าเฉลี่ยรายวิชาของระดับชั้น <?= e($child['grade_label']) ?></h3><p class="card__sub">ใช้เป็นแนวทางเปรียบเทียบ (คะแนนรายบุคคลจะแสดงเมื่อเชื่อมระบบวัดผล)</p></div></header>
        <ul class="list-rows">
<?php foreach ($subjects as $k => $s): ?>
            <li><div class="grow"><strong><?= e($s['name']) ?></strong></div><div class="bar" style="width:140px"><span style="width:<?= $gs[$k]['score'] ?>%"></span></div><span class="num" style="min-width:40px;text-align:right"><?= ais_num($gs[$k]['score'], 1) ?></span></li>
<?php endforeach; ?>
        </ul>
    </article>
    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title"><?= $role === 'parent' ? 'ข้อความจากครูประจำชั้น' : 'เป้าหมายของฉัน' ?></h3><p class="card__sub">สร้างจากข้อมูลการเรียนล่าสุด</p></div></header>
        <ul class="check-list">
<?php if ($role === 'parent'): ?>
            <li>ขอบคุณที่ร่วมดูแลการเรียนของนักเรียนอย่างต่อเนื่อง</li>
<?php foreach (array_slice(SchoolAnalytics::supportPlan($child), 0, 2) as $p): ?>
            <li><?= e($p) ?></li>
<?php endforeach; ?>
<?php else: ?>
            <li>มาเรียนให้ได้อย่างน้อย 96% ในภาคเรียนถัดไป</li>
            <li>ส่งงานให้ครบตามกำหนด ตั้งเป้าหมาย 90% ขึ้นไป</li>
            <li>เข้าร่วมกิจกรรมสอนเสริมวิชาที่สนใจหลังเลิกเรียน</li>
<?php endif; ?>
        </ul>
        <div style="margin-top:14px"><?= $role === 'parent' ? $soon('แจ้งเตือนผ่าน LINE', 'รับข่าวสารและการขาดเรียนแบบทันที', 'message-circle') : $soon('ตารางเรียนและการบ้าน', 'ดูงานที่ต้องส่งและกำหนดส่ง', 'calendar-days') ?></div>
    </article>
</section>
<?php endif;
tb_log('Page', 'role', 'END');
