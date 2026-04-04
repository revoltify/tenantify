<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Revoltify\Tenantify\Models\Concerns\ImplementsDomain;
use Revoltify\Tenantify\Models\Contracts\DomainInterface;

class Domain extends Model implements DomainInterface
{
    use ImplementsDomain;

    protected $guarded = [];

    /**
     * @return BelongsTo<Tenant, $this>
     * @phpstan-ignore method.childReturnType
     */
    public function tenant(): BelongsTo
    {
        /** @var class-string<Tenant> $tenantClass */
        $tenantClass = config('tenantify.models.tenant', Tenant::class);

        return $this->belongsTo($tenantClass);
    }
}
