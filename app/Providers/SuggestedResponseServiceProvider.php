<?php

namespace App\Providers;

use App\Contracts\SuggestedResponseServiceInterface;
use App\Services\SuggestedResponseService;
use Illuminate\Support\ServiceProvider;

class SuggestedResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            SuggestedResponseServiceInterface::class,
            SuggestedResponseService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
