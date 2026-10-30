<?php

declare(strict_types=1);

/**
 * AI Teacher Evaluation — list + per-teacher analysis (rendered by pages.js from JSON).
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'teacher-evaluation', 'START');

$evals = $a->teacherEvaluations();
$sum = $a->teacherSummary();
$sorted = $evals;
uasort($sorted, static fn ($x, $y) => strcmp($x['dept_label'] . $x['id'], $y['dept_label'] . $y['id']));
$depts = array_values(array_unique(array_column($evals, 'dept_label')));
$lowData = count(array_filter($evals, static fn ($t) => $t['completeness'] < 80));

ais_component('page-head', [
    'eyebrow' => ais_icon('sparkles', 'icon-xs') . ' AI Teacher Evaluation',
    'title'   => 'วิเคราะห์การปฏิบัติงานครูแบบหลายมิติ',
    'desc'    => 'สรุปข้อมูล 9 มิติจากหลายแหล่ง เพื่อสนับสนุนการนิเทศ การพัฒนา และการวางแผนภาระงาน',
    'actions' => '<a class="btn btn--ghost" href="' . e(ais_url('reports')) . '#teachers">' . ais_icon('file-text') . 'รายงานครู</a>',
]);
?>
<div class="principle">
    <?= ais_icon('scale') ?>
    <div>
        <strong>หลักการใช้งาน:</strong> AI เป็นเครื่องมือช่วยวิเคราะห์และสนับสนุนการประเมิน <strong>ไม่ใช่เครื่องมือตัดสินคุณภาพครูโดยอัตโนมัติ</strong>
        ผลวิเคราะห์ใช้ข้อมูล 9 มิติ โดยผลสัมฤทธิ์ของนักเรียนมีน้ำหนักเพียง 12% และทุกผลแสดงแหล่งข้อมูล ความครบถ้วน และข้อจำกัดกำกับไว้เสมอ
        ในเดโมนี้คะแนนคำนวณด้วยกฎ (Rule-based) จากข้อมูลจำลอง
    </div>
</div>

<div class="mini-stats">
    <div class="mini-stat tone-blue"><span class="mini-stat__icon"><?= ais_icon('users') ?></span><div><p class="mini-stat__label">ครูที่วิเคราะห์</p><p class="mini-stat__value"><?= count($evals) ?><small>คน</small></p></div></div>
    <div class="mini-stat tone-green"><span class="mini-stat__icon"><?= ais_icon('gauge') ?></span><div><p class="mini-stat__label">คะแนนหลายมิติเฉลี่ย</p><p class="mini-stat__value"><?= ais_num($sum['overall'], 1) ?><small>/100</small></p></div></div>
    <div class="mini-stat tone-orange"><span class="mini-stat__icon"><?= ais_icon('briefcase') ?></span><div><p class="mini-stat__label">ภาระงานสูงกว่าค่าเฉลี่ย 20%+</p><p class="mini-stat__value"><?= $sum['high_load'] ?><small>คน</small></p></div></div>
    <div class="mini-stat tone-yellow"><span class="mini-stat__icon"><?= ais_icon('database-zap') ?></span><div><p class="mini-stat__label">ข้อมูลยังไม่ครบถ้วน</p><p class="mini-stat__value"><?= $lowData ?><small>คน</small></p></div></div>
</div>

<div class="eval-layout" data-eval>
    <aside class="card teacher-list" aria-label="รายชื่อครู">
        <div class="toolbar">
            <label class="search"><?= ais_icon('search') ?><input type="search" placeholder="ค้นหาชื่อครู" data-eval-search aria-label="ค้นหาชื่อครู"></label>
        </div>
        <div class="toolbar">
            <select class="select select--pill" data-eval-dept aria-label="กลุ่มสาระ">
                <option value="">ทุกกลุ่มสาระ</option>
<?php foreach ($depts as $dept): ?>
                <option value="<?= e($dept) ?>"><?= e($dept) ?></option>
<?php endforeach; ?>
            </select>
            <select class="select select--pill" data-eval-sort aria-label="เรียงลำดับ">
                <option value="dept">เรียงตามกลุ่มสาระ</option>
                <option value="score-desc">คะแนนมาก → น้อย</option>
                <option value="score-asc">คะแนนน้อย → มาก</option>
                <option value="load">ภาระงานสูงสุด</option>
            </select>
        </div>
        <ul data-eval-list>
<?php foreach ($sorted as $t): ?>
            <li data-id="<?= e($t['id']) ?>" data-name="<?= e($t['name'] . ' ' . $t['dept_label']) ?>" data-dept="<?= e($t['dept_label']) ?>" data-score="<?= $t['overall'] ?>" data-load="<?= $t['load_index'] ?>">
                <button type="button" class="teacher-item" data-eval-select="<?= e($t['id']) ?>">
                    <span class="initials"><?= e(mb_substr(str_replace('ครู', '', $t['short']), 0, 1)) ?></span>
                    <span class="teacher-item__text"><strong><?= e($t['name']) ?></strong><small><?= e($t['dept_label']) ?><?= $t['completeness'] < 80 ? ' · ข้อมูลไม่ครบ' : '' ?></small></span>
                    <span class="score-pill tone-<?= e($t['band']['tone']) ?>"><?= ais_num($t['overall'], 1) ?></span>
                </button>
            </li>
<?php endforeach; ?>
        </ul>
    </aside>

    <section class="eval-detail" data-eval-detail aria-live="polite">
        <div class="card empty-state"><?= ais_icon('user-search') ?><p>เลือกครูจากรายชื่อเพื่อดูผลการวิเคราะห์รายบุคคล</p></div>
    </section>
</div>
<script type="application/json" id="eval-data"><?= ais_json(['teachers' => $evals, 'avgHours' => $sum['avg_hours'], 'avgLoad' => $sum['avg_load']]) ?></script>
<?php
tb_log('Page', 'teacher-evaluation', 'END');
