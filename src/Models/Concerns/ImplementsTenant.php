<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Concerns;

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

    public function initialize(): static
    {
        tenantify()->initialize($this);

        return $this;
    }

    public function terminate(): static
    {
        tenantify()->terminate();

        return $this;
    }

    public static function current(): ?static
    {
        return tenant();
    }

    public static function hasCurrent(): bool
    {
        return static::current() !== null;
    }

    public function isCurrent(): bool
    {
        return static::current()?->getTenantKey() === $this->getTenantKey();
    }
}
