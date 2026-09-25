<?php

namespace KTL\Hypernexus\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use KTL\Hypernexus\Exceptions\ApiException;
use KTL\Hypernexus\Http\Authentication\AuthenticatorFactory;

class BusinessCentralClient
{
    public function __construct(
        protected ?string $company = null,
    ) {
    }

    public function company(?string $company): static
    {
        $client = clone $this;

        $client->company = $company;

        return $client;
    }

    public function request(
        string $method,
        string $endpoint,
        array $query = [],
        array $data = [],
        array $headers = [],
    ): array {
        $response = $this->send(
            $method,
            $endpoint,
            $query,
            $data,
            $headers,
        );

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        throw ApiException::fromResponse($response);
    }

    protected function send(
        string $method,
        string $endpoint,
        array $query = [],
        array $data = [],
        array $headers = [],
    ): Response {
        $request = Http::baseUrl(
            rtrim(config('hypernexus.base_url'), '/')
        )
            ->timeout(config('hypernexus.timeout', 300))
            ->connectTimeout(
                config('hypernexus.connect_timeout', 60)
            )
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers);

        $request = AuthenticatorFactory::make()
            ->authenticate($request);

        $query = $this->prepareQuery($query);

        return match (strtolower($method)) {
            'get' => $request->get($endpoint, $query),

            'post' => $request->post($endpoint, $data),

            'put' => $request
                ->withHeaders(['If-Match' => '*'])
                ->put($endpoint, $data),

            'patch' => $request
                ->withHeaders(['If-Match' => '*'])
                ->patch($endpoint, $data),

            'delete' => $request
                ->withHeaders(['If-Match' => '*'])
                ->delete($endpoint),

            default => throw new \InvalidArgumentException(
                "Unsupported HTTP method [{$method}]."
            ),
        };
    }

    protected function prepareQuery(array $query): array
    {
        if ($this->company !== null) {
            $query['company'] ??= $this->company;
        }

        return $query;
    }
}