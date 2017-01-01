<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Code generator path to a custom template.
    |--------------------------------------------------------------------------
    |
    | Here you change the stub templates to use when generating code.
    | You can duplicate the "base_path('resources/codegenerator-templates/default')"
    | and call it what ever template your like "ex. skyblue".
    | Now, you can change the stubs to have your own templates generated.
    |
    |        
    | IMPORTANT: It is not recomended to modify the default template, rather create a new template.
    | If you modify the default template and then executed "php artisan vendor:publish" command,
    | it will override the default template causing you to lose your modification.
    |
    */

    'template' => base_path('resources/codegenerator-templates/default'),

    /*
    |--------------------------------------------------------------------------
    | The default path of where the field json files are located
    |--------------------------------------------------------------------------
    |
    | In this path, you can create json file to import the fields from.
    |
    */

    'fields_file_path' => base_path('resources/codegenerator-files'),

    /*
    |--------------------------------------------------------------------------
    | The default path of where the migrations will be generated from
    |--------------------------------------------------------------------------
    */

    'migrations_path' => base_path('database/migrations'),

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated from
    |--------------------------------------------------------------------------
    */
    'form_requests_path' => app_path('Http/Requests'),

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated from
    |--------------------------------------------------------------------------
    */
    'controllers_path' => 'Http/Controllers',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the models will be generated from
    |--------------------------------------------------------------------------
    */
    'models_path' => 'Models',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the migrations will be generated from
    |--------------------------------------------------------------------------
    */

    'languages_path' => base_path('resources/lang'),

    /*
    |--------------------------------------------------------------------------
    | The data-value to eloquent method mapping.
    |--------------------------------------------------------------------------
    |
    | In here you can add more keys to the array and eloquent method name as the value.
    | A list of eloquent methods can be found on this link https://laravel.com/docs/5.3/migrations#creating-columns
    | The only time you really have to add more items here is if you don't like using the existing data-value that are used
    | with the code generator.
    */
    'eloquent_type_to_method' =>
    [
        'char' => 'char',
        'date' => 'date',
        'datetime' => 'dateTime',
        'datetimetz' => 'dateTimeTz',
        'biginteger' => 'bigIncrements',
        'bigint' => 'bigIncrements',
        'blob' => 'binary',
        'binary' => 'binary',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'decimal' => 'decimal',
        'double' => 'double',
        'enum' => 'enum',
        'list' => 'enum',
        'float' => 'float',
        'int' => 'integer',
        'integer' => 'integer',
        'ipaddress' => 'ipAddress',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'longtext' => 'longText',
        'macaddress' => 'macAddress',
        'mediuminteger' => 'mediumInteger',
        'mediumint' => 'mediumInteger',
        'mediumtext' => 'mediumText',
        'morphs' => 'morphs',
        'string' => 'string',
        'varchar' => 'string',
        'nvarchar' => 'string',
        'text' => 'text',
        'time' => 'time',
        'timetz' => 'timeTz',
        'tinyinteger' => 'tinyInteger',
        'tinyint' => 'tinyInteger',
        'timestamp' => 'timestamp',
        'timestamptz' => 'timestampTz',
        'unsignedbiginteger' => 'unsignedBigInteger',
        'unsignedbigint' => 'unsignedBigInteger',
        'unsignedInteger' => 'unsignedInteger',
        'unsignedint' => 'unsignedInteger',
        'unsignedmediuminteger' => 'unsignedMediumInteger',
        'unsignedmediumint' => 'unsignedMediumInteger',
        'unsignedsmallinteger' => 'unsignedSmallInteger',
        'unsignedsmallint' => 'unsignedSmallInteger',
        'unsignedtinyinteger' => 'unsignedTinyInteger',
        'uuid' => 'uuid'
    ]
];
