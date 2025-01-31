# Tenantify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/revoltify/tenantify.svg)](https://packagist.org/packages/revoltify/tenantify)
[![Total Downloads](https://img.shields.io/packagist/dt/revoltify/tenantify.svg)](https://packagist.org/packages/revoltify/tenantify)
[![Tests](https://github.com/revoltify/tenantify/actions/workflows/run-tests.yml/badge.svg)](https://github.com/revoltify/tenantify/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/github/license/revoltify/tenantify)](https://github.com/revoltify/tenantify/blob/main/LICENSE.md)

A powerful and flexible single-database multi-tenant solution for Laravel 11, built with SOLID principles in mind.

## Features

- **Early Identification**: Initialize tenants during application boot for maximum performance
- **Flexible Resolution**: Support for domain and subdomain-based tenant resolution
- **Customizable Bootstrapping**: Add your own tenant bootstrapping logic
- **Tenant-Aware Systems**:
  - Queue system with automatic tenant context
  - Cache system with tenant-specific prefixes
  - Session management with tenant isolation
- **Built-in Spatie Permissions Support**: Automatic tenant-specific permission caching
- **Automatic Tenant Scoping**: Zero-effort tenant data isolation with the `BelongsToTenant` trait
- **High Performance**: Optimized resolver with optional caching
- **Developer Friendly**: Clear API with helper functions

## Requirements

- PHP 8.2 or higher
- Laravel 11.x

## Installation

1. Install the package via Composer:

```bash
composer require revoltify/tenantify
```

2. Run the installation command:

```bash
php artisan tenantify:install
```

This will publish the configuration file and migrations.

3. Run the migrations:

```bash
php artisan migrate
```

## Configuration

The package can be configured via the `config/tenantify.php` file. Here are the key configuration options:

### Early Initialization

```php
'early' => env('TENANTIFY_EARLY', false),
```

- `true`: Initializes tenant during application boot (recommended for fully tenant-aware applications)
- `false`: Manual initialization through middleware (recommended for partially tenant-aware applications)

### Custom Models

```php
'models' => [
    'tenant' => \App\Models\Tenant::class,
    'domain' => \App\Models\Domain::class,
],
```

### Resolver Configuration

```php
'resolver' => [
    'class' => \Revoltify\Tenantify\Resolvers\DomainResolver::class,
    'cache' => [
        'enabled' => env('TENANTIFY_CACHE_ENABLED', false),
        'ttl' => env('TENANTIFY_CACHE_TTL', 3600),
    ],
],
```

## Basic Usage

### Creating a Tenant

```php
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Models\Domain;

$tenant = Tenant::create([
    'name' => 'Example Organization'
]);

$tenant->domains()->create([
    'domain' => 'example.com'
]);
```

### Manual Tenant Initialization

```php
// Using facade
Tenantify::initialize($tenant);

// Using helper function
tenantify()->initialize($tenant);

// Initialize by ID
tenantify()->initialize($tenantId);
```

### Accessing Current Tenant

```php
// Using helper functions
$tenant = tenant();
$tenantId = tenant_id();

// Using facade
$tenant = Tenantify::tenant();
```

## Advanced Usage

### Custom Bootstrapper

Create a custom bootstrapper by extending the `AbstractBootstrapper` class:

```php
use Revoltify\Tenantify\Bootstrappers\AbstractBootstrapper;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;

class CustomBootstrapper extends AbstractBootstrapper
{
    protected int $priority = 10;

    public function bootstrap(TenantInterface $tenant): void
    {
        // Your bootstrapping logic here
    }

    public function revert(): void
    {
        // Your cleanup logic here
    }
}
```

Register your bootstrapper in the configuration:

```php
'bootstrappers' => [
    \App\Bootstrappers\CustomBootstrapper::class,
],
```

### Model Tenant Scoping

Add automatic tenant scoping to your models using the `BelongsToTenant` trait:

```php
use Illuminate\Database\Eloquent\Model;
use Revoltify\Tenantify\Models\Concerns\BelongsToTenant;

class Project extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'description'];
}
```

This will automatically:
- Add tenant ID on model creation
- Scope all queries to the current tenant
- Establish the tenant relationship

Usage example:
```php
// Creates a project for the current tenant automatically
$project = Project::create([
    'name' => 'New Project'
]);

// Queries are automatically scoped to the current tenant
$projects = Project::all(); // Only returns current tenant's projects

// Access the tenant relationship
$tenant = $project->tenant;
```

### Tenant-Aware Jobs

Make your job tenant-aware by implementing the `TenantAware` interface:

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Revoltify\Tenantify\Job\TenantAware;

class ProcessTenantData implements ShouldQueue, TenantAware
{
    public function handle(): void
    {
        // Job will automatically run in the correct tenant context
    }
}
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Revoltify](https://github.com/revoltify)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.