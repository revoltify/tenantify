<?php

use Illuminate\Contracts\Bus\Dispatcher;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Jobs\NotTenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TenantAwareTestJob;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TestJob;
use Spatie\Valuestore\Valuestore;

beforeEach(function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');

    $this->tenant = Tenant::create(['name' => 'Test']);

    $this->valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();
});

it('will fail a job when no tenant is present and queues are tenant aware by default', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $job = new TestJob($this->valuestore);

    try {
        app(Dispatcher::class)->dispatch($job);
    } catch (TenantNotFoundInTenantAwareJobException $exception) {
        // Assert the job did not run
        expect($this->valuestore->has('tenantId'))->toBeFalse();

        return;
    }

    $this->fail();
});

it('will fail a job when no tenant is present and job implements the TenantAware interface', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $job = new TenantAwareTestJob($this->valuestore);

    try {
        app(Dispatcher::class)->dispatch($job);
    } catch (TenantNotFoundInTenantAwareJobException $exception) {
        expect($this->valuestore)->has('tenantId')->toBeFalse();

        return;
    }

    $this->fail();
});

it('will not fail a job when no tenant is present and queues are not tenant aware by default', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', false);

    $job = new TestJob($this->valuestore);

    app(Dispatcher::class)->dispatch($job);

    expect($this->valuestore)
        ->has('tenantId')->toBeTrue()
        ->get('tenantId')->toBeNull();
});

test(
    'it will not fail a job when no tenant is present and job implements the NotTenantAware interface',
    function () {
        config()->set('tenantify.queue.tenant_aware_by_default', true);

        $job = new NotTenantAwareTestJob($this->valuestore);

        app(Dispatcher::class)->dispatch($job);

        expect($this->valuestore)
            ->has('tenantId')->toBeTrue()
            ->get('tenantId')->toBeNull();
    }
);

it('will forget any current tenant when starting a not tenant aware job', function () {
    $this->tenant->makeCurrent();

    $job = new NotTenantAwareTestJob($this->valuestore);

    // Simulate a tenant being set from a previous queue job
    expect(Tenant::checkCurrent())->toBeTrue();

    app(Dispatcher::class)->dispatch($job);

    // Assert that the active tenant was forgotten
    $this->assertNull(Tenant::current());
});
