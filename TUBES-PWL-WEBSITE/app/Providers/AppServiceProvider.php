<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strict Eloquent behavior in non-production environments
        // to catch N+1 issues, mass assignment vulnerabilities, and undefined properties.
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());
        \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
        \Illuminate\Database\Eloquent\Model::preventAccessingMissingAttributes(! app()->isProduction());
    }
}
