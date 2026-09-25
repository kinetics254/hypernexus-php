<?php

namespace KTL\Hypernexus\Http\Authentication;

use InvalidArgumentException;

class AuthenticatorFactory
{
    public static function make(): Authenticator
    {
        return match (strtoupper(config('hypernexus.auth_type', 'NTLM'))) {
            'NTLM' => new NtlmAuthenticator(),
            'BASIC' => new BasicAuthenticator(),

            default => throw new InvalidArgumentException(
                'Unsupported Business Central authentication type: ' .
                config('hypernexus.auth_type')
            ),
        };
    }
}