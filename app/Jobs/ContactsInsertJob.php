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
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ContactsInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $trengo = new Trengo(Http::trengo());
        $response = $trengo->createContact($this->contact)->sendRequest();

        $this->attachToProfile($response);
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
}
