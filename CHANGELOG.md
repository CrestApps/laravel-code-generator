
### Options Changes

 - The **--fields-file** option in all command have been renamed to **--resource-file** since that file is no longer just a fields-file. 
 - This completely drop support for fields from raw string. We relay heavy on the JSON-based resource-file. The resource-file allows you to define any relations, indexes and fields all at once.
 - The **--lang-file-name** options have been changed to **--language-filename** any where it exists
 - The **--without-migration** option with **create:resources** command has been reversed. It is now **--with-migration** and should only be passed when you need a new migration created.
 - The options `--names` in the `resource-file:create`, `resource-file:append`, or `resource-file:reduce` has been renamed to `--fields`.
 - `--indexes` and `--relations` have been added to the following commands `resource-file:create`, `resource-file:append`, or `resource-file:reduce` to allow you to interact with the resource-file freely.
 -- The options `--fields`, `--indexes` and `--relations` for the `resource-file:create`, `resource-file:append`, or `resource-file:reduce` commands accept complex string to allow you to pass more values to add to the resource-file.


## New Features
 - The CodeGenerator is now able to automatically create `hasOne` and `hasMany` relations when creating resource-file from existing database using `php artisan resource-file:from-database` command
 - A new file `codegenerator_custom.php` to allow you to store all of your custom configuration was added.
 - Added the ability to delete already uploaded file in edit mode.
 - The CodeGenerator allows you to add compound index in the resource-file directly to give you the ability to reuse the setting in the future.
 - The CodeGenerator allows you to add any type of relation in the resource-file directly to give you the ability to reuse the setting in the future.
 - New configuration option was added (i.e. `irregular_plurals`) to allow you to define a non-english singular to plural words. This is helpfull if your coding using a non-english language like Spanish or Frensh. For more info go to https://github.com/CrestApps/laravel-code-generator/pull/25
 - Added `plural_names_for` config option to allow the user to set whether to create the resource in a plural or singular version.
 - Added `controller_name_postfix` config option to allow the user to change the controller post-fix or even remove it altogether.
 - Added `form_request_name_postfix` config option to allow the user to change the form-request post-fix or even remove it altogether.
 - You can use Laravel 5.5 custom validation rule directly in the validations string.
 - Added `create_move_file_method` config option to allow the user to chose not to create moveFile method on every CRUD when file-upload is required.



## Template Changes
 - A new stub was added controller-getdata-method-5.5.stub to allow you to simplify your validation when using Laravel 5.5! This will make your code much cleaner and simpler thanks to the new Laravel 5.5 validation.
 - The stub `controller-getdata-method.stub` has a slight change to increase the security of the generate code. instead of using `$request->all()` to get all the data from the request, we do `$request->only([...])` to only get the fields that should be needed only!
 - The variable `[% use_auth_namespace %]` in the `form-request.stub` file has been renamed to `[% use_command_placeholder %]`.
 - New template file have been added `controller-getdata-method-5.5.stub`
- New template file have been added `controller-upload-method-5.3.stub` to improve the syntax in the moveFile() method for Laravel 5.3+.
- The content of the `form-file-field.blade.stub` were changed to show the uploaded filename.
- The content of the `layout.stub` and `layout-with-validation.stub` were updated to show the file name for uploaded files.


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

