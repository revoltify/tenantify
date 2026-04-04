<?php

declare(strict_types=1);

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\CallQueuedClosure;
use Revoltify\Tenantify\Bootstrappers\CacheBootstrapper;
use Revoltify\Tenantify\Bootstrappers\SessionBootstrapper;
use Revoltify\Tenantify\Models\Domain;
use Revoltify\Tenantify\Models\Tenant;
use Revoltify\Tenantify\Resolvers\DomainResolver;

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant & Domain Models
    |--------------------------------------------------------------------------
    */
    'models' => [
        'tenant' => Tenant::class,
        'domain' => Domain::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Initialization Configuration
    |--------------------------------------------------------------------------
    |
    | Configures when and how tenant initialization occurs, including fallback handling.
    |
    */
    'initialization' => [
        /*
        | Determines if the tenant is initialized early in the app lifecycle.
        | - true: Loads tenant at boot (use for global tenant-awareness).
        | - false: Requires manual initialization (e.g., via middleware).
        */
        'early' => env('TENANTIFY_EARLY', false),

        'fallback' => [
            // Type can be 'throw', 'view', 'redirect', 'abort', or 'custom'
            'type' => env('TENANTIFY_FALLBACK_TYPE', 'abort'),

            // Custom fallback handler class (only used if type is 'custom')
            'handler' => null,

            // View name for view fallback
            'view' => 'errors.tenant-not-found',

            // Status code for abort fallback
            'status_code' => 404,

            // Route name or URL for redirect fallback
            'redirect_to' => '/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bootstrappers
    |--------------------------------------------------------------------------
    |
    | The bootstrappers array lets you register classes that will be run when
    | a tenant is initialized. These classes should implement the
    | BootstrapperInterface.
    */
    'bootstrappers' => [
        CacheBootstrapper::class,
        SessionBootstrapper::class,
        // \Revoltify\Tenantify\Bootstrappers\SpatiePermissionsBootstrapper::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resolver Configuration
    |--------------------------------------------------------------------------
    |
    | Configure tenant resolution settings including the resolver class
    | and any resolver-specific settings
    |
    */
    'resolver' => [
        // The resolver class to use for tenant resolution
        'class' => DomainResolver::class,

        // Cache configuration for the resolver
        'cache' => [
            'enabled' => env('TENANTIFY_CACHE_ENABLED', false),
            'ttl' => env('TENANTIFY_CACHE_TTL', 3600), // 1 hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'tenant_aware_by_default' => false,

        'queueable_to_job' => [
            SendQueuedMailable::class => 'mailable',
            SendQueuedNotifications::class => 'notification',
            CallQueuedClosure::class => 'closure',
            CallQueuedListener::class => 'class',
            BroadcastEvent::class => 'event',
        ],

        'tenant_aware_jobs' => [
            // ...
        ],

        'not_tenant_aware_jobs' => [
            // ...
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    */
    'session' => [
        'prefix' => 'tenant',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'prefix' => 'tenant',
    ],
];
