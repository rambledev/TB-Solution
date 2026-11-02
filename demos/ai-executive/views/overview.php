<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('layout-dashboard') ?> Overview Dashboard</p>
        <h2 class="page-title">ภาพรวมการดำเนินงานของโรงเรียน</h2>
        <p class="page-desc" data-bind="period-text">ข้อมูลเดือนล่าสุดเทียบกับเดือนก่อน</p>
    </div>
    <div class="page-actions">
        <a class="btn btn--ghost" href="#reports"><?= exe_icon('file-text') ?>รายงานผู้บริหาร</a>
        <a class="btn btn--primary" href="#ai"><?= exe_icon('sparkles') ?>ดูการวิเคราะห์ AI</a>
    </div>
</div>

<article class="ai-strip">
    <span class="ai-strip__orb"><?= exe_icon('sparkles') ?></span>
    <div class="ai-strip__text">
        <p class="ai-strip__label">AI Daily Insight <span class="tag-sim">AI Simulation</span></p>
        <p id="ov-ai-summary" class="skeleton-text">กำลังวิเคราะห์ข้อมูล…</p>
    </div>
    <a class="btn btn--light btn--sm" href="#ai">อ่านเพิ่มเติม<?= exe_icon('arrow-right') ?></a>
</article>

<div class="kpi-grid" id="ov-kpis" aria-label="ตัวชี้วัดหลัก"></div>

<div class="grid">
    <article class="card span-7">
        <header class="card__head"><div><h3 class="card__title">Student Attendance Trend</h3><p class="card__sub">แนวโน้มการเข้าเรียนรายเดือนเทียบเป้าหมาย 95%</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="ov-att"></canvas></div>
    </article>
    <article class="card span-5">
        <header class="card__head"><div><h3 class="card__title">Student Performance Distribution</h3><p class="card__sub" data-bind="term-text">สัดส่วนผลการเรียน</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="ov-dist"></canvas></div>
    </article>
    <article class="card span-7">
        <header class="card__head"><div><h3 class="card__title">Academic Performance</h3><p class="card__sub">คะแนนเฉลี่ยรายระดับชั้น เทียบภาคเรียนก่อน</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="ov-acad"></canvas></div>
    </article>
    <article class="card span-5">
        <header class="card__head"><div><h3 class="card__title">Assignment Submission</h3><p class="card__sub">อัตราการส่งงานรายเดือน แยกช่วงชั้น</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="ov-sub"></canvas></div>
    </article>
    <article class="card span-8">
        <header class="card__head">
            <div><h3 class="card__title">Classroom Comparison</h3><p class="card__sub">คะแนนภาพรวมของแต่ละห้อง (คลิกแท่งกราฟเพื่อดูรายละเอียด)</p></div>
            <a class="icon-btn" href="#classrooms" aria-label="ไปหน้า Classroom Analytics"><?= exe_icon('arrow-up-right') ?></a>
        </header>
        <div class="chart" style="--h:300px"><canvas id="ov-rooms"></canvas></div>
    </article>
    <article class="card span-4">
        <header class="card__head"><div><h3 class="card__title">Student Development</h3><p class="card__sub">คะแนนพัฒนาการเฉลี่ย 3 ภาคเรียน</p></div></header>
        <div class="chart" style="--h:300px"><canvas id="ov-dev"></canvas></div>
    </article>
</div>

<div class="section-head">
    <div><h3 class="section-title"><?= exe_icon('lightbulb') ?> AI Insights ที่ควรให้ความสำคัญ</h3><p class="muted">ประเด็นสำคัญสูงสุด 3 อันดับจากการวิเคราะห์ข้อมูลชุดปัจจุบัน</p></div>
    <a class="btn btn--ghost btn--sm" href="#ai">ดูทั้งหมด<?= exe_icon('arrow-right') ?></a>
</div>
<div class="insight-grid" id="ov-insights"></div>
