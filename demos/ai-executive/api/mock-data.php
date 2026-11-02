<?php

declare(strict_types=1);

/**
 * GET api/mock-data.php?scenario=default|large|excellent|average|at-risk&seed=<int>
 * Returns a complete mock dataset as JSON (no database).
 */
require dirname(__DIR__) . '/includes/helpers.php';
require EXE_ROOT . '/data/generator.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$scenario = (string) ($_GET['scenario'] ?? exe_config('default_scenario'));
$seed = (int) ($_GET['seed'] ?? exe_config('default_seed'));
exe_log('Api', 'mock-data', 'REQUEST', ['scenario' => $scenario, 'seed' => $seed]);

if (!isset(exe_config('scenarios')[$scenario])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'ไม่พบรูปแบบข้อมูลที่เลือก กรุณาลองใหม่อีกครั้ง'], JSON_UNESCAPED_UNICODE);
    exe_log('Api', 'mock-data', 'END invalid scenario', ['scenario' => $scenario]);
    exit;
}

try {
    $seed = max(1, min(999999, $seed));
    $data = exe_generate_dataset($scenario, $seed);
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    exe_log('Api', 'mock-data', 'RESPONSE', ['ok' => true, 'students' => count($data['students'])]);
} catch (Throwable $error) {
    exe_log('Api', 'mock-data', 'ERROR', ['error' => $error->getMessage(), 'scenario' => $scenario, 'seed' => $seed], true);
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'ระบบกำลังดำเนินการ กรุณาลองใหม่อีกครั้ง'], JSON_UNESCAPED_UNICODE);
}
