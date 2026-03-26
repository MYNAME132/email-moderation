<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\OllamaServiceInterface;
use App\Contracts\OllamaResponseGenerationServiceInterface;
use App\Contracts\OllamaClassificationServiceInterface;
use App\Services\Ollama\OllamaService;
use App\Services\Ollama\OllamaResponseGenerationService;
use App\Services\Ollama\OllamaClassificationService;

class OllamaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OllamaServiceInterface::class, OllamaService::class);
        $this->app->bind(OllamaResponseGenerationServiceInterface::class, OllamaResponseGenerationService::class);
        $this->app->bind(OllamaClassificationServiceInterface::class, OllamaClassificationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
