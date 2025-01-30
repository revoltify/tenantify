<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Exceptions;

class TenantResolutionException extends TenantifyException
{
    public static function make()
    {
        return new self('An error occurred during tenant resolution.');
    }

    public static function invalidDomainFormat(string $domain)
    {
        return new self("Invalid domain format: {$domain}");
    }

    public static function tenantDoesNotExist(string $domain)
    {
        return new self("No tenant associated with domain: {$domain}");
    }
}
