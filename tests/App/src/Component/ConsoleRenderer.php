<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Annotation\Inject;
use Ep\Contract\ConsoleErrorRendererInterface;
use Throwable;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Log\LoggerInterface;

class ConsoleRenderer implements ConsoleErrorRendererInterface
{
    /**
     * @Inject
     */
    private LoggerInterface $log;

    public function render(Throwable $t, ConsoleRequestInterface $request): string
    {
        return sprintf(
            "%s: %s, File: %s\n",
            get_class($t),
            $t->getMessage(),
            $t->getFile() . ':' . $t->getLine()
        );
    }

    public function log(Throwable $t, ConsoleRequestInterface $request): void
    {
        $context = [
            'category' => get_class($t)
        ];

        $context['route'] = $request->getRoute();
        $context['arguments'] = $request->getArguments();
        $context['options'] = $request->getOptions();

        $this->log->emergency($this->render($t, $request), $context);
    }
}
