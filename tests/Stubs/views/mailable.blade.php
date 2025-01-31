@if (\Revoltify\Tenantify\Models\Tenant::hasCurrent())
    Current tenant ID: {{ \Revoltify\Tenantify\Models\Tenant::current()->id }}
@endif