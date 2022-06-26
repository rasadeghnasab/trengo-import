<?php

namespace App\Jobs;

use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Trengo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ContactsInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Contact $contact)
    {
    }

    public function handle()
    {
        if ($timestamp = Cache::get('api-limit')) {
            return $this->release($timestamp - time());
        }

        $trengo = new Trengo(Http::trengo());
        $response = $trengo->createContact($this->contact)->sendRequest();

        if ($response->failed() && $response->status() == 429) {
            $secondsRemaining = $response->header('Retry-After');

            Cache::put(
                'api-limit',
                now()->addSeconds($secondsRemaining)->timestamp,
                $secondsRemaining
            );

            return $this->release($secondsRemaining);
        }
    }

//    /**
//     * Execute the job.
//     *
//     * @return void
//     */
//    public function handle()
//    {
//        $api = new ExternalAPIController;
//
//        $counter = $api->hit();
//
//        if($api->status() === 429) {
//            dump(429, $counter);
//            return $this->release(20);
//        }
//
//        Artisan::call('redis:publish', ['counter' => $counter, 'timestamp']);
//    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
//    public function middleware()
//    {
//        return [new RateLimitedWithRedis('counter-jobs')];
//        return [new ThrottlesExceptionsWithRedis(10, 1)];
//        return [new RateLimited('counter-jobs')];
//    }

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
