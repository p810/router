<?php

namespace p810\Router;

trait ShorthandRegistrationMethods {
    public function get(string $route, callable $handler): self {
        return $this->register($route, 'get', $handler);
    }

    public function post(string $route, callable $handler): self {
        return $this->register($route, 'post', $handler);
    }

    public function all(string $route, callable $handler): self {
        return $this->register($route, '*', $handler);
    }
}