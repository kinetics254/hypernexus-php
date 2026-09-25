<?php

namespace KTL\Hypernexus\Http\Authentication;

use Illuminate\Http\Client\PendingRequest;

class BasicAuthenticator implements Authenticator
{
    public function authenticate(PendingRequest $request): PendingRequest
    {
        return $request->withBasicAuth(
            config('hypernexus.username'),
            config('hypernexus.password'),
        );
    }
}