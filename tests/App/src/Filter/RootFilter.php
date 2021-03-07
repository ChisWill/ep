<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep;
use Ep\Base\FilterTrait;
use Ep\Contract\FilterInterface;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Web\Service;

class RootFilter implements FilterInterface
{
    use FilterTrait;

    public function before($request)
    {
        tes('root start');
        // return Ep::getDi()->get(Service::class)->string('over');
        return true;
    }

    public function after($request, $response)
    {
        tes('root over');

        return $response;
    }
}
