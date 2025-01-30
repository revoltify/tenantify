<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public static string $tenantIdColumn = 'tenant_id';

    public function apply(Builder $builder, Model $model)
    {
        if (! tenantify()->isInitialized()) {
            return;
        }

        $builder->where($model->qualifyColumn(static::$tenantIdColumn), tenant()->getTenantKey());
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantify', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });
    }
}
