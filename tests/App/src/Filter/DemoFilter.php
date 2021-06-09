<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;
use Ep\Contract\FilterTrait;

class DemoFilter implements FilterInterface
{
    use FilterTrait;

    public function before($request)
    {
        t('demo start');
        return true;
    }

    public function after($request, $response)
    {
        t('demo over');

        return $response;
    }
}
