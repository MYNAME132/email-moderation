<?php

namespace App\Services\Ollama;

use App\Contracts\OllamaClassificationServiceInterface;
use App\Contracts\OllamaServiceInterface;
use Illuminate\Support\Facades\Log;

class OllamaClassificationService implements OllamaClassificationServiceInterface
{
    public function __construct(
        private OllamaServiceInterface $ollama
    ) {}

    public function classify(string $text): string
    {
        Log::info('AI processing triggered');

        $prompt = "
    Answer with ONLY one word: APPROVED or REJECTED.

    If email is automated, notification, or contains unsubscribe → REJECTED.

    Email:
    {$text}

    Answer:
    ";

        $raw = $this->ollama->generate($prompt);

        Log::info('Raw Ollama response', [
            'response' => $raw
        ]);

        $result = strtoupper(trim($raw));

        if (str_contains($result, 'APPROVED')) {
            return 'APPROVED';
        }

        if (str_contains($result, 'REJECTED')) {
            return 'REJECTED';
        }

        Log::warning('Unknown AI response, defaulting to REJECTED', [
            'response' => $raw
        ]);

        return 'REJECTED';
    }
}
