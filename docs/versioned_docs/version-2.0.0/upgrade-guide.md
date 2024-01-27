---
sidebar_position: 5
title: Upgrade Guide
---

## Upgrade Guide

 - In your composer.json file, update the `crestapps/laravel-code-generator` dependency to `2.2.*`.
 - Using the command-line, execute the following two commands to upgrade to the latest version of v2.2
     - `composer update`
     - `php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default --force`
 - If you will be using **Laravel-Collective**, execute the following commands update the default-collective template.
     - `php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default-collective --force`
 - Move any custom template "if any" from `resources/codegenerator-templates` to `resources/laravel-code-generator/templates`. **IMPORTANT** do not copy the default and default-collective folders.
 - Move all the file that are located in `resources/codegenerator-files` to `resources/laravel-code-generator/sources`. Now you should be able to delete the following two folders since they have been relocated `resources/codegenerator-templates` and `resources/codegenerator-files`.
 - Finally, there are some changes to the layout stub which are required. To override your existing layout call the following code`php artisan create:layout "My New App"`. If you are using your own layout, you may want to create a temporary layout and extract the updated css/js code into your own layout/assets. The following command will create a new file called "app\_temp.blade.php" `php artisan create:layout "My New App" --layout-filename=app_temp`
