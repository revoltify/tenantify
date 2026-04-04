<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
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
        if (! app()->bound(TenantInterface::class)) {
            return null;
        }

        /** @var TenantInterface&Model $tenant */
        $tenant = resolve(TenantInterface::class);

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
        return resolve(Tenantify::class);
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

if (! function_exists('clear_tenantify_cache')) {
    /**
     * Clear tenantify cache for the current tenant or all cache.
     *
     * @param  bool  $clearAll  Whether to clear all tenant cache instead of just current tenant
     * @return bool Whether cache was successfully cleared
     */
    function clear_tenantify_cache(bool $clearAll = false): bool
    {
        try {
            $resolver = tenantify()->getResolver();

            if ($clearAll) {
                $resolver->clearAllCache();
            } else {
                $resolver->clearCurrentCache();
            }

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
