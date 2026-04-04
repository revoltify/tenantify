<?php

declare(strict_types=1);

use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Models\UserTest;

it('can create a tenant', function (): void {
    $tenant = Tenant::query()->create([
        'name' => 'Test Tenant',
    ]);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'Test Tenant',
    ]);
});

test('tenant can be manually initialized', function (): void {
    // Test with Tenant model
    $tenant = Tenant::query()->create(['name' => 'Manual Corp']);
    $tenant->initialize();

    expect(tenantify()->isInitialized())->toBeTrue()
        ->and(tenant('id'))->toBe($tenant->id);

    $tenant->terminate();

    // Test with tenant_id
    tenantify()->initialize($tenant->id);

    expect(tenantify()->isInitialized())->toBeTrue()
        ->and(tenant()->id)->toBe($tenant->id);
});

test('can create and manage users for multiple tenants', function (): void {
    // Create first tenant and its user
    $tenant1 = Tenant::query()->create(['name' => 'First Corp']);
    $tenant1->initialize();

    $user1 = UserTest::query()->create([
        'name' => 'User One',
        'email' => 'user1@first.com',
        'password' => bcrypt('password'),
    ]);

    // Create second tenant and its user
    $tenant2 = Tenant::query()->create(['name' => 'Second Corp']);
    $tenant2->initialize();

    $user2 = UserTest::query()->create([
        'name' => 'User Two',
        'email' => 'user2@second.com',
        'password' => bcrypt('password'),
    ]);

    // Assert users belong to correct tenants
    expect($user1->tenant_id)->toBe($tenant1->id)
        ->and($user2->tenant_id)->toBe($tenant2->id);

    // Assert querying users respects current tenant
    $tenant1->initialize();
    expect(UserTest::query()->count())->toBe(1)
        ->and(UserTest::query()->first()->email)->toBe('user1@first.com');

    $tenant2->initialize();
    expect(UserTest::query()->count())->toBe(1)
        ->and(UserTest::query()->first()->email)->toBe('user2@second.com');
});
