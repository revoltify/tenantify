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

class Tenantify
{
    protected ?TenantInterface $tenant = null;

    protected bool $initialized = false;

    public function __construct(
        protected BootstrapperManager $bootstrapper
    ) {}

    public function initialize(TenantInterface|int|string $tenant): void
    {
        if (! $tenant instanceof TenantInterface) {
            $tenantId = $tenant;
            $tenant = $this->find($tenantId);

            if (! $tenant) {
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
        if ($this->isInitialized()) {

            $this->bootstrapper->revert();

            Event::dispatch(new TenantEnded($this->tenant));

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

    public function find(int|string $id): TenantInterface|Model|null
    {
        $tenantModel = config('tenantify.models.tenant', Tenant::class);

        return $tenantModel::whereId($id)->first();
    }

    public function getResolver(): ResolverInterface
    {
        return app(ResolverInterface::class);
    }
}
