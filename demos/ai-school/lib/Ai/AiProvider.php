<?php

declare(strict_types=1);

/**
 * Contract for chat assistant providers.
 *
 * reply() returns:
 *   text        string        answer (light markdown: **bold**, "- " bullets)
 *   sources     list<string>  data sources used
 *   suggestions list<string>  follow-up questions
 *   provider    string        provider id shown in the UI
 */
interface AiProvider
{
    public function id(): string;

    /**
     * @param list<array{role: string, content: string}> $history
     * @return array{text: string, sources: list<string>, suggestions: list<string>, provider: string}
     */
    public function reply(string $message, array $history = []): array;
}
