---
sidebar_position: 2
title: Getting Started
---

# Getting Started

### Installation

1. To download this package into your Laravel project, use the command-line to execute the following command

```
composer require crestapps/laravel-code-generator --dev
```

2. (You may skip this step when using Laravel >= 5.5)** To bootstrap the packages into your project while using command-line only, open the app/Providers/AppServiceProvider.php file in your project. Then, add the following code to the `register()` method.

```
if ($this->app->runningInConsole()) {
    $this->app->register('CrestApps\CodeGenerator\CodeGeneratorServiceProvider');
}
```

#### Optional Steps

If you like to override default settings, you may execute the following command to publish a default configuration file to `/config/laravel-code-generator.php`

```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=config
```

If you like to override the default template, you may execute the following command to publish the default template to `/resources/laravel-code-generator/templates/default`

```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default-template
```

### Getting Started Videos

####  How to use Laravel Code Generator to generate production ready code in seconds! 

<iframe class="responsive-video" src="https://www.youtube.com/embed/l21qNcsMAWg?si=RbBsmeN06mq-o_5n" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>


####  Create a CRUDs for all your database tables in seconds using Laravel and Laravel-Code-Generator

<iframe class="responsive-video" src="https://www.youtube.com/embed/infoecfXOCw?si=DYGOJjS7zgeNsrjv" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
