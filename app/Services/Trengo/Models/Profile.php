<?php

namespace App\Services\Trengo\Models;

use Illuminate\Support\Facades\Redis;

class Profile
{
    public function __construct(private string $id, private string $name)
    {
    }

    public function id(string $id = null): string
    {
        if ($id) {
            $this->id = $id;
        }

        return $this->id;
    }

    public function idHashMap(int $newId = null)
    {
        $redisKey = sprintf('profile_id_%s', $this->id());
        if ($newId) {
            return Redis::set($redisKey, $newId);
        }

        return Redis::get($redisKey);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
