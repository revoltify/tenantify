<?php

declare(strict_types=1);

use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Tenantify;

if (! function_exists('tenant')) {
    /**
     * Retrieve the current tenant or a specific attribute from the tenant's properties.
     *
     * @return TenantInterface|null|mixed
     */
    function tenant(?string $key = null): mixed
    {
        $tenant = app()->bound(TenantInterface::class) ? app(TenantInterface::class) : null;

        if (is_null($tenant)) {
            return null;
        }

        if (is_null($key)) {
            return $tenant;
        }

        return $tenant->getAttribute($key);
    }
}

if (! function_exists('tenantify')) {
    /**
     * Get the instance of Tenantify.
     */
    function tenantify(): Tenantify
    {
        return app(Tenantify::class);
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Retrieve the current tenant's ID.
     */
    function tenant_id(): int|string|null
    {
        $tenant = tenant();

        if (is_null($tenant)) {
            return null;
        }

        return $tenant->getTenantKey();
    }
}
