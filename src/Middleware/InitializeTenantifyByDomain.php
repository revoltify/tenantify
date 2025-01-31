<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Middleware;

use Closure;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Concerns\InitializesTenant;
use Revoltify\Tenantify\Resolvers\DomainResolver;
use Revoltify\Tenantify\Tenantify;

class InitializeTenantifyByDomain
{
    use InitializesTenant;

    public function __construct(
        protected Tenantify $tenantify,
        protected DomainResolver $resolver
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $this->tenantify->isInitialized()) {
            $this->initializeTenantify($this->resolver);
        }

        return $next($request);
    }
}
