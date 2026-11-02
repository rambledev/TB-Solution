<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('graduation-cap') ?> Student Analytics</p>
        <h2 class="page-title">ผลการเรียนและพัฒนาการนักเรียน</h2>
        <p class="page-desc" data-bind="scope-text">ทุกห้องเรียน · เลือกห้องจากตัวกรองด้านบนเพื่อดูรายละเอียด</p>
    </div>
</div>
<div class="grade-tabs" id="st-grade-tabs" role="tablist" aria-label="เลือกระดับชั้น"></div>
<div class="stat-grid" id="st-summary"></div>
<div class="grid">
    <article class="card span-6">
        <header class="card__head"><div><h3 class="card__title">ผลการเรียนเฉลี่ยรายชั้น</h3><p class="card__sub">GPA เฉลี่ย 3 ภาคเรียน</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="st-grade"></canvas></div>
    </article>
    <article class="card span-6">
        <header class="card__head"><div><h3 class="card__title">Subject Performance Analysis</h3><p class="card__sub">คะแนนเฉลี่ยรายวิชา เทียบภาคเรียนก่อน</p></div></header>
        <div class="chart" style="--h:280px"><canvas id="st-subject"></canvas></div>
    </article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('medal', 'tone-success') ?> Student Performance Ranking</h3><ol class="rank-list" id="st-top"></ol></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('trending-up', 'tone-primary') ?> พัฒนาการดีขึ้นมากที่สุด</h3><ol class="rank-list" id="st-improved"></ol></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('life-buoy', 'tone-danger') ?> ผลการเรียนต่ำกว่าเกณฑ์ (GPA &lt; 2.00)</h3><ol class="rank-list" id="st-below"></ol></article>
</div>
<article class="card">
    <header class="card__head"><div><h3 class="card__title">รายชื่อนักเรียน</h3><p class="card__sub">Academic Progress Tracking · คลิกแถวเพื่อดูพัฒนาการรายบุคคล</p></div></header>
    <div class="table-tools">
        <label class="search"><?= exe_icon('search') ?><input type="search" placeholder="ค้นหาชื่อหรือรหัสนักเรียน" data-table-search="st-table" aria-label="ค้นหานักเรียน"></label>
        <select class="select" data-table-filter="st-table" data-key="riskLabel" aria-label="ระดับความเสี่ยง">
            <option value="">ทุกระดับความเสี่ยง</option><option value="เสี่ยงสูง">เสี่ยงสูง</option><option value="เฝ้าระวัง">เฝ้าระวัง</option><option value="ปกติ">ปกติ</option>
        </select>
    </div>
    <div id="st-table"></div>
</article>
