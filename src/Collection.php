<?php

namespace p810\Router;

class Collection {
    /**
     * A multi-dimensional array of routes mapped to an array of
     * HTTP verbs to controllers.
     *
     * @var string[][]
     */
    protected $routes = [];

    /**
     * The default namespace within which to look for handlers.
     *
     * @var array
     */
    protected $namespace = '';

    function __construct() {
        $this->translator = new Translator;
    }

    public function register(string $route, string $method, callable $handler): self {
        $route = trim($route, '/');

        if (empty($method) || ! in_array($method, self::VERBS)) {
            throw new \InvalidArgumentException('Invalid HTTP method passed to Controller::register()');
        }

        /* Reverts 2294401 which removed this block accidentally.
         * If $route is empty after trim(), it's the index. */
        if (empty($route)) {
            $this->routes['/'][$method] = ['/^(\/)+$/m', $handler];

            return $this;
        }

        $expression = $this->translator->translate($route);

        if (!isset($this->routes[$route])) {
            $this->routes[$route] = [
                'expression' => $expression
            ];
        }

        $this->routes[$route][$method] = [$expression, $handler];

        return $this;
    }

    public function get(string $route, callable $handler): self {
        return $this->register($route, 'get', $handler);
    }

    public function post(string $route, callable $handler): self {
        return $this->register($route, 'post', $handler);
    }

    public function all(string $route, callable $handler): self {
        return $this->register($route, 'all', $handler);
    }

    public function match(string $route) {
        if ($route !== '/') {
            $route = trim($route, '/');
        }

        foreach ($this->routes as $definition => $list) {
            [$expression, $callback] = $list;

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