<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('database') ?> Mock Data Center</p>
        <h2 class="page-title">จัดการข้อมูลจำลอง</h2>
        <p class="page-desc">สร้าง เปลี่ยน Scenario หรือสุ่มข้อมูลใหม่ได้ทันทีโดยไม่ต้องแก้โค้ด · ทุกโมดูลใช้ข้อมูลชุดเดียวกัน · เก็บใน LocalStorage ของเบราว์เซอร์นี้</p>
    </div>
</div>
<div class="grid">
    <article class="card span-5 card--current">
        <header class="card__head"><div><h3 class="card__title">ชุดข้อมูลปัจจุบัน</h3><p class="card__sub">ใช้กับทุกหน้าใน Dashboard</p></div><span class="badge badge--ai" id="mk-source"></span></header>
        <dl class="kv" id="mk-current"></dl>
        <div class="control-grid">
            <button type="button" class="btn btn--primary" data-action="generate-current"><?= exe_icon('wand-sparkles') ?>Generate Data</button>
            <button type="button" class="btn btn--ai" data-action="randomize"><?= exe_icon('shuffle') ?>Randomize Data</button>
            <button type="button" class="btn btn--ghost" data-action="open-generator"><?= exe_icon('repeat') ?>Change Scenario</button>
            <button type="button" class="btn btn--danger" data-action="reset"><?= exe_icon('rotate-ccw') ?>Reset to Default</button>
        </div>
    </article>
    <article class="card span-7">
        <header class="card__head"><div><h3 class="card__title">สรุปข้อมูลในชุดปัจจุบัน</h3><p class="card__sub">จำนวนระเบียนแยกตามประเภทข้อมูล</p></div>
            <button type="button" class="btn btn--ghost btn--sm" data-action="download-json"><?= exe_icon('download') ?>ดาวน์โหลด JSON</button></header>
        <div class="stat-grid stat-grid--3" id="mk-summary"></div>
    </article>
</div>
<div class="section-head"><div><h3 class="section-title"><?= exe_icon('layers') ?> Mock Data Scenarios</h3><p class="muted">เลือก Scenario เพื่อเปลี่ยนข้อมูล KPI กราฟ ตาราง AI Insights การประเมินครู และรายงานทั้งหมด</p></div></div>
<div class="scenario-grid" id="mk-scenarios"></div>
