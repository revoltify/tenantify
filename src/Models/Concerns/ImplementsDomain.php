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
        return resolve(DomainInterface::class);
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
        $value = $this->getAttribute($this->getDomainKeyName());

        return is_numeric($value) ? (int) $value : (is_string($value) ? $value : '');
    }

    public function isCurrent(): bool
    {
        return static::current()?->getDomainKey() === $this->getDomainKey();
    }
}
