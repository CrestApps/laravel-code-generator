---
sidebar_position: 4
title: Resource File
---

## Resource Files

A JSON based file that allows you to define how you like your resource generated. You can define your fields, indexes, and model relations.

## Available Commands to Manage Resource Files

The option in between the square brackets `[]` must be replaced with a variable of your choice.
 - php artisan resource-file:create \[model-name\]
 - php artisan resource-file:append \[model-name\]
 - php artisan resource-file:reduce \[model-name\]
 - php artisan resource-file:delete \[model-name\]

### How to create resource-file?

:::note[Create a new resource file.]

 ```
 php artisan resource-file:create [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan resource-file:create Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | <a name="resource-filename"></a>--resource-filename | The name of the file to be created. When this option is left out, the file will be the plural-form of the model name. If the model name is `AssetCategory`, the file name will be `asset_categories.json`. |
 | <a name="fields"></a>--fields | A list of the field names to be created. The names should be separated by a comma. <br /><br /> You may also pass a complex string using the following schema <br /><br /> `--fields="name:colors;html-type:select;options:blue\|yellow\|green\|red\|white,name:second_field_name"` <br /><br /> Complex string are allowed and will be handy is some cases. However, in most cases all you need to pass is the field names as the common_definitions key in the configuration file will define most options for you out of the box using the name of the field.<br /><br /> Each field in the complex string must be seperated by a `,`. Also each property in the field must be seperated by `;` while each option of a property is seperated by `\|`. |
 | <a name="relations"></a>--relations | A list of the relations to be created. The string should follow the schema below <br /><br /> `--relations="name:comments;type:hasMany;field:title;params:App\Models\Comment\|post_id\|id"`  <br /><br /> Each relation in the string must be seperated by a `,`. Also each property in the relation must be seperated by `;` while each parameter of the params property seperated by `\|`. |
 | <a name="indexes"></a>--indexes | A list of the indexes to be created. The string should follow the schema below<br /><br />  `--indexes="name:first_last_name_index;columns:first_name\|last_name"`. <br /><br /> Each index in the string must be seperated by a `,`. Also each property in the index must be seperated by `;` while each field name in the columns property seperated `\|`.|
 | --translation-for | [Described above](#translation-for) |
 | --force | This option will override any file that already exist. |
:::

### How to add resources to existing resource-file?

:::note[Appends a new fields, indexes, or relations to an existing resource-file. If the resource-file does not exists one will be created]

 ```
 php artisan resource-file:append Post --fields=notes,created_by
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:form-request Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --fields | [Described above](#fields) |
 | --relations | [Described above](#relations) |
 | --indexes | [Described above](#indexes) |
 | --resource-filename| [Described above](#resource-filename) |
 | --translation-for | [Described above](#translation-for) |
:::

### How to remove resources to existing resource-file?

 > If the resource-file becomes empty, it will automatically get deleted by calling the `resource-file:delete` command.

:::note[Removes fields, indexes, or relations to an existing resource-file.]

 ```
 php artisan resource-file:reduce [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan resource-file:reduce Post --fields=notes,created_by
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --fields | [Described above](#fields) |
 | --relations | [Described above](#relations) |
 | --indexes | [Described above](#indexes) |
 | --resource-filename| [Described above](#resource-filename) |
:::

### How to delete existing resource-file?

> It is recommended to use this command to delete file instead of manually deleting it. This command will also delete the mapped relation in the resource_map file.

:::note[Delete existing resource-file. ]

 ```
 php artisan resource-file:delete [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan resource-file:delete Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --fields | [Described above](#fields) |
 | --relations | [Described above](#relations) |
 | --indexes | [Described above](#indexes) |
 | --resource-filename| [Described above](#resource-filename) |
:::

### How to create a resource's file from existing database?

> Are you looking to convert existing application to Laravel framework? Or, looking to use database-first instead of code-first approach? No problem! This package allows you to create a resource's file from existing database.
>
> You can easily take advantage of this feature by passing `--table-exists` option to the `create:resources` command to automatically generate all the resources from existing database's table.

:::note[Convert your existing database into resource file, then the create:resources command is used to generate the resources]

 ```
 php artisan resource-file:from-database [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan resource-file:from-database Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --table-name | [Described above](#table-name) |
 | --database-name | [Described above](#database-name) |
 | --resource-file | [Described above](#resource-file) |
 | --resource-filename| [Described above](#resource-filename) |
 | --translation-for| [Described above](#translation-for) |
 | --force | This option will override any file that already exist. |

:::


### Fields

> The minimum requirement for creating a field is a unique name. However, the code-generator is very flexible and allows you to have full control on the fields. Below all the available properties for defining a field

#### HTML Properties
 | Property name | Description |
 | ----------- | ----------- |
 | name | A unique name for the field. This is a required field. |
 | label | A user-friendly title to describe the field. If this option is left out, the field's name is used to generate a title. |
 | validation | You can pass any valid Laravel validation rule. The rules should be separated by bar `\|`. <br /><br /> For example: `required\|string\|min:2\|max:255` <br /><br />Start with Laravel 5.5, you can define custom validation rules and pass them as well. For example, to use a custom validation rule called Uppercase in addition to the required rule, you can pass this string required|new Uppercase.<br /><br /> To learn more about the valid options please visit [Laravel documentation](https://laravel.com/docs/master/validation#available-validation-rules). <br /><br /> When the rule `required` is not used, the field in the migration file will automatically become nullable. |
 | html-type | Default value: `text`. A valid property will be one of the following options <br /> `text`, `textarea`, `password`,`email`,`checkbox`,`radio`,`number`,`select`,`hidden`,`file`,`selectRange`,`selectMonth`, or `multipleSelect`. <br /><br />Note: when using file type, after the file is uploaded to the designated path, the filename is stored in the database by default. For everything to work properly, the data-type must be of some sort of a string type. Or modify the behavior of moveFile method to handle the new file.<br /><br /> By default this process stores the uploaded file in the path defined in config file. <br /><br />Note: when using checkbox, or multipleSelect, the items are stored in the database as JSON string. Additionally, the items in the index or form views are displayed separated by the value provided in the delimiter property. |
 | delimiter | Default value: "; ". When generating a form with checkbox or a select menu that accepts multiple answers, we need either store the results in a foreign model or store the records in a string field. By default, the code generator will convert the multiple options that a user selected into a JSON string before the results are stored using a Eloquent-mutator method. <br /><br />When the data is presented on the show and/or index views, the options are displayed separated by the value of the delimiter. Of course, you can always change this behavior to fit your needs by removing the accessor and mutator methods in the model and modifying the views accordingly. |
 | css-class | You can add custom css class(es) to the html input. Any value is placed in this option will be appended to the field's `class="..."` property. Classes that are already set in the views will not be replaced. |
 | date-format | Default value: "m/d/Y H:i A". Any field with the type date, time or datetime can be formatted different when it is displayed. You can change the display format using this option. |
 | html-value | A default value to set the field to. When using multiple options based html-type like checkbox, multipleSelect you can set this property to array of values to set multiple values by default. Ex, `["Red","Green"]` |
 | options | If you used select, checkbox, or radio for the html-type property, this is where you provide the options. Here are some example of the schema. <br /> <br /> A simple array: In this option, the value will be the numeric index value of the item in the array.  <br /> ```"options": ["Prefer not to say","Male","Female"]```.<br /> Using explicit values <br /> ```"options": { "": Prefer not to say", "male": "Male","female": "Female"}```. <br /> Using multiple language phrases for each option <br /> ```"options": {"en":{"":"Prefer not to say","male":"Male","female":"Female"},"ar":{"":"Prefer not to say in Arabic","male":"Male in Arabic","female":"Female in Arabic"},"fr":{"":"Prefer not to say in French","male":"Male in French","female":"Female in French"}}``` |
 | is-inline-options | Default value: false. If the html-type is set to radio or checkbox, setting this option to true will put the items next to each other instead of a vertical list. |
 | placeholder or place-holder | You can set a placeholder value when html-type is set to text, number, email, textarea or select. |
 | is-on-index | Default value: `true`.  Setting the value to `false` will prevent from adding this field to the index view. |
 | is-on-form | Default value: `true`.  Setting the value to `false` will prevent from adding this field to the form view. | 
 | is-on-show | Default value: `true`.  Setting the value to `false` will prevent from adding this field to the show view. | 
 | is-on-views | Default value: `true`. Setting the value to `false` will prevent from adding this field to the index, form or show view. This is just a short way to change the visibility for all views.  | 
 | is-header | Default value: false. Only one field can be set to a header. The header field will be use as the page header in the show view. The key `common_header_patterns` in the configuration file, allow you to list the common field name to automatically set them as header. |
 
 #### Database Properties
 | Property name | Description |
 | ----------- | ----------- |
 | data-type | Default is `varchar`. The database column type. The following are valid types: <br /> ```'char', 'date', 'datetime', 'datetimetz', 'biginteger', 'bigint', 'blob', 'binary', 'bool', 'boolean', 'decimal', 'double', 'enum', 'list', 'float', 'int', 'integer', 'ipaddress', 'json', 'jsonb', 'longtext', 'macaddress', 'mediuminteger', 'mediumint', 'mediumtext', 'morphs', 'string', 'varchar', 'nvarchar', 'text', 'time', 'timetz', 'tinyinteger', 'tinyint', 'timestamp', 'timestamptz', 'unsignedbiginteger', 'unsignedbigint', 'unsignedInteger', 'unsignedint', 'unsignedmediuminteger', 'unsignedmediumint', 'unsignedsmallinteger', 'unsignedsmallint', 'unsignedtinyinteger', 'uuid', 'uuid'``` <br /><br /> Note: you can add short cuts if needed to in the `laravel-code-generator.php` config file.You can add new mapping to the eloquent_type_to_method array. | 
 | data-type-params |  This option allows you to specify parameters for the data type. Please ensure you provide valid parameters otherwise unexpected behavior will occur. For example, varchar and char will only need a maximum of one integer parameter where double, decimal and float require two integer parameters. <br /><br /> Command line example with specifying a decimal precision and scale: `data-type-params=5,2`. JSON file example `"data-type-params": [5,2]`<br /><br />If this option left out while some sort of a string data-type was used along with a max validation rule, the max value is used to limit the length of the sting in the database when a migration is generated | 
 | data-value | Default value is null.  The default value for the database column. | 
 | is-auto-increment | Default value is false.  Setting this value to true will make this column a primary with auto increment identity. | 
 | is-primary | Default value is false. You can set this field as the primary for retrieving records from the database. You can only set one column as the primary. If you set multiple fields are primary, the first one will be selected and the rest will be ignored. <br /><br />Note: if you set the is-auto-increment field, this option will automatically get set. Ths only time this can be used is to create a primary field you don't wish for the database to auto assign it. | 
 | is-index | Default value is false. Setting this value to true will add index to this column.  | 
 | is-unique | Default value is false.  Setting this value to true will add a unique index to this column.  | 
 | is-nullable | Default value is false. Setting this value to true will make this column nullable. <br /><br />Note: when setting this option to true, the default value will be set to NULL unless you pass a different value to data-value. <br /><br /> When the validation rule contains "nullable", "required_if", "required_unless", "required_with", "required_with_all", "required_without", "required_without_all" or does NOT contains "required" rule, this flag will automatically gets set. | 
 | is-unsigned | Default value is false.  Setting this value to true will make this column unsigned. This option should only be used with numeric types only. | 
 | comment |  This option will allow you to add meta description of the field in the database.  | 
 | is-data |  This option will allow you to casts a data filed to a Carbon object.  | 
 | cast-as |   This option will allow you to cast a field to php's native type.   | 
 | foreign-relation |   This option will allow you to create a foreign relation between the models. <br /><br /> ```json {"name":"creator","type":"belongsTo","params":["App\\User","created_by"],"field":"name"}```   | 
 | foreign-constraint |   This option will allow you to create a foreign relation between the models.  <br /><br /> ```json {"field":"user_id","references":"id","on":"users","on-delete":"cascade","on-update":"cascade","references-model":"App\\Models\\User"} ``` | 
 | on-store |   This option allows you to set a fixed value on the store action. For example, Illuminate\Support\Facades\Auth::Id(); will set the value to the current user id when the model is first created. Assuming you're using [Laravel Authentication](https://laravel.com/docs/master/authentication).   | 
 | on-update |   Similar to on-storeThis option allows you to set a fixed value on the update action.   | 


### Managing fields using JSON file

Storing the field's specification in a JSON file enables you to easily reuse the field with multiple commands. It also allows you to recreate the resources in the future if you decided to add/remove fields after the views have been crafted. The JSON files are typically stored in /resources/laravel-generator. If you don’t like where these files are kept, you can change that path from the config/laravelgenerator.php file.

The following command should be used to manage the resource-file to make this process easier.

 - php artisan resource-file:from-database [model-name]
 - php artisan resource-file:create [model-name]
 - php artisan resource-file:append [model-name]
 - php artisan resource-file:reduce [model-name]
 - php artisan resource-file:delete [model-name]

### Resources mapping file

The resources-map file, is a JSON file that is used to keep track of the fields-file and the model classes to allow you to create the resources all at once.

The default file name is `resources_map.json` and can be changed from the configuration file.

When using `resource-file:create`, `resource-file:from-database` or `resource-file:delete` commands, a file called resources_map.json is automatically updated.

The following is the structure of the file.

```json
 {
    {
        "model-name": "Brand",
        "resource-file": "brands.json"
    },
    {
        "model-name": "Customer",
        "resource-file": "customers.json",
        "table-name": "customers_table"
    }
}
```

 All option that are available to the `create:resources` can be used in the mapping file to make creating resources for all models customizable. Here is an example 

 ```json 
  {
    {
        "model-name": "Customer",
        "resource-file": "customers.json",
        "table-name": "customers_table",
        "routes-prefix" "customers_prefix"
    }
}
 ```

To generate all the resources mapped in the resources_map.json file, use the following command

```
php artisan create:mapped-resources [model-name]
```

### Generating clean and complete fields out of the box!

When using the commands that generate fields, our goal is to generate fields configured and ready for use without having to make any change to the generated fields.

While it is not possible to cover 100% of the use cases, Laravel-code-generator is shipped with a powerful configuration option to allow you to add conditions to handle your own use case.

The key `common_definitions` in the `config/laravel-code-generator.php` file allows you match field name using pattern then set the properties accordingly.

For example, you may want to add a global date, time, or datetime picker using javascript for any field where its name ends with `_at`.

You can do that by adding the following entry

```json
[
    'match' => ['*_at'],
    'set'   => [
        'class'   => 'datetime-picker',
    ]
]
```

The same thing can be done for any field that ends with `_date` or starts with `date_of``
```json
[
    'match' => ['*_date','date_of_*'],
    'set'   => [
        'class'   => 'date-picker',
    ]
]
```

Of course, you can set any of the field's option like html-type, data-type, data-type-params or foreign relation. You can set the configuration as fits your environment, then you'll be able to create fields-file ready to generate resources with minimal work!

The conditions are applied to each field top to bottom, the configuration at the bottom of the array will take presence over the once on the top in case multiple conditions were matched.


:::info
It is strongly recommended to read the comments above each option in the configuration file to help you understand and customize the generator to fit your needs! 
:::


### Foreign Relations

If you're using a code-first-approach and like to define relations between your models, you can easily define that in the relations keyword entry of the resource-file. Each relation can be defined using the following schema

```json
{
    "name": "posts",            // the name of the relation
    "type": "hasMany",          // the type of the relation
    "params": [                 // the parameters for the used relation.
        "App\\Models\\Comment",
        "post_id",
        "id"
    ],
    "field": "name"             // the name of the field on the foreign model to use as display value
}
```
:::info
When creating `hasOne()` or `belongsTo()` relations, it be best to define them at the field level using the foreign-relation option.
Composite Indexes
:::


### Composite Indexes
If you're using a code-first-approach and like to define indexes with multiple columns, you can easily do that by adding these indexed to the Indexes keyword entry in the resource-file file. Each composite index can be defined using the following schema

```json
{
    "name": "owner",  // The name of the index to use, if no name is set a one will be generated.
    "type": "unique", // Valid index type is one of the following 'index','unique' or 'primary'. If the type is not provided, 'index' is used.
    "columns": [      // List of the columns' names to be included in the index left to right.
        "first_name",
        "last_name"
    ]
}
```
