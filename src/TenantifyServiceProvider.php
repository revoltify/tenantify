<?php

declare(strict_types=1);

namespace Revoltify\Tenantify;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;
use Revoltify\Tenantify\Commands\Install;
use Revoltify\Tenantify\Concerns\InitializesTenant;
use Revoltify\Tenantify\Managers\BootstrapperManager;
use Revoltify\Tenantify\Managers\DatabaseSessionManager;
use Revoltify\Tenantify\Managers\FallbackManager;
use Revoltify\Tenantify\Managers\QueueManager;
use Revoltify\Tenantify\Models\Contracts\DomainInterface;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;
use Revoltify\Tenantify\Resolvers\DomainResolver;

final class TenantifyServiceProvider extends ServiceProvider
{
    use InitializesTenant;

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfig();
        $this->registerBindings();
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->bootQueueManager();

        if ($this->app->runningInConsole()) {
            $this->bootConsole();
        } else {
            $this->bootTenantify();
        }
    }

    /**
     * Merge the package configuration with the application configuration.
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tenantify.php', 'tenantify');
    }

    /**
     * Register core bindings in the service container.
     */
    private function registerBindings(): void
    {
        $this->registerResolver();
        $this->registerBootstrappers();
        $this->registerFallbackManager();
        $this->registerCoreBindings();
        $this->registerSessionHandler();
    }

    /**
     * Register bootstrappers with the BootstrapperManager.
     */
    private function registerBootstrappers(): void
    {
        // Register BootstrapperManager as a singleton
        $this->app->singleton(BootstrapperManager::class, function (Application $app): BootstrapperManager {
            $manager = new BootstrapperManager;

            // Register bootstrappers from the configuration
            collect(config('tenantify.bootstrappers', []))->each(function ($bootstrapper) use ($manager, $app): void {
                // Register the bootstrapper as a singleton
                $app->singleton($bootstrapper);

                // Add the bootstrapper to the BootstrapperManager
                $manager->addBootstrapper($app->make($bootstrapper));
            });

            return $manager;
        });
    }

    /**
     * Register the tenant resolver.
     */
    private function registerResolver(): void
    {
        $this->app->singleton(
            ResolverInterface::class,
            config('tenantify.resolver.class')
        );
    }

    /**
     * Register the fallback manager as a singleton
     */
    private function registerFallbackManager(): void
    {
        $this->app->singleton(FallbackManager::class);
    }

    /**
     * Register core bindings
     */
    private function registerCoreBindings(): void
    {
        // Register Tenantify as a singleton
        $this->app->singleton(Tenantify::class);

        // Register QueueManager as a singleton
        $this->app->singleton(QueueManager::class);

        // Register GlobalCache as a singleton
        $this->app->singleton('globalCache', fn (Application $app): CacheManager => new CacheManager($app));

        // Bind TenantInterface to the current tenant
        $this->app->bind(TenantInterface::class, fn (Application $app): ?TenantInterface => $app->make(Tenantify::class)->tenant());

        $this->app->bind(DomainInterface::class, fn (): ?DomainInterface => DomainResolver::$currentDomain);
    }

    /**
     * Register session handler
     */
    private function registerSessionHandler(): void
    {
        $this->app->make(SessionManager::class)->extend('database', function (Application $app): DatabaseSessionManager {
            $connection = $app->make(ConnectionResolverInterface::class)->connection(config('session.connection'));
            $table = config('session.table', 'sessions');
            $minutes = config('session.lifetime');

            return new DatabaseSessionManager(
                $connection, $table, $minutes, $app
            );
        });
    }

    /**
     * Boot Queue Manager
     */
    private function bootQueueManager(): void
    {
        $this->callAfterResolving('queue', function (): void {
            $this->app->make(QueueManager::class)->initialize();
        });
    }

    /**
     * Boot Console
     */
    private function bootConsole(): void
    {
        $this->commands([
            Install::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/tenantify.php' => config_path('tenantify.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Initialize Tenantify and publish resources.
     */
    private function bootTenantify(): void
    {
        if ($this->shouldInitializeEarlyTenant()) {
            $this->initializeTenantify();
        }
    }

    /**
     * Determine if early tenant initialization should occur.
     *
     * Early initialization occurs when:
     * 1. Early initialization is enabled
     * 2. The application is not running in console
     */
    private function shouldInitializeEarlyTenant(): bool
    {
        return config('tenantify.initialization.early', false) === true;
    }
}
