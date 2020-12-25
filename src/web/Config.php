<?php

namespace ep\web;

use ep\base\Config as BaseConfig;

class Config extends BaseConfig
{
    public function __construct()
    {
        parent::__construct();

        $this->setDi([
            'request' => Request::class,
            'response' => [
                '__class' => Response::class,
                'view' => new View
            ],
        ]);
    }
}
