<?php

namespace Revoltify\Tenantify\Tests\Stubs\Jobs;

use Revoltify\Tenantify\Jobs\NotTenantAware;

class NotTenantAwareTestJob extends TestJob implements NotTenantAware {}
