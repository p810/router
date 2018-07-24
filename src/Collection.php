<?php

namespace p810\Router;

class Collection {
    use ShorthandRegistrationMethods;

    /**
     * A multi-dimensional array of routes mapped to an array of
     * HTTP verbs to controllers.
     *
     * @var string[][]
     */
    protected $routes = [];

    function __construct() {
        $this->parser   = new Translator;
        $this->resolver = new Resolver;
    }

    public function register(string $route, string $method, $handler): self {
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

        $expression = $this->parser->translate($route);

        if (!isset($this->routes[$route])) {
            $this->routes[$route] = [
                'expression' => $expression
            ];
        }

        $this->routes[$route][$method] = [$expression, $handler];

        return $this;
    }

    public function match(string $route, string $method) {
        if ($route !== '/') {
            $route = trim($route, '/');
        }

        foreach ($this->routes as $route => $data) {
            if (preg_match($data['expression'], $route, $matches) === false) {
                continue;
            }

            if (! array_key_exists($method, $data)) {
                if (! array_key_exists('*', $data)) {
                    throw new Exception\BadRequestException('No handler is defined for this HTTP method');
                } else {
                    $method = '*';
                }
            }

            $args     = $this->parser->getArguments($route, $matches);
            $callback = $this->routes[$route][$method];

            return $this->resolver($callback, $args);
        }

        throw new Exception\UnmatchedRouteException;
    }

    public function setControllerNamespace(string $namespace): self {
        $this->resolver->setControllerNamespace($namespace);

        return $this;
    }
}