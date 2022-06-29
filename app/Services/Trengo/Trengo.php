<?php

namespace App\Services\Trengo;

use App\Services\Trengo\Routes\ContactsRoutesTrait;
use App\Services\Trengo\Routes\ProfilesRoutesTrait;
use App\Services\Interfaces\HttpCallable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Trengo implements HttpCallable
{
    use ContactsRoutesTrait, ProfilesRoutesTrait;

    private PendingRequest $http;
    private string $method;
    private string $path;
    private array $data = [];

    public function __construct()
    {
    }

    public function sendRequest(string $endpoint, ?array $data = []): Response
    {
        $this->validate($endpoint);

        $http = $this->$endpoint(...$data);

        $method = $this->method;
        $path = $this->path;

        return $http->$method($path, $this->data);
    }

    private function validate(string $endpoint)
    {
        if (!method_exists($this, $endpoint)) {
            // it can be a custom exception
            throw new \Exception(sprintf('%s endpoint is not defined. Please check the name and try again', $endpoint));
        }
    }

    private function authenticate()
    {
        return Http::trengo()
            ->acceptJson()
            ->withToken(config('trengo.token'));
    }
}
