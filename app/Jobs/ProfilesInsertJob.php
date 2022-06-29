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

class ProfilesInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private HttpCallable $http, private Profile $profile)
    {
    }

    public function handle()
    {
        $response = $this->http->sendRequest('createProfile', [$this->profile]);

        if ($response->successful() && $response->status() === 201) {
            $this->profile->idHashMap($response->json('id'));
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
