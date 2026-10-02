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
        bool $rawResponse = false,
    ): array {
        $response = $this->send(
            $method,
            $endpoint,
            $query,
            $data,
            $headers,
        );

        if (! $response->successful()) {
            throw ApiException::fromResponse($response);
        }

        $data = $response->json();

        if ($rawResponse) {
            return $data;
        }

        return $this->normalizeResponse($data['value'] ?? $data);
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

            'post' => $request->post(
                $this->withQuery($endpoint, $query),
                $data
            ),

            'put' => $request
                ->withHeaders(['If-Match' => '*'])
                ->put(
                    $this->withQuery($endpoint, $query),
                    $data
                ),

            'patch' => $request
                ->withHeaders(['If-Match' => '*'])
                ->patch(
                    $this->withQuery($endpoint, $query),
                    $data
                ),

            'delete' => $request
                ->withHeaders(['If-Match' => '*'])
                ->delete(
                    $this->withQuery($endpoint, $query)
                ),

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

    protected function withQuery(
        string $endpoint,
        array $query,
    ): string {
        if (empty($query)) {
            return $endpoint;
        }

        return $endpoint . '?' . http_build_query(
                $query,
                '',
                '&',
                PHP_QUERY_RFC3986
            );
    }

    protected function normalizeResponse(mixed $data): array
    {
        return is_array($data)
            ? $data
            : [$data];
    }
}