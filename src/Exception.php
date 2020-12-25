<?php

namespace ep;

class Exception extends \Exception
{
    const NOT_FOUND_CTRL = 40401;
    const NOT_FOUND_ACTION = 40402;
    const NOT_FOUND_LAYOUT = 40403;
    const NOT_FOUND_DATA = 40404;

    const ERROR = 50000;

    public function __construct($code, $message = null)
    {
        if (is_int($code)) {
            parent::__construct($message ?: $this->mapper()[$code], $code);
        } else {
            parent::__construct($code, self::ERROR);
        }
    }

    private function mapper(): array
    {
        return [
            self::NOT_FOUND_CTRL => 'Controller is not found.',
            self::NOT_FOUND_ACTION => 'Action is not found.',
            self::NOT_FOUND_LAYOUT => 'Layout is not found.',
        ];
    }
}
