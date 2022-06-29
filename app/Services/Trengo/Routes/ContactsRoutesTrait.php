<?php

namespace App\Services\Trengo\Routes;

use App\Services\Trengo\Models\Contact;
use Illuminate\Http\Client\PendingRequest;

trait ContactsRoutesTrait
{
    private function contacts(int $page = 1, ?string $term = null): PendingRequest
    {
        $this->method = 'get';
        $this->path = '/contacts';
        $this->data = [
            'page' => $page,
        ];

        if ($term) {
            $this->data['term'] = $term;
        }

        return $this->authenticate();
    }

    private function createContact(Contact $contact): PendingRequest
    {
        $this->method = 'post';
        $this->path = sprintf('/channels/%s/contacts', config('trengo.channels_id.email'));
        $this->data = $contact->toArray();

        return $this->authenticate();
    }

    private function deleteContact(int $contactId): PendingRequest
    {
        $this->method = 'delete';
        $this->path = "/contacts/{$contactId}";

        return $this->authenticate();
    }
}
