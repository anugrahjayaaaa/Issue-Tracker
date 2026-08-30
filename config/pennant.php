<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pennant Store
    |--------------------------------------------------------------------------
    |
    | Here you will specify the default store that Pennant should use when
    | storing and resolving feature flag values. Pennant ships with the
    | ability to store flag values in an in-memory array or database.
    |
    | Supported: "array", "database"
    |
    */

    'default' => env('PENNANT_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Pennant Stores
    |--------------------------------------------------------------------------
    |
    | Here you may configure each of the stores that should be available to
    | Pennant. These stores shall be used to store resolved feature flag
    | values - you may configure as many as your application requires.
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'features',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Application Feature Flags
    |--------------------------------------------------------------------------
    |
    | Declares every module-level feature flag with a human label. Values are
    | resolved/stored by Laravel Pennant (DB store). A flag absent here is
    | treated as disabled (fail-closed) by Pennant's `Feature::active()`.
    |
    */

    'features' => [
        'users' => ['label' => 'Users'],
        'roles' => ['label' => 'Roles'],
        'permissions' => ['label' => 'Permissions'],
        'audit' => ['label' => 'Audit Log'],
        'sessions' => ['label' => 'Sessions'],
        'api-tokens' => ['label' => 'API Tokens'],
        'translations' => ['label' => 'Translations'],
        'logs' => ['label' => 'Logs'],
        'telescope' => ['label' => 'Telescope'],
        'periscope' => ['label' => 'Periscope'],
        'plans' => ['label' => 'Plans'],
        'billing' => ['label' => 'Billing'],
    ],

];
