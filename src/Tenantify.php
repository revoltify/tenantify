<?php

declare(strict_types=1);

namespace Revoltify\Tenantify;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Revoltify\Tenantify\Events\TenantEnded;
use Revoltify\Tenantify\Events\TenantInitialized;
use Revoltify\Tenantify\Exceptions\TenantNotFoundException;
use Revoltify\Tenantify\Managers\BootstrapperManager;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;

final class Tenantify
{
    private ?TenantInterface $tenant = null;

    private bool $initialized = false;

    public function __construct(
        private readonly BootstrapperManager $bootstrapper
    ) {}

    public function initialize(TenantInterface|int|string $tenant): void
    {
        if (! $tenant instanceof TenantInterface) {
            $tenantId = $tenant;
            $tenant = $this->find($tenantId);

            if (! $tenant instanceof TenantInterface) {
                throw TenantNotFoundException::make($tenantId);
            }
        }

        if ($this->initialized) {
            $this->terminate();
        }

        $this->tenant = $tenant;

        $this->initialized = true;

        Event::dispatch(new TenantInitialized($tenant));

        $this->bootstrapper->bootstrap($tenant);
    }

    public function terminate(): void
    {
        if ($this->initialized && $this->tenant instanceof TenantInterface) {

            $this->bootstrapper->revert();

            $tenant = $this->tenant;
            Event::dispatch(new TenantEnded($tenant));

            $this->tenant = null;

            $this->initialized = false;
        }
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function tenant(): ?TenantInterface
    {
        return $this->tenant;
    }

    public function find(int|string $id): ?TenantInterface
    {
        /** @var class-string<Model> $tenantModel */
        $tenantModel = config('tenantify.models.tenant', Tenant::class);

        /** @var TenantInterface|null $tenant */
        $tenant = $tenantModel::whereId($id)->first();

        return $tenant;
    }

    public function getResolver(): ResolverInterface
    {
        return resolve(ResolverInterface::class);
    }
}
