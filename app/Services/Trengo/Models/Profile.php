<?php

namespace App\Services\Trengo\Models;

class Profile
{
    public function __construct(private string $id, private string $name)
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function idHashMap(?int $newId)
    {
        if($newId) {
            return Cache::put(sprinf('contact_id_%s', $this->getId()), $newId);
        }

        return Cache::get(sprinf('contact_id_%s', $this->getId()));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
