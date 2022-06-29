<?php

namespace App\Services\Trengo;

use App\Services\Trengo\Routes\ContactsRoutesTrait;
use App\Services\Trengo\Routes\ProfilesRoutesTrait;
use App\Services\Interfaces\HttpCallable;
use App\Services\Trengo\Models\Contact;
use App\Services\Trengo\Models\Profile;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class Trengo implements HttpCallable
{
    use ContactsRoutesTrait, ProfilesRoutesTrait;

    private PendingRequest $http;
    private string $method;
    private string $path;
    private array $data = [];

    public function __construct(PendingRequest $http)
    {
        $this->http = $http;
    }

    public function sendRequest(string $endpoint, ?array $data = []): Response
    {
        $this->validate($endpoint);

        $this->$endpoint(...$data);

        $method = $this->method;
        $path = $this->path;

        return $this->http->$method($path, $this->data);
    }

    private function validate(string $endpoint)
    {
        if (!method_exists($this, $endpoint)) {
            // it can be a custom exception
            throw new \Exception(sprintf('%s endpoint is not defined. Please check the name and try again'));
        }
    }

    private function authenticate(): self
    {
        $this->http
            ->acceptJson()
            ->withToken(config('trengo.token'));

        return $this;
    }
}
