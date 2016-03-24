<?php

namespace p810\Router;

class Collection
{
    /**
     * A list of routes and their expressions/callbacks.
     *
     * @access protected
     * @var array
     */
    protected $routes = [];


    /**
     * The default namespace within which to look for controllers that are registered as handlers.
     *
     * @access protected
     * @var array
     */
    protected $namespace = '';


    /**
     * Resolves an instance of p810\Amethyst\Router\Translator.
     *
     * @return void
     */
    function __construct()
    {
        $this->translator = new Translator;
    }


    /**
     * Translates a route and registers it to Collection::$routes with its expression and handler.
     *
     * @param string $route The route to translate and register.
     * @param mixed $handler A Callable value or a string with a class name and method, in the form Class::method.
     * @return void
     */
    public function register($route, $handler)
    {
        $route = trim($route, '/');

        if (empty($route)) {
            $this->routes['/'] = ['/^(\/)+$/m', $handler];

            return;
        }

        $expression = $this->translator->translate($route);

        $this->routes[$route] = [$expression, $handler];
    }


    /**
     * Searches for a matching route and calls its handler if one is found.
     *
     * @param string $route The route to search for in Collection::$routes.
     * @return mixed
     */
    public function match($route)
    {
        if ($route !== '/') {
            $route = trim($route, '/');
        }

        foreach ($this->routes as $definition => $list) {
            list($expression, $callback) = $list;

            if (preg_match($expression, $route, $matches) == false) {
                continue;
            }

            array_shift($matches);

            $tokens = explode('/', $definition);
            
            $arguments = [];

            foreach ($tokens as $key => $token) {
                if (stripos($token, '{') !== false) {
                    if ($key > count($matches) - 1) {
                        $arguments[] = null;
                    } else {
                        $arguments[] = trim($matches[$key], '/');
                    }
                }
            }

            if (is_string($callback) && stripos($callback, '::') !== false) {
                $callback = explode('::', $callback);

                list($class, $method) = $callback;

                if (!empty($this->namespace)) {
                    $class = $this->namespace . $class;
                }

                $callback = [new $class, $method];
            }

            return call_user_func_array($callback, $arguments);
        }

        throw new UnmatchedRouteException;
    }


    /**
     * Overrides the value for Collection::$namespace.
     *
     * @param string $namespace The namespace to set the property value to.
     * @return void
     */
    public function setControllerNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
}