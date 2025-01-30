<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Spatie\Permission\PermissionRegistrar;

class SpatiePermissionsBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 50;

    protected ?PermissionRegistrar $registrar;

    public function __construct()
    {
        // Check if Spatie Permission class exists
        if (class_exists('Spatie\Permission\PermissionRegistrar')) {
            $this->registrar = app(PermissionRegistrar::class);
        } else {
            $this->registrar = null;
        }
    }

    public function bootstrap(TenantInterface $tenant): void
    {
        // Only execute if Spatie Permissions is installed
        if ($this->registrar) {
            $this->registrar->cacheKey = 'tenant_'.$tenant->getTenantKey().'_permission';
        }
    }

    public function revert(): void
    {
        // Only execute if Spatie Permissions is installed
        if ($this->registrar) {
            $this->registrar->cacheKey = 'permission';
        }
    }
}
