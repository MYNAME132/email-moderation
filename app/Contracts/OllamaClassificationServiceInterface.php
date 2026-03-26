<?php

namespace App\Contracts;

interface OllamaClassificationServiceInterface
{
    public function classify(string $text): string;
}
