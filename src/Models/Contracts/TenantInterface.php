<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Contracts;

interface TenantInterface
{
    public function getTenantKey(): int|string;

    public function getTenantKeyName(): string;

    public function initialize(): static;

    public function terminate(): static;

    public static function current(): ?static;

    public static function hasCurrent(): bool;

    public function isCurrent(): bool;
}
