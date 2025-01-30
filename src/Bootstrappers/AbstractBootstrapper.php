<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Bootstrappers;

use Revoltify\Tenantify\Bootstrappers\Contracts\BootstrapperInterface;

abstract class AbstractBootstrapper implements BootstrapperInterface
{
    protected int $priority = 0;

    public function getPriority(): int
    {
        return $this->priority;
    }
}
