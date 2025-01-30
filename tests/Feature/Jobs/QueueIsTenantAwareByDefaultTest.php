<?php

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Jobs\NotTenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TestJob;
use Spatie\Valuestore\Valuestore;

beforeEach(function () {
    Event::fake(JobFailed::class);

    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant = Tenant::create(['name' => 'Test']);

    $this->valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();

    Event::assertNotDispatched(JobFailed::class);
});

it('will inject the current tenant id in a job', function () {
    $this->tenant->makeCurrent();

    $job = new TestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->tenant->forget();

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($this->tenant->id)->toEqual($currentTenantIdInJob);
});

it('will inject the right tenant even when the current tenant switches', function () {
    $anotherTenant = Tenant::create(['name' => 'Test 2']);

    $this->tenant->makeCurrent();

    $job = new TestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once');

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($this->tenant->id)->toEqual($currentTenantIdInJob);

    $anotherTenant->makeCurrent();

    $job = new TestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once');

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($anotherTenant->id)->toEqual($currentTenantIdInJob);
});

it('will not make jobs tenant aware if the config settings is set to false', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $this->tenant->makeCurrent();

    $job = new TestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');
    expect($currentTenantIdInJob)->toBeNull();
});

it('will always make jobs tenant aware if they implement the TenantAware interface', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $this->tenant->makeCurrent();

    $job = new TenantAwareTestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');
    expect($this->tenant->id)->toEqual($currentTenantIdInJob);
});

it('will not make a job tenant aware if it implements NotTenantAware', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->makeCurrent();

    $job = new NotTenantAwareTestJob($this->valuestore);
    app(Dispatcher::class)->dispatch($job);

    $this->artisan('queue:work --once')->assertExitCode(0);

    $currentTenantIdInJob = $this->valuestore->get('tenantId');

    expect($currentTenantIdInJob)->toBeNull();
});
