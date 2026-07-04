<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Application Feature Version
    |--------------------------------------------------------------------------
    |
    | Defines the active backend version. Modules with a target version
    | greater than this active version will be dynamically disabled.
    |
    */
    'version' => (int) env('APP_FEATURE_VERSION', 1),

    /*
    |--------------------------------------------------------------------------
    | Module Version Mapping
    |--------------------------------------------------------------------------
    |
    | Defines the minimum version required to access each backend module
    | and settings sub-panel.
    |
    */
    'modules' => [
        // Sidebar Modules
        'overview'   => 1,
        'crawlers'   => 1,
        'ai-content' => 1,
        'jobs'       => 1,
        'master'     => 1,
        'queues'     => 1,
        'settings'   => 1,
        'audit'      => 1,
        'users'      => 1,
        'analytics'  => 3,
        'marketing'  => 4,
        'rbac'       => 7,

        // Settings Sub-panels & Specific Options
        'settings.site'               => 1,
        'settings.layout'             => 1,
        'settings.integrations'       => 1,
        'settings.security'           => 1,
        'settings.security.backups'   => 7,
        'settings.operations'         => 1,
        'settings.media'              => 2,
    ],
];
