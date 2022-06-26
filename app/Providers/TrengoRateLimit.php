<?php

namespace App\Providers;

use App\Jobs\ProfilesInsertJob;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class TrengoRateLimit extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Http::macro('trengo', function () {
            return Http::baseUrl(config('trengo.baseURL'));
//                ->withOptions([
//                    'debug' => true,
//                ]);
        });
//        RateLimiter::for('counter-jobs', function ($job) {
////            return Limit::perMinute(5);
//            return (Limit::perMinute(5))->response(function() use ($job) {
//                $job->fail();
//            });
//        });
    }
}
