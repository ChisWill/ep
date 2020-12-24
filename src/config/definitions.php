<?php

use ep\web\Request;
use ep\web\Response;
use ep\web\View;

return [
    'webRequest' => Request::class,
    'webResponse' => [
        '__class' => Response::class,
        'view' => new View
    ]
];
