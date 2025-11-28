<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\DueDateAlert;
use App\Observers\DueDateAlertObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        DueDateAlert::observe(DueDateAlertObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
