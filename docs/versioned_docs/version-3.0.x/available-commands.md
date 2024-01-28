---
sidebar_position: 3
title: Available Commands
---

## Available Commands

The option in between the square brackets `[]` must be replaced with a variable of your choice.

 - **Main commands**
     - php artisan create:layout \[application-name\]
     - php artisan create:scaffold \[model-name\]
     - php artisan create:controller \[model-name\]
     - php artisan create:model \[model-name\]
     - php artisan create:form-request \[model-name\]
     - php artisan create:routes \[model-name\]
     - php artisan create:migration \[model-name\]
     - php artisan create:language \[model-name\]
     - php artisan create:mapped-resources
 - **Views commands**
     - php artisan create:views \[model-name\]
     - php artisan create:index-view \[model-name\]
     - php artisan create:create-view \[model-name\]
     - php artisan create:edit-view \[model-name\]
     - php artisan create:show-view \[model-name\]
     - php artisan create:form-view \[model-name\]
 - **Resource's files commands**
     - php artisan resource-file:from-database \[model-name\]
     - php artisan resource-file:create \[model-name\]
     - php artisan resource-file:append \[model-name\]
     - php artisan resource-file:reduce \[model-name\]
     - php artisan resource-file:delete \[model-name\]
 - **Migration commands**
     - php artisan migrate-all
     - php artisan migrate:rollback-all
     - php artisan migrate:reset-all
     - php artisan migrate:refresh-all
     - php artisan migrate:status-all
 - **API generation commands**
     - php artisan create:api-scaffold
     - php artisan create:api-controller
     - php artisan create:api-resource
 - **API documentation generation commands**
     - php artisan api-docs:scaffold
     - php artisan api-docs:create-controller
     - php artisan api-docs:create-view

## Important Naming Convention

Laravel-Code-Generator strive to generate highly readable, and error free code. In order to keep your code readable, it is important to follow a good naming convention when choosing names for your models, fields, tables, relations and so on. Here is a list of recommendation that we believe is important to keep your code clean and highly readable.

1. Since each model represents a single object/row in a list/database, naming the model should be written in singular-form while using [Studly Case](https://laravel.com/docs/5.5/helpers#method-studly-case). For example, `Post` and `PostCategory`...
2. Since a database is a collection of model's object, table naming should always be plural and written in lowercase while using [Snake Case](https://en.wikipedia.org/wiki/Snake_case). For example, `users`, `post_categories`...
3. Primary keys should be named `id` in the table.
4. Since the foreign key represents a foreign/other table, the name should always end with `_id`. For example, `post_id`, `user_id`, `post_category_id`...
5. Field naming should always be in a singular-form and written in lowercase while using [Snake Case](https://en.wikipedia.org/wiki/Snake_case). For example, `title`, `first_name`, `description`...


## Examples

The following example assumes that we are trying to create a CRUD called AssetCategory with the fields listed below.

 - id
 - name
 - description
 - is_active

:::info

A layout is required for the default views! You can use <a href="#view-layout">command</a> to create a layout using the command-line. Of course you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/) in your layout for the default templates to work properly. Additionally, you can chose to design your own templates using a different or no css framework.
::::


### Basic example

```
php artisan resource-file:create AssetCategory --fields=id,name,description,is_active
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json`

```
php artisan create:scaffold AssetCategory
```
The above command will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views, the routes, and migration file!


### Basic example using translations for English and Arabic

```
php artisan resource-file:create AssetCategory --fields=id,name,description,is_active --translation-for=en,ar
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json`

```
php artisan create:scaffold AssetCategory
```

The above command will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views, the routes, and migration file!


### Basic example with form-request

```
php artisan resource-file:create AssetCategory --fields=id,name,description,is_active
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json`

```
php artisan create:scaffold AssetCategory --with-form-request
```

The above command will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views, the routes, and migration file!


### Basic example with soft-delete and migration

```
php artisan resource-file:create AssetCategory --fields=id,name,description,is_active
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json``

```
php artisan create:scaffold AssetCategory --with-soft-delete --with-migration
```

The above command will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views, the routes, and migration file!


### Creating resources from existing database.

```
php artisan create:scaffold AssetCategory --table-exists
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json`

Then it will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views and the routes!

You may also create a resource-file from existing database separately using `php artisan resource-file:form-database AssetCategory`


### Creating resources from existing database with translation for English and Arabic

```
php artisan create:scaffold AssetCategory --table-exists --translation-for=en,ar
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json``

Then it will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views and the routes!

You may also create a [resource-file](./resource-file.md) from existing database separately using 

```
php artisan resource-file:form-database AssetCategory --translation-for=en,ar
```

### Creating resources from existing database with translation for English and Arabic in two step for better control over the fields!

```
php artisan resource-file:form-database AssetCategory --translation-for=en,ar
php artisan create:scaffold AssetCategory
```

The above command will create [resource-file](./resource-file.md) names `/resources/laravel-code-generator/asset_categories.json`

Then it will create a model `app/Models/AssetCategory`, a controller `app/Http/Controllers/AssetCategoriesController`, all views and the routes!



## How To

::: Info
All examples below assumes that you already created a [resource-file](./resource-file.md) (i.e resources/codegenerator-fields/posts.json. This file can be created using the following command `php artisan resource-file:create Post --fields=id,title,details,is_active`)
:::

### <a name="view-layout">How to create "views-layout"?</a>

:::note[To create a new layout for your application.]

 ```
 php artisan create:layout [application-name]
 ```

 The argument `[application-name]` should be replaced with the name of the application you are creating. For example: 

 ```
 php artisan create:layout "My New Laravel App"
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --layout-filename | The name of the layout file to be used. | `app` (i.e, creates `app.blade.php`) |
 | --layout-directory | The directory to create the layout under. | `layouts` |
 | <a name="template-name"></a>--template-name | This option allows you to use a different template at run time. When this option is left out, the default template is used.<br /><br /> Note: the default template can be set from the config file (i.e `config/laravel-code-generator.php`) by setting the template key to a different value. | `layouts` |
 | --force | This option will override the layout if one already exists. |  |

:::


### How to create resources (complete CRUD)?

:::note[Create multiple resources at the same time. It can be invoked every time the resource-file is modified to recreate the resources all over again.]

 ```
 php artisan create:scaffold [model-name]
 ```

 The argument `[model-name]` should be replaced with the name of the model you are creating. For example: 

 ```
 php artisan create:scaffold Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | <a name="resource-file"></a>--resource-file | The name of the file to import resource from. This option allows you to have all resources such as fields, indexes and relations in one JSON file, and then import it from the command line. This is a powerful feature which makes it easy to configure the fields, then reuse the same fields in multiple command now or in the future. More documentation on how to manage [resource-file](./resource-file.md) can be found in the "Managing fields using JSON file" section.  | the plural-form of the model name. If the model name is AssetCategory, the name will then be asset_categories.json |
 | <a name="controller-name"></a>--controller-name | The name of the controller to create. If the provided value does not end with the word "Controller" it will be appended. | The controller's name will be generated using the plural-form of the giving model's name. In the above example, the controller will be called "PostsController". |
 | <a name="controller-extends"></a>--controller-extends | Specify which class should the controller extends. Note: the default value is  can be set change by modifying config file (i.e `config/laravel-code-generator.php`). | `default-controller` which means use the settings from the configurations by default `Http\Controllers\Controller`  |
 | <a name="with-auth"></a>--with-auth | Adds the `auth:api` to the controller which prevents any un-authenticated users to access the resources. |  |
 | <a name="routes-prefix"></a>--routes-prefix | Prefix of the route group. | `default-form` which uses the plural-form of the model name. However, this is something can be changed from the configuration file `plural_names_for` key. |
 | <a name="models-per-page"></a>--models-per-page | How many models to show per page on the index view. | `25` |
 | <a name="with-form-request"></a>--with-form-request | Instead of placing the field's validation rules directly in the controller class, this option will extract the rules into a separate form-request class. The form-request class allows you to do more complex validation, cleans up your controller, and increases your code reusability. By default, the method `authorize()` is set to return false for your application's security. This method must be modified to return a true value for the store and update requests to be allowed. Otherwise, the request will be Forbidden. When using `--with-auth` option, the `authorize()` method return `Auth::check()` which should always return true at this point. | |
 | <a name="table-name"></a>--table-name | The database's table name. If this option is left out, it is assumed that the table name is the plural-form of the model-name. In the above example, the table name will be "posts". If the model name is AssetCategory, the table name will be "asset_categories".  |  |
 | <a name="table-exists"></a>--table-exists | This option allows you to generate resources from existing database table. When this option is used, the database's name is assumes to be the plural-form of the provided "model-name". Of course, the table name can be set to a different value by passing the `--table-name option`. <br /><br /> When using this option, the command `php artisan resource-file:from-database` is called behind the scenes to generate a a [resource-file](./resource-file.md) first. The name of the generated [resource-file](./resource-file.md) will be named the plural-form of the model, unless an explicit name is provided using the `--resource-file`` option. This file will allow you to change the default behavior and recreate the view to fit your needs. <br /><br /> This option is currently available only for MySql database only. It will not work if used with a different driver. <br /><br /> Note: To create multiple-language translation from existing database, use the `--translation-for option`. |
 | <a name="translation-for"></a>--translation-for | A comma separated languages. When creating resources from existing database using the `--table-exists options`, `--translation-for` allows you to create multi-language labels. You still have to provide translation for the corresponding language but it will get everything setup for you. <br /><br /> If this option is left out, no translation key's will be generated. <br /><br /> For example, passing `--translation-for=en,ar,fr` will create label under the following languages en, ar and fr. <br /><br />This option will only work when using `--table-exists` option otherwise it is ignored.  |  |
 | <a name="language-filename"></a>--language-filename | The languages file name to put the labels "if any" in. When no value is provided, the file name will be the plural-form of the provided model name. <br /><br /> Note: if the file already exists, and the same key field name exists in the file, no message will be added.<br /><br /> This option will only work when using --table-exists option. |  |
 | <a name="primary-key"></a>--primary-key | The field's name of the primary key. The default value can be overridden by setting the is-auto-increment or the is-primary flag to true in the fields setup. | `id` |
 | <a name="with-soft-delete"></a>--with-soft-delete | Enables the soft-delete feature that Eloquent provides. |  |
 | <a name="without-timestamps"></a>--without-timestamps | Prevent Eloquent from maintaining both `created_at` and the `updated_at` properties. |  |
 | <a name="with-migration"></a>--with-migration | This option will create a migration for your resource. <br /><br /> Behind the scenes, this option invokes the `create:migration` command to create the required migration. |  |
 | <a name="migration-class-name"></a>--migration-class-name | The name of the migration class. If this option is not set, a name will be generated based on the model name. |  |
 | <a name="connection-name"></a>--connection-name | Eloquent uses the configured default database connection. Providing a value here will tell Eloquent to connect using the provided connection. |  |
 | <a name="engine-name"></a>--engine-name | A specific engine name for the database's table can be provided here. |  |
 | <a name="controller-directory"></a>--controller-directory | The directory where the controller should be created under. For example, if the word "Frontend" was provided, the controller will be created in `App/Http/Controllers/Frontend` directory. <br /><br /> The default path where the controller will be created can be set from the config file `config/laravel-code-generator.php`. |  |
 | <a name="model-directory"></a>--model-directory | A directory where the model will be created under. The default path where the model will be created can be set from the config file `config/laravel-code-generator.php`.|  |
 | <a name="views-directory"></a>--views-directory | The name of the directory to create the views under. If this option is left out, the views will be created in `/resources/views` |  |
 | <a name="form-request-directory"></a>--form-request-directory | The directory where the form-request should be created under.<br /><br /> For example, if the word "Frontend" was provided, the form-request will be created in `App/Http/Requests/Frontend` directory. The default path where the form-request will be created can be set from the config file `config/laravel-code-generator.php` |
 | --fields | [Described here](./resource-file#fields) |  |
 | --template-name | [Described above](#template-name) |  |
 | --force | This option will override the layout if one already exists. |  |
:::


### How to create multiple resources at once?

:::note[Create multiple resources at the same time]

 ```
    php artisan create:mapped-resources
 ```

When using `resource-file:create`, `resource-file:from-database` or `resource-file:delete` the `resources_map.json` file is updated behind the scenes. This options create multiple resources for all the resources found in the `resources/laravel-code-generator/sources/resources_map.json` at the same time. The resources can be invoked every time any of the resource-file is modified to recreate the resources all over again.

 | Option | Description |
 | ----------- | ----------- |
 | --controller-extends | [Described above](#controller-extends) |
 | --with-auth | [Described above](#with-auth) |
 | --models-per-page | [Described above](#models-per-page) |
 | --with-form-request | [Described above](#with-form-request) |
 | <a name="without-form-request"></a>--without-form-request | Allow you to create all the resources excluding the form-request if one is used. Note: when creating a controller with a form-request the form-request is injected into the action methods. Thus, in order to create the form-request based controller, you would have to use `--with-form-request` and `--with-form-request` so the controller know you are using form-request but avoid overriding existing form-request. |
 | --form-request-directory | [Described above](#form-request-directory) |
 | --table-exists | [Described above](#table-exists) |
 | --translation-for | [Described above](#translation-for) |
 | --primary-key | [Described above](#primary-key) | 
 | --with-soft-delete | [Described above](#with-soft-delete) |
 | --without-timestamps | [Described above](#without-timestamps) |
 | --with-migration | [Described above](#with-migration) |
 | --connection-name | [Described above](#connection-name) |
 | --engine-name | [Described above](#engine-name) |
 | --controller-directory | [Described above](#controller-directory) |
 | --model-directory | [Described above](#model-directory) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --mapping-filename | This option allows you to pass the name of the mapping-directory file. When this option is left out, the default `resources_map.json`` file will be used. |
 | --force | This option will override the layout if one already exists. |
:::


### How to create a controller?

:::note[Create a controller for your resource.]

 ```
 php artisan create:controller [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:controller Posts
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --controller-name | [Described above](#controller-name) |
 | --controller-directory | [Described above](#controller-directory) |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --models-per-page | [Described above](#models-per-page) |
 | --language-filename | [Described above](#language-filename) |
 | --with-auth | [Described above](#with-auth) |
 | --with-form-request | [Described above](#with-form-request) |
 | --without-form-request | [Described above](#without-form-request) |
 | --form-request-directory | [Described above](#form-request-directory) |
 | --model-directory | [Described above](#model-directory) |
 | --views-directory | [Described above](#views-directory) |
 | <a name="without-languages"></a>--without-languages | Allow you to create all the resources excluding the language file if one is needed. Note: the language file will only be created if the resource file contains translations. |
 | <a name="without-model"></a>--without-model | Allow you to create all the resources excluding the model. |
 | <a name="without-views"></a>--without-views | Allow you to create all the resources excluding the views. |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::



### How to create a model?

:::note[Create a model.]

 ```
 php artisan create:model [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:model Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --table-name | [Described above](#table-name) |
 | --primary-key | [Described above](#primary-key) | 
 | --with-soft-delete | [Described above](#with-soft-delete) |
 | --without-timestamps | [Described above](#without-timestamps) |
 | --model-directory | [Described above](#model-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create routes?

:::note[Create routes for your CRUD operations.]

 ```
 php artisan create:routes [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:routes Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --controller-name | [Described above](#controller-name) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | <a name="without-route-clause">--without-route-clause</a> | Create the routes without where clause for the id. It may be used when the primary key is not an integer |
 | <a name="for-api">--for-api</a> | Create API based routes. |
  | <a name="for-version">--for-version</a> | provide the version of the api to create the routes for. |
 | --table-name | [Described above](#table-name) |
 | --template-name | [Described above](#template-name) |
:::

### How to create all standard CRUD views (i.e. Create, Read, Update and Delete)?

> When creating views using the `create:views`, `create:create-view` or `create:update-view` an additional view called "form-view" is created. The "form-view" contains the form fields to prevent code duplication.

:::note[Create routes for views for CRUD operations.]

 ```
 php artisan create:views [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:views Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | <a name="layout-name"></a>--layout-name | Default value `layouts.app`. A different layout could be used to generate the views. This can easily be done by providing a different layout name. For example, if the physical path to a different layout was `/resources/views/layouts/template/newlayout.blade.php`` then its name would be `layouts.template.newlayout`. |
 | <a name="only-views"></a>--only-views | The only views to be created. A comma separated string with the name of the views to create. By default, create the create,edit,index,show, and form views. |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a view for the Create Operation?

:::note[Create a create-view.]

 ```
 php artisan create:create-view [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:create-view Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --layout-name | [Described above](#layout-name) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::


### How to create a view for the Edit Operation?

:::note[Create an edit-view.]

 ```
 php artisan create:edit-view [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:edit-view Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --layout-name | [Described above](#layout-name) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a view for the List Operation?

:::note[Create an index-view.]

 ```
 php artisan create:index-view [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:index-view Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --layout-name | [Described above](#layout-name) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a view for the Display Operation?

:::note[Create an show-view.]

 ```
 php artisan create:show-view [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:show-view Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --layout-name | [Described above](#layout-name) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a form-view?

:::note[Create an form-view.]

 ```
 php artisan create:form-view [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:form-view Post
 ```

 | Option | Description | Default |
 | ----------- | ----------- | ----------- |
 | --resource-file | [Described above](#resource-file) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --layout-name | [Described above](#layout-name) |
 | --views-directory | [Described above](#views-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a database migration?

:::note[Create a database migration.]

 ```
 php artisan create:migration [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:migration Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --table-name | [Described above](#table-name) |
 | --resource-file | [Described above](#resource-file) |
 | --migration-class-name | [Described above](#migration-class-name) |
 | --with-soft-delete | [Described above](#with-soft-delete) |
 | --without-timestamps | [Described above](#without-timestamps) |
 | --connection-name | [Described above](#connection-name) |
 | --engine-name | [Described above](#engine-name) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override the file if it already exists. |
:::

### How to create form-request?

:::note[Create a form-request for request validation.]

 ```
 php artisan create:form-request [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:form-request Post
 ```

 | Option | Description |
 | ----------- | ----------- | 
 | --class-name | [Described above](#class-name) |
 | --resource-file | [Described above](#resource-file) |
 | --with-auth | [Described above](#with-auth) |
 | --routes-prefix | [Described above](#routes-prefix) |
 | --form-request-directory | [Described above](#form-request-directory) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::

### How to create a language file?

:::note[Create a new language file.]

 ```
 php artisan create:language [model-name]
 ```

The argument `[model-name]` should be replaced with the name of the model you are creating. For example:

 ```
 php artisan create:language Post
 ```

 | Option | Description |
 | ----------- | ----------- |
 | --language-filename | [Described above](#language-filename) |
 | --resource-file | [Described above](#resource-file) |
 | --template-name | [Described above](#template-name) |
 | --force | This option will override any file that already exist. |
:::
