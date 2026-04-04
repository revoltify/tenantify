<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Concerns;

trait ImplementsTenant
{
    public static function current(): ?static
    {
        /** @var static|null $tenant */
        $tenant = tenant();

        return $tenant;
    }

    public static function hasCurrent(): bool
    {
        return static::current() !== null;
    }

    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getTenantKey(): int|string
    {
        $value = $this->getAttribute($this->getTenantKeyName());

        return is_numeric($value) ? (int) $value : (is_string($value) ? $value : '');
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

    public function isCurrent(): bool
    {
        return static::current()?->getTenantKey() === $this->getTenantKey();
    }
}
