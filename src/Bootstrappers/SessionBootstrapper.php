<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

class SessionBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 10;

    private string $originalPrefix;

    public function bootstrap(TenantInterface $tenant): void
    {
        $this->originalPrefix = config('session.cookie');

        config(['session.cookie' => $this->generatePrefix($tenant)]);
    }

    public function revert(): void
    {
        config(['session.cookie' => $this->originalPrefix]);
    }

    private function generatePrefix(TenantInterface $tenant)
    {
        return str($this->originalPrefix)
            ->rtrim('_')
            ->append('_')
            ->append(config('tenantify.session.prefix', 'tenant'))
            ->append('_')
            ->append($tenant->getTenantKey())
            ->toString();
    }
}
