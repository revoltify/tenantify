<?php

namespace Revoltify\Tenantify\Tests\Stubs\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Revoltify\Tenantify\Jobs\TenantAware;

class BroadcastTenantAware implements ShouldBroadcast, TenantAware
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
