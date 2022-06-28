<?php

namespace App\Services\Trengo\Routes;

use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;

trait ProfilesRoutesTrait
{
    public function profiles(int $page = 1, string $term = null): self
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

    public function createProfile(Profile $profile): self
    {
        $this->method = 'post';
        $this->path = '/profiles';
        $this->data = $profile->toArray();

        return $this->authenticate();
    }

    public function deleteProfile(int $profileId): self
    {
        $this->method = 'delete';
        $this->path = "/profiles/{$profileId}";

        return $this->authenticate();
    }

    public function attachContactToProfile(Contact $contact, Profile $profile, string $type): self
    {
        $this->method = 'post';
        $this->path = sprintf('/profiles/%s/contacts', $profile->id());
        $this->data = ['contact_id' => $contact->id(), 'type' => $type];

        return $this->authenticate();
    }
}
