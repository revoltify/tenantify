<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Revoltify\Tenantify\Managers\CacheManager;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class CacheBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 20;

    private ?Factory $originalCache = null;

    public function __construct(private readonly Application $app) {}

    public function bootstrap(TenantInterface $tenant): void
    {
        $this->resetFacadeCache();

        $this->originalCache ??= $this->app->make(Factory::class);

        $this->app->extend('cache', fn (): CacheManager => new CacheManager($this->app));
    }

    public function revert(): void
    {
        $this->resetFacadeCache();

        $this->app->extend('cache', fn (): ?Factory => $this->originalCache);

        $this->originalCache = null;
    }

    public function resetFacadeCache(): void
    {
        Cache::clearResolvedInstances();
    }
}
