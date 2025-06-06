<?php

use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Models\UserTest;

it('can create a tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Test Tenant',
    ]);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'Test Tenant',
    ]);
});

test('tenant can be manually initialized', function () {
    // Test with Tenant model
    $tenant = Tenant::create(['name' => 'Manual Corp']);
    $tenant->initialize();

    expect(tenantify()->isInitialized())->toBeTrue()
        ->and(tenant('id'))->toBe($tenant->id);

    $tenant->terminate();

    // Test with tenant_id
    tenantify()->initialize($tenant->id);

    expect(tenantify()->isInitialized())->toBeTrue()
        ->and(tenant()->id)->toBe($tenant->id);
});

test('can create and manage users for multiple tenants', function () {
    // Create first tenant and its user
    $tenant1 = Tenant::create(['name' => 'First Corp']);
    $tenant1->initialize();

    $user1 = UserTest::create([
        'name' => 'User One',
        'email' => 'user1@first.com',
        'password' => bcrypt('password'),
    ]);

    // Create second tenant and its user
    $tenant2 = Tenant::create(['name' => 'Second Corp']);
    $tenant2->initialize();

    $user2 = UserTest::create([
        'name' => 'User Two',
        'email' => 'user2@second.com',
        'password' => bcrypt('password'),
    ]);

    // Assert users belong to correct tenants
    expect($user1->tenant_id)->toBe($tenant1->id)
        ->and($user2->tenant_id)->toBe($tenant2->id);

    // Assert querying users respects current tenant
    $tenant1->initialize();
    expect(UserTest::count())->toBe(1)
        ->and(UserTest::first()->email)->toBe('user1@first.com');

    $tenant2->initialize();
    expect(UserTest::count())->toBe(1)
        ->and(UserTest::first()->email)->toBe('user2@second.com');
});
