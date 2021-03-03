<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;

class OtherFilter implements FilterInterface
{
    public function before($request)
    {
        tes('other start');
    }

    public function after($request, $response)
    {
        tes('other over');

        return $response;
    }
}
