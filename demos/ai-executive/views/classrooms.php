<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('school') ?> Classroom Analytics</p>
        <h2 class="page-title">เปรียบเทียบประสิทธิภาพห้องเรียน</h2>
        <p class="page-desc">คะแนนภาพรวมของห้อง = ผลการเรียน 35% · การเข้าเรียน 25% · การส่งงาน 25% · พัฒนาการ 15%</p>
    </div>
    <div class="page-actions">
        <div class="seg" role="group" aria-label="ตัวชี้วัด" id="cr-metric">
            <button type="button" class="is-active" data-metric="overall">ภาพรวม</button>
            <button type="button" data-metric="score">คะแนน</button>
            <button type="button" data-metric="att">เข้าเรียน</button>
            <button type="button" data-metric="sub">ส่งงาน</button>
            <button type="button" data-metric="devDelta">พัฒนาการ</button>
        </div>
    </div>
</div>
<article class="card">
    <header class="card__head"><div><h3 class="card__title">Classroom Comparison Chart</h3><p class="card__sub" id="cr-chart-sub">คะแนนภาพรวมของห้อง</p></div></header>
    <div class="chart" style="--h:320px"><canvas id="cr-chart"></canvas></div>
</article>
<div class="room-grid" id="cr-grid"></div>
