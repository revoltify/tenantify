<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Concerns;

use Revoltify\Tenantify\Models\Contracts\DomainInterface;

trait ImplementsDomain
{
    public static function current(): ?static
    {
        if (! app()->bound(DomainInterface::class)) {
            return null;
        }

        /** @var static */
        return app(DomainInterface::class);
    }

    public static function hasCurrent(): bool
    {
        return static::current() !== null;
    }

    public function getDomainKeyName(): string
    {
        return $this->getKeyName();
    }

    public function getDomainKey(): int|string
    {
        return $this->getAttribute($this->getDomainKeyName());
    }

    public function isCurrent(): bool
    {
        return static::current()?->getDomainKey() === $this->getDomainKey();
    }
}
