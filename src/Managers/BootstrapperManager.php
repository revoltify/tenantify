<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Revoltify\Tenantify\Bootstrappers\Contracts\BootstrapperInterface;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class BootstrapperManager
{
    /**
     * @var array<int, BootstrapperInterface>
     */
    private array $bootstrappers = [];

    public function addBootstrapper(BootstrapperInterface $bootstrapper): void
    {
        $this->bootstrappers[] = $bootstrapper;
        usort($this->bootstrappers, fn (BootstrapperInterface $a, BootstrapperInterface $b): int => $a->getPriority() - $b->getPriority());
    }

    public function bootstrap(TenantInterface $tenant): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap($tenant);
        }
    }

    public function revert(): void
    {
        foreach (array_reverse($this->bootstrappers) as $bootstrapper) {
            $bootstrapper->revert();
        }
    }
}
