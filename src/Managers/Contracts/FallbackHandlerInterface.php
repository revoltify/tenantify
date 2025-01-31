<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers\Contracts;

interface FallbackHandlerInterface
{
    /**
     * Handle tenant not found scenario
     *
     * @param  string  $domain  Current domain that failed resolution
     * @return mixed
     */
    public function handle(string $domain);
}
