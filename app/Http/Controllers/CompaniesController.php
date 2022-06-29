<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilesBatchStoreRequest;
use App\Imports\ProfilesImport;
use App\Imports\ContactsImport;
use App\Jobs\ContactsBatchInsertJob;
use App\Jobs\ContactsDeleteJob;
use App\Jobs\ProfilesBatchInsertJob;
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
        $profiles = Excel::toCollection(new ProfilesImport, $request->file('companies'))->first();
        $contacts = Excel::toCollection(new ContactsImport, $request->file('contacts'))->first();

        $trengo = new Trengo(Http::trengo());

        Bus::chain([
            new ProfilesBatchInsertJob($trengo, $profiles),
            new ContactsBatchInsertJob($trengo, $contacts),
        ])->dispatch();

        return response([
            'message' => __('Inserting Profiles and Contacts is in progress...'),
        ]);
    }

    public function purgeProfiles(): Response
    {
        $trengo = new Trengo(Http::trengo());

        $response = $trengo->sendRequest('profiles', [1]);
        while (!empty($response->json('data'))) {
            foreach ($response->json('data') as $profile) {
                ProfilesDeleteJob::dispatchSync($trengo, $profile['id']);
            }
            $response = $trengo->sendRequest('profiles', [1]);
        }

        return response([
            'message' => __('All profiles has been cleared'),
        ]);
    }


    public function purgeContacts(): Response
    {
        $trengo = new Trengo(Http::trengo());

        $response = $trengo->sendRequest('contacts', [1]);
        while (!empty($response->json('data'))) {
            foreach ($response->json('data') as $contacts) {
                ContactsDeleteJob::dispatchSync($trengo, $contacts['id']);
            }
            $response = $trengo->sendRequest('contacts', [1]);
        }

        return response([
            'message' => __('All contacts has been cleared'),
        ]);
    }
}
