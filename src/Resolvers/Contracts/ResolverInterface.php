<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Resolvers\Contracts;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

interface ResolverInterface
{
    public function resolve(): ?TenantInterface;
}
