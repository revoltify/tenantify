<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Middleware;

use Closure;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Resolvers\DomainResolver;
use Revoltify\Tenantify\Tenantify;

class InitializeTenantifyByDomain
{
    public function __construct(
        protected Tenantify $tenantify,
        protected DomainResolver $resolver
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $this->tenantify->isInitialized()) {
            $tenant = $this->resolver->resolve();
            $this->tenantify->initialize($tenant);
        }

        return $next($request);
    }
}
