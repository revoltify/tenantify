<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Exceptions;

final class TenantNotFoundException extends TenantifyException
{
    public static function make(int|string $tenantId): self
    {
        return new self(sprintf('The tenant with ID [%s] could not be identified.', $tenantId));
    }

    public static function forDomain(string $domain): self
    {
        return new self(sprintf('The tenant with Domain [%s] could not be identified.', $domain));
    }
}
