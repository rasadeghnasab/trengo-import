<?php

namespace App\Jobs;

use App\Services\Interfaces\HttpCallable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;

class ContactsDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private HttpCallable $http, private int $contactId)
    {
    }

    public function handle()
    {
        $this->http->sendRequest('deleteContact', [$this->contactId]);
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
