
### Options Changes

 - The **--fields-file** option in all command have been renamed to **--resource-file** since that file is no longer just a fields-file. 
 - This completely drop support for fields from raw string. We relay heavy on the JSON-based resource-file. The resource-file allows you to define any relations, indexes and fields all at once.


## New Features
 - The CodeGenerator is now able to automatically create `hasOne` and `hasMany` relations when creating resource-file from existing database using `php artisan resource-file:from-database` command
 - The CodeGenerator allows you to add compound index in the resource-file directly to give you the ability to reuse the setting in the future.
 - The CodeGenerator allows you to add any type of relation in the resource-file directly to give you the ability to reuse the setting in the future.
 - New configuration option was added (i.e. `plural_definitions`) to allow you to define a non-english singular to plural words. This is helpfull if your coding using a non-english language like Spanish or Frensh. For more info go to https://github.com/CrestApps/laravel-code-generator/pull/25

## Command Changes
The following command have been renamed

 - The command create:fields-file has been renamed to resource-file:from-database
 - The command fields-file:create has been renamed to resource-file:create
 - The command fields-file:delete has been renamed to resource-file:delete
 - The command fields-file:append has been renamed to resource-file:append
 - The command fields-file:reduce has been renamed to resource-file:reduce


## Eliminated Options

The following options have been removed from all commands
 - --fields
 - --fillable
 - --relationships
 - --indexes
 - --foreign-keys

