<?php

namespace Ep\Tests\Basic\Component;

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

    protected function error($error, $errno = 500, $body = []): ResponseInterface
    {
        return $this->json([
            'errno' => $errno,
            'error' => $error,
            'body' => $body
        ]);
    }
}
