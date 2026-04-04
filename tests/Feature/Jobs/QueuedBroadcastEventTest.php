<?php

declare(strict_types=1);

use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Events\BroadcastNotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Events\BroadcastTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Events\TestEvent;
use Revoltify\Tenantify\Tests\Stubs\Listeners\ListenerNotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Listeners\ListenerTenantAware;

beforeEach(function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');
    config()->set('mail.default', 'log');

    $this->tenant = Tenant::query()->create(['name' => 'Test']);
});

it('will fail when no tenant is present and listeners are tenant aware by default', function (): void {
    Event::listen(TestEvent::class, ListenerTenantAware::class);

    Broadcast::event(new BroadcastTenantAware('Hello world!'));
})->throws(TenantNotFoundInTenantAwareJobException::class);

it('will not fail when no tenant is present and listeners are tenant aware by default', function (): void {
    Event::listen(TestEvent::class, ListenerNotTenantAware::class);
    Broadcast::event(new BroadcastNotTenantAware('Hello world!'));

    $this->expectExceptionMessage('Method '.Dispatcher::class.'::assertDispatchedTimes does not exist.');

    Event::assertDispatchedTimes(TestEvent::class);
});

it('will inject the current tenant id', function (): void {
    $this->tenant->initialize();

    Event::listen(TestEvent::class, ListenerNotTenantAware::class);

    expect(
        Broadcast::event(new BroadcastTenantAware('Hello world!'))
    )->toBeInstanceOf(PendingBroadcast::class);
});
