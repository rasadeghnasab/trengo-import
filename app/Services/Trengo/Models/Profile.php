<?php

namespace App\Services\Trengo\Models;

use Illuminate\Support\Facades\Cache;

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
        $cacheKey = sprintf('contact_id_%s', $this->getId());
        if($newId) {
            return Cache::put($cacheKey, $newId);
        }

        return Cache::get($cacheKey);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
