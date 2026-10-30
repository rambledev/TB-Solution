<?php

declare(strict_types=1);

/**
 * OpenAI Chat Completions provider. The model only sees the aggregated demo
 * facts from SchoolAnalytics::chatContext() — never raw student records.
 */
final class OpenAiProvider implements AiProvider
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly SchoolAnalytics $analytics,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeout = 25,
    ) {
    }

    public function id(): string
    {
        return 'openai';
    }

    public function reply(string $message, array $history = []): array
    {
        tb_log('OpenAiProvider', 'reply', 'START', ['model' => $this->model]);

        $system = "คุณคือผู้ช่วยวิเคราะห์ข้อมูลสำหรับผู้บริหารโรงเรียน ตอบเป็นภาษาไทย กระชับ ใช้ bullet เมื่อเหมาะสม "
            . "ใช้เฉพาะข้อมูลใน CONTEXT เท่านั้น ถ้าไม่มีข้อมูลให้บอกตรง ๆ เน้นการช่วยเหลือนักเรียนและการพัฒนาครู ไม่ตัดสินบุคคล\n"
            . 'CONTEXT: ' . json_encode($this->analytics->chatContext(), JSON_UNESCAPED_UNICODE);

        $messages = [['role' => 'system', 'content' => $system]];
        foreach (array_slice($history, -6) as $turn) {
            if (in_array($turn['role'] ?? '', ['user', 'assistant'], true)) {
                $messages[] = ['role' => $turn['role'], 'content' => mb_substr((string) $turn['content'], 0, 2000)];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $payload = json_encode(['model' => $this->model, 'messages' => $messages, 'temperature' => 0.3], JSON_UNESCAPED_UNICODE);
        tb_log('OpenAiProvider', 'reply', 'REQUEST', ['url' => self::ENDPOINT, 'messages' => count($messages)]);

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $this->apiKey],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        tb_log('OpenAiProvider', 'reply', 'RESPONSE', ['status' => $status, 'error' => $error, 'bytes' => is_string($raw) ? strlen($raw) : 0]);

        $json = is_string($raw) ? json_decode($raw, true) : null;
        $text = $json['choices'][0]['message']['content'] ?? null;
        if ($status !== 200 || !is_string($text) || $text === '') {
            throw new RuntimeException('OpenAI request failed: HTTP ' . $status . ' ' . $error);
        }

        tb_log('OpenAiProvider', 'reply', 'END');
        return [
            'text' => $text,
            'sources' => ['ข้อมูลสรุปจากระบบ (ข้อมูลจำลอง)'],
            'suggestions' => [],
            'provider' => $this->id(),
        ];
    }
}
