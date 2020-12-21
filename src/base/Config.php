<?php

namespace ep\base;

abstract class Config
{
    public $defaultController = 'index';
    public $defaultAction = 'index';
    public $controllerNamespace = 'src\\controller';
    public $viewFilePath;

    public $routeRules = [];
}
