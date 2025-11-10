<?php

namespace App\Providers;

use App\View\Composers\MenuComposer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        // Using view composer to set following variables globally
        View::composer('*', MenuComposer::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Blade::if('role', function (...$roles) {
            $user = Auth::user();

            return $user && in_array($user->roleCode, $roles);
        });

        Blade::if('unlessrole', function (...$roles) {
            $user = Auth::user();

            return ! $user || ! in_array($user->roleCode, $roles);
        });
    }
}
