<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Contract\InterceptorInterface;
use Ep\Tests\App\Filter\DemoFilter;
use Ep\Tests\App\Filter\OtherFilter;
use Ep\Tests\App\Filter\RootFilter;
use Ep\Web\ServerRequest;
use Ep\Web\Service;
use Psr\Http\Message\ResponseInterface;

class Interceptor implements InterceptorInterface
{
    public function includePath(): array
    {
        return [
            ['/', RootFilter::class],
            // ['/test', DemoFilter::class],
        ];
    }

    public function excludePath(): array
    {
        return [
            // ['/other', OtherFilter::class]
        ];
    }
}
