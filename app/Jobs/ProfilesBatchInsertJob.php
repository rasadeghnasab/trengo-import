<?php

namespace App\Jobs;

use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProfilesBatchInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private HttpCallable $http, private Collection $profiles)
    {
    }

    public function handle()
    {
        foreach ($this->profiles as $profile) {
            $profileObject = new Profile($profile->get('id'), $profile->get('name'));
            ProfilesInsertJob::dispatch($this->http, $profileObject);
        }
    }

    public function middleware()
    {
        return [new RateLimitedWithRedis('trengo')];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addHours(12);
    }
}
