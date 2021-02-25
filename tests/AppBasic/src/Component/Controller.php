<?php

declare(strict_types=1);

namespace Ep\Tests\Basic\Component;

use Psr\Http\Message\ResponseInterface;

class Controller extends \Ep\Web\Controller
{
    /**
     * @param array|string $body
     */
    protected function success($body = []): ResponseInterface
    {
        return $this->json([
            'errno' => 0,
            'error' => 'OK',
            'body' => $body
        ]);
    }

    /**
     * @param array|string $error
     * @param array|string $body
     */
    protected function error($error, int $errno = 500, $body = []): ResponseInterface
    {
        return $this->json([
            'errno' => $errno,
            'error' => $error,
            'body' => $body
        ]);
    }
}
