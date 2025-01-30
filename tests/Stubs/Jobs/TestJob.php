<?php

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Revoltify\Tenantify\Models\Tenant;
use Spatie\Valuestore\Valuestore;

class TestJob implements ShouldQueue
{
    use Queueable;

    public Valuestore $valuestore;

    public function __construct(Valuestore $valuestore)
    {
        $this->valuestore = $valuestore;
    }

    public function handle()
    {
        $this->valuestore->put('tenantId', Tenant::current()?->id);
        $this->valuestore->put('tenantName', Tenant::current()?->name);
    }
}
