<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Middleware;

use Closure;
use Illuminate\Http\Request;
use Revoltify\Tenantify\Concerns\InitializesTenant;
use Revoltify\Tenantify\Tenantify;

class InitializeTenantify
{
    use InitializesTenant;

    public function __construct(
        protected Tenantify $tenantify
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $this->tenantify->isInitialized()) {
            $this->initializeTenantify();
        }

        return $next($request);
    }
}
