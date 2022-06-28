<?php

namespace App\Jobs;

use App\Services\Trengo\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProfilesBatchInsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $maxExceptions = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Collection $profiles)
    {
    }

    public function handle()
    {
        $trengoRateLimit = config('trengo.rateLimitPerMinute');

        foreach ($this->profiles->chunk($trengoRateLimit) as $index => $profiles) {
            foreach ($profiles as $profile) {
                $profileObject = new Profile($profile->get('id'), $profile->get('name'));
//                $this->dispatch((new ProfilesInsertJob($profileObject))->delay(now()->addMinutes($index)));
                ProfilesInsertJob::dispatch($profileObject);
            }
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
