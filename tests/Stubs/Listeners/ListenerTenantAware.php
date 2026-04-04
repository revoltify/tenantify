<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Revoltify\Tenantify\Jobs\TenantAware;

final class ListenerTenantAware implements ShouldQueue, TenantAware
{
    public function handle(): void {}
}
