<?php

declare(strict_types=1);

use Revoltify\Tenantify\Models\Tenant;
use Spatie\Valuestore\Valuestore;

beforeEach(function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $this->tenant = Tenant::query()->create(['name' => 'Test']);
});

it('succeeds with closure job when queues are tenant aware by default', function (): void {
    $valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();

    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->initialize();

    dispatch(function () use ($valuestore): void {
        $tenant = Tenant::current();

        $valuestore->put('tenantId', $tenant?->getTenantKey());
        $valuestore->put('tenantName', $tenant?->name);
    });

    $this->artisan('queue:work --once')->assertExitCode(0);

    expect($valuestore->get('tenantId'))->toBe($this->tenant->getTenantKey())
        ->and($valuestore->get('tenantName'))->toBe($this->tenant->name);
});

it('fails with closure job when queues are not tenant aware by default', function (): void {
    $valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();

    $this->tenant->initialize();

    dispatch(function () use ($valuestore): void {
        $tenant = Tenant::current();

        $valuestore->put('tenantId', $tenant?->getTenantKey());
        $valuestore->put('tenantName', $tenant?->name);
    });

    $this->artisan('queue:work --once')->assertExitCode(0);

    expect($valuestore->get('tenantId'))->toBeNull()
        ->and($valuestore->get('tenantName'))->toBeNull();
});

it('succeeds with closure job when a tenant is specified', function (): void {
    $valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();

    $currentTenant = $this->tenant;

    dispatch(function () use ($valuestore, $currentTenant): void {
        $currentTenant->initialize();

        $tenant = Tenant::current();

        $valuestore->put('tenantId', $tenant?->getTenantKey());
        $valuestore->put('tenantName', $tenant?->name);

        $currentTenant->terminate();
    });

    $this->artisan('queue:work --once')->assertExitCode(0);

    expect($valuestore->get('tenantId'))->toBe($this->tenant->getTenantKey())
        ->and($valuestore->get('tenantName'))->toBe($this->tenant->name);
});
