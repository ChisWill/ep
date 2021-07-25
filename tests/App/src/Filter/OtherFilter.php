<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;
use Ep\Contract\FilterTrait;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\Middleware\InitMiddleware;

class OtherFilter implements FilterInterface
{
    use FilterTrait;

    public function __construct()
    {
        $this->setMiddlewares([
            InitMiddleware::class,
            AddMiddleware::class,
            FilterMiddleware::class,
        ]);
    }

    public function before($request)
    {
        // t('other start');
        return true;
    }

    public function after($request, $response)
    {
        // t('other over');

        return $response;
    }
}
