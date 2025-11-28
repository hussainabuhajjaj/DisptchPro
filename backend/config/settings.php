<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Register settings classes here. Each class extends Spatie\LaravelSettings\Settings
    | and defines a group name.
    |
    */
    'settings' => [
        App\Settings\GeneralSettings::class,
        App\Settings\SeoSettings::class,
        App\Settings\FooterSettings::class,
        App\Settings\MediaSettings::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default repository
    |--------------------------------------------------------------------------
    |
    | Using database repository.
    |
    */
    'default_repository' => 'database',

    'repositories' => [
        'database' => [
            'type' => Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository::class,
            'model' => Spatie\LaravelSettings\Models\SettingsProperty::class,
            'table' => 'settings',
        ],
    ],
];
