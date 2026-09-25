<?php

namespace KTL\Hypernexus\Endpoint;

use KTL\Hypernexus\Exceptions\EndpointNotRegisteredException;
use KTL\Hypernexus\Http\BusinessCentralClient;

class EndpointRegistry
{
    public function __construct(
        protected BusinessCentralClient $client,
    ) {
    }

    public function get(string $name): Endpoint
    {
        $path = config("hypernexus.endpoints.{$name}");

        if ($path === null) {
            throw new EndpointNotRegisteredException($name);
        }

        return new Endpoint(
            $name,
            $path,
            $this->client,
        );
    }

    public function has(string $name): bool
    {
        return config("hypernexus.endpoints.{$name}") !== null;
    }

    public function all(): array
    {
        return config('hypernexus.endpoints', []);
    }
}