<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
            'redirect' => $this->handleRedirect(),
            'abort' => $this->handleAbort(),
            'custom' => $this->handleCustom($domain),
            default => $this->handleThrow($domain),
        };
    }

    private function handleThrow(string $domain): never
    {
        throw TenantNotFoundException::forDomain($domain);
    }

    /**
     * @return Response|\Illuminate\Contracts\View\View|never
     */
    private function handleView(string $domain)
    {
        $view = config('tenantify.initialization.fallback.view', 'errors.tenant-not-found');
        $view = is_string($view) ? $view : '';

        if (! View::exists($view)) {
            return $this->handleThrow($domain);
        }

        return response()->view($view, [
            'domain' => $domain,
        ]);
    }

    private function handleRedirect(): RedirectResponse
    {
        $redirectTo = config('tenantify.initialization.fallback.redirect_to', '/');
        $redirectTo = is_string($redirectTo) ? $redirectTo : '/';

        return redirect($redirectTo);
    }

    private function handleAbort(): void
    {
        $statusCode = config('tenantify.initialization.fallback.status_code', 404);
        $statusCode = is_numeric($statusCode) ? (int) $statusCode : 404;
        abort($statusCode);
    }

    /**
     * @return mixed
     */
    private function handleCustom(string $domain)
    {
        $handlerClass = config('tenantify.initialization.fallback.handler');
        $handlerClass = is_string($handlerClass) ? $handlerClass : '';

        if (! $handlerClass || ! class_exists($handlerClass)) {
            return $this->handleThrow($domain);
        }

        $handler = resolve($handlerClass);

        if (! $handler instanceof FallbackHandlerInterface) {
            return $this->handleThrow($domain);
        }

        return $handler->handle($domain);
    }
}
