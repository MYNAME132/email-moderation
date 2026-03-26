<?php

namespace App\Providers;

use App\Services\EmailService;
use App\Contracts\EmailServiceInterface;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            EmailServiceInterface::class,
            EmailService::class
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
