<?php

namespace App\Services\Trengo\Models;

class Contact
{
    public function __construct(
        private string $profileId,
        private string $name,
        private string $email,
        private ?string $phone,
        private ?string $dateOfBirth
    ) {
    }

    public function profileId()
    {
        $cacheKey = sprintf('profile_id_%s', $this->profileId);

        return Cache::get($cacheKey, null);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->dateOfBirth,
        ];
    }
}
