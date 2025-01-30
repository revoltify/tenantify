<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Facades;

use Illuminate\Support\Facades\Facade;

class Tenantify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Revoltify\Tenantify\Tenantify::class;
    }
}
