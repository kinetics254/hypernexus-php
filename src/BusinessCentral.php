<?php

namespace KTL\Hypernexus;

use KTL\Hypernexus\Endpoint\Endpoint;
use KTL\Hypernexus\Endpoint\EndpointRegistry;
use KTL\Hypernexus\Http\BusinessCentralClient;

class BusinessCentral
{
    public function __construct(
        protected BusinessCentralClient $client,
        protected EndpointRegistry $registry,
    ) {
    }

    public function endpoint(string $name): Endpoint
    {
        return $this->registry->get($name);
    }

    public function company(string $name): static
    {
        $instance = clone $this;

        $instance->client = $this->client->company($name);

        return $instance;
    }

    public function get(
        string $endpoint,
        array $query = [],
    ): array {
        return $this->client->request(
            'GET',
            $endpoint,
            $query,
        );
    }

    public function post(
        string $endpoint,
        array $data = [],
    ): array {
        return $this->client->request(
            'POST',
            $endpoint,
            data: $data,
        );
    }
}