<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Jobs\NotTenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TestJob;
use Spatie\Valuestore\Valuestore;

beforeEach(function (): void {
    Event::fake(JobFailed::class);

    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant = Tenant::query()->create(['name' => 'Test']);

    $this->valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();

    Event::assertNotDispatched(JobFailed::class);
});

it('will inject the current tenant id in a job', function (): void {
    $this->tenant->initialize();

    $job = new TestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->tenant->terminate();

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($this->tenant->id)->toEqual($currentTenantIdInJob);
});

it('will inject the right tenant even when the current tenant switches', function (): void {
    $anotherTenant = Tenant::query()->create(['name' => 'Test 2']);

    $this->tenant->initialize();

    $job = new TestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once');

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($this->tenant->id)->toEqual($currentTenantIdInJob);

    $anotherTenant->initialize();

    $job = new TestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once');

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($anotherTenant->id)->toEqual($currentTenantIdInJob);
});

it('will not make jobs tenant aware if the config settings is set to false', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $this->tenant->initialize();

    $job = new TestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');
    expect($currentTenantIdInJob)->toBeNull();
});

it('will always make jobs tenant aware if they implement the TenantAware interface', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $this->tenant->initialize();

    $job = new TenantAwareTestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');
    expect($this->tenant->id)->toEqual($currentTenantIdInJob);
});

it('will not make a job tenant aware if it implements NotTenantAware', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->initialize();

    $job = new NotTenantAwareTestJob($this->valuestore);
    resolve(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($currentTenantIdInJob)->toBeNull();
});
