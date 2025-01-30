<?php

namespace Revoltify\Tenantify\Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Revoltify\Tenantify\TenantifyServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('jobs')->truncate();

        View::addLocation(__DIR__.'/Stubs/views');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            TenantifyServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        // This will ensure your package migrations run
        $this->loadMigrationsFrom(__DIR__.'/Stubs/database/migrations');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Set up your environment for testing
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
