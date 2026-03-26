<?php

namespace App\Services\Ollama;

use App\Contracts\OllamaServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService implements OllamaServiceInterface
{
    public function generate(string $prompt): string
    {
        try {

            $response = Http::timeout(120)
                ->retry(3, 3000)
                ->post(
                    config('ollama.base_url') . '/api/generate',
                    [
                        'model' => config('ollama.model'),
                        'prompt' => $prompt,
                        'stream' => false,
                    ]
                );

            if (!$response->successful()) {

                Log::error('Ollama HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new \Exception('Ollama returned HTTP ' . $response->status());
            }

            $data = $response->json();

            Log::info('Ollama API response', [
                'response' => $data['response'] ?? null
            ]);

            return $data['response'] ?? '';
        } catch (\Throwable $e) {

            Log::error('Ollama service failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
