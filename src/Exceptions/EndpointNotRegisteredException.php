<?php

namespace KTL\Hypernexus\Exceptions;

class EndpointNotRegisteredException extends BusinessCentralException
{
    public function __construct(
        public readonly string $endpoint,
    ) {
        parent::__construct(
            "Business Central endpoint [{$endpoint}] is not registered."
        );
    }
}