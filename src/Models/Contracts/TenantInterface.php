<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Contracts;

interface TenantInterface
{
    public function getTenantKey(): int|string;

    public function getTenantKeyName(): string;

    public static function current(): ?static;

    public static function checkCurrent(): bool;

    public static function forgetCurrent(): ?static;

    public function makeCurrent(): static;

    public function forget(): static;

    public function isCurrent(): bool;
}
