<?php

declare(strict_types=1);

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Tests\Stubs\Mail\MailableNotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Mail\MailableTenantAware;

beforeEach(function (): void {
    config()->set('tenantify.queue.tenant_aware_by_default', true);
    config()->set('queue.default', 'sync');
    config()->set('mail.default', 'log');

    $this->tenant = Tenant::query()->create(['name' => 'Test']);
});

it('will fail when no tenant is present and mailables are tenant aware by default', function (): void {
    Mail::to('test@revoltify.net')->queue(new MailableTenantAware);
})->throws(TenantNotFoundInTenantAwareJobException::class);

it('will not fail when no tenant is present and mailables are tenant aware by default', function (): void {
    Mail::to('test@revoltify.net')->queue(new MailableNotTenantAware);

    $this->expectExceptionMessage('Method '.Mailer::class.'::assertSentCount does not exist.');

    Mail::assertSentCount(1);
});

it('will inject the current tenant id', function (): void {
    $this->tenant->initialize();

    expect(
        Mail::to('test@revoltify.net')->queue(new MailableTenantAware)
    )->toEqual(0);
});
