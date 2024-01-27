---
sidebar_position: 6
title: Release Notes
---

## Release Notes

Version 2.2 introduces very exciting features, more flexibility and less work for you out of the box! It also, adds support for the new features that were introduced in Laravel 5.5. Follow is a list of all new features and changes that were introduced.

### New Futures

#### Smart Migrations Engine

> Whaaaat?!! Yup that's right, version 2.2 introduce a very powerful feature which keeps track of all your migrations. After migrating, each time, you add/delete a field/index from your resource file, the code-generator will only generate a migration to add/drop and drop/add columns as needed! Keep in mind that you still have to tell the generator that you need to create a new migration using `create:migration` command or the `--with-migration` option for the `create:resources` command.
> 
> Another migration related feature was to organizing your migration files! When uses migrations heavily, finding a specific migration may be overwhelming due to the number of file. This feature, allow you to group all your migrations into sub-folders. Please note that this feature is off by default, to turn it on, set organize\_migrations to true.
> 
> You're probably thinking "Laravel only detects migrations in the main folder... boooo!" That is correct! However, if you are using Laravel 5.3+, version 2.2 of Laravel-code-generator include five new commands to help you interact with migration from all folders. Check out the "Command Changes" below for more info about the new commands.

Previously Laravel-Code-Generator was limited to `belongsTo()` type relation. Now, when creating resources from existing database's table, the code-generator is able to create `hasOne()` and `hasMany()` relations by scanning the database's constrains and analyzing its existing data.
In the resource-file you can now define any [Eloquent relations](https://laravel.com/docs/5.5/eloquent-relationships). Each relation should follow the [foreign-relation](https://crestapps.com/%7B!!%20URL::route($routeName,%20['version'%20=%3E%20$version])%20!!%7D#foreign-relations) schema below. Additionally, you can define [composite/multi-columns](https://crestapps.com/%7B!!%20URL::route($routeName,%20['version'%20=%3E%20$version])%20!!%7D#composite-indexes) indexes! Each index should follow the [index schema](https://crestapps.com/%7B!!%20URL::route($routeName,%20['version'%20=%3E%20$version])%20!!%7D#composite-indexes) listed below.
> 
> When using Laravel 5.5, you can pass custom Validation Rule object directly in you resource file and the generator will add it to the validation rules! For more info [check out the validation option below](https://crestapps.com/%7B!!%20URL::route($routeName,%20['version'%20=%3E%20$version])%20!!%7D#field-validation)
> 
> Improved the file uploading process to allow you to delete uploaded file
> 
> `--indexes` and `--relations` have been added to the following commands `resource-file:create`, `resource-file:append`, or `resource-file:reduce` to allow you to interact with the resource-file freely.
> 
> The options `--fields`, `--indexes` and `--relations` for the `resource-file:create`, `resource-file:append`, or `resource-file:reduce` commands accept complex string to allow you to pass more values to add to the resource-file. For example, `--fields="name:colors;html-type:select;options:blue|yellow|green|red|white,name:second_field_name"`

#### More configurations so you can type less and do more!

> **plural\_names\_for** was added to the configuration file to allow you to set your own plural-form vs singular-form preference when naming controller, form-request, resource-file, language file, table-name and route group. If you like your controllers to be in a plural-form, you can simply change the default behavior from the configuration file!
> 
> **controller\_name\_postfix** was added to the configuration file to allow you to change the controller's postfix. If you don't like to post fix your controllers with the word Controller, you can set this to an empty string or any other value.
> 
> **form\_request\_name\_postfix** was added to the configuration file to allow you to change the form-request's postfix. If you don't like to post fix your form-request with the word FormRequest, you can set this to an empty string or any other value.
> 
> **irregular\_plurals** was added to the configuration file. The code-generator heavily uses Laravel helpers `str_plural()` and `str_singular()` to generate readable code to make your code spectacular. The problem is the both generate incorrect words for irregular plurals. If you are using a language other than English, you can define a word with each with its plural-form to help the generator keep your code readable.
> 
> **create\_move\_file\_method** was added to the configuration file. This option will allow the user to chose not to create moveFile method on every CRUD when file-upload is required. If you set this to false, it is your responsibility make sure that the moveFile method exists in a higher level of your code like `App\Http\Controllers\Controller`.
> 
> New configuration file (i.e `config/code_generator_custom.php`) was added to allow you to override the default configuration. This way, you won't lose any of your custom configuration when upgrading which is important! For more info, read the config file.

### Cleaner!

> In addition to storing fields in the JSON file, indexes and relations can be stored in the same file too! For that reason, the option \--fields-file have been renamed to \--resource-file in all the commands.
> 
> Version 2.2 completely dropped support for raw fields, indexes, and relations as announced in previous documents. Storing resources in JSON file is much better, easier to manage, easier to regenerate resources in the future, shorter/cleaner commands, and much more flexible!
> 
> Thanks to the request validation improvement in Laravel 5.5, the controller code is much cleaner.
> 
> When the `ConvertEmptyStringsToNull` middleware is registered, we no longer convert empty string to null manually since the middleware will do just that.
> 
> The `--without-migration` option with `php artisan create:resources` command has been reversed. It is now `--with-migration` and should only be passed when you need a new migration created.
> 
> For consistency, the \--lang-file-name option have been renamed to \--language-filename.
> 
> The options `--names` in the `resource-file:create`, `resource-file:append`, and `resource-file:reduce` has been renamed to `--fields`.

### Command Changes

> _The following commands were renamed_
> 
> The command `create:fields-file` has been renamed to `resource-file:from-database`
> 
> The command `fields-file:create` has been renamed to `resource-file:create`
> 
> The command `fields-file:delete` has been renamed to `resource-file:delete`
> 
> The command `fields-file:append` has been renamed to `resource-file:append`
> 
> The command `fields-file:reduce` has been renamed to `resource-file:reduce`
> 
> _The following commands were added_
> 
> `php artisan migrate-all` command was added. It allow you to run all of your outstanding migrations from all folders
> 
> `php artisan migrate:rollback-all` command was added and it allows you to rolls back the last "batch" of migrations, which may include multiple migration from all folders.
> 
> `php artisan migrate:reset-all` command was added to allow you to roll back all of your application's migrations from all folder.
> 
> `php artisan migrate:refresh-all` command was added to allow you to invoke the `migrate:rollback-all` command then immediately invokes the `migrate:migrate-all` command.
> 
> `php artisan migrate:status-all` command was added to allow you to checks the status of all your migration from all folders.

### Bug Free!

> All known bugs have been addressed!

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

* * *