<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Analytics\PostHogService;
use Illuminate\Support\ServiceProvider;

/**
 * Bindings applicatifs généraux (PostHog, etc.).
 */
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostHogService::class);
    }
}
