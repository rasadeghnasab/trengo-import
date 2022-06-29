<?php

namespace App\Services\Trengo\Routes;

use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use Illuminate\Http\Client\PendingRequest;

trait ProfilesRoutesTrait
{
    private function profiles(int $page = 1, string $term = null): PendingRequest
    {
        $this->method = 'get';
        $this->path = '/profiles';
        $this->data = [
            'page' => $page,
        ];

        if ($term) {
            $this->data['term'] = $term;
        }

        return $this->authenticate();
    }

    private function createProfile(Profile $profile): PendingRequest
    {
        $this->method = 'post';
        $this->path = '/profiles';
        $this->data = $profile->toArray();

        return $this->authenticate();
    }

    private function deleteProfile(int $profileId): PendingRequest
    {
        $this->method = 'delete';
        $this->path = "/profiles/{$profileId}";

        return $this->authenticate();
    }

    private function attachContactToProfile(Contact $contact, Profile $profile, string $type): PendingRequest
    {
        $this->method = 'post';
        $this->path = sprintf('/profiles/%s/contacts', $profile->id());
        $this->data = ['contact_id' => $contact->id(), 'type' => $type];

        return $this->authenticate();
    }
}
