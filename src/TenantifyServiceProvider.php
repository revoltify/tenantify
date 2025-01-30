<?php

declare(strict_types=1);

namespace Revoltify\Tenantify;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Revoltify\Tenantify\Commands\Install;
use Revoltify\Tenantify\Managers\BootstrapperManager;
use Revoltify\Tenantify\Managers\DatabaseSessionManager;
use Revoltify\Tenantify\Managers\QueueManager;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Resolvers\Contracts\ResolverInterface;

class TenantifyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfig();
        $this->registerBindings();
    }

    /**
     * Merge the package configuration with the application configuration.
     */
    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tenantify.php', 'tenantify');
    }

    /**
     * Register core bindings in the service container.
     */
    protected function registerBindings(): void
    {
        $this->registerResolver();
        $this->registerBootstrappers();
        $this->registerCoreBindings();
        $this->registerSessionHandler();
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
     * Register bootstrappers with the BootstrapperManager.
     */
    protected function registerBootstrappers(): void
    {
        // Register BootstrapperManager as a singleton
        $this->app->singleton(BootstrapperManager::class, function (Application $app) {
            $manager = new BootstrapperManager;

            // Register bootstrappers from the configuration
            collect(config('tenantify.bootstrappers', []))->each(function ($bootstrapper) use ($manager, $app) {
                // Register the bootstrapper as a singleton
                $app->singleton($bootstrapper);

                // Add the bootstrapper to the BootstrapperManager
                $manager->addBootstrapper($app->make($bootstrapper));
            });

            return $manager;
        });
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
        $this->app->singleton('globalCache', function (Application $app) {
            return new CacheManager($app);
        });

        // Bind TenantInterface to the current tenant
        $this->app->bind(TenantInterface::class, function (Application $app) {
            return $app->make(Tenantify::class)->tenant();
        });
    }

    /**
     * Register session handler
     */
    private function registerSessionHandler()
    {
        $this->app['session']->extend('database', function (Application $app) {
            $connection = $app['db']->connection(config('session.connection'));
            $table = config('session.table', 'sessions');
            $minutes = config('session.lifetime');

            return new DatabaseSessionManager(
                $connection, $table, $minutes, $app
            );
        });
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->bootTenantify();

    }

    /**
     * Initialize Tenantify and publish resources.
     */
    private function bootTenantify(): void
    {
        $this->bootQueueManager();

        $this->publishResources();

        if ($this->shouldInitializeEarlyTenant()) {
            $this->initializeTenantify();
        }
    }

    /**
     * Boot Queue Manager
     */
    private function bootQueueManager()
    {
        $this->app->make(QueueManager::class)->initialize();
    }

    /**
     * Publish package resources
     */
    private function publishResources(): void
    {
        if ($this->isRunningInConsole()) {

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
        return config('tenantify.early', true)
        && ! $this->isRunningInConsole();
    }

    /**
     * Check if the application is running in the console.
     */
    private function isRunningInConsole(): bool
    {
        return $this->app->runningInConsole();
    }

    /**
     * Initialize tenant for the current request.
     */
    private function initializeTenantify(): void
    {
        try {
            $resolver = $this->app->make(ResolverInterface::class);
            $tenantify = $this->app->make(Tenantify::class);

            $tenant = $resolver->resolve();
            $tenantify->initialize($tenant);
        } catch (\Exception $e) {
            Log::error('Tenant initialization failed: '.$e->getMessage());
        }
    }
}
