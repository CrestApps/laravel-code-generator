# An awesome code generator for laravel framework - with client-side validation

For full documentation and live demo please visit <a href="https://crestapps.com/laravel-code-generator/docs/2.2" target="_blank" title="Laravel Code Generator Documentation">CrestApps.com</a>

## Introduction

A clean code generator for Laravel framework that will save you time! This awesome tool will help you generate resources like views, controllers, routes, migrations, languages or request-forms! It is extremely flexible and customizable to cover many on the use cases. It is shipped with cross-browsers compatible template, along with a client-side validation to modernize your application.

## Features

* Create very clean, reusable and highly readable code to build on.
* Create full resources using a single command with/without **migration** or from **existing database**.
* Create standard CRUD controllers with simple or form-request validation.
* Utilizes JSON based resource-file to allow you to define your resources. Resource-file allows you to easily regenerate the resource at any time even when the business rules change.
* Create model with relations.
* Create named routes.
* Create standard CRUD views.
* Customizable view’s templates to enable you to change the standard look and feel of your application.
* Change the template at run time to generate different views.
* Client-side validation.
* Create view's layouts with and without client-side validation.
* Very flexible and rich with configurable options.
* It auto create the code to allow you to completly upload files from the client to the server.
* Auto multiple-response storing in the database.
* Create form-request for complex validation.
* Ability to generate views with and without Laravel-Collective.
* Nicely deals with and format datetime, date or time field.
* Auto handles any boolean field.
* Auto add foreign relations to the model.
* Auto use foreign relation in the controller and the views when needed.
* Lots of documentation.

## Installation

1. To download this package into your laravel project, use the command-line to execute the following command

```
composer require crestapps/laravel-code-generator --dev
```
 
2. **(You may skip this step when using Laravel >= 5.5)** To bootstrap the packages into your project while using command-line only, open the app/Providers/AppServiceProvider.php file in your project. Then, add the following code to the register() method.

Add the following line to bootstrap laravel-code-generator to the framework.

```
if ($this->app->runningInConsole()) {
    $this->app->register('CrestApps\CodeGenerator\CodeGeneratorServiceProvider');
}
```

3. Execute the following command from the command-line to publish the package's config and the default template to start generating awesome code.
```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default
```

> A layout is required for the default views! The code generator allows you to create a layout using the command-line. Of cource you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/ "CSS bootstrap framework") in your layout for the default templates to work properly. Additionally, you can chose to you design your own templetes using a different or no css framework. 

## Available Commands

> The command in between the square brackets [] must be replaced with a variable of your choice.

* php artisan create:layout [application-name]
* php artisan create:resources [model-name]
* php artisan create:mapped-resources
* php artisan create:controller [model-name]
* php artisan create:model [model-name]
* php artisan create:routes [model-name]
* php artisan create:views [model-name]
* php artisan create:create-view [model-name]
* php artisan create:edit-view [model-name]
* php artisan create:index-view [model-name]
* php artisan create:show-view [model-name]
* php artisan create:form-view [model-name]
* php artisan create:migration [model-name]
* php artisan create:form-request [model-name]
* php artisan create:language [model-name]
* php artisan resource-file:from-database [model-name]
* php artisan resource-file:create [model-name]
* php artisan resource-file:append [model-name]
* php artisan resource-file:reduce [model-name]
* php artisan resource-file:delete [model-name]

> Full documentation available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/docs/2.2 "Laravel Code Generator Documentation"). 

> Live demo is available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/demos/v2-1 "Laravel Code Generator Live Demo"). 


## Examples

The following example assumes that we are trying to create a CRUD called <var>AssetCategory</var> with the fields listed below.

- id
- name
- description
- is_active


#### Basic example

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --names=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><code>php artisan create:resources AssetCategory</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views, the routes, and migration file!</var></small></p>
</blockquote>


###	Basic example using translations for English and Arabic

<blockquote>
<p><code>php artisan fields-file:create AssetCategory --names=id,name,description,is_active --translation-for=en,ar</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><code>php artisan create:resources AssetCategory</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views, the routes, and migration file!</var></small></p>
</blockquote>

### Basic example with form-request

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --names=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><code>php artisan create:resources AssetCategory --with-form-request</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views, the routes, and migration file!</var></small></p>
</blockquote>

### Basic example with soft-delete and migration

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --names=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><code>php artisan create:resources AssetCategory --with-soft-delete --with-migration</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views, the routes, and migration file!</var></small></p>
</blockquote>

### Creating resources from existing database

<blockquote>
<p><code>php artisan create:resources AssetCategory --table-exists</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views and the routes!</var></small></p>
<p><small>You may also create a resource-file from existing database separately using <code>php artisan resource-file:form-database AssetCategory</code></small></p>
</blockquote>


### Creating resources from existing database with translation for English and Arabic

<blockquote>
<p><code>php artisan create:resources AssetCategory --table-exists --translation-for=en,ar</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views and the routes!</var></small></p>
<p><small>You may also create a resource-file from existing database separately using <code>php artisan resource-file:form-database AssetCategory --translation-for=en,ar</code></small></p>
</blockquote>

### Creating resources from existing database with translation for English and Arabic in two step for better control over the fields!

<blockquote>
<p><code>php artisan resource-file:form-database AssetCategory --translation-for=en,ar</code></p>
<p><code>php artisan create:resources AssetCategory</code></p>
<p><small>The above command will create resource-file names <var>/resources/codegenerator-files/asset_categories.json</var></small></p>
<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AsseyCategoriesController, all views and the routes!</var></small></p>
</blockquote>


## Prologue
* <a href="https://crestapps.com/laravel-code-generator/docs/2.2#release-notes">Release Notes</a>
* <a href="https://crestapps.com/laravel-code-generator/docs/2.2#upgrade-guide">Upgrade Guide</a>

## License

"Laravel Code Generator" is an open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank" title="MIT license">MIT license</a>

