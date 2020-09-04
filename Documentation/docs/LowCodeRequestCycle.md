


# MIDDLEWARE

```
/**
 * The application's route middleware groups.
 *
 * @var array
 */
protected $middlewareGroups = [
  'web' => [

      \App\Http\Middleware\BeforeFilter::class, // kstych

      ... Laravel Builtin ...

      \App\Http\Middleware\AfterFilter::class, // kstych

  ],

  'api' => [
      ... Laravel Builtin ...

      \App\Http\Middleware\BeforeFilter::class, // kstych
      \App\Http\Middleware\AfterFilter::class, // kstych
  ],
];

/**
* The application's route middleware.
*
* These middleware may be assigned to groups or used individually.
*
* @var array
*/
protected $routeMiddleware = [

... Laravel Builtin ...

  'module_access' => \App\Http\Middleware\ModuleAccess::class,  // kstych
  'kstychauth' => \App\Http\Middleware\KstychAuth::class, //  kstych
];
```

### Routing

```
/* Index */
Route::get('/',
    array('uses'=> 'AppController@index', 'as'=>'app.root')
    );


Route::get('favicon.ico',
    array('uses' => 'AppController@favicon', 'as'=>'favicon.index'));



// app feature
Route::resource('app','AppController');

// for User Management
Route::resource('auth','AuthController');



// modules
foreach(Config::get('kstych.modules',[]) as $module=>$modulearr)
{
  if(file_exists(
    app_path('Http/Controllers/'.$module.'Controller.php')))
  {
    Route::resource(strtolower($module),$module.'Controller');
  }
  else{
    Route::resource(strtolower($module),'KstychController');
    }
}

// asset handler
Route::get('app/asset/{path}' ,
array('uses'=>'AppController@asset','as'=>'asset.get'))->where('path', '.*');

```

## What is `Config::get('kstych.modules',[])`
```
[
    "UniversityPortal" => [
      "disp" => "University Portal Management Application",
      "icon" => "",
      "dash" => "YES",
      "hash" => "UniversityPortal__UniversityPortal",
      "indexsidebar" => "Yes",
      "user" => "Auth",
      "gates" => [],
      "submenu" => [],
      "models" => [
        "document" => [
          "Document",
          "Document",
          "",
        ],
        "notice" => [
          "Notice",
          "Notice",
          "",
        ],
      ],
    ],
    "App" => [
      "disp" => "App",
      "icon" => "icon-display4",
      "dash" => "",
      "hash" => "App__App",
      "indexsidebar" => "",
      "user" => "Guest",
      "submenu" => [],
      "models" => [],
    ],
    "Auth" => [
      "disp" => "Auth",
      "icon" => "icon-gear",
      "dash" => "",
      "hash" => "Auth__Auth",
      "indexsidebar" => "",
      "user" => "Guest",
      "submenu" => [],
      "models" => [],
    ],
    "Admin" => [
      "disp" => "Admin",
      "icon" => "icon-gear",
      "dash" => "",
      "hash" => "Admin__Admin",
      "indexsidebar" => "",
      "user" => "Auth",
      "submenu" => [],
      "models" => [
        "user" => [
          "User",
          "User",
          "icon-user",
        ],
        "profile" => [
          "Profile",
          "Profile",
          "icon-profile",
        ],
        "subscription" => [
          "Subscription",
          "Subscription",
          "icon-coins",
        ],
        "usersubscription" => [
          "UserSubscription",
          "UserSubscription",
          "icon-coins",
        ],
        "sequence" => [
          "Sequence",
          "Sequence",
          "icon-list-numbered",
        ],
        "ksetting" => [
          "KSetting",
          "KSetting",
          "icon-clipboard3",
        ],
        "lang" => [
          "Lang",
          "Lang",
          "icon-text-color",
        ],
        "userrole" => [
          "UserRole",
          "UserRole",
          "icon-user-check",
        ],
        "role" => [
          "Role",
          "Role",
          "icon-user-check",
        ],
        "rolegroup" => [
          "RoleGroup",
          "RoleGroup",
          "icon-users4",
        ],
        "rolemodule" => [
          "RoleModule",
          "RoleModule",
          "icon-gear",
        ],
        "group" => [
          "Group",
          "Group",
          "icon-users4",
        ],
        "acl" => [
          "ACL",
          "ACL",
          "icon-shield-check",
        ],
        "maillog" => [
          "MailLog",
          "MailLog",
          "icon-mail-read",
        ],
        "formlog" => [
          "FormLog",
          "FormLog",
          "icon-file-text",
        ],
        "filelog" => [
          "FileLog",
          "FileLog",
          "icon-files-empty",
        ],
        "calllog" => [
          "CallLog",
          "CallLog",
          "icon-phone2",
        ],
        "userlog" => [
          "UserLog",
          "UserLog",
          "icon-height",
        ],
        "userform" => [
          "UserForm",
          "UserForm",
          "icon-file-text",
        ],
        "workflowlog" => [
          "WorkflowLog",
          "WorkflowLog",
          "icon-tree6",
        ],
        "workflowsteplog" => [
          "WorkflowStepLog",
          "WorkflowStepLog",
          "icon-puzzle",
        ],
      ],
    ],
    "Designer" => [
      "disp" => "Designer",
      "icon" => "icon-magic-wand2",
      "dash" => "",
      "hash" => "Designer__Designer",
      "indexsidebar" => "",
      "user" => "Auth",
      "submenu" => [],
      "models" => [
        "workflowdef" => [
          "WorkflowDef",
          "WorkflowDef",
          "icon-tree6",
        ],
      ],
    ],
    "User" => [
      "disp" => "User",
      "icon" => "icon-user",
      "dash" => "",
      "hash" => "User__User",
      "indexsidebar" => "",
      "user" => "Auth",
      "gates" => [
        "WorkflowAdmin",
        "WfTaskDisOwn",
      ],
      "submenu" => [],
      "models" => [
        "maillog" => [
          "MailLog",
          "MailLog",
          "icon-mail-read",
        ],
      ],
    ],
  ]

```
