<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Revoltify\Tenantify\Models\Concerns\ImplementsTenant;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

class Tenant extends Model implements TenantInterface
{
    use ImplementsTenant;

    protected static $modelsShouldPreventAccessingMissingAttributes = false;

    protected $table = 'tenants';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public function domains(): HasMany
    {
        $domainClass = config('tenantify.models.domain', Domain::class);

        return $this->hasMany($domainClass);
    }
}
