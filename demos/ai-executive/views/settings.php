<div class="page-head">
    <div>
        <p class="eyebrow"><?= exe_icon('settings') ?> Settings</p>
        <h2 class="page-title">ตั้งค่า</h2>
        <p class="page-desc">การตั้งค่าบันทึกในเบราว์เซอร์นี้เท่านั้น</p>
    </div>
</div>
<div class="grid">
    <article class="card span-6">
        <h3 class="card__title">การแสดงผล</h3>
        <ul class="setting-list">
            <li><div><strong>ธีม</strong><small>สว่าง / มืด / ตามระบบ</small></div>
                <div class="seg" role="group" aria-label="ธีม" id="set-theme">
                    <button type="button" data-theme-set="light"><?= exe_icon('sun') ?>สว่าง</button>
                    <button type="button" data-theme-set="dark"><?= exe_icon('moon') ?>มืด</button>
                    <button type="button" data-theme-set="system"><?= exe_icon('monitor') ?>ระบบ</button>
                </div></li>
            <li><div><strong>ย่อ Sidebar</strong><small>แสดงเฉพาะไอคอนบนหน้าจอใหญ่</small></div><label class="switch"><input type="checkbox" id="set-sidebar"><span class="switch__track"></span><span class="sr-only">ย่อ Sidebar</span></label></li>
            <li><div><strong>จำนวนแถวต่อหน้า</strong><small>ใช้กับตารางทั้งหมด</small></div>
                <select class="select" id="set-pagesize" aria-label="จำนวนแถวต่อหน้า"><option value="10">10</option><option value="20">20</option><option value="50">50</option></select></li>
            <li><div><strong>ลดภาพเคลื่อนไหว</strong><small>ปิดแอนิเมชันของกราฟ</small></div><label class="switch"><input type="checkbox" id="set-motion"><span class="switch__track"></span><span class="sr-only">ลดภาพเคลื่อนไหว</span></label></li>
        </ul>
    </article>
    <article class="card span-6">
        <h3 class="card__title">ข้อมูลและระบบ</h3>
        <ul class="setting-list">
            <li><div><strong>ล้างข้อมูลในเบราว์เซอร์</strong><small>ลบชุดข้อมูลจำลองและการตั้งค่าทั้งหมด แล้วกลับสู่ค่าเริ่มต้น</small></div><button type="button" class="btn btn--danger btn--sm" data-action="clear-all"><?= exe_icon('trash-2') ?>ล้างข้อมูล</button></li>
            <li><div><strong>แหล่งข้อมูล</strong><small>Mock Data จาก PHP Array (ไม่ใช้ฐานข้อมูล)</small></div><span class="badge">No Database</span></li>
            <li><div><strong>AI Engine</strong><small>AI Simulation (Rule-based) บนข้อมูลจำลอง ไม่เรียก API ภายนอก</small></div><span class="badge badge--ai">Simulation</span></li>
            <li><div><strong>การเข้าใช้งาน</strong><small>Demo เปิดใช้ได้ทันทีโดยไม่ต้อง Login</small></div><span class="badge">Demo Mode</span></li>
        </ul>
    </article>
</div>
