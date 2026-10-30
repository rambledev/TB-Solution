<?php

declare(strict_types=1);

/**
 * POST api/chat.php  {"message": "...", "history": [{role, content}]}
 * Returns {ok, text, sources, suggestions, provider} as JSON.
 */
require dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

tb_log('Api', 'chat', 'START', ['method' => $_SERVER['REQUEST_METHOD'] ?? '']);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'กรุณาส่งคำถามผ่านหน้าต่างแชต'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
$message = trim((string) ($input['message'] ?? ''));
$history = is_array($input['history'] ?? null) ? $input['history'] : [];
tb_log('Api', 'chat', 'REQUEST', ['message' => mb_substr($message, 0, 120), 'history' => count($history)]);

if ($message === '' || mb_strlen($message) > 500) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'กรุณาพิมพ์คำถามความยาวไม่เกิน 500 ตัวอักษร'], JSON_UNESCAPED_UNICODE);
    tb_log('Api', 'chat', 'END invalid input');
    exit;
}

try {
    $result = AiAssistant::reply(SchoolAnalytics::instance(), $message, $history);
    $response = ['ok' => true] + $result;
} catch (Throwable $error) {
    tb_log('Api', 'chat', 'ERROR', ['error' => $error->getMessage(), 'message' => $message], true);
    http_response_code(500);
    $response = ['ok' => false, 'message' => 'ขณะนี้ผู้ช่วยยังไม่สามารถตอบได้ กรุณาลองใหม่อีกครั้ง'];
}

tb_log('Api', 'chat', 'RESPONSE', ['ok' => $response['ok'], 'provider' => $response['provider'] ?? null]);
echo json_encode($response, JSON_UNESCAPED_UNICODE);
