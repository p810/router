# Router

> Respond to any requested URI in your application through a single point of entry.

## Installation

```
composer require p810/router
```

## Usage

A route is any URI that you wish for your application to respond to. Routes must be contained within an instance of `RouteCollection`. The route may be checked against `RouteCollection::match()`, which will either throw an exception (`UnmatchedRouteException`) or call the specified callback.

Note that in order to use this, you must configure your Web server to direct requests to the script where this code is placed (e.g., `.htaccess` for Apache).

### Loading controllers

You may set a default namespace from which to load classes by calling `Collection::setControllerNamespace()`. Just tell it which namespace is the base of your controllers.

```php
$router->setControllerNamespace('MyApp\\Controllers\\');
```

### Dynamic routes

You may pass arguments into your route by using one of the following tokens.

| Token  |        Match        |
| -----  | ------------------- |
| {int}  | Any integer (0-9).  |
| {word} | A word (a-zA-Z 0-9).|

You may specify that a token is optional by prefixing the closing brace with a question mark, like so: `{token?}`

### Example

```php
<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

/**
 * Create a collection and register a route to it.
 */

use p810\Router\UnmatchedRouteException;
use p810\Router\Collection as RouteCollection;

$router = new RouteCollection;

$router->register('/', function () {
    return 'Hello world!'; 
});

$router->register('/{word}', function ($name) {
    return sprintf('Hello %s, how are you today?', $name); 
});


/**
 * Attempt to match the route based on the current URI.
 *
 * The returned result will then be output.
 */

try {
    $result = $router->match( $_SERVER['REQUEST_URI'] );
} catch (UnmatchedRouteException $e) {
    http_response_code(404);

    $result = 'The requested resource could not be found!';
}

print $result;
```