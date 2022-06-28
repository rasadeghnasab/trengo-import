<?php

namespace App\Jobs;

use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use App\Services\Trengo\Trengo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ContactProfileAttachJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Contact $contact, private Profile $profile)
    {
    }

    public function handle()
    {
        if ($timestamp = Cache::get('api-limit')) {
            return $this->release($timestamp - time());
        }

        $trengo = new Trengo(Http::trengo());
        $response = $trengo->attachContactToProfile($this->contact, $this->profile, 'EMAIL')->sendRequest();

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
