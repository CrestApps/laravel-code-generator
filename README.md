
[![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=An%20intelligent%20code%20generator%20for%20Laravel%20framework%20which%20will%20save%20you%20lots%20of%20time!&url=https://github.com/CrestApps/laravel-code-generator&hashtags=laravel,laravel-code-generator,laravel-crud,code-generator,crud-generator,laravel-crud-generator)

## Introduction

An intelligent code generator for Laravel framework that will save you time! This awesome tool will help you generate resources like views, controllers, routes, migrations, languages and/or form-requests! It is extremely flexible and customizable to cover many on the use cases. It is shipped with cross-browsers compatible template, along with a client-side validation to modernize your application.

For full documentation and live demo please visit <a href="https://laravel-code-generator.crestapps.com" target="_blank" title="Laravel Code Generator Documentation">CrestApps.com</a>

## Features

- Craft clean, reusable, and highly readable code for seamless development.
- Generate complete resources effortlessly with a single command, supporting both migration and existing database scenarios.
- Streamline resource creation for all existing database tables with a single command.
- Save and recreate fields using a JSON file, ensuring adaptability to changing business needs.
- Leverage JSON-based resource files for easy regeneration, even when business rules evolve.
- Generate standard CRUD controllers with simple or form-request validation.
- Customize view templates to alter the standard look and feel of your application.
- Create models with relations for comprehensive data representation.
- Establish named routes with and without grouping for efficient navigation.
- Generate standard CRUD views to facilitate a consistent user experience.
- Smart migration engine tracks generated migrations to only create necessary ones.
- Intelligent handling of model relations to simplify development.
- Highly flexible with rich configurable options to suit diverse needs.
- Easy commands for resource-file creation, addition, or reduction.
- Full support for generating multi-language applications.
- Implement client-side validation for enhanced user interaction.
- Efficiently handle file uploading and store multiple responses in the database.
- Generate form-request to clean up controllers and boost code reusability.
- Create view layouts with and without client-side validation.
- Change templates at runtime for diverse view generation.
- Ability to generate views with or without Laravel-Collective integration.
- Seamless handling of date, time, or datetime fields.
- Automatic management of boolean fields for hassle-free development.

## Installation

1. To download this package into your Laravel project, use the command-line to execute the following command

	```
	composer require crestapps/laravel-code-generator --dev
	```
 
2. **(You may skip this step when using Laravel >= 5.5)** To bootstrap the packages into your project while using command-line only, open the app/Providers/AppServiceProvider.php file in your project. Then, add the following code to the register() method.

	Add the following line to bootstrap `laravel-code-generator` to the framework.

	```
	if ($this->app->runningInConsole()) {
	    $this->app->register('CrestApps\CodeGenerator\CodeGeneratorServiceProvider');
	}
	```

## Lessons
Checkout our channel on <a href="https://www.youtube.com/channel/UCkEd0nOoRf3o0ahspAu7Y9w/videos" target="_blank" title="CrestApps YouTube Channel">YouTube.com</a> 
* https://youtu.be/l21qNcsMAWg
* https://youtu.be/infoecfXOCw


## Available Commands

> The command in between the square brackets **[]** must be replaced with a variable of your choice.

<ul>
	<li>
		<strong>Main commands</strong>
		<ul>
		    <li>php artisan create:scaffold [model-name]</li>
		    <li>php artisan create:controller [model-name]</li>
		    <li>php artisan create:model [model-name]</li>
		    <li>php artisan create:form-request [model-name]</li>
		    <li>php artisan create:routes [model-name]</li>
		    <li>php artisan create:migration [model-name]</li>
		    <li>php artisan create:language [model-name]</li>
		    <li>php artisan create:mapped-resources</li>
	    </ul>
	</li>
	<li>
		<strong>API commands</strong>
		<ul>
		    <li>php artisan create:api-scaffold [model-name]</li>
		    <li>php artisan create:api-controller [model-name]</li>
		    <li>php artisan create:api-resources [model-name]</li>
	    </ul>
	</li>
	<li>
		<strong>API Documentations commands</strong>
		<ul>
		    <li>php artisan api-docs:scaffold [model-name]</li>
		    <li>php artisan api-doc:create-controller [model-name]</li>
		    <li>php artisan api-doc:create-view [model-name]</li>
	    </ul>
	</li>
	<li>
		<strong>Views commands</strong>
		<ul>
			<li>php artisan create:layout [application-name]</li>
			<li>php artisan create:views [model-name]</li>
			<li>php artisan create:index-view [model-name]</li>
		    <li>php artisan create:create-view [model-name]</li>
		    <li>php artisan create:edit-view [model-name]</li>
		    <li>php artisan create:show-view [model-name]</li>
		    <li>php artisan create:form-view [model-name]</li>
	    </ul>
	</li>
	<li>
		<strong>Resource's files commands</strong>
		<ul>
		    <li>php artisan resource-file:from-database [model-name]</li>
		    <li>php artisan resource-file:create [model-name]</li>
		    <li>php artisan resource-file:append [model-name]</li>
		    <li>php artisan resource-file:reduce [model-name]</li>
		    <li>php artisan resource-file:delete [model-name]</li>
	    </ul>
	</li>
	<li>
		<strong>Migration commands</strong>
		<ul>
		    <li>php artisan migrate-all</li>
		    <li>php artisan migrate:rollback-all</li>
		    <li>php artisan migrate:reset-all</li>
		    <li>php artisan migrate:refresh-all</li>
		    <li>php artisan migrate:status-all</li>
	    </ul>
	</li>
</ul>


## Contribution

Are you interested in supporting this project and making a contribution? Here's how you can get involved:

- Begin by showing your appreciation for this package on GitHub by giving it a **star**.
- Share this project with others to encourage ongoing enhancements and the introduction of new features.
- Report any bugs, provide comments, share ideas, or express your thoughts about this project by creating an issue on GitHub.
- Contributors are encouraged! If you're passionate about this project, consider addressing existing issues by submitting a pull request.
- If possible, consider [sponsoring the project](https://github.com/sponsors/CrestApps).


## What did you create with this package?
I'd love to know if your site was generated using this package and list your logo on the documentation site. Please email using my contact info found in `composer.json` file.

## Examples

The following example assumes that we are trying to create a CRUD called <var>AssetCategory</var> with the fields listed below.

- id
- name
- description
- is_active


#### Basic example - CRUD with migration

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --fields=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><code>php artisan create:scaffold AssetCategory --with-migration</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and migration class!</var></small></p>
</blockquote>

#### Basic example - CRUD with migration - Shortcut

<blockquote>
<p><code>php artisan create:scaffold AssetCategory --with-migration --fields=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var> first. Then, it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and migration class!</var>. This is a short way to issuing both `resource-file:create` and `create:scaffold` in one line</small></p>
</blockquote>


#### Basic API example - CRUD with migration

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --fields=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><code>php artisan create:scaffold AssetCategory --with-migration</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and migration class!</var></small></p>
</blockquote>

#### Basic example using translations for English and Arabic - with migration

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --fields=id,name,description,is_active --translation-for=en,ar</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><code>php artisan create:scaffold AssetCategory --with-migration</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and migration class!</var></small></p>
</blockquote>

#### Basic example with form-request

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --fields=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><code>php artisan create:scaffold AssetCategory --with-form-request</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and <var>app/Http/Requests/AssetCategoriesFormRequest</var> class!</var></small></p>
</blockquote>

#### Basic example with soft-delete and migration

<blockquote>
<p><code>php artisan resource-file:create AssetCategory --fields=id,name,description,is_active</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><code>php artisan create:scaffold AssetCategory --with-soft-delete --with-migration</code></p>
<p><small>The above command will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views, the routes, and migration file!</var></small></p>
</blockquote>

#### Creating resources from existing database

<blockquote>
<p><code>php artisan create:scaffold AssetCategory --table-exists</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var>. It is going to assume that the table name is called "asset_categories" in your database. If that is not the case, you can use <var>--table-name=some_other_table_name</var></small></p>

<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views and the routes!</var></small></p>
<p><small>You may also create a resource-file from existing database separately using <code>php artisan resource-file:from-database AssetCategory</code></small></p>
</blockquote>


#### Creating resources from existing database with translation for English and Arabic

<blockquote>
<p><code>php artisan create:scaffold AssetCategory --translation-for=en,ar --table-exists</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views and the routes!</var></small></p>
<p><small>You may also create a resource-file from existing database separately using <code>php artisan resource-file:from-database AssetCategory --translation-for=en,ar</code></small></p>
</blockquote>

#### Creating resources from existing database with translation for English and Arabic in two step for better control over the fields!

<blockquote>
<p><code>php artisan resource-file:from-database AssetCategory --translation-for=en,ar</code></p>
<p><code>php artisan create:scaffold AssetCategory</code></p>
<p><small>The above command will create resource-file names <var>/resources/laravel-code-generator/sources/asset_categories.json</var></small></p>
<p><small>Then it will create a model <var>app/Models/AssetCategory</var>, a controller <var>app/Http/Controllers/AssetCategoriesController, all views and the routes!</var></small></p>
</blockquote>

s
* <a href="https://crestapps.com/laravel-code-generator/docs/2.3#upgrade-guide">Upgrade Guide</a>

## License

"Laravel Code Generator" is an open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank" title="MIT license">MIT license</a>
