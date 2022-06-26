<?php

namespace App\Services\Trengo\Routes;

use App\Services\Trengo\Models\Contact;

trait ContactsRoutesTrait
{
    public function createContact(Contact $contact): self
    {
        $this->method = 'post';
        $this->path = sprintf('/profiles/%s/contacts', $contact->profileId());
        $this->data = $contact->toArray();

        return $this->authenticate();
    }
}
