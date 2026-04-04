<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Revoltify\Tenantify\Jobs\NotTenantAware;
use Revoltify\Tenantify\Tests\Stubs\Events\TestEvent;

final class ListenerNotTenantAware implements NotTenantAware, ShouldQueue
{
    public function handle(TestEvent $event): void {}
}
