<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIService
{
    public function generateLinkedInPost(string $topic, ?string $tone = null): string
    {
        $apiKey = config('services.openai.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $tone = $tone ?: 'professional, practical, and engaging';

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-5.4-mini'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => 'You write polished LinkedIn posts. Return only the final post text. Do not include a title, explanation, markdown heading, or surrounding quotes.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Write one LinkedIn post about this topic: {$topic}\n\nTone: {$tone}\nLength: 900-1300 characters.\nInclude a clear hook, useful insight, and a soft closing question. Use short paragraphs. Avoid exaggerated claims.",
                    ],
                ],
                'max_output_tokens' => 700,
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error.message') ?? 'OpenAI post generation failed.');
        }

        $text = $response->json('output_text') ?: $this->extractOutputText($response->json('output', []));

        if (blank($text)) {
            throw new RuntimeException('OpenAI returned an empty post.');
        }

        return trim($text);
    }

    private function extractOutputText(array $output): string
    {
        $parts = [];

        foreach ($output as $item) {
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
        }

        return trim(implode("\n", $parts));
    }
}
