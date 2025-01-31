<?php

namespace Revoltify\Tenantify\Contracts;

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
