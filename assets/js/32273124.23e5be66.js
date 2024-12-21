"use strict";(self.webpackChunklaravel_code_generator=self.webpackChunklaravel_code_generator||[]).push([[6991],{1757:(e,o,r)=>{r.r(o),r.d(o,{assets:()=>a,contentTitle:()=>i,default:()=>h,frontMatter:()=>s,metadata:()=>l,toc:()=>d});var n=r(4848),t=r(8453);const s={sidebar_position:6,title:"Release Notes"},i=void 0,l={id:"release-notes",title:"Release Notes",description:"Release Notes",source:"@site/versioned_docs/version-2.0.0/release-notes.md",sourceDirName:".",slug:"/release-notes",permalink:"/docs/2.0.0/release-notes",draft:!1,unlisted:!1,editUrl:"https://github.com/CrestApps/laravel-code-generator/edit/master/docs/versioned_docs/version-2.0.0/release-notes.md",tags:[],version:"2.0.0",sidebarPosition:6,frontMatter:{sidebar_position:6,title:"Release Notes"},sidebar:"docsSidebar",previous:{title:"Upgrade Guide",permalink:"/docs/2.0.0/upgrade-guide"},next:{title:"Laravel Collective",permalink:"/docs/2.0.0/laravel-collective"}},a={},d=[{value:"Release Notes",id:"release-notes",level:2},{value:"New Futures",id:"new-futures",level:3},{value:"Smart Migrations Engine",id:"smart-migrations-engine",level:4},{value:"More configurations so you can type less and do more!",id:"more-configurations-so-you-can-type-less-and-do-more",level:4},{value:"Cleaner!",id:"cleaner",level:3},{value:"Command Changes",id:"command-changes",level:3},{value:"Bug Free!",id:"bug-free",level:3},{value:"Upgrade Guide",id:"upgrade-guide",level:2}];function c(e){const o={a:"a",blockquote:"blockquote",code:"code",em:"em",h2:"h2",h3:"h3",h4:"h4",hr:"hr",li:"li",p:"p",strong:"strong",ul:"ul",...(0,t.R)(),...e.components};return(0,n.jsxs)(n.Fragment,{children:[(0,n.jsx)(o.h2,{id:"release-notes",children:"Release Notes"}),"\n",(0,n.jsx)(o.p,{children:"Version 2.2 introduces very exciting features, more flexibility and less work for you out of the box! It also, adds support for the new features that were introduced in Laravel 5.5. Follow is a list of all new features and changes that were introduced."}),"\n",(0,n.jsx)(o.h3,{id:"new-futures",children:"New Futures"}),"\n",(0,n.jsx)(o.h4,{id:"smart-migrations-engine",children:"Smart Migrations Engine"}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsxs)(o.p,{children:["Whaaaat?!! Yup that's right, version 2.2 introduce a very powerful feature which keeps track of all your migrations. After migrating, each time, you add/delete a field/index from your resource file, the code-generator will only generate a migration to add/drop and drop/add columns as needed! Keep in mind that you still have to tell the generator that you need to create a new migration using ",(0,n.jsx)(o.code,{children:"create:migration"})," command or the ",(0,n.jsx)(o.code,{children:"--with-migration"})," option for the ",(0,n.jsx)(o.code,{children:"create:resources"})," command."]}),"\n",(0,n.jsx)(o.p,{children:"Another migration related feature was to organizing your migration files! When uses migrations heavily, finding a specific migration may be overwhelming due to the number of file. This feature, allow you to group all your migrations into sub-folders. Please note that this feature is off by default, to turn it on, set organize_migrations to true."}),"\n",(0,n.jsx)(o.p,{children:'You\'re probably thinking "Laravel only detects migrations in the main folder... boooo!" That is correct! However, if you are using Laravel 5.3+, version 2.2 of Laravel-code-generator include five new commands to help you interact with migration from all folders. Check out the "Command Changes" below for more info about the new commands.'}),"\n"]}),"\n",(0,n.jsxs)(o.p,{children:["Previously Laravel-Code-Generator was limited to ",(0,n.jsx)(o.code,{children:"belongsTo()"})," type relation. Now, when creating resources from existing database's table, the code-generator is able to create ",(0,n.jsx)(o.code,{children:"hasOne()"})," and ",(0,n.jsx)(o.code,{children:"hasMany()"})," relations by scanning the database's constrains and analyzing its existing data.\nIn the resource-file you can now define any ",(0,n.jsx)(o.a,{href:"https://laravel.com/docs/5.5/eloquent-relationships",children:"Eloquent relations"}),". Each relation should follow the ",(0,n.jsx)(o.a,{href:"https://crestapps.com/%7B!!%20URL::route($routeName,%20%5B'version'%20=%3E%20$version%5D)%20!!%7D#foreign-relations",children:"foreign-relation"})," schema below. Additionally, you can define ",(0,n.jsx)(o.a,{href:"https://crestapps.com/%7B!!%20URL::route($routeName,%20%5B'version'%20=%3E%20$version%5D)%20!!%7D#composite-indexes",children:"composite/multi-columns"})," indexes! Each index should follow the ",(0,n.jsx)(o.a,{href:"https://crestapps.com/%7B!!%20URL::route($routeName,%20%5B'version'%20=%3E%20$version%5D)%20!!%7D#composite-indexes",children:"index schema"})," listed below."]}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsxs)(o.p,{children:["When using Laravel 5.5, you can pass custom Validation Rule object directly in you resource file and the generator will add it to the validation rules! For more info ",(0,n.jsx)(o.a,{href:"https://crestapps.com/%7B!!%20URL::route($routeName,%20%5B'version'%20=%3E%20$version%5D)%20!!%7D#field-validation",children:"check out the validation option below"})]}),"\n",(0,n.jsx)(o.p,{children:"Improved the file uploading process to allow you to delete uploaded file"}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"--indexes"})," and ",(0,n.jsx)(o.code,{children:"--relations"})," have been added to the following commands ",(0,n.jsx)(o.code,{children:"resource-file:create"}),", ",(0,n.jsx)(o.code,{children:"resource-file:append"}),", or ",(0,n.jsx)(o.code,{children:"resource-file:reduce"})," to allow you to interact with the resource-file freely."]}),"\n",(0,n.jsxs)(o.p,{children:["The options ",(0,n.jsx)(o.code,{children:"--fields"}),", ",(0,n.jsx)(o.code,{children:"--indexes"})," and ",(0,n.jsx)(o.code,{children:"--relations"})," for the ",(0,n.jsx)(o.code,{children:"resource-file:create"}),", ",(0,n.jsx)(o.code,{children:"resource-file:append"}),", or ",(0,n.jsx)(o.code,{children:"resource-file:reduce"})," commands accept complex string to allow you to pass more values to add to the resource-file. For example, ",(0,n.jsx)(o.code,{children:'--fields="name:colors;html-type:select;options:blue|yellow|green|red|white,name:second_field_name"'})]}),"\n"]}),"\n",(0,n.jsx)(o.h4,{id:"more-configurations-so-you-can-type-less-and-do-more",children:"More configurations so you can type less and do more!"}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.strong,{children:"plural_names_for"})," was added to the configuration file to allow you to set your own plural-form vs singular-form preference when naming controller, form-request, resource-file, language file, table-name and route group. If you like your controllers to be in a plural-form, you can simply change the default behavior from the configuration file!"]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.strong,{children:"controller_name_postfix"})," was added to the configuration file to allow you to change the controller's postfix. If you don't like to post fix your controllers with the word Controller, you can set this to an empty string or any other value."]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.strong,{children:"form_request_name_postfix"})," was added to the configuration file to allow you to change the form-request's postfix. If you don't like to post fix your form-request with the word FormRequest, you can set this to an empty string or any other value."]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.strong,{children:"irregular_plurals"})," was added to the configuration file. The code-generator heavily uses Laravel helpers ",(0,n.jsx)(o.code,{children:"str_plural()"})," and ",(0,n.jsx)(o.code,{children:"str_singular()"})," to generate readable code to make your code spectacular. The problem is the both generate incorrect words for irregular plurals. If you are using a language other than English, you can define a word with each with its plural-form to help the generator keep your code readable."]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.strong,{children:"create_move_file_method"})," was added to the configuration file. This option will allow the user to chose not to create moveFile method on every CRUD when file-upload is required. If you set this to false, it is your responsibility make sure that the moveFile method exists in a higher level of your code like ",(0,n.jsx)(o.code,{children:"App\\Http\\Controllers\\Controller"}),"."]}),"\n",(0,n.jsxs)(o.p,{children:["New configuration file (i.e ",(0,n.jsx)(o.code,{children:"config/code_generator_custom.php"}),") was added to allow you to override the default configuration. This way, you won't lose any of your custom configuration when upgrading which is important! For more info, read the config file."]}),"\n"]}),"\n",(0,n.jsx)(o.h3,{id:"cleaner",children:"Cleaner!"}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsx)(o.p,{children:"In addition to storing fields in the JSON file, indexes and relations can be stored in the same file too! For that reason, the option --fields-file have been renamed to --resource-file in all the commands."}),"\n",(0,n.jsx)(o.p,{children:"Version 2.2 completely dropped support for raw fields, indexes, and relations as announced in previous documents. Storing resources in JSON file is much better, easier to manage, easier to regenerate resources in the future, shorter/cleaner commands, and much more flexible!"}),"\n",(0,n.jsx)(o.p,{children:"Thanks to the request validation improvement in Laravel 5.5, the controller code is much cleaner."}),"\n",(0,n.jsxs)(o.p,{children:["When the ",(0,n.jsx)(o.code,{children:"ConvertEmptyStringsToNull"})," middleware is registered, we no longer convert empty string to null manually since the middleware will do just that."]}),"\n",(0,n.jsxs)(o.p,{children:["The ",(0,n.jsx)(o.code,{children:"--without-migration"})," option with ",(0,n.jsx)(o.code,{children:"php artisan create:resources"})," command has been reversed. It is now ",(0,n.jsx)(o.code,{children:"--with-migration"})," and should only be passed when you need a new migration created."]}),"\n",(0,n.jsx)(o.p,{children:"For consistency, the --lang-file-name option have been renamed to --language-filename."}),"\n",(0,n.jsxs)(o.p,{children:["The options ",(0,n.jsx)(o.code,{children:"--names"})," in the ",(0,n.jsx)(o.code,{children:"resource-file:create"}),", ",(0,n.jsx)(o.code,{children:"resource-file:append"}),", and ",(0,n.jsx)(o.code,{children:"resource-file:reduce"})," has been renamed to ",(0,n.jsx)(o.code,{children:"--fields"}),"."]}),"\n"]}),"\n",(0,n.jsx)(o.h3,{id:"command-changes",children:"Command Changes"}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsx)(o.p,{children:(0,n.jsx)(o.em,{children:"The following commands were renamed"})}),"\n",(0,n.jsxs)(o.p,{children:["The command ",(0,n.jsx)(o.code,{children:"create:fields-file"})," has been renamed to ",(0,n.jsx)(o.code,{children:"resource-file:from-database"})]}),"\n",(0,n.jsxs)(o.p,{children:["The command ",(0,n.jsx)(o.code,{children:"fields-file:create"})," has been renamed to ",(0,n.jsx)(o.code,{children:"resource-file:create"})]}),"\n",(0,n.jsxs)(o.p,{children:["The command ",(0,n.jsx)(o.code,{children:"fields-file:delete"})," has been renamed to ",(0,n.jsx)(o.code,{children:"resource-file:delete"})]}),"\n",(0,n.jsxs)(o.p,{children:["The command ",(0,n.jsx)(o.code,{children:"fields-file:append"})," has been renamed to ",(0,n.jsx)(o.code,{children:"resource-file:append"})]}),"\n",(0,n.jsxs)(o.p,{children:["The command ",(0,n.jsx)(o.code,{children:"fields-file:reduce"})," has been renamed to ",(0,n.jsx)(o.code,{children:"resource-file:reduce"})]}),"\n",(0,n.jsx)(o.p,{children:(0,n.jsx)(o.em,{children:"The following commands were added"})}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"php artisan migrate-all"})," command was added. It allow you to run all of your outstanding migrations from all folders"]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"php artisan migrate:rollback-all"}),' command was added and it allows you to rolls back the last "batch" of migrations, which may include multiple migration from all folders.']}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"php artisan migrate:reset-all"})," command was added to allow you to roll back all of your application's migrations from all folder."]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"php artisan migrate:refresh-all"})," command was added to allow you to invoke the ",(0,n.jsx)(o.code,{children:"migrate:rollback-all"})," command then immediately invokes the ",(0,n.jsx)(o.code,{children:"migrate:migrate-all"})," command."]}),"\n",(0,n.jsxs)(o.p,{children:[(0,n.jsx)(o.code,{children:"php artisan migrate:status-all"})," command was added to allow you to checks the status of all your migration from all folders."]}),"\n"]}),"\n",(0,n.jsx)(o.h3,{id:"bug-free",children:"Bug Free!"}),"\n",(0,n.jsxs)(o.blockquote,{children:["\n",(0,n.jsx)(o.p,{children:"All known bugs have been addressed!"}),"\n"]}),"\n",(0,n.jsx)(o.h2,{id:"upgrade-guide",children:"Upgrade Guide"}),"\n",(0,n.jsxs)(o.ul,{children:["\n",(0,n.jsxs)(o.li,{children:["In your composer.json file, update the ",(0,n.jsx)(o.code,{children:"crestapps/laravel-code-generator"})," dependency to ",(0,n.jsx)(o.code,{children:"2.2.*"}),"."]}),"\n",(0,n.jsxs)(o.li,{children:["Using the command-line, execute the following two commands to upgrade to the latest version of v2.2","\n",(0,n.jsxs)(o.ul,{children:["\n",(0,n.jsx)(o.li,{children:(0,n.jsx)(o.code,{children:"composer update"})}),"\n",(0,n.jsx)(o.li,{children:(0,n.jsx)(o.code,{children:'php artisan vendor:publish --provider="CrestApps\\CodeGenerator\\CodeGeneratorServiceProvider" --tag=default --force'})}),"\n"]}),"\n"]}),"\n",(0,n.jsxs)(o.li,{children:["If you will be using ",(0,n.jsx)(o.strong,{children:"Laravel-Collective"}),", execute the following commands update the default-collective template.","\n",(0,n.jsxs)(o.ul,{children:["\n",(0,n.jsx)(o.li,{children:(0,n.jsx)(o.code,{children:'php artisan vendor:publish --provider="CrestApps\\CodeGenerator\\CodeGeneratorServiceProvider" --tag=default-collective --force'})}),"\n"]}),"\n"]}),"\n",(0,n.jsxs)(o.li,{children:['Move any custom template "if any" from ',(0,n.jsx)(o.code,{children:"resources/codegenerator-templates"})," to ",(0,n.jsx)(o.code,{children:"resources/laravel-code-generator/templates"}),". ",(0,n.jsx)(o.strong,{children:"IMPORTANT"})," do not copy the default and default-collective folders."]}),"\n",(0,n.jsxs)(o.li,{children:["Move all the file that are located in ",(0,n.jsx)(o.code,{children:"resources/codegenerator-files"})," to ",(0,n.jsx)(o.code,{children:"resources/laravel-code-generator/sources"}),". Now you should be able to delete the following two folders since they have been relocated ",(0,n.jsx)(o.code,{children:"resources/codegenerator-templates"})," and ",(0,n.jsx)(o.code,{children:"resources/codegenerator-files"}),"."]}),"\n",(0,n.jsxs)(o.li,{children:["Finally, there are some changes to the layout stub which are required. To override your existing layout call the following code",(0,n.jsx)(o.code,{children:'php artisan create:layout "My New App"'}),'. If you are using your own layout, you may want to create a temporary layout and extract the updated css/js code into your own layout/assets. The following command will create a new file called "app_temp.blade.php" ',(0,n.jsx)(o.code,{children:'php artisan create:layout "My New App" --layout-filename=app_temp'})]}),"\n"]}),"\n",(0,n.jsx)(o.hr,{})]})}function h(e={}){const{wrapper:o}={...(0,t.R)(),...e.components};return o?(0,n.jsx)(o,{...e,children:(0,n.jsx)(c,{...e})}):c(e)}},8453:(e,o,r)=>{r.d(o,{R:()=>i,x:()=>l});var n=r(6540);const t={},s=n.createContext(t);function i(e){const o=n.useContext(s);return n.useMemo((function(){return"function"==typeof e?e(o):{...o,...e}}),[o,e])}function l(e){let o;return o=e.disableParentContext?"function"==typeof e.components?e.components(t):e.components||t:i(e.components),n.createElement(s.Provider,{value:o},e.children)}}}]);