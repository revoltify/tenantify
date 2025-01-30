<?php

namespace Revoltify\Tenantify\Tests\Stubs\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Revoltify\Tenantify\Jobs\TenantAware;
use Revoltify\Tenantify\Tests\Stubs\Events\TestEvent;

class ListenerTenantAware implements ShouldQueue, TenantAware
{
    public function handle(TestEvent $event): void {}
}
