<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Revoltify\Tenantify\Jobs\TenantAware;

final class TenantAwareTestJob extends TestJob implements TenantAware {}
