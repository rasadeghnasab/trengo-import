<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilesBatchStoreRequest;
use App\Imports\ProfilesImport;
use App\Imports\ContactsImport;
use App\Jobs\ContactsBatchInsertJob;
use App\Jobs\ContactsDeleteJob;
use App\Jobs\ContactsInsertJob;
use App\Jobs\ProfilesBatchInsertJob;
use App\Jobs\ProfilesInsertJob;
use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use App\Services\Trengo\Trengo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProfilesDeleteJob;

class CompaniesController extends Controller
{
    private function dummyProfiles(int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $profile = new Profile("profile_id_${i}", "name:${i}");
            ProfilesInsertJob::dispatch($profile);
        }

        return response('created');
    }

    private function dummyContacts(int $count)
    {
        $channel_id = config('trengo.channels_id.email');
        for ($i = 0; $i < $count; $i++) {
            $contact = new Contact(
                'id:'.$i,
                "contact_id:{$i}",
                "random_email{$i}@email.com",
                $channel_id,
                'name:'.$i
            );
            $profile = new Profile("profile_id_${i}", "name:${i}");
            $profile->id($profile->idHashMap());

            ContactsInsertJob::dispatch($contact, $profile);
        }

        return response('contacts created');
    }

    private function dummy()
    {
        $profilesResponse = $this->dummyProfiles(13);
        $contactsResponse = $this->dummyContacts(13);

        return $profilesResponse;
        return $contactsResponse;
    }

    public function batchStore(ProfilesBatchStoreRequest $request): Response
    {
//        return $this->dummy();
        $trengoRateLimit = config('trengo.rateLimitPerMinute');

        $profiles = Excel::toCollection(new ProfilesImport, $request->file('companies'))
            ->first();
        $contacts = Excel::toCollection(new ContactsImport, $request->file('contacts'))
            ->first();

        Bus::chain([
            new ProfilesBatchInsertJob($profiles),
            new ContactsBatchInsertJob($contacts, $profiles->chunk($trengoRateLimit)->count()),
        ])->dispatch();

        return response([
            'message' => __('Inserting Profiles and Contacts is in progress...'),
        ]);
    }

    public function purgeProfiles(): Response
    {
        $trengo = new Trengo(Http::trengo());

        $response = $trengo->profiles(1)->sendRequest();
        do {
            foreach ($response->json('data') as $profile) {
                ProfilesDeleteJob::dispatchSync($profile['id']);
            }
            $response = $trengo->profiles(1)->sendRequest();
        } while (!empty($response->json('data')));

        return response([
            'message' => __('All profiles has been cleared'),
        ]);
    }


    public function purgeContacts(): Response
    {
        $trengo = new Trengo(Http::trengo());

        $response = $trengo->contacts(1)->sendRequest();
        do {
            foreach ($response->json('data') as $contacts) {
                ContactsDeleteJob::dispatchSync($contacts['id']);
            }
            $response = $trengo->contacts(1)->sendRequest();
        } while (!empty($response->json('data')));

        return response([
            'message' => __('All contacts has been cleared'),
        ]);
    }
}
