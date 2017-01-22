<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The default template to use
    |--------------------------------------------------------------------------
    |
    | Here you change the stub templates to use when generating code.
    | You can duplicate the "default" template folder
    | and call it what ever template your like "ex. skyblue".
    | Now, you can change the stubs to have your own templates generated.
    |
    |        
    | IMPORTANT: It is not recomended to modify the default template, rather create a new template.
    | If you modify the default template and then executed "php artisan vendor:publish" command,
    | it will override the default template causing you to lose your modification.
    |
    */

    'template' => 'default',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the templates are located
    |--------------------------------------------------------------------------
    |
    | In this path, you can add more templates.
    |
    */

    'templates_path' => base_path('resources/codegenerator-templates'),


    /*
    |--------------------------------------------------------------------------
    | Array of templetes that should be generated with Laravel-Collective
    |--------------------------------------------------------------------------
    |
    | If you want to generate code by using laravel-collective, you must first
    | install the backage and then list of package name that should be 
    | generated using Laravel-Collective extensions.
    */

    'laravel_collective_templates' => [
        'default-collective'
    ],



    /*
    |--------------------------------------------------------------------------
    | The default path of where the uploaded files lives!
    |--------------------------------------------------------------------------
    |
    |
    */
    
    'files_upload_path' => public_path('uploads'),

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
    | The data-value to Eloquent method mapping.
    |--------------------------------------------------------------------------
    |
    | In here you can add more keys to the array and Eloquent method name as the value.
    | A list of Eloquent methods can be found on this link https://laravel.com/docs/5.3/migrations#creating-columns
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
    ],


    /*
    |--------------------------------------------------------------------------
    | Eloquent method to html-type mapping
    |--------------------------------------------------------------------------
    |
    | This is the mapping used to convert database-column into html field
    */
    'eloquent_type_to_html_type' =>
    [
        'char' => 'text',
        'date' => 'text',
        'dateTime' => 'text',
        'dateTimeTz' => 'text',
        'bigIncrements' => 'number',
        'bigIncrements' => 'number',
        'binary' => 'textarea',
        'boolean' => 'radio',
        'decimal' => 'number',
        'double' => 'number',
        'enum' => 'select',
        'float' => 'number',
        'integer' => 'number',
        'integer' => 'number',
        'ipAddress' => 'text',
        'json' => 'checkbox',
        'jsonb' => 'checkbox',
        'longText' => 'textarea',
        'macAddress' => 'text',
        'mediumInteger' => 'number',
        'mediumText' => 'textarea',
        'string' => 'text',
        'text' => 'textarea',
        'time' => 'text',
        'timeTz' => 'text',
        'tinyInteger' => 'number',
        'tinyInteger' => 'number',
        'timestamp' => 'text',
        'timestampTz' => 'text',
        'unsignedBigInteger' => 'number',
        'unsignedBigInteger' => 'number',
        'unsignedInteger' => 'number',
        'unsignedInteger' => 'number',
        'unsignedMediumInteger' => 'number',
        'unsignedMediumInteger' => 'number',
        'unsignedSmallInteger' => 'number',
        'unsignedSmallInteger' => 'number',
        'unsignedTinyInteger' => 'number',
        'uuid' => 'text',
    ]
];
