<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilesBatchStoreRequest;
use App\Imports\ProfilesImport;
use App\Imports\ContactsImport;
use App\Jobs\ContactsInsertJob;
use App\Jobs\ProfilesInsertJob;
use App\Services\Trengo\Models\Profile;
use App\Services\Trengo\Trengo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class CompaniesController extends Controller
{
    public function batchStore(ProfilesBatchStoreRequest $request)
    {
        $trengoRateLimit = config('trengo.rateLimitPerMinute');

        $profilesChunks = Excel::toCollection(new ProfilesImport, $request->file('companies'))
            ->first()->chunk($trengoRateLimit);
        $contactsChunks = Excel::toCollection(new ContactsImport, $request->file('contacts'))
            ->first()->chunk($trengoRateLimit);

        $this->addProfiles($profilesChunks);
        $this->addContacts($contactsChunks, $profilesChunks->count());
    }

    private function addProfiles(Collection $profilesChunks)
    {
        foreach ($profilesChunks as $index => $profiles) {
            foreach ($profiles as $profile) {
                $profileObject = new Profile($profile->get('id'), $profile->get('name'));
                $this
                    ->dispatch((new ProfilesInsertJob($profileObject))->delay(now()->addMinutes($index)));
            }
        }
    }

    private function addContacts(Collection $contactsChunks, int $startIndexFrom = 0)
    {
        foreach ($contactsChunks as $index => $contacts) {
            foreach ($contacts as $contact) {
                $contactObject = new Contact(
                    $contact->get('company_id'),
                    $contact->get('name'),
                    $contact->get('email'),
                    $contact->get('phone'),
                    $contact->get('date_of_birth'),
                );

                $profileId = $contactObject->profileId();
                if (is_null($profileId)) {
                    Cache::put(sprintf('NOT_EXISTS_%s', $contact->get('company_id')));
                    continue;
                }

                $this
                    ->dispatch((new ContactsInsertJob($contactObject))->delay(now()->addMinutes($startIndexFrom + $index)));
            }
        }
    }
}
