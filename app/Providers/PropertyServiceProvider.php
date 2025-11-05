<?php

namespace App\Providers;

use App\Services\PropertyDescriptionService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Property-related services
 *
 * Registers and binds services following SOLID principles,
 * specifically Dependency Inversion (depend on abstractions, not concretions).
 */
class PropertyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind PropertyDescriptionService as a singleton
        // This ensures one instance is shared throughout the request lifecycle
        $this->app->singleton(PropertyDescriptionService::class, function ($app) {
            return new PropertyDescriptionService();
        });

        // Bind DescriptionScoringService as a singleton
        $this->app->singleton(DescriptionScoringService::class, function ($app) {
            return new DescriptionScoringService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
