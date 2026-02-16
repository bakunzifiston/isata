<?php

namespace App\Providers;

use App\Services\ConnectionService;
use App\View\Composers\DashboardComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConnectionService::class, fn () => new ConnectionService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.dashboard', DashboardComposer::class);
    }
}
