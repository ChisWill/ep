<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Contract\ContextTrait;
use Ep\Contract\WebErrorRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class WebErrorRenderer implements WebErrorRendererInterface
{
    use ContextTrait;

    public string $id = 'demo';

    /**
     * @Inject
     */
    private LoggerInterface $log;

    public function render(Throwable $t, ServerRequestInterface $request): string
    {
        return $this->getView()->renderPartial('error', compact('t', 'request'));
    }

    public function log(Throwable $t, ServerRequestInterface $request): void
    {
        $this->log->critical($t->getMessage());
    }
}
