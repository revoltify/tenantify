<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Revoltify\Tenantify\Managers\CacheManager;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

class CacheBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 20;

    protected $originalCache;

    public function __construct(protected Application $app) {}

    public function bootstrap(TenantInterface $tenant): void
    {
        $this->resetFacadeCache();

        $this->originalCache = $this->originalCache ?? $this->app['cache'];

        $this->app->extend('cache', function () {
            return new CacheManager($this->app);
        });
    }

    public function revert(): void
    {
        $this->resetFacadeCache();

        $this->app->extend('cache', function () {
            return $this->originalCache;
        });

        $this->originalCache = null;
    }

    public function resetFacadeCache()
    {
        Cache::clearResolvedInstances();
    }
}
