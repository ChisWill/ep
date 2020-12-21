<?php

namespace ep\base;

class Controller
{
    private $router;

    protected function getControllerName($className)
    {
        $pieces = explode('\\', $className);
        $name = array_pop($pieces);
        return lcfirst(str_replace('Controller', '', $name));
    }
}
