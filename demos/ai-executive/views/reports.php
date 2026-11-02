<div class="page-head no-print">
    <div>
        <p class="eyebrow"><?= exe_icon('file-chart-column') ?> AI Executive Report</p>
        <h2 class="page-title">รายงานสำหรับผู้บริหาร</h2>
        <p class="page-desc">รูปแบบทางการ สำหรับนำเสนอผู้อำนวยการและคณะกรรมการสถานศึกษา</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn--ghost" data-action="report-print"><?= exe_icon('printer') ?>Print</button>
        <button type="button" class="btn btn--ghost" data-action="report-pdf"><?= exe_icon('file-down') ?>Export PDF</button>
        <button type="button" class="btn btn--primary" data-action="report-excel"><?= exe_icon('file-spreadsheet') ?>Export Excel</button>
    </div>
</div>
<div class="report-controls no-print">
    <label class="field-inline"><span>ช่วงเวลา</span>
        <select class="select" data-report="range"><option value="1">เดือนล่าสุด</option><option value="3">3 เดือน</option><option value="6" selected>6 เดือน</option></select>
    </label>
    <label class="field-inline"><span>ปีการศึกษา</span>
        <select class="select" data-report="year"><option value="2569" selected>2569</option><option value="2568">2568</option></select>
    </label>
    <label class="field-inline"><span>ภาคเรียน</span>
        <select class="select" data-report="semester"><option value="1" selected>1</option><option value="2">2</option></select>
    </label>
    <span class="muted hide-sm"><?= exe_icon('info') ?> Export PDF: เลือก “บันทึกเป็น PDF” ในหน้าต่างพิมพ์</span>
</div>
<article class="report" id="rp-doc"></article>
