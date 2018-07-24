<?php

namespace p810\Router;

class Translator
{
    /**
     * A map of tokens that may be used in routes and their RegEx counterparts.
     *
     * @access protected
     * @var array
     */
    protected $tokens = [
        '{int}'  => '\d',
        '{word}' => '\w+'
    ];

    /**
     * Generates a regular expression based on the string provided to this method.
     *
     * @param string $route The string to form the regular expression from.
     * @throws UnexpectedValueException if a token is found that isn't registered in Translator::$tokens.
     * @return string
     */
    public function translate(string $route): string {
        $tokens = explode('/', $route);

        $expression = [];
        foreach ($tokens as $key => $token) {
            $quantifier = '+';
            $segment    = '';

            if ($this->isOptional($token)) {
                end($tokens);

                if ($key !== key($tokens)) {
                    throw new \Exception('Only the last value in a route may be optional');
                }

                $quantifier = '?';
                $segment    = '(\/';

                $token = str_replace('?', '', $token);
            } else {
                if ($key === 0) {
                    $segment = '(';
                } else {
                    $segment = '\/(';
                }
            }

            $special = stripos($token, '{');

            if ($special !== false) {
                if (!array_key_exists($token, $this->tokens)) {
                    throw new \UnexpectedValueException('Unknown special token');
                }

                $segment .= $this->tokens[$token];
            } else {
                $segment .= $token;
            }

            $expression[] = $segment . ')' . $quantifier;
        }

        $expression = implode('', $expression);
        $expression = '/^' . $expression . '$/';

        return $expression;
    }

    protected function isOptional(string $token): bool {
        $position = stripos($token, '?');

        if ($position === false) {
            return false;
        } elseif (is_numeric($position)) {
            return true;
        }
    }

    public function getArguments(string $route, array $matches): array {
        array_shift($matches);

        $tokens = explode('/', $route);
        
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
    }
}