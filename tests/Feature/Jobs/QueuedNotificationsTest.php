<?php

declare(strict_types=1);

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Tests\Stubs\Models\TenantNotifiable;
use Revoltify\Tenantify\Tests\Stubs\Notifications\NotificationNotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Notifications\NotificationTenantAware;

beforeEach(function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');
    config()->set('mail.default', 'log');

    $this->tenant = TenantNotifiable::query()->create(['name' => 'Test']);
});

it('will fail when no tenant is present and mailables are tenant aware by default', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->notify((new NotificationTenantAware)->delay(now()->addSecond()));

    Notification::assertNothingSent();
})->throws(TenantNotFoundInTenantAwareJobException::class);

it('will not fail when no tenant is present and mailables are tenant aware by default', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->notify((new NotificationNotTenantAware));

    $this->expectExceptionMessage('Method '.ChannelManager::class.'::assertCount does not exist.');

    Notification::assertCount(1);
});

it('will inject the current tenant id', function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->initialize();

    $this->tenant->notify((new NotificationTenantAware)->delay(now()->addSecond()));

    $this->expectExceptionMessage('Method '.ChannelManager::class.'::assertNothingSent does not exist.');

    Notification::assertNothingSent();
});
