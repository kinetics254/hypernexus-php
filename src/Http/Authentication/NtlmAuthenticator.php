<?php

namespace KTL\Hypernexus\Http\Authentication;

use Illuminate\Http\Client\PendingRequest;

class NtlmAuthenticator implements Authenticator
{
    public function authenticate(PendingRequest $request): PendingRequest
    {
        return $request->withOptions([
            'curl' => [
                CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
                CURLOPT_USERPWD => sprintf(
                    '%s:%s',
                    config('hypernexus.username'),
                    config('hypernexus.password'),
                ),
            ],
        ]);
    }
}