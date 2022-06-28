<?php

namespace App\Services\Trengo\Routes;

use App\Services\Trengo\Models\Contact;

trait ContactsRoutesTrait
{
    public function contacts(int $page = 1, ?string $term = null): self
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

    public function createContact(Contact $contact): self
    {
        $this->method = 'post';
        $this->path = sprintf('/channels/%s/contacts', config('trengo.channels_id.email'));
        $this->data = $contact->toArray();

        return $this->authenticate();
    }

    public function deleteContact(int $contactId): self
    {
        $this->method = 'delete';
        $this->path = "/contacts/{$contactId}";

        return $this->authenticate();
    }
}
