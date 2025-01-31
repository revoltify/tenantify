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

trait InitializesTenant
{
    /**
     * Initialize the tenant for the current request.
     *
     * @param  ResolverInterface|null  $resolver  Custom tenant resolver
     *
     * @throws TenantInitializationException When tenant initialization critically fails
     */
    public function initializeTenantify(?ResolverInterface $resolver = null)
    {
        try {
            $resolver = $resolver ?? app()->make(ResolverInterface::class);
            $tenantify = app()->make(Tenantify::class);

            $tenant = $resolver->resolve();
            $tenantify->initialize($tenant);
        } catch (Exception $e) {
            // Handle failure in tenant initialization
            $this->handleTenantInitializationFailure($e);
        }
    }

    /**
     * Handle tenant initialization failure with proper logging and fallback mechanisms.
     *
     * @param  Exception  $e  The exception that occurred during initialization
     *
     * @throws TenantInitializationException When fallback handling fails
     */
    protected function handleTenantInitializationFailure(Exception $e): void
    {
        $request = request();
        $domain = $request->getHost();

        Log::error('Tenant initialization failed: '.$e->getMessage(), [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'domain' => $domain,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            $fallbackManager = app()->make(FallbackManager::class);
            $response = $fallbackManager->handle($domain);

            if ($response instanceof Response || $response instanceof RedirectResponse) {
                $response->send();
                exit;
            }
        } catch (Exception $e) {
            Log::critical('Fallback handling failed', [
                'message' => $e->getMessage(),
                'domain' => $domain,
            ]);

            throw new TenantInitializationException(
                'Both tenant initialization and fallback handling failed',
                previous: $e
            );
        }

    }
}
