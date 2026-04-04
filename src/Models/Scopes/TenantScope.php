<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class TenantScope implements Scope
{
    public static string $tenantIdColumn = 'tenant_id';

    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! tenantify()->isInitialized()) {
            return;
        }

        /** @var TenantInterface $tenant */
        $tenant = tenant();

        $builder->where($model->qualifyColumn(self::$tenantIdColumn), $tenant->getTenantKey());
    }

    /**
     * @param  Builder<Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantify', fn (Builder $builder) => $builder->withoutGlobalScope(TenantScope::class));
    }
}
