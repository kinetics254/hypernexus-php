<?php

namespace KTL\Hypernexus\Endpoint;

use KTL\Hypernexus\Http\BusinessCentralClient;
use KTL\Hypernexus\Query\ODataKey;
use KTL\Hypernexus\Query\QueryBuilder;

class Endpoint
{
    public function __construct(
        protected string $name,
        protected string $path,
        protected BusinessCentralClient $client,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function query(): QueryBuilder
    {
        return new QueryBuilder(
            $this->client,
            $this,
        );
    }

    public function get(array $query = []): array
    {
        return $this->client->request(
            'GET',
            $this->path,
            $query,
        );
    }

    public function create(array $data): array
    {
        return $this->client->request(
            'POST',
            $this->path,
            data: $data,
        );
    }

    public function update(
        string|array $keys,
        array $data
    ): array {
        return $this->client->request(
            'PATCH',
            $this->resourceUrl($keys),
            data: $data,
        );
    }

    public function delete(string|array $keys): array
    {
        return $this->client->request(
            'DELETE',
            $this->resourceUrl($keys),
        );
    }

    protected function resourceUrl(string|array $keys): string
    {
        return $this->path . ODataKey::format($keys);
    }
}