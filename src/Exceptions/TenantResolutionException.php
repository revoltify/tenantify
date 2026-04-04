<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Exceptions;

final class TenantResolutionException extends TenantifyException
{
    public static function make(): self
    {
        return new self('An error occurred during tenant resolution.');
    }

    public static function invalidDomainFormat(string $domain): self
    {
        return new self('Invalid domain format: '.$domain);
    }

    public static function tenantDoesNotExist(string $domain): self
    {
        return new self('No tenant associated with domain: '.$domain);
    }
}
