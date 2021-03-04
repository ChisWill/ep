<?php

declare(strict_types=1);

namespace Ep\Tests\App;

use Ep\Contract\FilterInterface;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class Module implements FilterInterface
{
    public function before($request)
    {
        if ($request instanceof ServerRequest) {
            // Web
        } else {
            // Console
        }

        return true;
    }

    public function after($request, $response)
    {
        if ($response instanceof ResponseInterface) {
            $response = $response->withStatus(404);
        }
        return $response;
    }
}
