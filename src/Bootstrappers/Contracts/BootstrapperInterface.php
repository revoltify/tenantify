<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers\Contracts;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

interface BootstrapperInterface
{
    public function bootstrap(TenantInterface $tenant): void;

    public function revert(): void;

    public function getPriority(): int;
}
