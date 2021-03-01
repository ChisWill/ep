<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Contract\InterceptorInterface;
use Ep\Web\ServerRequest;
use Ep\Web\Service;
use Psr\Http\Message\ResponseInterface;

class Interceptor implements InterceptorInterface
{
    public function includePath(): array
    {
        return [
            ['/', [$this, 'testRoot']],
            ['/te', [$this, 'testShop']],
        ];
    }

    public function excludePath(): array
    {
        return [
            ['/test', [$this, 'textExclude']]
        ];
    }

    public function testRoot(ServerRequest $request)
    {
        return true;
    }

    public function testShop(ServerRequest $request)
    {
        return true;
    }

    public function textExclude()
    {
        return true;
    }
}
