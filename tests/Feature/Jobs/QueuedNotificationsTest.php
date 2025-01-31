<?php

use Illuminate\Support\Facades\Notification;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Tests\Stubs\Models\TenantNotifiable;
use Revoltify\Tenantify\Tests\Stubs\Notifications\NotificationNotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Notifications\NotificationTenantAware;

beforeEach(function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');
    config()->set('mail.default', 'log');

    $this->tenant = TenantNotifiable::create(['name' => 'Test']);
});

it('will fail when no tenant is present and mailables are tenant aware by default', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->notify((new NotificationTenantAware)->delay(now()->addSecond()));

    Notification::assertNothingSent();
})->throws(TenantNotFoundInTenantAwareJobException::class);

it('will not fail when no tenant is present and mailables are tenant aware by default', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->notify((new NotificationNotTenantAware));

    $this->expectExceptionMessage("Call to undefined method Illuminate\Notifications\Channels\MailChannel::assertCount()");

    Notification::assertCount(1);
});

it('will inject the current tenant id', function () {
    config()->set('tenantify.queue.tenant_aware_by_default', true);

    $this->tenant->initialize();

    $this->tenant->notify((new NotificationTenantAware)->delay(now()->addSecond()));

    $this->expectExceptionMessage("Call to undefined method Illuminate\Notifications\Channels\MailChannel::assertNothingSent()");

    Notification::assertNothingSent();
});
