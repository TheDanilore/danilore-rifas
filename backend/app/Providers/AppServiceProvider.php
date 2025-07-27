<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Rifa;
use App\Observers\RifaObserver;

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
        // Registrar el Observer para Rifa
        Rifa::observe(RifaObserver::class);
    }
}
