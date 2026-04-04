<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Cache\CacheManager as BaseCacheManager;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class CacheManager extends BaseCacheManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    protected function getPrefix(array $config)
    {
        /** @var TenantInterface|null $tenant */
        $tenant = tenant();

        $prefix = config('tenantify.cache.prefix', 'tenant');
        $prefix = is_string($prefix) ? $prefix : 'tenant';

        return str((string) parent::getPrefix($config))
            ->rtrim('_')
            ->append('_')
            ->append($prefix)
            ->append('_')
            ->append((string) ($tenant?->getTenantKey() ?? ''))
            ->append('_')
            ->toString();
    }
}
