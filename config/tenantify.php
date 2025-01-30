<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Early Initialization
    |--------------------------------------------------------------------------
    |
    | Determines when tenant initialization occurs in the application lifecycle.
    |
    | - true: Initializes tenant during application boot
    |         Use this when every request must be tenant-aware
    |
    | - false: Tenant must be initialized manually (e.g., via middleware)
    |          Use this when you want control over when tenant starts
    |          or only need tenant features for specific routes
    |
    */
    'early' => env('TENANTIFY_EARLY', false),

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
        \Revoltify\Tenantify\Bootstrappers\SpatiePermissionsBootstrapper::class,
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
        'tenant_aware_by_default' => true,

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
