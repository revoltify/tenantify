<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Concerns;

use Revoltify\Tenantify\Models\Contracts\TenantInterface;

trait ImplementsTenant
{
    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getTenantKey(): int|string
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function makeCurrent(): static
    {
        tenantify()->initialize($this);

        return $this;
    }

    public function forget(): static
    {
        tenantify()->end();

        return $this;
    }

    public static function current(): ?static
    {
        if (! app()->has(TenantInterface::class)) {
            return null;
        }

        return app(TenantInterface::class);
    }

    public static function checkCurrent(): bool
    {
        return static::current() !== null;
    }

    public function isCurrent(): bool
    {
        return static::current()?->getTenantKey() === $this->getTenantKey();
    }

    public static function forgetCurrent(): ?static
    {
        return tap(static::current(), fn (?TenantInterface $tenant) => $tenant?->forget());
    }
}
