<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DomainInterface
{
    public function tenant(): BelongsTo;

    public function getDomainKey(): int|string;

    public function getDomainKeyName(): string;

    public static function current(): ?static;

    public static function hasCurrent(): bool;

    public function isCurrent(): bool;
}
