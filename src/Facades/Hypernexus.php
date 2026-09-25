<?php

namespace KTL\Hypernexus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \KTL\Hypernexus\Endpoint\Endpoint endpoint(string $name)
 * @method static \KTL\Hypernexus\BusinessCentral company(string $name)
 *
 * @see \KTL\Hypernexus\BusinessCentral
 */
class Hypernexus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \KTL\Hypernexus\BusinessCentral::class;
    }
}