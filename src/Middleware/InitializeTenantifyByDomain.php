<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Middleware;

use Closure;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Concerns\InitializesTenant;
use Revoltify\Tenantify\Resolvers\DomainResolver;
use Revoltify\Tenantify\Tenantify;

final class InitializeTenantifyByDomain
{
    use InitializesTenant;

    public function __construct(
        private Tenantify $tenantify,
        private DomainResolver $resolver
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->tenantify->isInitialized()) {
            $this->initializeTenantify($this->resolver);
        }

        return $next($request);
    }
}
