<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;

class RootFilter implements FilterInterface
{
    public function before($request)
    {
        tes('root start');
    }

    public function after($request, $response)
    {
        tes('root over');

        return $response;
    }
}
