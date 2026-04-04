<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class SessionBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 10;

    private ?string $originalPrefix = null;

    public function bootstrap(TenantInterface $tenant): void
    {
        $prefix = config('session.cookie');
        $this->originalPrefix = is_string($prefix) ? $prefix : '';

        config(['session.cookie' => $this->generatePrefix($tenant)]);
    }

    public function revert(): void
    {
        config(['session.cookie' => $this->originalPrefix]);
    }

    private function generatePrefix(TenantInterface $tenant): string
    {
        $tenantPrefix = config('tenantify.session.prefix', 'tenant');
        $tenantPrefix = is_string($tenantPrefix) ? $tenantPrefix : 'tenant';

        return str((string) $this->originalPrefix)
            ->rtrim('_')
            ->append('_')
            ->append($tenantPrefix)
            ->append('_')
            ->append((string) $tenant->getTenantKey())
            ->toString();
    }
}
