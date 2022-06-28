<?php

namespace App\Jobs;

use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use App\Services\Trengo\Trengo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    public function __construct(private Contact $contact, private ?Profile $profile = null)
    {
    }

    public function handle()
    {
        if ($timestamp = Cache::get('api-limit')) {
            return $this->release($timestamp - time());
        }

        $trengo = new Trengo(Http::trengo());
        $response = $trengo->createContact($this->contact)->sendRequest();

        $this->attachToProfile($response);

        $this->releaseDueToRateLimit($response);
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

    /**
     * @param  Response  $response
     * @return void
     */
    private function attachToProfile(Response $response): void
    {
        if ($response->successful() && !is_null($this->profile)) {
            $this->contact->id($response->json('id'));

            ContactProfileAttachJob::dispatch($this->contact, $this->profile);
        }
    }

    /**
     * @param  Response  $response
     * @return void
     */
    private function releaseDueToRateLimit(Response $response): void
    {
        if ($response->failed() && $response->status() == 429) {
            $secondsRemaining = $response->header('Retry-After');

            Cache::put(
                'api-limit',
                now()->addSeconds($secondsRemaining)->timestamp,
                $secondsRemaining
            );

            $this->release($secondsRemaining);
        }
    }
}
