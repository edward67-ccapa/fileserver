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
        if ($this->app->environment('production') || config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Livewire\Livewire::setScriptRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::get('/livewire/livewire.js', $handle);
        });

        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle);
        });
    }
}
