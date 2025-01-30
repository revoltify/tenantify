<?php

use Illuminate\Contracts\Bus\Dispatcher;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Jobs\TestJob;
use Spatie\Valuestore\Valuestore;

beforeEach(function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');

    $this->tenant = Tenant::create(['name' => 'Test']);

    $this->valuestore = Valuestore::make(tempFile('tenantAware.json'))->flush();
});

it('will check if updating the current tenant, the next job uses fresh data', function () {
    $this->tenant->makeCurrent();

    $tenantOriginalName = $this->tenant->name;

    app(Dispatcher::class)->dispatch(new TestJob($this->valuestore));

    $this->artisan('queue:work --once');

    expect($this->valuestore->get('tenantName'))->toBe($tenantOriginalName);

    $tenantUpdatedName = $tenantOriginalName.' - Edited';

    Tenant::query()
        ->where('id', $this->tenant->id)
        ->update(['name' => $tenantUpdatedName]);

    app(Dispatcher::class)->dispatch(new TestJob($this->valuestore));

    $this->artisan('queue:work --once');

    expect($this->valuestore->get('tenantName'))->toBe($tenantUpdatedName);
});
