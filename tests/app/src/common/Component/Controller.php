<?php

namespace Ep\Tests\App\Common\Component;

use Psr\Http\Message\ResponseInterface;

class Controller extends \Ep\Web\Controller
{
    protected function success($body = []): ResponseInterface
    {
        return $this->json([
            'errno' => 0,
            'error' => 'OK',
            'body' => $body
        ]);
    }

    protected function error($error = [], $body = []): ResponseInterface
    {
        return $this->json([
            'errno' => 500,
            'error' => $error,
            'body' => $body
        ]);
    }
}
