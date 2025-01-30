<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Revoltify\Tenantify\Models\Contracts\DomainInterface;

class Domain extends Model implements DomainInterface
{
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        $tenantClass = config('tenantify.models.tenant', Tenant::class);

        return $this->belongsTo($tenantClass);
    }
}
