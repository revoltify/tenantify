<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Exceptions;

class TenantNotFoundException extends TenantifyException
{
    public static function make(int|string $tenantId)
    {
        return new self("The tenant with ID [{$tenantId}] could not be identified.");
    }
}
