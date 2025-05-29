<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Resolvers;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;

abstract class AbstractResolver implements ResolverInterface
{
    protected bool $useCache;

    protected int $cacheTTL;

    protected string $cachePrefix;

    public static $currentDomain;

    public function __construct(
        protected Request $request,
        protected Cache $cache
    ) {
        $this->initializeCache();
    }

    protected function initializeCache(): void
    {
        $this->useCache = config('tenantify.resolver.cache.enabled', false);
        $this->cacheTTL = config('tenantify.resolver.cache.ttl', 3600);
        $this->cachePrefix = $this->getCachePrefix();
    }

    protected function getCachePrefix(): string
    {
        return 'tenant_'.strtolower(class_basename($this)).'_';
    }

    protected function getCacheKey(string $key): string
    {
        return $this->cachePrefix.md5($key);
    }

    public function clearCache(string $key): void
    {
        if ($this->useCache) {
            $this->cache->forget($this->getCacheKey($key));
        }
    }

    public function clearCurrentCache(): void
    {
        $identifier = $this->getIdentifierFromRequest();
        $this->clearCache($identifier);
    }

    public function clearAllCache(): void
    {
        if ($this->useCache) {
            $this->cache->getStore()->flush();
        }
    }

    protected function remember(string $key, callable $callback): mixed
    {
        if (! $this->useCache) {
            return $callback();
        }

        return $this->cache->remember(
            $this->getCacheKey($key),
            $this->cacheTTL,
            $callback
        );
    }

    public function resolve(): ?TenantInterface
    {
        $identifier = $this->getIdentifierFromRequest();

        $tenant = $this->findTenant($identifier);

        $this->setCurrentDomain($tenant);

        try {
            return $this->remember(
                $identifier,
                fn () => $tenant
            );
        } catch (\Exception $e) {
            $this->clearCache($identifier);
            throw $e;
        }
    }

    private function setCurrentDomain(TenantInterface $tenant): void
    {
        /** @phpstan-ignore-next-line */
        static::$currentDomain = $tenant->domains->where('domain', $this->request->getHost())->first();
    }

    protected function getTenantModel(): string
    {
        return config('tenantify.models.tenant', Tenant::class);
    }

    abstract protected function findTenant(string $identifier): ?TenantInterface;

    abstract protected function getIdentifierFromRequest(): string;
}
