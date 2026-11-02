<div class="page-head">
    <div>
        <p class="eyebrow eyebrow--ai"><?= exe_icon('user-check') ?> AI Teacher Evaluation</p>
        <h2 class="page-title">วิเคราะห์ประสิทธิภาพการจัดการเรียนรู้ของครู</h2>
        <p class="page-desc">ใช้ข้อมูล 10 ด้าน ตั้งแต่ภาระงาน ผลลัพธ์ของนักเรียน การพัฒนาตนเอง ไปจนถึง Feedback จากนักเรียนและผู้บริหาร</p>
    </div>
</div>
<div class="notice notice--warning">
    <?= exe_icon('scale') ?>
    <div><strong>ข้อสำคัญ:</strong> คะแนนในหน้านี้เป็นตัวชี้วัดจำลองเพื่อสาธิตระบบ <strong>ไม่ใช่การตัดสินคุณภาพครูโดยอัตโนมัติ</strong> การนำไปใช้จริงต้องพิจารณาบริบทของห้องเรียน ภาระงาน และปัจจัยประกอบอื่น ๆ ร่วมกับการพูดคุยกับครู</div>
</div>
<div class="stat-grid" id="tc-summary"></div>
<div class="grid">
    <article class="card span-5">
        <header class="card__head"><div><h3 class="card__title"><?= exe_icon('sparkles', 'tone-ai') ?> AI Teacher Insights</h3><p class="card__sub">AI Simulation จากข้อมูลชุดปัจจุบัน</p></div></header>
        <ul class="insight-list" id="tc-insights"></ul>
    </article>
    <article class="card span-7">
        <header class="card__head">
            <div><h3 class="card__title">คะแนนประเมินเฉลี่ยรายกลุ่มสาระ</h3><p class="card__sub">เทียบภาระงานเฉลี่ย (ชั่วโมง/สัปดาห์)</p></div>
        </header>
        <div class="chart" style="--h:300px"><canvas id="tc-dept"></canvas></div>
    </article>
</div>
<article class="card">
    <header class="card__head"><div><h3 class="card__title">Teacher Evaluation Table</h3><p class="card__sub">คลิกที่แถวเพื่อดูรายละเอียดรายบุคคล</p></div></header>
    <div class="table-tools">
        <label class="search"><?= exe_icon('search') ?><input type="search" placeholder="ค้นหาชื่อครูหรือกลุ่มสาระ" data-table-search="tc-table" aria-label="ค้นหาครู"></label>
        <select class="select" data-table-filter="tc-table" data-key="department" id="tc-dept-filter" aria-label="กลุ่มสาระ"><option value="">ทุกกลุ่มสาระ</option></select>
        <select class="select" data-table-filter="tc-table" data-key="status" aria-label="สถานะ">
            <option value="">ทุกสถานะ</option><option value="โดดเด่น">โดดเด่น</option><option value="ดี">ดี</option><option value="พัฒนาได้">พัฒนาได้</option><option value="ควรได้รับการสนับสนุน">ควรได้รับการสนับสนุน</option>
        </select>
    </div>
    <div id="tc-table"></div>
</article>
