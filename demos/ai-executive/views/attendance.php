<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('calendar-check') ?> Attendance Analytics</p>
        <h2 class="page-title">วิเคราะห์การเข้าเรียน</h2>
        <p class="page-desc" data-bind="scope-text">ทุกห้องเรียน</p>
    </div>
</div>
<div class="stat-grid" id="at-summary"></div>
<div class="grid">
    <article class="card span-7">
        <header class="card__head"><div><h3 class="card__title">อัตราการเข้าเรียนรายวัน</h3><p class="card__sub">วันเรียนในเดือนล่าสุด (ต.ค. 2569)</p></div></header>
        <div class="chart" style="--h:270px"><canvas id="at-daily"></canvas></div>
    </article>
    <article class="card span-5">
        <header class="card__head"><div><h3 class="card__title">Attendance Trend รายเดือน</h3><p class="card__sub">มาเรียน · ขาด · สาย (เฉลี่ยต่อคน)</p></div></header>
        <div class="chart" style="--h:270px"><canvas id="at-monthly"></canvas></div>
    </article>
    <article class="card span-12">
        <header class="card__head"><div><h3 class="card__title">เปรียบเทียบการเข้าเรียนแต่ละห้อง</h3><p class="card__sub">เดือนล่าสุด · เส้นประ = เป้าหมาย 95%</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="at-rooms"></canvas></div>
    </article>
</div>
<article class="card">
    <header class="card__head"><div><h3 class="card__title">นักเรียนที่ต้องติดตามด้านการเข้าเรียน</h3><p class="card__sub">อัตราเข้าเรียนต่ำกว่า 90% หรือขาดเรียนต่อเนื่องตั้งแต่ 3 วัน</p></div></header>
    <div class="table-tools">
        <label class="search"><?= exe_icon('search') ?><input type="search" placeholder="ค้นหาชื่อ รหัส หรือห้อง" data-table-search="at-table" aria-label="ค้นหานักเรียน"></label>
    </div>
    <div id="at-table"></div>
</article>
