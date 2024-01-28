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

3. Execute the following command from the command-line to publish the package's config and the default template to start generating awesome code.

```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default
```

A layout is required for the default views! The code generator allows you to create a layout using the command-line. Of course you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/) in your layout for the default templates to work properly. Additionally, you can chose to design your own templates using a different or no css framework. For more info on how to create a custom template [click here](https://crestapps.com/laravel-code-generator/docs/2.2#how-to-create-custom-template)!


### Getting Started Videos

####  How to use Laravel Code Generator to generate production ready code in seconds! 

<iframe width="840" height="472" src="https://www.youtube.com/embed/l21qNcsMAWg?si=RbBsmeN06mq-o_5n" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>


####  Create a CRUDs for all your database tables in seconds using Laravel and Laravel-Code-Generator

<iframe width="840" height="472" src="https://www.youtube.com/embed/infoecfXOCw?si=DYGOJjS7zgeNsrjv" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
