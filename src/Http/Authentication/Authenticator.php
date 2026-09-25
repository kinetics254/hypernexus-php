<?php

namespace KTL\Hypernexus\Http\Authentication;

use Illuminate\Http\Client\PendingRequest;

interface Authenticator
{
    public function authenticate(PendingRequest $request): PendingRequest;
}