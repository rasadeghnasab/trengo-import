<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Client\Response;

interface HttpCallable
{
    public function sendRequest(string $endpoint, ?array $data = []): Response;
}
