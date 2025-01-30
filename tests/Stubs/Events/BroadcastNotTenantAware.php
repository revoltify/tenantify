<?php

namespace Revoltify\Tenantify\Tests\Stubs\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Revoltify\Tenantify\Jobs\NotTenantAware;

class BroadcastNotTenantAware implements NotTenantAware, ShouldBroadcast
{
    public function __construct(
        public string $message,
    ) {}

    public function broadcastOn()
    {
        return [
            new Channel('test-channel'),
        ];
    }
}
