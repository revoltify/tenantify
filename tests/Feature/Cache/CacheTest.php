<?php

use Illuminate\Support\Facades\Cache;
use Revoltify\Tenantify\Facades\GlobalCache;
use Revoltify\Tenantify\Models\Tenant;

test('cache is tenant aware while global cache is not', function () {
    // Setup tenants
    $tenant1 = Tenant::create(['name' => 'Cache Corp 1']);
    $tenant2 = Tenant::create(['name' => 'Cache Corp 2']);

    // Test tenant-aware cache
    tenantify()->initialize($tenant1);
    Cache::put('test-key', 'tenant1-value');

    tenantify()->initialize($tenant2);
    Cache::put('test-key', 'tenant2-value');

    // Assert tenant isolation
    tenantify()->initialize($tenant1);
    expect(Cache::get('test-key'))->toBe('tenant1-value');

    tenantify()->initialize($tenant2);
    expect(Cache::get('test-key'))->toBe('tenant2-value');

    // Test global cache
    GlobalCache::put('global-key', 'global-value');

    tenantify()->initialize($tenant1);
    expect(GlobalCache::get('global-key'))->toBe('global-value');

    tenantify()->initialize($tenant2);
    expect(GlobalCache::get('global-key'))->toBe('global-value');
});
