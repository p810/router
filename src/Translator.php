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
    public function translate($route)
    {
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

    
    /**
     * Determines if a special token is optional.
     *
     * @param string $token A special token coming from a route.
     * @return boolean
     */
    protected function isOptional($token)
    {
        $position = stripos($token, '?');

        if ($position === false) {
            return false;
        } elseif (is_numeric($position)) {
            return true;
        }
    }
}