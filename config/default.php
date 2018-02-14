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
    | The default path of where the api-based controllers will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'controllers_path' => 'Http/Controllers',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the controllers will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'api_controllers_path' => 'Http/Controllers/Api',

    /*
    |--------------------------------------------------------------------------
    | The default path to store the controller for the api-documentation.
    |--------------------------------------------------------------------------
    |
     */
    'api_docs_controller_path' => 'Http/Controllers/ApiDocs',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the models will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'models_path' => 'Models',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the api-resource will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'api_resources_path' => 'Http/Resources',

    /*
    |--------------------------------------------------------------------------
    | The default path of where the collection-api-resource will be generated into.
    |--------------------------------------------------------------------------
    |
     */
    'api_resources_collection_path' => 'Http/Resources/Collections',

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
                'api-description' => 'The [% field_name %] of the model.',
            ],
        ],
        [
            'match' => 'id',
            'set' => [
                'is-on-form' => false,
                'is-on-index' => false,
                'is-on-show' => false,
                'is-api-visible' => true,
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
                'is-api-visible' => false,
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
        'api-resource-name' => true,
        'api-resource-collection-name' => true,
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
    | A string to postfix the api-resource name with.
    |--------------------------------------------------------------------------
    |
    | If you don't like to post fix the api-resource with "Resource" you can
    | set this value to an empty string. Or, you can set it to any other value.
    |
     */
    'api_resource_name_postfix' => 'Resource',

    /*
    |--------------------------------------------------------------------------
    | A string to postfix the collection-api-resource name with.
    |--------------------------------------------------------------------------
    |
    | If you don't like to post fix the collection-api-resource with "Collection"
    | you can set this value to an empty string. Or, you can set it to any other
    | value.
    |
     */
    'api_resource_collection_name_postfix' => 'Collection',

    /*
    |--------------------------------------------------------------------------
    | The default path to store the api-documentation.
    |--------------------------------------------------------------------------
    |
     */
    'api_docs_path' => 'api-docs',

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
    | Non-Field base labels to be replaced in the views and api-documentation.
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
            'text' => 'Click Ok to delete [% model_name_title %].',
            'template' => 'confirm_delete',
            'in-function-with-collective' => true,
        ],
        'none_available' => [
            'text' => 'No [% model_name_plural_title %] Available.',
            'template' => 'no_models_available',
        ],
        'model_plural' => [
            'text' => '[% model_name_plural_title %]',
            'template' => 'model_plural',
        ],
        'model_was_added' => [
            'text' => '[% model_name_title %] was successfully added.',
            'template' => 'model_was_added',
        ],
        'model_was_retrieved' => [
            'text' => '[% model_name_title %] was successfully retrieved.',
            'template' => 'model_was_retrieved',
        ],
        'models_were_retrieved' => [
            'text' => '[% model_name_plural_title %] were successfully retrieved.',
            'template' => 'models_were_retrieved',
        ],
        'model_was_updated' => [
            'text' => '[% model_name_title %] was successfully updated.',
            'template' => 'model_was_updated',
        ],
        'model_was_deleted' => [
            'text' => '[% model_name_title %] was successfully deleted.',
            'template' => 'model_was_deleted',
        ],
        'unexpected_error' => [
            'text' => 'Unexpected error occurred while trying to process your request.',
            'template' => 'unexpected_error',
        ],

        /* The following keys are used for the api-documentation */
        'available_resources' => [
            'text' => 'Available Resources',
            'template' => 'available_resources',
        ],
        'request_title' => [
            'text' => 'Request',
            'template' => 'request_title',
        ],
        'parameters_title' => [
            'text' => 'Parameters',
            'template' => 'parameters_title',
        ],
        'response_title' => [
            'text' => 'Response',
            'template' => 'response_title',
        ],
        'header_title' => [
            'text' => 'Header',
            'template' => 'header_title',
        ],
        'this_parameter_is_an_http_header' => [
            'text' => 'This parameter is an HTTP header',
            'template' => 'this_parameter_is_an_http_header',
        ],
        'request_was_successful' => [
            'text' => 'Request was successfull.',
            'template' => 'request_was_successful',
        ],
        'boolean_title' => [
            'text' => 'Boolean',
            'template' => 'boolean_title',
        ],
        'string_title' => [
            'text' => 'String',
            'template' => 'string_title',
        ],
        'integer_title' => [
            'text' => 'Integer',
            'template' => 'integer_title',
        ],
        'decimal_title' => [
            'text' => 'Decimal',
            'template' => 'decimal_title',
        ],
        'file_title' => [
            'text' => 'File',
            'template' => 'file_title',
        ],
        'array_title' => [
            'text' => 'Array',
            'template' => 'array_title',
        ],
        'datetime_title' => [
            'text' => 'DateTime',
            'template' => 'datetime_title',
        ],
        'time_title' => [
            'text' => 'Time',
            'template' => 'time_title',
        ],
        'date_title' => [
            'text' => 'Date',
            'template' => 'date_title',
        ],
        'array_of_strings' => [
            'text' => 'Array of strings',
            'template' => 'array_of_strings',
        ],
        'the_success_message' => [
            'text' => 'The success message',
            'template' => 'the_success_message',
        ],
        'parameters_title' => [
            'text' => 'Parameters',
            'template' => 'parameters_title',
        ],
        'key_title' => [
            'text' => 'Key',
            'template' => 'key_title',
        ],
        'type_title' => [
            'text' => 'Type',
            'template' => 'type_title',
        ],
        'parameter_title' => [
            'text' => 'Parameter',
            'template' => 'parameter_title',
        ],
        'request_type_title' => [
            'text' => 'Request Type',
            'template' => 'request_type_title',
        ],
        'path_title' => [
            'text' => 'Path',
            'template' => 'path_title',
        ],
        'data_type_title' => [
            'text' => 'Data Type',
            'template' => 'data_type_title',
        ],
        'description_title' => [
            'text' => 'Description',
            'template' => 'description_title',
        ],
        'model_definition_title' => [
            'text' => 'Model Definition',
            'template' => 'model_definition_title',
        ],
        'model_id_camel_case' => [
            'text' => '[% model_name %]Id',
            'template' => 'model_id_camel_case',
        ],
        'the_error_message' => [
            'text' => 'The error message.',
            'template' => 'the_error_message',
        ],
        'name_title' => [
            'text' => 'Name',
            'template' => 'name_title',
        ],
        'parameter_type_title' => [
            'text' => 'Parameter Type',
            'template' => 'parameter_type_title',
        ],
        'validation_title' => [
            'text' => 'Validation',
            'template' => 'validation_title',
        ],
        'primary_key_type_title' => [
            'text' => 'Primary Key',
            'template' => 'primary_key_type_title',
        ],
        'required_title' => [
            'text' => 'Required',
            'template' => 'required_title',
        ],
        'body_title' => [
            'text' => 'Body',
            'template' => 'body_title',
        ],
        'this_parameter_is_part_of_the_path' => [
            'text' => 'This parameter is part of the url',
            'template' => 'this_parameter_is_part_of_the_path',
        ],
        'this_parameter_is_part_of_the_body' => [
            'text' => 'This parameter is part of the body',
            'template' => 'this_parameter_is_part_of_the_body',
        ],
        'no_parameters' => [
            'text' => 'No parameters.',
            'template' => 'no_parameters',
        ],
        'parameter_name_title' => [
            'text' => 'Parameter Name',
            'template' => 'parameter_name_title',
        ],
        'field_name_title' => [
            'text' => 'Field Name',
            'template' => 'field_name_title',
        ],
        'field_type_title' => [
            'text' => 'Field Type',
            'template' => 'field_type_title',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Labels to use to generate the text in the api-documentation.
    |--------------------------------------------------------------------------
    |
    | Here you can define labels to be used when creating the api-documentation.
    | You can define how would you like the text to be generated.
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
     */
    'generic_api_documentation_labels' => [
        'access_token_with_bearer' => 'The access token prefixed with the "Bearer " key word.',
        'index_route_description' => 'Retrieve existing [% model_name_plural %].',
        'index_route_response_description' => 'The API\'s response will be JSON based data. The JSON object will be structured as follow',
        'the_key_is_the_model_property_and_the_value_is_the_model_value' => 'The array\'s key is the [% model_name %] property name where the value is the assigned value to the retrieved [% model_name %].',
        'link_to_retrieve_first_page' => 'Link to retrieve first page.',
        'link_to_retrieve_last_page' => 'Link to retrieve last page.',
        'link_to_retrieve_previous_page' => 'Link to retrieve previous page.',
        'link_to_retrieve_next_page' => 'Link to retrieve next page.',
        'the_number_of_current_page' => 'The number of current page.',
        'the_index_of_the_first_retrieved_item' => 'The index of first retrieved [% model_name %].',
        'the_number_of_the_last_page' => 'The number of the last page.',
        'the_base_link_to_the_resource' => 'The base link to the api resource.',
        'the_number_of_models_per_page' => 'The number of [% model_name_plural %] per page.',
        'the_index_of_the_last_retrieved_item' => 'The index of last retrieved [% model_name %].',
        'the_total_of_available_pages' => 'The total of the available pages.',
        'store_route_description' => 'Create new [% model_name %].',
        'store_route_response_description' => 'The API\'s response will be JSON based data. The JSON object will be structured as follow',
        'update_route_description' => 'Update existsing [% model_name %].',
        'update_route_response_description' => 'The API\'s response will be JSON based data. The JSON object will be structured as follow',
        'show_route_description' => 'Retrieve existsing [% model_name %].',
        'show_route_response_description' => 'The API\'s response will be JSON based data. The JSON object will be structured as follow',
        'the_id_of_model_to_retrieve' => 'The unique id of the [% model_name %] to retrieve',
        'destroy_route_description' => 'Delete existsing [% model_name %].',
        'destroy_route_response_description' => 'The API\'s response will be JSON based data. The JSON object will be structured as follow',
        'the_id_of_model_to_delete' => 'The id of the [% model_name %] to delete.',
        'general_description' => 'Allows you to list, create, edit, show and delete [% model_name_plural %].',
        'indicate_whether_the_request_was_successful_or_not' => 'Indicate whether the request was successful or not.',
        'the_id_of_the_model' => 'The id of the [% model_name %].',
        'this_parameter_must_be_present_in_the_request' => 'This parameter must be present in the request.',
        'the_request_failed_validation' => 'The request failed validation.',
        'list_of_the_invalid_errors' => 'List of the invalid errors.',
        'the_requested_model_does_not_exists' => 'The requested [% model_name %] does not exists.',
        'the_user_does_not_have_permission_to_access_the_requested_resource' => 'User does not have permission to access the requested resource.',
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
