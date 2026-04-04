<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Models;

use Illuminate\Notifications\Notifiable;
use Revoltify\Tenantify\Models\Tenant;

final class TenantNotifiable extends Tenant
{
    use Notifiable;

    protected $table = 'tenants';

    protected $appends = [
        'email',
    ];

    protected function getEmailAttribute(): string
    {
        return 'test@revoltify.net';
    }
}
