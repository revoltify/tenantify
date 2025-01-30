<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Revoltify\Tenantify\Models\Scopes\TenantScope;

trait BelongsToTenant
{
    public static $tenantIdColumn = 'tenant_id';

    public static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->getAttribute(static::$tenantIdColumn) && ! $model->relationLoaded('tenant')) {
                if (tenantify()->isInitialized()) {
                    $model->setAttribute(static::$tenantIdColumn, tenant()->getTenantKey());
                    $model->setRelation('tenant', tenant());
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(config('tenantify.tenant_model'));
    }
}
