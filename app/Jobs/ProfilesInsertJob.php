<?php

namespace App\Jobs;

use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Profile;
use App\Services\Trengo\Trengo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Str;

class ProfilesInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Profile $profile)
    {
    }

    public function handle()
    {
        $trengo = new Trengo(Http::trengo());
        $response = $trengo->createProfile($this->profile)->sendRequest();

        if($response->successful() && $response->status() === 201) {
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
