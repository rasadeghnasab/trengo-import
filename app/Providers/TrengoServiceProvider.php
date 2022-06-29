<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class TrengoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('trengo', function () {
            return Limit::perMinute(config('trengo.rateLimitPerMinute'));
        });

        Http::macro('trengo', function () {
            return Http::baseUrl(config('trengo.baseURL'));
        });
    }
}
