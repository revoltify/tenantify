<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Events\Contracts;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

interface TenantEventInterface
{
    public function getTenant(): TenantInterface;
}
