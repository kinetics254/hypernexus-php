<?php

namespace KTL\Hypernexus\Exceptions;

use Illuminate\Http\Client\Response;

class ApiException extends BusinessCentralException
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array|string|null $response = null,
    ) {
        parent::__construct($message, $status);
    }

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();

        $message =
            data_get($body, 'error.message')
            ?? data_get($body, 'message')
            ?? $response->body()
            ?? 'Unknown Business Central API error.';

        return new static(
            message: $message,
            status: $response->status(),
            response: $body ?? $response->body(),
        );
    }
}