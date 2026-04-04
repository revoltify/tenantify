<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Database\Query\Builder;
use Illuminate\Session\DatabaseSessionHandler;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class DatabaseSessionManager extends DatabaseSessionHandler
{
    /**
     * Get a fresh query builder instance for the table with tenant scope.
     *
     * @return Builder
     */
    protected function getQuery()
    {
        $query = parent::getQuery();

        if ($this->hasTenant()) {
            $query->where('tenant_id', $this->getTenantId());
        }

        return $query;
    }

    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array<string, mixed>
     */
    protected function getDefaultPayload($data)
    {
        /** @var array<string, mixed> $payload */
        $payload = parent::getDefaultPayload($data);

        if ($this->hasTenant()) {
            $payload['tenant_id'] = $this->getTenantId();
        }

        return $payload;
    }

    /**
     * Check if tenant is initialized.
     */
    private function hasTenant(): bool
    {
        return app()->bound(TenantInterface::class) && tenant() !== null;
    }

    /**
     * Get current tenant ID safely.
     */
    private function getTenantId(): int|string|null
    {
        /** @var int|string|null $id */
        $id = tenant('id');

        return $id;
    }
}
