# An awesome code generator for laravel framework

For full documentation please visit This is <a href="https://crestapps.com/laravel-code-generator/docs/1.0" target="_new" title="Laravel 5.1+ Awesome Code Generator documentation">CrestApps.com</a>


## Introduction

Clean code generator for Laravel framework that will save you time! This awesome tool will help you generate views, controllers, routes, migration or request forms! The source code of this project can be found at GitHub

## Features

* Create standard CRUD controllers with standard or form-request validation.
* Create model.
* Create named routes.
* Create standard CRUD views.
* Create Form-Request
* Customizable view’s templates to enable you to change the standard look and feel of your application.
* Create layouts.
* Create languages/Locale files.



## Installation

> If you don't already have `LaravelCollective Forms & HTML package` installed it will be installed for you. However you'll still have to update your config/app.php to bootstrap the package to the framework.


 Using the command line execute the following command 
 ```
 composer require crestapps/laravel-code-generator --dev
 ```
 
Open the `config/app.php` file in your project and do the following two steps
First, look for the `providers` array. Add the following service provider to it.

```
//Add this line to bootstrap laravel-code-generator to the framework
CrestApps\CodeGenerator\CodeGeneratorServiceProvider::class,

//Only add this line if one does not already exists.
//The following line will bootstrap LaravelCollective to the framework.
Collective\Html\HtmlServiceProvider::class,
```

Second, look for the `aliases` array in the `config/app.php` file. Add the following code to it only if `LaravelCollective Forms & HTML` package was already installed.
```
//Only add these two line if one does not already exists.
//The following two line will finish bootstraping LaravelCollective to the framework
'Form' => Collective\Html\FormFacade::class,
'Html' => Collective\Html\HtmlFacade::class,
```


Finally, publish the config and the default template to start generating awesome code.

```
php artisan vendor:publish --provider="CrestApps\\CodeGenerator\\CodeGeneratorServiceProvider"
```

> A layout is required for the default views! The code generator allows you to create a layout using the command line. Of cource you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/ "CSS bootstrap framework") in your layout for the default templates to work properly. Additionally, you can chose to you design your own templetes and not use [CSS bootstrap framework](http://getbootstrap.com/ "CSS bootstrap framework" target="_blank") altogether.



## Available Commands

> The command in wetween the square brackets [] must be replaced with a variable of your choice.

* php artisan create:resource [model-name]
* php artisan create:layout [application-name]
* php artisan create:controller [controller-name]
* php artisan create:model [model-name]
* php artisan create:routes [controller-name]
* php artisan create:views [model-name]
* php artisan create:create-view [model-name]
* php artisan create:edit-view [model-name]
* php artisan create:index-view [model-name]
* php artisan create:show-view [model-name]
* php artisan create:form-view [model-name]
* php artisan create:migration [table-name]
* php artisan create:form-request [class-name]
* php artisan create:language [language-file-name]

> Full documentation available at [CrestApps.com](https://crestapps.com/laravel-code-generator/docs/1.0 "Laravel 5.1+ Awesome Code Generator documentation" target="_blank"). 


## License

The Laravel-CodeGenerator is open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_new" title="MIT license">MIT license</a>

