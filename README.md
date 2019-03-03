
[![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=An%20intelligent%20code%20generator%20for%20Laravel%20framework%20which%20will%20save%20you%20lots%20of%20time!&url=https://github.com/CrestApps/laravel-code-generator&hashtags=laravel,laravel-code-generator,laravel-crud,code-generator,crud-generator,laravel-crud-generator)

## Introduction

An intelligent code generator for Laravel framework that will save you time! This awesome tool will help you generate resources like views, controllers, routes, migrations, languages and/or form-requests! It is extremely flexible and customizable to cover many on the use cases. It is shipped with cross-browsers compatible template, along with a client-side validation to modernize your application.

For full documentation and live demo please visit <a href="https://crestapps.com/laravel-code-generator/docs/2.3" target="_blank" title="Laravel Code Generator Documentation">CrestApps.com</a>

## Features

<ul>
	<li>One step installation when using Laravel 5.5+</li>
	<li>Create very clean, reusable and highly readable code to build on.</li>
	<li>Create full resources using a single command with <strong>migration</strong> or from <strong>existing database</strong>.</li>
	<li>Creates full resources for all of the existing tables in the database using one command.</li>
	<li>Create full API-based resources using a single command with <strong>migration</strong> or from <strong>existing database</strong>.</li>
	<li>Create beautiful documentation for your API.</li>
	<li>Create api-resource and api-resource-collection with Laravel 5.5+.</li>
	<li>Allows you to save the fields in a JSON file and recreate resources when the business needs changes.</li>
	<li>Utilizes JSON based resource-file to allow you to define your resources. Resource-file allows you to easily regenerate the resource at any time even when the business rules change.</li>
	<li>Create standard CRUD controllers with simple or form-request validation.</li>
	<li>Customizable view’s templates to enable you to change the standard look and feel of your application.</li>
    <li>Create model with relations.</li>
    <li>Create named routes with and without group.</li>
    <li>Create standard CRUD views.</li>
    <li>Smart migration engine! Keeps track of all generated migrations to only create the needed migration.</li>
    <li>Intelligent enough to automatically handles the relations between the models.</li>
    <li>Very flexible and rich with configurable options.</li>
    <li>Easy commands to create resource-file; additionally, add or reduce existing resource-file.</li>
    <li>Full capability to generate multi-languages applications.</li>
    <li>Client-side validation.</li>
    <li>File uploading handling.</li>
    <li>Auto store multiple-response in the database.</li>
    <li>Create form-request to clean up your controller and increase your code reusability.</li>
    <li>Create view's layouts with and without client-side validation.</li>
    <li>Change the template at run time to generate different views.</li>
    <li>Ability to generate views with and without Laravel-Collective.</li>
    <li>Nicely handles any date, time or datetime field.</li>
    <li>Auto handles any boolean field.</li>
    <li>Very easy to use with lots of documentation.</li>
</ul>

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

> A layout is required for the default views! The code generator allows you to create a layout using the command-line. Of cource you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/ "CSS bootstrap framework") in your layout for the default templates to work properly. Additionally, you can chose to you design your own templetes using a different or no css framework. 

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

> Full documentation available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/docs/2.3 "Laravel Code Generator Documentation"). 

> Live demo is available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/demos/v2-3 "Laravel Code Generator Live Demo"). 

## Contribution
Do you like this project and want to contribute?
- **HELP WANTED** Version `v2.3` needs to be documented before it can be released. If you are able to contribute, please read the <a href="https://github.com/CrestApps/laravel-code-generator/blob/v2.3/CHANGELOG.md">change-log</a> in <a href="https://github.com/CrestApps/laravel-code-generator/tree/v2.3">v2.3 branch</a> and document it in the <a href="https://github.com/CrestApps/crestapps-site">CrestApps-site</a> repository. For any help, my email can be found in the `composer.json` file, feel free to send me an email.
- Please start by ***Staring*** this package on GitHub.
- Sharing this projects with others is your way of saying keep improvements and new awesome feature coming.
- Report any bugs or send us any comments, idea, thought that you may have about this project as an issue on GitHub.

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


## What's new?
* <a href="https://crestapps.com/laravel-code-generator/docs/2.3#release-notes">Release Notes</a>
* <a href="https://crestapps.com/laravel-code-generator/docs/2.3#upgrade-guide">Upgrade Guide</a>

## License

"Laravel Code Generator" is an open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank" title="MIT license">MIT license</a>

