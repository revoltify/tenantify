<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Cache\CacheManager as BaseCacheManager;

class CacheManager extends BaseCacheManager
{
    protected function getPrefix(array $config)
    {
        return str(parent::getPrefix($config))
            ->rtrim('_')
            ->append('_')
            ->append(config('tenantify.cache.prefix', 'tenant'))
            ->append('_')
            ->append(tenant()->getTenantKey())
            ->append('_')
            ->toString();
    }
}
