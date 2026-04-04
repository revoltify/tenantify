<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface TenantInterface
{
    public static function current(): ?static;

    public static function hasCurrent(): bool;

    /**
     * @return HasMany<Model, Model>
     */
    public function domains(): HasMany;

    public function getTenantKey(): int|string;

    public function getTenantKeyName(): string;

    public function initialize(): static;

    public function terminate(): static;

    public function isCurrent(): bool;
}
