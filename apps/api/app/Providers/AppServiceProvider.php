<?php

namespace App\Providers;

use App\Support\CurrentFarm;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One CurrentFarm per request (scoped = fresh per container lifecycle).
        $this->app->scoped(CurrentFarm::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
