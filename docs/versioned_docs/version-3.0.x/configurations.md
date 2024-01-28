---
sidebar_position: 5
title: Configurations
---

## Configurations

Laravel-Code-Generator ships with lots of configurable option to give you control of the generated code. It is strongly recommended that you read the comments block above each option in the config/laravel-code-generator.php file to get familiar with all available options.

To override or extend the default settings to prevent you from losing your setting when upgrading the package. The `config/laravel-code-generator.php` is a dedicated file to store your options. This file will always be controlled by you and will never be overridden by the package. To override any configuration found in `config/laravel-code-generator.php`, simple add the same option in your custom file. The generator will look at the your configuration before falling back to the default config. Note, any array based option will be extended not overridden.

:::info
If you like to override default settings, you may execute the following command to publish a default configuration file to `/config/laravel-code-generator.php`

```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=config
```
:::

The most important option in the configuration file is `common_definitions`. This option allows you to set the default properties of new field using the name of that field. Your goal should be to generate 100% ready resource-file using this config. It will save you lots of time since all your fields will get generated using the desired properties. In another words, when using `resource-file:create`, `resource-file:append` or `resource-file:from-database` to create resource file, the generated JSON will be 100% ready for you without any manual modification.
