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
    public function batchStore(ProfilesBatchStoreRequest $request): Response
    {
        $trengoRateLimit = config('trengo.rateLimitPerMinute');

        $profiles = Excel::toCollection(new ProfilesImport, $request->file('companies'))->first();
        $contacts = Excel::toCollection(new ContactsImport, $request->file('contacts'))->first();

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
