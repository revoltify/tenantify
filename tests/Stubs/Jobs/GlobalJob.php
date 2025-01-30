<?php

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Revoltify\Tenantify\Jobs\NotTenantAware;

class GlobalJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public function handle()
    {
        return tenant()->id ?? null;
    }
}
