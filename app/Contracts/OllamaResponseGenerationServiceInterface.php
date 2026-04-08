<?php

namespace App\Contracts;

interface OllamaResponseGenerationServiceInterface
{
    public function generateResponses(string $emailContent): array;
    public function generateFromPrompt(string $prompt, string $emailContent): array;
}
