<?php

use Illuminate\Contracts\Bus\Dispatcher;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TestJob;
use Spatie\Valuestore\Valuestore;

beforeEach(function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');
    config()->set('mail.default', 'log');

    $this->tenant = Tenant::create(['name' => 'Test']);
    $this->valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();
});

it('succeeds with jobs in tenant aware jobs list', function () {
    config()->set('tenantify.queue.tenant_aware_jobs', [TestJob::class]);

    $this->tenant->makeCurrent();

    app(Dispatcher::class)->dispatch(new TestJob($this->valuestore));

    expect($this->valuestore->get('tenantId'))->toBe($this->tenant->getTenantKey())
        ->and($this->valuestore->get('tenantName'))->toBe($this->tenant->name);
});

it('fails with jobs in not tenant aware jobs list', function () {
    config()->set('tenantify.queue.not_tenant_aware_jobs', [TestJob::class]);

    $this->tenant->makeCurrent();

    app(Dispatcher::class)->dispatch(new TestJob($this->valuestore));

    expect($this->valuestore->get('tenantId'))->toBeNull()
        ->and($this->valuestore->get('tenantName'))->toBeNull();
});
