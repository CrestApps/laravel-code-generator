---
sidebar_position: 10
title: Laravel Collective
---

## Using Laravel-Collective to generate views

:::info 
 To use Laravel-Collective to generate view, you'll have to install the [Laravel-Collective](https://github.com/LaravelCollective/html) package. 
 :::

 Laravel-Code-Generator is capable of fully generating views using Laravel-Collective package. In fact, it is shipped with a template based on Laravel-collective called "default-collective".

By default, the template "default-collective" is not published to the resources folder as it is not needed out of the box. To publish it, use the command-line to execute the following command.

```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default-collective
```

### How to generate views using the Laravel-Collective package?

There are two ways to generate views using Laravel-Collective

- Via the package configuration

  > Open the config file of the package /config/codegenerator.php change the value of the key template to default-collective

- Or, via command-line

  > Change the template name at run time. In another words, pass the following option--template-name=default-collective from command-line

### How to create a new template based on Laravel-Collective?

First, duplicate the folder `/resources/codegenerator-templates/default-collective`` and name it anything your like.

Second, open up the package config file and add the new template name to the `laravel_collective_templates` array.
