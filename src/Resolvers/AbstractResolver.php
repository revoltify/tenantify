<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Resolvers;

use Closure;
use Exception;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Models\Contracts\DomainInterface;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;

abstract class AbstractResolver implements ResolverInterface
{
    /** @var DomainInterface|null */
    public static $currentDomain;

    protected bool $useCache = false;

    protected int $cacheTTL = 3600;

    protected string $cachePrefix;

    public function __construct(
        protected Request $request,
        protected Cache $cache
    ) {
        $this->initializeCache();
    }

    abstract protected function findTenant(string $identifier): TenantInterface;

    abstract protected function getIdentifierFromRequest(): string;

    final public function clearCache(string $key): void
    {
        if ($this->useCache) {
            $this->cache->forget($this->getCacheKey($key));
        }
    }

    final public function clearCurrentCache(): void
    {
        $identifier = $this->getIdentifierFromRequest();
        $this->clearCache($identifier);
    }

    final public function clearAllCache(): void
    {
        if ($this->useCache) {
            $this->cache->getStore()->flush();
        }
    }

    final public function resolve(): ?TenantInterface
    {
        $identifier = $this->getIdentifierFromRequest();

        $tenant = $this->findTenant($identifier);

        $this->setCurrentDomain($tenant);

        try {
            /** @var TenantInterface|null $resolvedTenant */
            $resolvedTenant = $this->remember(
                $identifier,
                fn (): TenantInterface => $tenant
            );

            return $resolvedTenant;
        } catch (Exception $exception) {
            $this->clearCache($identifier);
            throw $exception;
        }
    }

    protected function initializeCache(): void
    {
        $this->useCache = (bool) config('tenantify.resolver.cache.enabled', false);

        $ttl = config('tenantify.resolver.cache.ttl', 3600);
        $this->cacheTTL = is_numeric($ttl) ? (int) $ttl : 3600;

        $this->cachePrefix = $this->getCachePrefix();
    }

    protected function getCachePrefix(): string
    {
        return 'tenant_'.mb_strtolower(class_basename($this)).'_';
    }

    protected function getCacheKey(string $key): string
    {
        return $this->cachePrefix.md5($key);
    }

    /**
     * @template TCacheValue
     *
     * @param  Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    protected function remember(string $key, Closure $callback): mixed
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

    protected function getTenantModel(): string
    {
        $model = config('tenantify.models.tenant', Tenant::class);

        return is_string($model) ? $model : Tenant::class;
    }

    private function setCurrentDomain(TenantInterface $tenant): void
    {
        $domain = $tenant->domains()->where('domain', $this->request->getHost())->first();

        /** @var DomainInterface|null $domainInterface */
        $domainInterface = $domain;

        static::$currentDomain = $domainInterface;
    }
}
