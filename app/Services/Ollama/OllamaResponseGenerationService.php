<?php

namespace App\Services\Ollama;

use App\Contracts\OllamaResponseGenerationServiceInterface;
use App\Contracts\OllamaServiceInterface;
use Illuminate\Support\Facades\Log;

class OllamaResponseGenerationService implements OllamaResponseGenerationServiceInterface
{
    public function __construct(
        private OllamaServiceInterface $ollama
    ) {}

    public function generateResponses(string $emailContent): array
    {
        Log::info('AI response generation triggered');

        $prompt = "
                    You are an email assistant.

                    Generate 3 short, polite and professional reply suggestions.

                    Rules:
                    - Each response must be 1-2 sentences
                    - No explanations
                    - No numbering
                    - No bullet points
                    - Only responses
                    - Separate each response by a new line

                    Email:
                    {$emailContent}

                    Responses:
                    ";

        $raw = $this->ollama->generate($prompt);

        Log::info('Raw AI responses', [
            'response' => $raw
        ]);

        return $this->parseResponses($raw);
    }

    private function parseResponses(string $raw): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $raw);

        $responses = array_filter(array_map(function ($line) {
            return trim($line);
        }, $lines));

        return array_slice($responses, 0, 3);
    }
}
