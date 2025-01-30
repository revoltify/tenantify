@if (\Revoltify\Tenantify\Models\Tenant::checkCurrent())
    Current tenant ID: {{ \Revoltify\Tenantify\Models\Tenant::current()->id }}
@endif