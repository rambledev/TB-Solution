<?php

declare(strict_types=1);

/**
 * AI Intelligence Center — analysis domains, insight cards and the chat assistant.
 *
 * @var SchoolAnalytics $a
 */
tb_log('Page', 'ai-insights', 'START');

$insights = $a->insights();
$domains = $a->analysisDomains();
$mode = AiAssistant::mode();
$counts = array_count_values(array_column($insights, 'priority'));
$suggestions = ['สรุปภาพรวมโรงเรียนในเดือนนี้', 'ห้องเรียนใดมีอัตราการขาดเรียนสูงที่สุด', 'วิชาใดมีผลการเรียนลดลง', 'ครูคนใดมีภาระงานสูง', 'โรงเรียนควรปรับปรุงเรื่องใดก่อน', 'มีนักเรียนที่ควรติดตามกี่คน'];

ais_component('page-head', [
    'eyebrow' => ais_icon('sparkles', 'icon-xs') . ' AI Intelligence Center',
    'title'   => 'ผู้ช่วยวิเคราะห์ข้อมูลสำหรับผู้บริหาร',
    'desc'    => 'สรุปประเด็นสำคัญจากข้อมูลนักเรียน ครู ผลการเรียน การเข้าเรียน ภาระงาน และผลการดำเนินงาน พร้อมถามตอบได้ทันที',
    'actions' => '<a class="btn btn--primary" href="#chat">' . ais_icon('message-square') . 'ไปที่ AI Chat</a>',
]);
?>
<div class="ai-disclaimer" style="margin-bottom:var(--gap)">
    <?= ais_icon('info') ?>
    <div><strong>โหมดสาธิต:</strong> ผลวิเคราะห์และคำตอบในหน้านี้สร้างจาก<strong>กฎการวิเคราะห์ (Rule-based Simulation)</strong> บนข้อมูลจำลอง ยังไม่ได้ใช้โมเดล AI วิเคราะห์ข้อมูลจริง
    ระบบออกแบบให้เชื่อมต่อ OpenAI API ได้ในเฟสถัดไป · สถานะปัจจุบัน: <?= e($mode['label']) ?></div>
</div>

<div class="ai-layout">
    <div>
        <article class="card" style="margin-bottom:var(--gap)">
            <header class="card__head">
                <div><h3 class="card__title">AI Analysis — ขอบเขตการวิเคราะห์</h3><p class="card__sub">ข้อมูล <?= e($a->periodLabel()) ?> (<?= e($a->periodRange()) ?>) · ภาคเรียน <?= e(SchoolData::CURRENT_TERM) ?></p></div>
                <span class="scan">วิเคราะห์ล่าสุด <?= e(ais_thai_date('2026-10-30')) ?></span>
            </header>
            <div class="domain-grid">
<?php foreach ($domains as $dm): ?>
                <div class="domain">
                    <div class="domain__top">
                        <span class="domain__icon"><?= ais_icon($dm['icon']) ?></span>
<?php if ($dm['findings'] > 0): ?>
                        <span class="badge badge--warn"><?= $dm['findings'] ?> ประเด็น</span>
<?php else: ?>
                        <span class="badge badge--good">ปกติ</span>
<?php endif; ?>
                    </div>
                    <p class="domain__label"><?= e($dm['label']) ?></p>
                    <p class="domain__value"><?= e($dm['value']) ?></p>
                    <p class="domain__note"><?= e($dm['note']) ?></p>
                </div>
<?php endforeach; ?>
            </div>
        </article>

        <div class="section-block__head">
            <div>
                <h3 class="section-title"><?= ais_icon('lightbulb') ?> AI Insights (<?= count($insights) ?>)</h3>
                <p class="muted">แต่ละประเด็นแสดงข้อมูลสนับสนุน สาเหตุที่เป็นไปได้ และข้อเสนอแนะ</p>
            </div>
            <div class="toolbar" style="margin:0" role="group" aria-label="กรองตามความสำคัญ">
                <button type="button" class="chip is-active" data-insight-filter="*" aria-pressed="true">ทั้งหมด <span class="chip__count"><?= count($insights) ?></span></button>
<?php foreach (['high', 'medium', 'low'] as $p): ?>
                <button type="button" class="chip" data-insight-filter="<?= $p ?>" aria-pressed="false"><?= e(ais_priority_label($p)) ?> <span class="chip__count"><?= $counts[$p] ?? 0 ?></span></button>
<?php endforeach; ?>
            </div>
        </div>
        <div class="insight-grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr))">
<?php foreach ($insights as $insight) {
    ais_component('insight-card', ['insight' => $insight]);
} ?>
        </div>
    </div>

    <aside class="card chat" id="chat" data-chat aria-label="AI Chat Assistant">
        <header class="chat__head">
            <span class="ai-orb"><?= ais_icon('bot') ?></span>
            <div>
                <h3>AI Chat Assistant</h3>
                <p><?= $mode['live'] ? 'เชื่อมต่อโมเดลภาษา' : 'ตอบตามประเภทคำถามจากข้อมูลจำลอง' ?></p>
            </div>
            <button type="button" class="btn-icon" data-chat-clear aria-label="ล้างประวัติแชต" title="ล้างประวัติแชต"><?= ais_icon('trash-2') ?></button>
        </header>
        <div class="chat__log" data-chat-log role="log" aria-live="polite">
            <div class="msg">
                <span class="msg__avatar"><?= ais_icon('sparkles') ?></span>
                <div class="msg__bubble">
                    <p>สวัสดีครับ ผมช่วยสรุปและตอบคำถามจากข้อมูลของ <strong><?= e(ais_config('school.name')) ?></strong> ได้</p>
                    <p>ลองเลือกคำถามตัวอย่างด้านล่าง หรือพิมพ์คำถามของคุณได้เลยครับ</p>
                </div>
            </div>
        </div>
        <div class="chat__suggest" data-chat-suggest>
<?php foreach ($suggestions as $s): ?>
            <button type="button" data-chat-ask="<?= e($s) ?>"><?= e($s) ?></button>
<?php endforeach; ?>
        </div>
        <form class="chat__form" data-chat-form>
            <label class="sr-only" for="chat-input">พิมพ์คำถาม</label>
            <input id="chat-input" type="text" maxlength="500" placeholder="ถามเกี่ยวกับข้อมูลโรงเรียน…" autocomplete="off" required>
            <button type="submit" class="btn btn--primary" aria-label="ส่งคำถาม"><?= ais_icon('send') ?></button>
        </form>
        <p class="chat__mode"><?= ais_icon('shield-check', 'icon-xs') ?> <?= e($mode['label']) ?> · ไม่ส่งข้อมูลรายบุคคลออกนอกระบบ</p>
    </aside>
</div>
<?php
tb_log('Page', 'ai-insights', 'END');
