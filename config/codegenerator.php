<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The default template to use.
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
    | The default path where the templates are located.
    |--------------------------------------------------------------------------
    |
    | In this path, you can add more templates.
    |
    */
    'templates_path' => 'resources/codegenerator-templates',

    /*
    |--------------------------------------------------------------------------
    | Array of templetes that should be generated with Laravel-Collective.
    |--------------------------------------------------------------------------
    |
    | If you want to generate code by using laravel-collective, you must first
    | install the package. Then add the tamplate name that should be using 
    | Laravel-Collective extensions when generating code.
    */
    'laravel_collective_templates' => [
        'default-collective'
    ],

    /*
    |--------------------------------------------------------------------------
    | The default path of where the uploaded files lives.
    |--------------------------------------------------------------------------
    |
    |
    */
    'files_upload_path' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | The default format for the datetime output
    |--------------------------------------------------------------------------
    |
    |
    */
    'datetime_out_format' => 'm/d/Y H:i A',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the field json files are located
    |--------------------------------------------------------------------------
    |
    | In this path, you can create json file to import the fields from.
    |
    */
    'fields_file_path' => 'resources/codegenerator-files',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the migrations will be generated into.
    |--------------------------------------------------------------------------
    */
    'migrations_path' => 'database/migrations',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated into.
    |--------------------------------------------------------------------------
    */
    'form_requests_path' => 'Http/Requests',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated into.
    |--------------------------------------------------------------------------
    */
    'controllers_path' => 'Http/Controllers',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the models will be generated into.
    |--------------------------------------------------------------------------
    */
    'models_path' => 'Models',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the languages will be generated into.
    |--------------------------------------------------------------------------
    */
    'languages_path' => 'resources/lang',

    /*
    |--------------------------------------------------------------------------
    | Key phrases that are will be used to determine if a field name should be used for header.
    |--------------------------------------------------------------------------
    */
    'common_header_patterns' => ['title','name','label','header'],

    /*
    |--------------------------------------------------------------------------
    | Key phrases that are will be used to determine if a field should be a primary key.
    |--------------------------------------------------------------------------
    */
    'common_id_patterns' => ['id'],

    /*
    |--------------------------------------------------------------------------
    | Patterns to use to determine "datetime" fields if date-type is not provided
    |--------------------------------------------------------------------------
    |
    | If the date-type for a field is not, the codegenerator scanns the field's name 
    | for a matching pattern. If the name matches any of the set pattern, the the 
    | field's type will automaticly be set to datetime.
    |
    */
    'common_datetime_patterns' => ['*_at'],

    /*
    |--------------------------------------------------------------------------
    | Patterns to use to determine "boolean" fields if date-type is not provided
    |--------------------------------------------------------------------------
    |
    | If the date-type for a field is not, the codegenerator scanns the field's name 
    | for a matching pattern. If the name matches any of the set pattern, the the 
    | field's type will automaticly be set to boolean.
    |
    */
    'common_boolean_patterns' => ['is_*','has_*'],

    /*
    |--------------------------------------------------------------------------
    | Key phrases that are will be used to determine if a field should have a relation.
    |--------------------------------------------------------------------------
    |
    | When creating resources from existing database, the codegenerator scans
    | the field's name for a mattching pattern. When found, these field are considred 
    | foreign keys even if the database does not have a foreign constraints.
    | Here you can specify patterns to help the generator understand your
    | database naming convension.
    |
    */
    'common_key_patterns' => ['*_id','*_by'],
    
    /*
    |--------------------------------------------------------------------------
    | Common foreign keys definitions
    |--------------------------------------------------------------------------
    |
    | The code generator uses "common_key_patterns" array to scan the field name 
    | for matching patterns to determine if a field should be considred foreign key.
    | "common_foreign_keys" allows you to set global configuration for common foreign key.
    | Each key in the array represents the field name. The value is an array of definitions
    | to use. Here is description of the definition array
    | 
    | "name" will be used to create the relation method's name.
    | "type" will be used to set the relation type
    | "params" the parameters to use for the "type" relation.
    | "field" the field on the foreign model to use as display name.
    | "on-create" sets a value to use when this model is created. If your using an authentication
    |             system such as laravel build in authentication. you can automaticly set 
    |             the user Id who created the model by setting this value to 'Auth::Id();'.
    | "on-update" sets a value to use when this model is update.
    |
    */

    "common_foreign_keys" => [
        "owner_id"      => [
            "name"      => "owner",
            "type"      => "belongsTo",
            "params"    => [
                "App\\User",
                "owner_id"
            ],
            "field"     => "name",
            "on-store"  => null,
            "on-update" => null,
        ],
        "operator_id"   => [
            "name"      => "operator",
            "type"      => "belongsTo",
            "params"    => [
                "App\\User",
                "operator_id"
            ],
            "field"     => "name",
            "on-store"  => null,
            "on-update" => null,
        ],
        "author_id"     => [
            "name"      => "author",
            "type"      => "belongsTo",
            "params"    => [
                "App\\User",
                "author_id"
            ],
            "field"     => "name",
            "on-store"  => null,
            "on-update" => null,
        ],
        "created_by"    => [
            "name"      => "creator",
            "type"      => "belongsTo",
            "params"    => [
                "App\\User",
                "created_by"
            ],
            "field"     => "name",
            "on-store"  => "Illuminate\Support\Facades\Auth::Id();",
            "on-update" => null,
        ],
        "updated_by"    => [
            "name"      => "updator",
            "type"      => "belongsTo",
            "params"    => [
                "App\\User",
                "created_by"
            ],
            "field"     => "name",
            "on-store" => null,
            "on-update" => "Illuminate\Support\Facades\Auth::Id();",
        ]
    ],

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
        'boolean' => 'checkbox',
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
