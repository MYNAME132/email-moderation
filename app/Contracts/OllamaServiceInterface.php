<?php

namespace App\Contracts;

interface OllamaServiceInterface
{
    public function generate(string $prompt): string;
}
