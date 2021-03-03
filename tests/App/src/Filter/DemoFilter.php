<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;

class DemoFilter implements FilterInterface
{
    public function before($request)
    {
        tes('demo start');
    }

    public function after($request, $response)
    {
        tes('demo over');

        return $response;
    }
}
