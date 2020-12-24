<?php

namespace webapp\config;

use ep\helper\Ep;
use ep\web\Config;

class WebConfig extends Config
{
    public function __construct()
    {
        $this->controllerNamespace = 'webapp\\controller';
    }
}
