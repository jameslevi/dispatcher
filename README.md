# Dispatcher

![](https://img.shields.io/badge/packagist-v1.0.0-informational?style=flat&logo=<LOGO_NAME>&logoColor=white&color=2bbc8a) ![](https://img.shields.io/badge/license-MIT-informational?style=flat&logo=<LOGO_NAME>&logoColor=white&color=2bbc8a)  

Is a simple routing engine for PHP applications.

## Features
1. Supports GET, POST, PUT, PATCH, DELETE and HEAD method.
2. Enables you to inject event-based callbacks.
3. Before and after middleware support.
4. Lightweight and easy to use.

## Installation
1. You can install via composer.
```
composer require jameslevi/dispatcher
```
2. Add the composer autoload mechanism if not using any framework.
```php
require_once __DIR__.'/vendor/autoload.php';
```

## Basic Implementation
```php
<?php

// Make a new router instance.
$router = new Graphite\Component\Dispatcher\Dispatcher();

// Execute callback each time error occurs.
$router->onError(function($request) {

});

// Register middleware #1.
$router->middleware(function($request) {

});

// Register middleware #2.
$router->middleware(function($request) {

});

// Register route #1.
$router->get("/", function($request) {

    return "Home";
});

// Register route #2.
$router->post("/register", function($request) {

    return "Register";
});

// Register after middleware #1.
$router->afterMiddleware(function($request) {

});

// Execute callback each time request ends.
$router->onDestroy(function($request) {

});

// Run your application.
$router->run();
```

## Request Methods
**GET** - Retrieve resource representation/information.
```php
$router->get("/members", function($request) {});
```
**POST** - Create new resource.
```php
$router->post("/members", function($request) {});
```
**PUT** - Update existing resource.
```php
$router->put("/members/{id}", function($request) {});
```
**PATCH** - Make a partial update on a resource.
```php
$router->patch("/members/{id}", function($request) {});
```
**DELETE** - Delete a resource.
```php
$router->delete("/members/{id}", function($request) {});
```
**HEAD** - Request method identical to GET method but has no body.

## Multiple Method Support
**Any** - Route that can use any request method.
```php
$router->any("/dashboard", function($request) {});
```
**Match** - Multiple request method support.
```php
$router->match(array("put", "patch"), "/member/{id}", function($request) {});
```

## Event Callbacks
**Create** - Triggered at the beginning of the request.
```php
$router->onCreate(function($request) {});
```
**Before Middleware** - Triggered before the middleware handler iteration.
```php
$router->onBeforeMiddleware(function($request) {});
```
**Middleware** - Triggered each time a middleware is called.
```php
$router->onMiddleware(function($request) {});
```
**Middleware Abort** - Triggered each time abort is called inside a middleware.
```php
$router->onMiddlewareAbort(function($request) {});
```
**Before Action** - Triggered before doing the route action.
```php
$router->onBeforeAction(function($request) {});
```
**After Action** - Triggered after doing the route action.
```php
$router->onAfterAction(function($request) {});
```
**Body Sent** - Triggered when the response body was sent.
```php
$router->onBodySent(function($request) {});
```
**Destroy** - Triggered at the very end of the request.
```php
$router->onDestroy(function($request) {});
```
**Error** - Triggered each time error occurs.
```php
$router->onError(function($request) {});
```
**Route Matched** - Triggered if request route matches.
```php
$router->onRouteMatched(function($request) {});
```
## Error Handling
You can set default error callback for all errors.
```php
$router->setDefaultErrorCallback(function($request) {

    return $request->responseMessage();
});
```
You can also set callback for each error code.
```php
$router->setErrorCallback(404, function() {
  
    return "Page Not Found";
});
```
## Before Middlewares
You can implement multiple middlewares before doing the request action.
```php
// Register your middleware #1.
$router->middleware(function($request) {

});

// Register your middleware #2.
$router->middleware(function($request) {

});
```
You can also implement middleware callbacks using classes.
```php
<?php

namespace App\Middleware;

class TestMiddleware {

    public function handle($request) {
    
    }
    
}
```
You can now use this class as your middleware.
```php
$router->middleware('App\Middleware\TestMiddleware@handle');
```
## After Middlewares
You can also implement multiple middlewares after doing the route action.
```php
$router->afterMiddleware('App\Middleware\TestMiddleware@handle');
```
## Actions
This are things you wish to accomplish in each request.
```php
$router->get("/", function($request) {

    return "Hello World";
});
```
## Controllers
Instead of defining all actions in a closure, you may wish to organize everything inside a controller class.
```php
<?php

namespace App\Controller;

class MyController {

    public function home($request) {
    
        return "Home";
    }
    
    public function aboutUs($request) {
    
        return "About Us";
    }
    
}
```
```php
$router->get("/home", "App\Controller\MyController@home");
$router->get("/about-us", "App\Controller\MyController@aboutUs");
```
## GET Parameters
You can access all the GET parameters using the get method from the request object.
```php
$router->get("/members", function($request) {

    return $request->get("per_page");
});
```
## POST, PUT, PATCH, DELETE Parameters
You can use the "post" method from the request object to retrieve POST parameters.
```php
$router->post("/members/{id}", function($request) {

    return $request->post("token");
});
```
## URI Embeded Parameters
You can also use segment of the request URI as a parameter.
```php
$router->get("/members/{member_id}", function($request) {

    return $request->member_id;
});
```
## Forced Abort
You can immediately terminate the request using "abort" method.
```php
$router->abort(503); // Return "Service Unavailable" response.
```
## Headers
You can assign headers using the "setHeader" method.
```php
$router->setHeader("Content-Type", "application/json");
```
## Service Availability
You can down the service availability using the "down" method.
```php
$router->down();
```
You can also down individual routes.
```php
$router->get('/dashboard', 'App\Controller\MyController@dashboard')->down();
```
## Request Helpers
**Method** - Return the request method used during the request.
```php
$request->method();
```
**URI** - Return the current request uri excluding the query string.
```php
$request->uri();
```
**Port** - Return the server port number.
```php
$request->port();
```
**Protocol** - Return the server protocol.
```php
$request->protocol();
```
**Secure** - Determine if request is through secure connection.
```php
$request->secure();
```
**Localhost** - Determine if request is in localhost.
```php
$request->localhost();
```
**XMLHttpRequest** - Determine if request is using XMLHttpRequest.
```php
$request->xmlHttpRequest();
```

## Contribution
For issues, concerns and suggestions, you can email James Crisostomo via nerdlabenterprise@gmail.com.

## License
This package is an open-sourced software licensed under [MIT](https://opensource.org/licenses/MIT) License.
