<?php

namespace App\Services\Trengo;

use App\Services\Trengo\Routes\ContactsRouteTrait;
use App\Services\Trengo\Routes\ProfilesRouteTrait;
use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class Trengo implements HttpCallable
{
    use ContactsRouteTrait, ProfilesRouteTrait;

    private PendingRequest $http;
    private string $method;
    private string $path;
    private array $data = [];

    public function __construct(PendingRequest $http)
    {
        $this->http = $http;
    }

    public function sendRequest(): Response
    {
        $method = $this->method;
        $path = $this->path;
        $data = $this->data;

        $this->http->acceptJson();

        return $this->http->$method($path, $data);
    }

    private function authenticate(): self
    {
        $this->http
            ->acceptJson()
            ->withToken(config('trengo.token'));

        return $this;
    }
}
