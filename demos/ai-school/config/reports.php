<?php

declare(strict_types=1);

/**
 * Executive report catalogue (rendered by report.php).
 */
return [
    'overview'  => ['title' => 'รายงานภาพรวมโรงเรียน',  'icon' => 'school',          'tone' => 'blue',   'pages' => '2 หน้า', 'desc' => 'KPI หลัก สรุปสถานการณ์แบบ SWOT และข้อเสนอแนะสำหรับผู้บริหาร'],
    'students'  => ['title' => 'รายงานนักเรียน',         'icon' => 'graduation-cap',  'tone' => 'green',  'pages' => '2 หน้า', 'desc' => 'จำนวนนักเรียนรายระดับชั้น การเข้าเรียน และการย้ายเข้า–ย้ายออก'],
    'academics' => ['title' => 'รายงานผลสัมฤทธิ์',       'icon' => 'book-open-check', 'tone' => 'yellow', 'pages' => '2 หน้า', 'desc' => 'GPA รายระดับชั้น คะแนนรายวิชา อัตราผ่าน/ไม่ผ่าน เทียบภาคเรียนก่อน'],
    'teachers'  => ['title' => 'รายงานครู',              'icon' => 'users',           'tone' => 'deep',   'pages' => '2 หน้า', 'desc' => 'ภาระงาน ชั่วโมงสอน การพัฒนาตนเอง และภาพรวมการวิเคราะห์หลายมิติ'],
    'insights'  => ['title' => 'รายงาน AI Insights',     'icon' => 'sparkles',        'tone' => 'sky',    'pages' => '3 หน้า', 'desc' => 'ประเด็นสำคัญที่ตรวจพบ ข้อมูลสนับสนุน สาเหตุ และข้อเสนอแนะ'],
    'risk'      => ['title' => 'รายงานนักเรียนที่ต้องติดตาม', 'icon' => 'heart-handshake', 'tone' => 'orange', 'pages' => '2 หน้า', 'desc' => 'จำนวนตามระดับ สาเหตุที่พบ และแนวทางช่วยเหลือ (ปกปิดชื่อ)'],
];
