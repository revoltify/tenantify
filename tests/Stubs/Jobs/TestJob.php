<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Revoltify\Tenantify\Models\Tenant;
use Spatie\Valuestore\Valuestore;

class TestJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Valuestore $valuestore) {}

    public function handle(): void
    {
        $this->valuestore->put('tenantId', Tenant::current()?->id);
        $this->valuestore->put('tenantName', Tenant::current()?->name);
    }
}
