<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Revoltify\Tenantify\Exceptions\TenantResolutionException;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

class DomainResolver extends AbstractResolver
{
    protected function findTenant(string $domain): ?TenantInterface
    {
        if (! $this->isValidDomain($domain)) {
            throw TenantResolutionException::invalidDomainFormat($domain);
        }

        $tenantModel = $this->getTenantModel();

        /** @var TenantInterface $tenant */
        $tenant = $tenantModel::whereHas('domains', function (Builder $query) use ($domain) {
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

        if (empty($domain)) {
            throw TenantResolutionException::make();
        }

        return $this->makeDomain($domain);
    }

    private function makeDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        if (current($parts) === 'www') {
            $domain = substr(implode('.', $parts), 4);
        }

        return $domain;
    }

    private function isValidDomain(string $domain): bool
    {
        return (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain);
    }
}
