<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant & Domain Models
    |--------------------------------------------------------------------------
    */
    'models' => [
        'tenant' => \Revoltify\Tenantify\Models\Tenant::class,
        'domain' => \Revoltify\Tenantify\Models\Domain::class,
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
        \Revoltify\Tenantify\Bootstrappers\CacheBootstrapper::class,
        \Revoltify\Tenantify\Bootstrappers\SessionBootstrapper::class,
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
        'class' => \Revoltify\Tenantify\Resolvers\DomainResolver::class,

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
            \Illuminate\Mail\SendQueuedMailable::class => 'mailable',
            \Illuminate\Notifications\SendQueuedNotifications::class => 'notification',
            \Illuminate\Queue\CallQueuedClosure::class => 'closure',
            \Illuminate\Events\CallQueuedListener::class => 'class',
            \Illuminate\Broadcasting\BroadcastEvent::class => 'event',
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
