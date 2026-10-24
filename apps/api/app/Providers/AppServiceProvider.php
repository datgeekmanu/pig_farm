<?php

namespace App\Providers;

use App\Support\CurrentFarm;
use Illuminate\Auth\Notifications\ResetPassword;
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
        // Reset links point at the frontend, which POSTs token+email to /reset-password.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $base = rtrim((string) config('app.frontend_url'), '/');

            return $base.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
