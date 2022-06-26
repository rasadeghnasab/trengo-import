<?php

namespace App\Services\Trengo\Routes;

trait ProfilesRoutesTrait
{
    public function profiles(): self
    {
        $this->method = 'get';
        $this->path = '/profiles';

        return $this->authenticate();
    }

    public function createProfile(Profile $profile): self
    {
        $this->method = 'post';
        $this->path = '/profiles';
        $this->data = $profile->toArray();

        return $this->authenticate();
    }
}
