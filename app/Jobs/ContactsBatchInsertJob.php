<?php

namespace App\Jobs;

use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class ContactsBatchInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Collection $contacts)
    {
    }

    public function handle()
    {
        $channelId = config('trengo.channels_id.email');

        foreach ($this->contacts as $contact) {
            $contactObject = new Contact(
                $contact->get('company_id'),
                $contact->get('id'),
                $contact->get('email'),
                $channelId,
                $contact->get('name'),
            );

            $profileId = $contactObject->profileId();
            if (is_null($profileId)) {
                Redis::set(sprintf('NOT_EXISTS:%s', $contact->get('company_id')), 'error');
                continue;
            }

            $profileObject = new Profile($profileId, '');

            ContactsInsertJob::dispatch($contactObject, $profileObject);
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
