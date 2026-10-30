<?php

declare(strict_types=1);

/**
 * Data Import Center — sources, upload simulation (client-side) and import history.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'data-import', 'START');

$imports = $a->data()['imports'];
$status = array_count_values(array_column($imports, 'status'));
$sources = [
    'excel'  => ['label' => 'Excel',        'icon' => 'file-spreadsheet', 'tone' => 'green',  'desc' => '.xlsx / .xls ตามเทมเพลต',           'ready' => true],
    'csv'    => ['label' => 'CSV',          'icon' => 'file-text',        'tone' => 'blue',   'desc' => 'ตรวจสอบคอลัมน์และแสดงตัวอย่างทันที', 'ready' => true],
    'pdf'    => ['label' => 'PDF',          'icon' => 'file-type',        'tone' => 'red',    'desc' => 'อ่านตารางจากเอกสาร (ทดลอง)',        'ready' => true],
    'manual' => ['label' => 'Manual Entry', 'icon' => 'keyboard',         'tone' => 'yellow', 'desc' => 'บันทึกผ่านแบบฟอร์ม',                'ready' => true],
    'api'    => ['label' => 'API',          'icon' => 'plug',             'tone' => 'deep',   'desc' => 'เชื่อมระบบทะเบียนกลาง (SIS)',       'ready' => false],
    'db'     => ['label' => 'Database',     'icon' => 'database',         'tone' => 'sky',    'desc' => 'MySQL / MariaDB โดยตรง',             'ready' => false],
];
$templates = [
    'students'   => ['label' => 'ข้อมูลนักเรียน',      'cols' => 'student_code, full_name, gender, grade, room, homeroom_teacher_id'],
    'attendance' => ['label' => 'การเข้าเรียน',        'cols' => 'student_code, room, term, attendance_rate, absent_days'],
    'grades'     => ['label' => 'ผลการเรียน',          'cols' => 'student_code, room, term, gpa, submission_rate'],
    'teachers'   => ['label' => 'ข้อมูลครูและภาระงาน', 'cols' => 'teacher_id, full_name, department, teaching_hours, lesson_log_rate, training_hours'],
];
$sourceLabel = array_map(static fn ($s) => $s['label'], $sources) + ['db' => 'Database'];
$statusLabel = ['imported' => 'Imported', 'pending' => 'Pending', 'failed' => 'Failed'];

ais_component('page-head', [
    'eyebrow' => ais_icon('database', 'icon-xs') . ' Data Import Center',
    'title'   => 'จัดการแหล่งข้อมูลและการนำเข้า',
    'desc'    => 'นำเข้าข้อมูลจากหลายแหล่งเพื่อให้การวิเคราะห์เป็นปัจจุบัน — ในเดโมนี้การนำเข้าเป็นการจำลองในเบราว์เซอร์ ไม่บันทึกถาวร',
]);
?>
<div class="mini-stats">
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('layers') ?></span><div><p class="mini-stat__label">รายการนำเข้าทั้งหมด</p><p class="mini-stat__value" data-import-total><?= count($imports) ?></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('circle-check') ?></span><div><p class="mini-stat__label">Imported</p><p class="mini-stat__value" data-import-count="imported"><?= $status['imported'] ?? 0 ?></p></div></div>
    <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('clock') ?></span><div><p class="mini-stat__label">Pending</p><p class="mini-stat__value" data-import-count="pending"><?= $status['pending'] ?? 0 ?></p></div></div>
    <div class="mini-stat tone-red"><span class="mini-stat__icon"><?= ais_icon('circle-x') ?></span><div><p class="mini-stat__label">Failed</p><p class="mini-stat__value" data-import-count="failed"><?= $status['failed'] ?? 0 ?></p></div></div>
</div>

<h3 class="section-title" style="margin-bottom:12px"><?= ais_icon('plug') ?> แหล่งข้อมูล</h3>
<div class="source-grid" style="margin-bottom:var(--gap)">
<?php foreach ($sources as $key => $s): ?>
    <button type="button" class="source tone-<?= e($s['tone']) ?>" <?= $s['ready'] ? 'data-source="' . e($key) . '"' : 'data-coming-soon="การเชื่อมต่อ ' . e($s['label']) . ' จะเปิดให้ใช้งานในเฟสถัดไป (Coming Soon)"' ?>>
        <span class="source__icon"><?= ais_icon($s['icon']) ?></span>
        <strong><?= e($s['label']) ?></strong>
        <small><?= e($s['desc']) ?></small>
        <?= $s['ready'] ? '<span class="badge badge--good">พร้อมใช้งาน (Demo)</span>' : '<span class="badge badge--soon">Coming Soon</span>' ?>
    </button>
<?php endforeach; ?>
</div>

<section class="bento">
    <article class="span-7 card" id="upload">
        <header class="card__head">
            <div><h3 class="card__title">อัปโหลดไฟล์</h3><p class="card__sub">รองรับ .csv .xlsx .xls .pdf ขนาดไม่เกิน 5 MB</p></div>
            <button type="button" class="btn btn--soft btn--sm" data-source="manual"><?= ais_icon('keyboard') ?>บันทึกด้วยตนเอง</button>
        </header>
        <div class="field" style="margin-bottom:12px">
            <label for="import-dataset">ประเภทข้อมูล</label>
            <select class="select" id="import-dataset" data-import-dataset>
<?php foreach ($templates as $key => $t): ?>
                <option value="<?= e($key) ?>" data-cols="<?= e($t['cols']) ?>"><?= e($t['label']) ?></option>
<?php endforeach; ?>
            </select>
        </div>
        <label class="dropzone" data-dropzone>
            <input type="file" accept=".csv,.xlsx,.xls,.pdf" hidden data-import-file>
            <span class="dropzone__icon"><?= ais_icon('upload') ?></span>
            <strong>ลากไฟล์มาวาง หรือคลิกเพื่อเลือกไฟล์</strong>
            <small>คอลัมน์ที่ต้องมี: <span data-import-cols><?= e(reset($templates)['cols']) ?></span></small>
        </label>
        <div class="steps" data-import-steps>
            <span data-step="upload"><?= ais_icon('upload', 'icon-xs') ?>อัปโหลด</span>
            <span data-step="validate"><?= ais_icon('scan-search', 'icon-xs') ?>ตรวจสอบ</span>
            <span data-step="preview"><?= ais_icon('table', 'icon-xs') ?>ตัวอย่างข้อมูล</span>
            <span data-step="import"><?= ais_icon('database', 'icon-xs') ?>นำเข้า</span>
        </div>
        <div data-import-result></div>
    </article>

    <article class="span-5 card">
        <header class="card__head"><div><h3 class="card__title">ไฟล์ตัวอย่างและเทมเพลต</h3><p class="card__sub">สร้างจากข้อมูลจำลองของ TB Academy (CSV เปิดด้วย Excel ได้)</p></div></header>
        <ul class="list-rows">
<?php foreach ($templates as $key => $t): ?>
            <li>
                <span class="initials"><?= ais_icon('file-spreadsheet') ?></span>
                <div class="grow"><strong><?= e($t['label']) ?></strong><small><?= e($t['cols']) ?></small></div>
                <a class="btn btn--ghost btn--xs" href="api/sample.php?type=<?= e($key) ?>" download><?= ais_icon('download') ?>CSV</a>
            </li>
<?php endforeach; ?>
        </ul>
        <div class="ai-disclaimer" style="margin-top:12px"><?= ais_icon('lightbulb') ?><div>ลองดาวน์โหลดไฟล์ตัวอย่าง แล้วอัปโหลดกลับเข้ามาเพื่อดูขั้นตอนตรวจสอบและนำเข้า</div></div>
    </article>

    <article class="span-12 card">
        <header class="card__head">
            <div><h3 class="card__title">ประวัติการนำเข้าข้อมูล</h3><p class="card__sub">แสดง <span data-table-count="import-table"><?= count($imports) ?></span> รายการ</p></div>
        </header>
        <div class="toolbar">
            <label class="search"><?= ais_icon('search') ?><input type="search" placeholder="ค้นหาไฟล์หรือชุดข้อมูล" data-table-search="import-table" aria-label="ค้นหาประวัติการนำเข้า"></label>
            <button type="button" class="chip is-active" data-table-filter="import-table" data-filter-key="status" data-filter-value="*" aria-pressed="true">ทั้งหมด</button>
<?php foreach ($statusLabel as $k => $label): ?>
            <button type="button" class="chip" data-table-filter="import-table" data-filter-key="status" data-filter-value="<?= e($k) ?>" aria-pressed="false"><?= e($label) ?></button>
<?php endforeach; ?>
        </div>
        <div class="table-wrap">
            <table class="table" id="import-table" data-table>
                <thead><tr>
                    <th data-sort="text">รหัส</th>
                    <th data-sort="text">ไฟล์ / แหล่งข้อมูล</th>
                    <th data-sort="text">ชุดข้อมูล</th>
                    <th data-sort="num" class="num">จำนวนรายการ</th>
                    <th data-sort="text">สถานะ</th>
                    <th data-sort="text">วันที่</th>
                    <th>หมายเหตุ</th>
                </tr></thead>
                <tbody data-import-rows>
<?php foreach ($imports as $imp): ?>
                    <tr data-status="<?= e($imp['status']) ?>">
                        <td><?= e($imp['id']) ?></td>
                        <td><div class="cell-main"><div><strong><?= e($imp['file']) ?></strong><small><?= e($sourceLabel[$imp['source']] ?? $imp['source']) ?> · <?= e($imp['by']) ?></small></div></div></td>
                        <td><?= e($imp['dataset']) ?></td>
                        <td class="num"><?= ais_num($imp['records']) ?></td>
                        <td><span class="badge badge--<?= e($imp['status']) ?>"><?= e($statusLabel[$imp['status']]) ?></span></td>
                        <td data-value="<?= e($imp['date']) ?>"><?= e(ais_thai_date($imp['date'])) ?></td>
                        <td><small class="muted"><?= e($imp['note']) ?></small></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php
tb_log('Page', 'data-import', 'END');
