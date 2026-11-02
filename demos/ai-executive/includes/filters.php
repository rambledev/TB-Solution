<?php

declare(strict_types=1);

/**
 * Shared dashboard filters (date range, academic year, semester, classroom).
 * Options for classrooms are filled from the current dataset by dashboard.js.
 */
?>
<div class="filters" id="filters" role="region" aria-label="ตัวกรองข้อมูล">
    <span class="filters__label"><?= exe_icon('sliders-horizontal') ?><span class="hide-sm">ตัวกรอง</span></span>
    <label class="field-inline"><span>ช่วงเวลา</span>
        <select class="select" data-filter="range" aria-label="ช่วงเวลา">
            <option value="1">เดือนล่าสุด</option>
            <option value="3">3 เดือนล่าสุด</option>
            <option value="6" selected>6 เดือนล่าสุด</option>
        </select>
    </label>
    <label class="field-inline"><span>ปีการศึกษา</span>
        <select class="select" data-filter="year" aria-label="ปีการศึกษา">
            <option value="2569" selected>2569</option>
            <option value="2568">2568</option>
        </select>
    </label>
    <label class="field-inline"><span>ภาคเรียน</span>
        <select class="select" data-filter="semester" aria-label="ภาคเรียน">
            <option value="1" selected>ภาคเรียนที่ 1</option>
            <option value="2">ภาคเรียนที่ 2</option>
        </select>
    </label>
    <label class="field-inline"><span>ห้องเรียน</span>
        <select class="select" data-filter="room" aria-label="ห้องเรียน" id="filter-room">
            <option value="all">ทุกห้องเรียน</option>
        </select>
    </label>
    <button type="button" class="btn btn--ghost btn--sm" data-action="reset-filters"><?= exe_icon('filter-x') ?>ล้างตัวกรอง</button>
</div>
