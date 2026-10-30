<?php

declare(strict_types=1);

/**
 * GET api/sample.php?type=students|attendance|grades|teachers
 * Downloads a CSV template filled with demo data (UTF-8 BOM so Excel shows Thai correctly).
 */
require dirname(__DIR__) . '/bootstrap.php';

$type = (string) ($_GET['type'] ?? 'students');
tb_log('Api', 'sample', 'START', ['type' => $type]);

$data = SchoolData::get();
$rows = match ($type) {
    'students' => array_merge(
        [['student_code', 'full_name', 'gender', 'grade', 'room', 'homeroom_teacher_id']],
        array_map(static fn ($s) => [$s['code'], $s['name'], $s['gender'], $s['grade_label'], $s['room'], $s['homeroom']], array_values($data['students']))
    ),
    'attendance' => array_merge(
        [['student_code', 'room', 'term', 'attendance_rate', 'absent_days']],
        array_map(static fn ($s) => [$s['code'], $s['room'], SchoolData::CURRENT_TERM, $s['attendance'], $s['absent_days']], array_values($data['students']))
    ),
    'grades' => array_merge(
        [['student_code', 'room', 'term', 'gpa', 'submission_rate']],
        array_map(static fn ($s) => [$s['code'], $s['room'], SchoolData::CURRENT_TERM, $s['gpa'] ?? '', $s['submission']], array_values(array_filter($data['students'], static fn ($s) => $s['gpa'] !== null)))
    ),
    'teachers' => array_merge(
        [['teacher_id', 'full_name', 'department', 'teaching_hours', 'lesson_log_rate', 'training_hours']],
        array_map(static fn ($t) => [$t['id'], $t['name'], $t['dept_label'], $t['hours'], $t['lesson_log'], $t['training_hours']], array_values($data['teachers']))
    ),
    default => null,
};

if ($rows === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ไม่พบไฟล์ตัวอย่างที่ต้องการ';
    tb_log('Api', 'sample', 'END unknown type', ['type' => $type]);
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tb-academy-' . $type . '-sample.csv"');
$out = fopen('php://output', 'wb');
fwrite($out, "\xEF\xBB\xBF");
foreach ($rows as $row) {
    fputcsv($out, $row, ',', '"', '');
}
fclose($out);
tb_log('Api', 'sample', 'END', ['rows' => count($rows) - 1]);
