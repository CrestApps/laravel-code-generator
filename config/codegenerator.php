<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The default template to use.
    |--------------------------------------------------------------------------
    |
    | Here you change the stub templates to use when generating code.
    | You can duplicate the 'default' template folder and call it whatever
    | template name you like 'ex. skyblue'. Now, you can change the stubs to
    | have your own templates generated.
    |
    |
    | IMPORTANT: It is not recommended to modify the default template, rather
    | create a new template. If you modify the default template and then
    | executed 'php artisan vendor:publish' command, will override your changes!
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
    'templates_path' => 'resources/laravel-code-generator/templates',

    /*
    |--------------------------------------------------------------------------
    | Array of templates that should be generated with Laravel-Collective.
    |--------------------------------------------------------------------------
    |
    | If you want to generate code by using laravel-collective, you must first
    | install the package. Then add the tamplate name that should be using
    | Laravel-Collective extensions when generating code.
    |
     */
    'laravel_collective_templates' => [
        'default-collective',
    ],

    /*
    |--------------------------------------------------------------------------
    | The default path of where the uploaded files live.
    |--------------------------------------------------------------------------
    |
    | You can use Laravel Storage filesystem. By default, the code-generator
    | uses the default file system.
    | For more info about Laravel's file system visit
    | https://laravel.com/docs/5.5/filesystem
    |
     */
    'files_upload_path' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | Should the moveFile method be generated for every resources when needed
    |--------------------------------------------------------------------------
    |
    | The code-generator will generate a method called "moveFile" method each
    | time a file upload is required (i.e. protected function moveFile($file).)
    | This method is needed to move the file from the request to a permanent
    | place on your server.
    |
    | However, if you want to generate multiple CRUDs that handles file
    | uploading, you may wish to put this method in a higher level of your code
    | to prevent redundancy and your code is kept clean.
    |
    | If you decided to move this method to a higher level like
    | App\Http\Controllers\Controller base class
    | a new App\Http\Requests\FormRequest base class, you can set this option to
    | `false` to prevent the generator from creating this method
    | for every CRUD. Should you set it to `false` it is your responsibility to
    | ensure that this method exists otherwise a MethodNotFound exception thrown.
    |
     */
    'create_move_file_method' => true,

    /*
    |--------------------------------------------------------------------------
    | The default output format for datetime fields.
    |--------------------------------------------------------------------------
    |
    | This output format can also be changed at the field level using the
    | "date-format" property of the field.
    |
     */
    'datetime_out_format' => 'j/n/Y g:i A',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the json resource-files are located.
    |--------------------------------------------------------------------------
    |
    | In this path, you can create json file to import the resources from.
    |
     */
    'resource_file_path' => 'resources/laravel-code-generator/sources',

    /*
    |--------------------------------------------------------------------------
    | The default path for any system used file
    |--------------------------------------------------------------------------
    |
     */
    'system_files_path' => 'resources/laravel-code-generator/system',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the migrations will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'migrations_path' => 'database/migrations',

    /*
    |--------------------------------------------------------------------------
    | Should the code generator use smart migrations?
    |--------------------------------------------------------------------------
    |
    | This option will allow the code generator to create or alter migration.
    | when needed. This option will only work for tables that are not
    | yet generated or generated using laravel-code-generator.
    |
    | To create a migration, you still have to use create:migration command
    | or --with-migration option when using create:resources command but the
    | system will know what migration should be created.
    |
     */
    'use_smart_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Should the code generator organize the new migrations?
    |--------------------------------------------------------------------------
    |
    | This option will allow the code generator to group the migration related
    | to the same table is a separate folder. The folder name will be the name
    | of the table.
    |
    | It is recommended to set this value to true, then use crest apps command
    | to migrate instead of the build in command.
    |
    | php artisan migrate-all
    | php artisan migrate:rollback-all
    | php artisan migrate:reset-all
    | php artisan migrate:refresh-all
    | php artisan migrate:status-all
    |
     */
    'organize_migrations' => false,

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'form_requests_path' => 'Http/Requests',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'controllers_path' => 'Http/Controllers',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the models will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'models_path' => 'Models',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the languages will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'languages_path' => 'resources/lang',

    /*
    |--------------------------------------------------------------------------
    | The name of the default resources map file.
    |--------------------------------------------------------------------------
    |
     */
    'default_mapper_file_name' => 'resources_map.json',

    /*
    |--------------------------------------------------------------------------
    | Should the code generator auto manage resources mappers?
    |--------------------------------------------------------------------------
    |
     */
    'auto_manage_resource_mapper' => true,

    /*
    |--------------------------------------------------------------------------
    | Key phrases that are will be used to determine if a field name should be
    | used for header.
    |--------------------------------------------------------------------------
    |
    | You may use * as a while card in the name. For example, "head*" will
    | match any field name that starts with the word "head"
    |
     */
    'common_header_patterns' => [
        'title',
        'name',
        'label',
        'subject',
        'head*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patterns to use to generate the html placeholders.
    |--------------------------------------------------------------------------
    |
    | When creating the fields, the code generator follows a pattern to generate
    | placeholders for the html code. Here you can define which html-types should
    | the generator create placeholder for. Also, you can define how would you like
    | the text to read when no placeholder is assigned.
    |
    | The follwowing templates can be used to. assuming the field name is owner_name
    | [% field_name %]                   <=> "owner name"
    | [% field_name_sentence %]          <=> "Owner name"
    | [% field_name_plural %]            <=> "owner names"
    | [% field_name_plural_title %]      <=> "Owner Names"
    | [% field_name_snake %]             <=> "owner_name"
    | [% field_name_studly %]            <=> "OwnerName"
    | [% field_name_slug %]              <=> "owner-name"
    | [% field_name_kebab %]             <=> "owner-name"
    | [% field_name_title %]             <=> "Owner Name"
    | [% field_name_title_upper %]       <=> "OWNER NAME"
    | [% field_name_plural_variable %]   <=> "ownerNames"
    | [% field_name_singular_variable %] <=> "ownerName"
    |
     */
    'placeholder_by_html_type' => [
        'text' => 'Enter [% field_name %] here...',
        'number' => 'Enter [% field_name %] here...',
        'password' => 'Enter [% field_name %] here...',
        'email' => 'Enter [% field_name %] here...',
        'select' => 'Select [% field_name %]',
        'multipleSelect' => 'Select [% field_name %]',
    ],

    /*
    |--------------------------------------------------------------------------
    | Key phrases that are will be used to determine if a field should have a relation.
    |--------------------------------------------------------------------------
    |
    | When creating resources from existing database, the codegenerator scans
    | the field's name for a mattching pattern. When found, these field are considered
    | foreign keys even when the database does not have a foreign constraints.
    | Here you can specify patterns to help the generator understand your
    | database naming convension.
    |
     */
    'common_key_patterns' => [
        '*_id',
        '*_by',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patterns to use to pre-set field's properties.
    |--------------------------------------------------------------------------
    |
    | To make constructing fields easy, the code-generator scans the field's name
    | for a matching pattern. If the name matches any of these patterns, the
    | field's properties will be set accordingly. Defining pattern will save
    | you from having to re-define the properties for common fields.
    |
     */
    'common_definitions' => [
        [
            'match' => '*',
            'set' => [
                // You may use any of the field templates to create the label
                'labels' => '[% field_name_title %]',
            ],
        ],
        [
            'match' => 'id',
            'set' => [
                'is-on-form' => false,
                'is-on-index' => false,
                'is-on-show' => false,
                'html-type' => 'hidden',
                'data-type' => 'integer',
                'is-primary' => true,
                'is-auto-increment' => true,
                'is-nullable' => false,
                'is-unsigned' => true,
            ],
        ],
        [
            'match' => ['title', 'name', 'label', 'subject', 'head*'],
            'set' => [
                'is-nullable' => false,
                'data-type' => 'string',
                'data-type-params' => [255],
            ],
        ],
        [
            'match' => ['*count*', 'total*', '*number*', '*age*'],
            'set' => [
                'html-type' => 'number',
            ],
        ],
        [
            'match' => ['description*', 'detail*', 'note*', 'message*'],
            'set' => [
                'is-on-index' => false,
                'html-type' => 'textarea',
                'data-type-params' => [1000],
            ],
        ],
        [
            'match' => ['picture', 'file', 'photo', 'avatar'],
            'set' => [
                'is-on-index' => false,
                'html-type' => 'file',
            ],
        ],
        [
            'match' => ['*password*'],
            'set' => [
                'html-type' => 'password',
            ],
        ],
        [
            'match' => ['*email*'],
            'set' => [
                'html-type' => 'email',
            ],
        ],
        [
            'match' => ['*_id', '*_by'],
            'set' => [
                'data-type' => 'integer',
                'html-type' => 'select',
                'is-nullable' => false,
                'is-unsigned' => true,
                'is-index' => true,
            ],
        ],
        [
            'match' => ['*_at'],
            'set' => [
                'data-type' => 'datetime',
            ],
        ],
        [
            'match' => ['created_at', 'updated_at', 'deleted_at'],
            'set' => [
                'data-type' => 'datetime',
                'is-on-form' => false,
                'is-on-index' => false,
                'is-on-show' => true,
            ],
        ],
        [
            'match' => ['*_date', 'date_*'],
            'set' => [
                'data-type' => 'date',
                'date-format' => 'j/n/Y',
            ],
        ],
        [
            'match' => ['is_*', 'has_*'],
            'set' => [
                'data-type' => 'boolean',
                'html-type' => 'checkbox',
                'is-nullable' => false,
                'options' => ["No", "Yes"],
            ],
        ],
        [
            'match' => 'created_by',
            'set' => [
                'title' => 'Creator',
                'data-type' => 'integer',
                'foreign-relation' => [
                    'name' => 'creator',
                    'type' => 'belongsTo',
                    'params' => [
                        'App\\User',
                        'created_by',
                    ],
                    'field' => 'name',
                ],
                'on-store' => 'Illuminate\Support\Facades\Auth::Id();',
            ],
        ],
        [
            'match' => ['updated_by', 'modified_by'],
            'set' => [
                'title' => 'Updater',
                'data-type' => 'integer',
                'foreign-relation' => [
                    'name' => 'updater',
                    'type' => 'belongsTo',
                    'params' => [
                        'App\\User',
                        'updated_by',
                    ],
                    'field' => 'name',
                ],
                'on-update' => 'Illuminate\Support\Facades\Auth::Id();',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plural vs singular naming conventions.
    |--------------------------------------------------------------------------
    |
     */
    'plural_names_for' => [
        'controller-name' => true,
        'request-form-name' => true,
        'route-group' => true,
        'language-file-name' => true,
        'resource-file-name' => true,
        'table-name' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | A string to postfix the controller name with.
    |--------------------------------------------------------------------------
    |
    | If you don't like to post fix the controller with "Controller" you can
    | set this value to an empty string. Or, you can set it to any other value.
    |
     */
    'controller_name_postfix' => 'Controller',

    /*
    |--------------------------------------------------------------------------
    | A string to postfix the form-request name with.
    |--------------------------------------------------------------------------
    |
    | If you don't like to post fix the form-request with "FormRequest" you can
    | set this value to an empty string. Or, you can set it to any other value.
    |
     */
    'form_request_name_postfix' => 'FormRequest',

    /*
    |--------------------------------------------------------------------------
    | Defining non-english or irregular plurals
    |--------------------------------------------------------------------------
    |
    | The code-generator heavily uses Laravel helpers "str_plural()"
    | and "str_singular()" to generate readable code to make your code spectacular.
    | The problem is the both of these functions support only English language
    | which covers most cases. If you are using a language other than english,
    | you can define a word with its plural-form to help the generator keep
    | your code readable.
    |
    | irregular_plurals must be an array where the key represents the singular-
    | form of the word, and the value represents the plural-form.
    |
     */
    'irregular_plurals' => [
        'software' => 'software',
    ],

    /*
    |--------------------------------------------------------------------------
    | Non-Field base labels to be replaced in the views.
    |--------------------------------------------------------------------------
    |
    | List of generic non-field labels to be replaced in the views.
    | The "key" of the array is the value to be used in the locale files.
    | The "text" key of the sub array, is the string to display in the view or add to the locale files.
    | The "template" key of the sub array, is the string to be use in the view for replacement.
    | The "in-function-with-collective" key of the sub array, tell the generator that,
    | this string would be used in a function or not.
    |
    | The follwowing templates can be used. Assuming the model name is AssetCategory
    | [% model_name %]                   <=> "asset category"
    | [% model_name_sentence %]          <=> "Asset category"
    | [% model_name_plural %]            <=> "asset categories"
    | [% model_name_plural_title %]      <=> "Asset Categories"
    | [% model_name_snake %]             <=> "asset_category"
    | [% model_name_studly %]            <=> "AssetCategory"
    | [% model_name_slug %]              <=> "asset-category"
    | [% model_name_kebab %]             <=> "asset-category"
    | [% model_name_title %]             <=> "Asset Category"
    | [% model_name_title_upper %]       <=> "ASSET CATEGORY"
    | [% model_name_plural_variable %]   <=> "assetCategories"
    | [% model_name_singular_variable %] <=> "assetCategory"
    |
    | ~Example
    | Let's say we need to add a new template in our views that reads the following
    | "Creating resources for ... was a breeze!"
    | The following entry can be added to the below array
    |
    |    'custom_template_1' => [
    |        'text'     => 'Creating resources for [% model_name %] was a breeze!',
    |        'template' => 'custom_template_example',
    |    ],
    | Finally, add [% custom_template_example %] in the view where you want it to appear!
    |
     */
    'generic_view_labels' => [
        'create' => [
            'text' => 'Create New [% model_name_title %]',
            'template' => 'create_model',
        ],
        'delete' => [
            'text' => 'Delete [% model_name_title %]',
            'template' => 'delete_model',
            'in-function-with-collective' => true,
        ],
        'edit' => [
            'text' => 'Edit [% model_name_title %]',
            'template' => 'edit_model',
        ],
        'show' => [
            'text' => 'Show [% model_name_title %]',
            'template' => 'show_model',
        ],
        'show_all' => [
            'text' => 'Show All [% model_name_title %]',
            'template' => 'show_all_models',
        ],
        'add' => [
            'text' => 'Add',
            'template' => 'add',
            'in-function-with-collective' => true,
        ],
        'update' => [
            'text' => 'Update',
            'template' => 'update',
            'in-function-with-collective' => true,
        ],
        'confirm_delete' => [
            'text' => 'Delete [% model_name_title %]?',
            'template' => 'confirm_delete',
            'in-function-with-collective' => true,
        ],
        'none_available' => [
            'text' => 'No [% model_name_plural_title %] Available!',
            'template' => 'no_models_available',
        ],
        'model_plural' => [
            'text' => '[% model_name_plural_title %]',
            'template' => 'model_plural',
        ],
        'model_was_added' => [
            'text' => '[% model_name_title %] was successfully added!',
            'template' => 'model_was_added',
        ],
        'model_was_updated' => [
            'text' => '[% model_name_title %] was successfully updated!',
            'template' => 'model_was_updated',
        ],
        'model_was_deleted' => [
            'text' => '[% model_name_title %] was successfully deleted!',
            'template' => 'model_was_deleted',
        ],
        'unexpected_error' => [
            'text' => 'Unexpected error occurred while trying to process your request!',
            'template' => 'unexpected_error',
        ],
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
    |
     */
    'eloquent_type_to_method' => [
        'char' => 'char',
        'date' => 'date',
        'datetime' => 'dateTime',
        'datetimetz' => 'dateTimeTz',
        'biginteger' => 'bigIncrements',
        'bigint' => 'bigIncrements',
        'tinyblob' => 'binary',
        'mediumblob' => 'binary',
        'blob' => 'binary',
        'longblob' => 'binary',
        'binary' => 'binary',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'bit' => 'boolean',
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
        'smallInteger' => 'smallInteger',
        'smallint' => 'smallInteger',
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
        'uuid' => 'uuid',
    ],

    /*
    |--------------------------------------------------------------------------
    | Eloquent method to html-type mapping.
    |--------------------------------------------------------------------------
    |
    | This is the mapping used to convert database-column into html field
    |
     */
    'eloquent_type_to_html_type' => [
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
    ],
];
