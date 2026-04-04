<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Revoltify\Tenantify\Jobs\NotTenantAware;

final class NotTenantAwareTestJob extends TestJob implements NotTenantAware {}
