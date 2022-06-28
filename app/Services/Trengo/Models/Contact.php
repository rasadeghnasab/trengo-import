<?php

namespace App\Services\Trengo\Models;

use Illuminate\Support\Facades\Redis;

class Contact
{
    public function __construct(
        private string $profileId,
        private string $id,
        private string $identifier,
        private string $channel_id,
        private string $name,
    ) {
    }

    public function profileId()
    {
        $redisKey = sprintf('profile_id_%s', $this->profileId);

        return Redis::get($redisKey, null);
    }

    public function id(string $id = null): string
    {
        if ($id) {
            $this->id = $id;
        }

        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'channel_id' => $this->channel_id,
            'name' => $this->name,
            'identifier' => str_replace('_', '', $this->identifier),
        ];
    }
}
