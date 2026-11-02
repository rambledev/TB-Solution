<div class="page-head">
    <div>
        <p class="eyebrow eyebrow--ai"><?= exe_icon('sparkles') ?> AI School Analytics</p>
        <h2 class="page-title">วิเคราะห์แนวโน้มและทิศทางของโรงเรียน</h2>
        <p class="page-desc">วิเคราะห์จากการเข้าเรียน ผลการเรียน การส่งงาน พฤติกรรม พัฒนาการ กิจกรรม และผลการดำเนินงานระดับห้อง/ระดับชั้น</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn--ai" data-action="rerun-ai"><?= exe_icon('refresh-cw') ?>วิเคราะห์อีกครั้ง</button>
    </div>
</div>

<div class="notice notice--ai">
    <?= exe_icon('info') ?>
    <div><strong>AI Simulation / Demo Analysis:</strong> ผลวิเคราะห์ในหน้านี้สร้างจากกฎการวิเคราะห์ (Rule-based) บน Mock Data ชุดปัจจุบัน ไม่ได้เรียกใช้ AI API ภายนอก และเปลี่ยนตาม Scenario ที่เลือกทันที</div>
</div>

<article class="card card--ai-hero">
    <div class="ai-hero__head">
        <span class="ai-orb"><?= exe_icon('brain') ?></span>
        <div><h3>ภาพรวมสถานการณ์โรงเรียน</h3><p id="ai-meta">วิเคราะห์จากข้อมูลชุดปัจจุบัน</p></div>
        <div class="health" id="ai-health"></div>
    </div>
    <p class="ai-hero__text" id="ai-summary"></p>
    <div class="ai-scan" id="ai-scan"></div>
</article>

<div class="grid">
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('thumbs-up', 'tone-success') ?> จุดแข็งของโรงเรียน</h3><ul class="check-list" id="ai-strengths"></ul></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('circle-alert', 'tone-warning') ?> จุดอ่อนของโรงเรียน</h3><ul class="dot-list" id="ai-weak"></ul></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('trending-up', 'tone-primary') ?> แนวโน้มที่ดีขึ้น</h3><ul class="check-list" id="ai-improve"></ul></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('siren', 'tone-danger') ?> ปัญหาที่ควรเร่งแก้ไข</h3><ul class="dot-list dot-list--danger" id="ai-urgent"></ul></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('users', 'tone-warning') ?> กลุ่มนักเรียนที่มีความเสี่ยง</h3><div id="ai-risk"></div></article>
    <article class="card span-4"><h3 class="card__title"><?= exe_icon('trophy', 'tone-success') ?> ห้องเรียนที่โดดเด่น</h3><ul class="rank-list" id="ai-stars"></ul></article>
    <article class="card span-12">
        <header class="card__head"><div><h3 class="card__title"><?= exe_icon('chart-spline', 'tone-ai') ?> การคาดการณ์แนวโน้มในอนาคต</h3><p class="card__sub">คาดการณ์อัตราการเข้าเรียน 3 เดือนข้างหน้าด้วยแนวโน้มเชิงเส้น (Linear Trend) จากข้อมูล 6 เดือน</p></div></header>
        <div class="forecast">
            <div class="chart" style="--h:260px"><canvas id="ai-forecast"></canvas></div>
            <div class="forecast__notes" id="ai-forecast-notes"></div>
        </div>
    </article>
</div>

<div class="section-head">
    <div><h3 class="section-title"><?= exe_icon('lightbulb') ?> AI Strategic Recommendations</h3><p class="muted">แบ่งตามระดับ พร้อมระดับความสำคัญและแนวทางดำเนินการ</p></div>
    <div class="tabs" role="tablist" id="ai-tabs">
        <button type="button" class="tab is-active" data-insight-tab="all">ทั้งหมด <span class="tab__count" data-count="all"></span></button>
        <button type="button" class="tab" data-insight-tab="critical">Critical Issues <span class="tab__count" data-count="critical"></span></button>
        <button type="button" class="tab" data-insight-tab="warning">Warning <span class="tab__count" data-count="warning"></span></button>
        <button type="button" class="tab" data-insight-tab="opportunity">Opportunities <span class="tab__count" data-count="opportunity"></span></button>
        <button type="button" class="tab" data-insight-tab="recommendation">Recommendations <span class="tab__count" data-count="recommendation"></span></button>
    </div>
</div>
<div class="insight-grid" id="ai-insights"></div>
