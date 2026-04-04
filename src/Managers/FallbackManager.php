<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Support\Facades\View;
use Revoltify\Tenantify\Exceptions\TenantNotFoundException;
use Revoltify\Tenantify\Managers\Contracts\FallbackHandlerInterface;

final class FallbackManager implements FallbackHandlerInterface
{
    public function handle(string $domain)
    {
        $fallbackType = config('tenantify.initialization.fallback.type', 'abort');

        return match ($fallbackType) {
            'throw' => $this->handleThrow($domain),
            'view' => $this->handleView($domain),
            'redirect' => $this->handleRedirect($domain),
            'abort' => $this->handleAbort($domain),
            'custom' => $this->handleCustom($domain),
            default => $this->handleThrow($domain),
        };
    }

    private function handleThrow(string $domain)
    {
        throw TenantNotFoundException::forDomain($domain);
    }

    private function handleView(string $domain)
    {
        $view = config('tenantify.initialization.fallback.view', 'errors.tenant-not-found');

        if (! View::exists($view)) {
            return $this->handleThrow($domain);
        }

        return response()->view($view, [
            'domain' => $domain,
        ]);
    }

    private function handleRedirect(string $domain)
    {
        $redirectTo = config('tenantify.initialization.fallback.redirect_to', '/');

        return redirect($redirectTo);
    }

    private function handleAbort(string $domain)
    {
        $statusCode = config('tenantify.initialization.fallback.status_code', 404);

        abort($statusCode);
    }

    private function handleCustom(string $domain)
    {
        $handlerClass = config('tenantify.initialization.fallback.handler');

        if (! $handlerClass || ! class_exists($handlerClass)) {
            return $this->handleThrow($domain);
        }

        $handler = app($handlerClass);

        if (! $handler instanceof FallbackHandlerInterface) {
            return $this->handleThrow($domain);
        }

        return $handler->handle($domain);
    }
}
