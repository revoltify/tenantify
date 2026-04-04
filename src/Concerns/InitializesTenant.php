<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Concerns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Revoltify\Tenantify\Exceptions\TenantInitializationException;
use Revoltify\Tenantify\Managers\FallbackManager;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;
use Revoltify\Tenantify\Tenantify;
use Throwable;

trait InitializesTenant
{
    /**
     * Initialize the tenant for the current request.
     *
     * @param  ResolverInterface|null  $resolver  Custom tenant resolver
     *
     * @throws TenantInitializationException When tenant initialization critically fails
     */
    public function initializeTenantify(?ResolverInterface $resolver = null): void
    {
        try {
            $resolver ??= app()->make(ResolverInterface::class);
            $tenantify = app()->make(Tenantify::class);

            $tenant = $resolver->resolve();
            $tenantify->initialize($tenant);
        } catch (Exception $exception) {
            // Handle failure in tenant initialization
            $this->handleTenantInitializationFailure($exception);
        }
    }

    /**
     * Handle tenant initialization failure with proper logging and fallback mechanisms.
     *
     *
     * @param  Exception  $e  The exception that occurred during initialization
     */
    protected function handleTenantInitializationFailure(Throwable $e): void
    {
        $domain = request()->getHost();

        Log::error('Tenant initialization failed: '.$e->getMessage(), [
            'message' => $e->getMessage(),
            'domain' => $domain,
        ]);

        $fallbackManager = app()->make(FallbackManager::class);
        $response = $fallbackManager->handle($domain);

        if ($response instanceof Response || $response instanceof RedirectResponse) {
            $response->send();
            exit;
        }
    }
}
