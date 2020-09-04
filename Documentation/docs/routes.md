This file is Extension Help Documentation for Routes Related Stuff

```
[root@26d35401d4bf md]# pwd Designer_Development_Conventions.md
/home/Kstych/Framework/application/resources/views/module/app/help/md
```
In this Documentation, we going to undestand the URL Scheme and Routing feature provided by laravel and lowcode framework.

# URL Routing

Let's Understand By a Simple URL Example. Assume We want to open `index.php` file available at `www/universityportal/view/index.php`</br>
<br>
So. In Browser URL we usually type `http://www.localhost.com/universityportal/view/index.php`


#### Catch in This Framework

```
http://www.localhost.com/{MODULE_ROUTE_NAME}?__{MODULE_PATH_ALIAS_NAME}={FILE_NAME}

```

Here, **MODULE_ROUTE_NAME** is the name of the Module.

 **MODULE_PATH_ALIAS_NAME** is the alias name that will refer to full path. Like __ctr__ refers to contrller folder and __view__ refers to view folder.

 **FILE_NAME** is the name of php file.

### Examples
When a module is created in the framework a resource route is automatically configured for it.
Example if a module is named "Blog", then we can use **Blog** as the MODULE_ROUTE_NAME.

Now.. if we want to access view files of **blog** Module. first, we have to create
a folder named as **blog** inside view folder. this is compulsory to keep folder name
in lowercase only.

Now, Whenever we will type `URL: http://localhost/blog?__view=index` framework first will
search for blog name folder inside view directory. then, it will generate `custom/ext/view/blog/index.blade.php`
path to access the index.blade.php file.

Let's take some more example to understand in more depth.

__Method One__

| Verb |  Path | Route Name | Override View |
|------|-------|------------|----------------|
| GET  | /**blog?__view=index** | **blog**.index | custom/ext/view/**blog**/index.blade.php |
| POST  | /**blog?__view=index** | **blog**.index | custom/ext/view/**blog**/index.blade.php |
| POST  | /**blog?__view=something** | **blog**.something | custom/ext/view/**blog**/something.blade.php |
| GET  |/**blog?__view=something** | **blog**.something | custom/ext/view/**blog**/something.blade.php |

URL : **http://localhost/blog?__view=index**

__Method Two__ ( Route Resource Method )

| Verb |  Path | Route Name | Override View |
|------|-------|------------|----------------|
| GET  | /**blog** | **blog**.index | custom/ext/view/**blog**/index.blade.php |
| GET  | /**blog**/{key} | **blog**.show | custom/ext/view/**blog**/show.blade.php |
| GET  | /**blog**/create | **blog**.create | custom/ext/view/**blog**/create.blade.php |
| POST | /**blog** | **blog**.store | custom/ext/view/**blog**/store.blade.php |
| GET  | /**blog**/{key}/edit | **blog**.edit | custom/ext/view/**blog**/edit.blade.php |
| POST | /**blog**/{key} | **blog**.update | custom/ext/view/**blog**/update.blade.php |
| POST | /**blog**/{key}/delete | **blog**.destroy | custom/ext/view/**blog**/destroy.blade.php |


### Assets
Like if We want to server assets or Apache Server supported file. we can use assets folder. Just move any folder inside `data/custom/ext/assets` and then we can browser using URL `localhost/custom/{HERE}`

As Example, Let's assume we have phpmyadmin folder inside assets folder.

Complete Path : __`data/custom/ext/assets/phpmyadmin/index.php`__
URL : `localhost/custom/phpmyadmin/index.php`

URL localhost/__custom__ is alias for `data/custom/ext/assets/`


### Controller Routing (Route Resource Method)

Although view overrides may be used directly for simpler cases, <br>
passing requests through controller classes is required in any non trivial application.<br>
controllers are the entry point for all custom business logic.<br>

In above example if you need to pass the request to a controller and not a view directly,<br>
you can pass "**__ctr**" parameter to any of the resource routes

| Verb |  Path | Route Name | Controller File @ Function |
|------|-------|------------|-----------------|
| GET  | /**blog**?__ctr=**Main** | **blog**.index | custom/ext/packages/**Blog**/Controller/**Main**.php @ index |
| GET  | /**blog**/{key}?__ctr=**Main** | **blog**.show | custom/ext/packages/**Blog**/Controller/**Main**.php @ show |
| GET  | /**blog**/create?__ctr=**Main** | **blog**.create | custom/ext/packages/**Blog**/Controller/**Main**.php @ create |
| POST | /**blog**?__ctr=**Main** | **blog**.store | custom/ext/packages/**Blog**/Controller/**Main**.php @ store |
| GET  | /**blog**/{key}/edit?__ctr=**Main** | **blog**.edit | custom/ext/packages/**Blog**/Controller/**Main**.php @ edit |
| POST | /**blog**/{key}?__ctr=**Main** | **blog**.update | custom/ext/packages/**Blog**/Controller/**Main**.php @ update |
| POST | /**blog**/{key}/delete?__ctr=**Main** | **blog**.destroy | custom/ext/packages/**Blog**/Controller/**Main**.php @ destroy |
<br>

**Namespace Convention**

Path "**custom/ext/packages**" is Namespaced as "**App\Custom**"<br>
So our **Main** controller above will have to be namespaced as "**App\Custom\Blog\Controller**"<br>


### Controller Example.
To Understand this, First we need to have knowledge of Laravel Route Resource functionality.
As We know, Controller classes used to perform logics before serving view response to client.
few universal methods that are usually used in controller classes, as used in Laravel `Route::resource` method

| Method | Description |
|--------|-------------|
| index  | Index Page|
| show   | Show Data |
| create | Create   |
| store  | Store    |
| edit   | Editing  |
| update | Updating |
| destroy| Deleting |


File Sample : custom/ext/packages/**Blog**/Controller/**Main**.php

```php
<?php

namespace App\Custom\Blog\Controller;

class Main
{
    public function __construct(){

    }


    public function index()
    {
        return view('custom.blog.index');
    }


    public function show($id)
    {
        return view('custom.blog.show',['id'=>$id]);
    }


    public function create()
    {
        return view('custom.blog.create');
    }


    public function store()
    {
        return view('custom.blog.store',['id'=>$id]);
    }


    public function edit($id)
    {
        return view('custom.blog.edit',['id'=>$id]);
    }


    public function update($id)
    {
        return view('custom.blog.update',['id'=>$id]);
    }


    public function destroy($id)
    {
        return view('custom.blog.destroy',['id'=>$id]);
    }
}

```


<br>
There is no limit on how many controllers you can have, <br>
by switching __ctr__ variable to a different controller, <br>
you can use same resource routes and pass through your choice of controller classes<br>



### Custom Classes <a name="customclasses"></a>

It is a good practice to keep controllers as light as possible and off-load custom logic to specialized classes<br>
as mentioned above the folder path "custom/ext/packages" is by convention namespaced to "App\Custom"<br>
so you are free to create as many classes within this namespace and use them in your controllers

for example a class "custom/ext/packages/**Blog**/**BlogFile**.php"<br>
```php
{!!'<'!!}?php namespace App\Custom\Blog;

class BlogFile
{
    //
}

```

### Models and ORM <a name="orm"></a>

Under the hood Eloquent ORM of Laravel is automatically instantiated for all Models created in the Designer<br>
You are not required to create Model Classes by yourself<br>
Once the Model is configured in the Designer UI you can directly use them as following<br>


```php
//Assuming Model name Post

$Post = kmodel('Post');                             // $Post is now Eloquent Model

$newpost = new $Post();
$newpost->name = "New Post";
$newpost->save();

$existingpost = $Post::where('name','=','New Post')->first();
$existingpost->delete();

$allposts = $Post::withTrashed()->get();            // All Eloquent fully available

$dbposts = DB::table('posts')->get();               // Using DB query builder

```



### Views <a name="views"></a>

Similarly all views are required by convention to be put under "custom/ext/views"<br>
This path is mapped to "resources/views/custom" path in Laravel setup<br><br>
Which means if you have a view file "custom/ext/views/**blog**/index.blade.php"<br>
You may use it like
```php
view('custom.blog.index');
Response::make('custom.blog.index');

{!!'@'!!}include('custom.blog.index');
```
<br>

Finally it is also possible to change the default landing page of your app<br>
By default if no overrides are defined then Login Page is shown<br>
To show your own UI you can create : "**custom/ext/views/app/index_guest.blade.php**"<br>
And for logged in Users : "**custom/ext/views/app/index.blade.php**"
