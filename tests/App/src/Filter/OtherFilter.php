<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Base\FilterTrait;
use Ep\Contract\FilterInterface;

class OtherFilter implements FilterInterface
{
    use FilterTrait;

    public function before($request)
    {
        tes('other start');
        return true;
    }

    public function after($request, $response)
    {
        tes('other over');

        return $response;
    }
}
