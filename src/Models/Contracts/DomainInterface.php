<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DomainInterface
{
    public function tenant(): BelongsTo;
}
