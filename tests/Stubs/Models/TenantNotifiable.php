<?php

namespace Revoltify\Tenantify\Tests\Stubs\Models;

use Illuminate\Notifications\Notifiable;
use Revoltify\Tenantify\Models\Tenant;

class TenantNotifiable extends Tenant
{
    use Notifiable;

    protected $table = 'tenants';

    protected $appends = [
        'email',
    ];

    public function getEmailAttribute()
    {
        return 'test@revoltify.net';
    }
}
