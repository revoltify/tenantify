<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Events;

use Illuminate\Queue\SerializesModels;
use Revoltify\Tenantify\Events\Contracts\TenantEventInterface;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

abstract class AbstractTenantEvent implements TenantEventInterface
{
    use SerializesModels;

    public function __construct(
        protected TenantInterface $tenant
    ) {}

    public function getTenant(): TenantInterface
    {
        return $this->tenant;
    }
}
