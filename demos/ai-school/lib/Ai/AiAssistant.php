<?php

declare(strict_types=1);

/**
 * Picks the configured provider and falls back to the rule-based mock on any failure.
 */
final class AiAssistant
{
    public static function provider(SchoolAnalytics $analytics): AiProvider
    {
        $ai = ais_config('ai', []);
        if (($ai['provider'] ?? 'mock') === 'openai' && ($ai['openai_key'] ?? '') !== '') {
            return new OpenAiProvider($analytics, $ai['openai_key'], $ai['openai_model'], (int) $ai['timeout']);
        }
        return new MockAiProvider($analytics);
    }

    public static function mode(): array
    {
        $ai = ais_config('ai', []);
        $live = ($ai['provider'] ?? 'mock') === 'openai' && ($ai['openai_key'] ?? '') !== '';
        return $live
            ? ['id' => 'openai', 'label' => 'เชื่อมต่อ OpenAI (' . $ai['openai_model'] . ')', 'live' => true]
            : ['id' => 'mock', 'label' => 'โหมดจำลอง (Rule-based) บนข้อมูลตัวอย่าง', 'live' => false];
    }

    public static function reply(SchoolAnalytics $analytics, string $message, array $history = []): array
    {
        tb_log('AiAssistant', 'reply', 'START', ['length' => mb_strlen($message)]);

        $provider = self::provider($analytics);
        try {
            $result = $provider->reply($message, $history);
        } catch (Throwable $error) {
            tb_log('AiAssistant', 'reply', 'ERROR provider failed, using mock', ['provider' => $provider->id(), 'error' => $error->getMessage()], true);
            $result = (new MockAiProvider($analytics))->reply($message, $history);
        }

        tb_log('AiAssistant', 'reply', 'END', ['provider' => $result['provider']]);
        return $result;
    }
}
