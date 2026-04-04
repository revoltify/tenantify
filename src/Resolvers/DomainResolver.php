<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Revoltify\Tenantify\Exceptions\TenantResolutionException;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

final class DomainResolver extends AbstractResolver
{
    protected function findTenant(string $domain): TenantInterface
    {
        if (! $this->isValidDomain($domain)) {
            throw TenantResolutionException::invalidDomainFormat($domain);
        }

        /** @var class-string<Model> $tenantModel */
        $tenantModel = $this->getTenantModel();

        /** @var TenantInterface|null $tenant */
        $tenant = $tenantModel::query()->whereHas('domains', function (Builder $query) use ($domain): void {
            $query->where('domain', $domain);
        })->first();

        if (! $tenant) {
            throw TenantResolutionException::tenantDoesNotExist($domain);
        }

        return $tenant;
    }

    protected function getIdentifierFromRequest(): string
    {
        $domain = $this->request->getHost();

        if ($domain === '' || $domain === '0') {
            throw TenantResolutionException::make();
        }

        return $this->makeDomain($domain);
    }

    private function makeDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        if (current($parts) === 'www') {
            return mb_substr(implode('.', $parts), 4);
        }

        return $domain;
    }

    private function isValidDomain(string $domain): bool
    {
        return (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain);
    }
}
