<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Base\FilterTrait;
use Ep\Contract\FilterInterface;

class DemoFilter implements FilterInterface
{
    use FilterTrait;

    public function before($request)
    {
        tes('demo start');
        return true;
    }

    public function after($request, $response)
    {
        tes('demo over');

        return $response;
    }
}
