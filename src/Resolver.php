<?php

namespace p810\Router;

class Resolver {
    function __invoke(callable $handler, array $arguments) {
        if (is_string($handler) && stripos($handler, '::') !== false) {
            [$class, $method] = explode('::', $handler);

            $handler = [new $class, $method];
        }

        return $callback(...$arguments);
    }

    public function setControllerNamespace(string $namespace): self {
        $this->namespace = $namespace;

        return $this;
    }
}