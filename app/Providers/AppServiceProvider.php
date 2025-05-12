<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\View\Composers\MenuComposer;

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
        // Using view composer to set following variables globally
        View::composer('*', MenuComposer::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
