<?php

namespace p810\Router;

class Collection {
    /**
     * A list of routes and their expressions/callbacks.
     *
     * @access protected
     * @var array
     */
    protected $routes = [];

    /**
     * The default namespace within which to look for handlers.
     *
     * @access protected
     * @var array
     */
    protected $namespace = '';

    function __construct() {
        $this->translator = new Translator;
    }

    public function register(string $route, callable $handler): self {
        $route = trim($route, '/');

        if (empty($route)) {
            throw new \InvalidArgumentException('Collection::register() is missing a route');
        }

        $expression = $this->translator->translate($route);

        $this->routes[$route] = [$expression, $handler];

        return $this;
    }

    public function match(string $route) {
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

                [$class, $method] = $callback;

                if (!empty($this->namespace)) {
                    $class = $this->namespace . $class;
                }

                $callback = [new $class, $method];
            }

            return $callback(...$arguments);
        }

        throw new Exception\UnmatchedRouteException;
    }

    public function setControllerNamespace(string $namespace): self {
        $this->namespace = $namespace;

        return $this;
    }
}