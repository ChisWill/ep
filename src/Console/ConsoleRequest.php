<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Standard\ConsoleRequestInterface;
use RuntimeException;

class ConsoleRequest implements ConsoleRequestInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRoute(): string
    {
        return '/' . ($_SERVER['argv'][1] ?? '');
    }

    private ?array $params = null;

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        if ($this->params === null) {
            $this->params = [];
            $count = count($_SERVER['argv']);
            if ($count > 2) {
                for ($i = 2; $i < $count; $i++) {
                    try {
                        [$k, $v] = explode('=', $_SERVER['argv'][$i]);
                        $this->params[$k] = $v;
                    } catch (RuntimeException $e) {
                        $this->params[$k] = null;
                    }
                }
            }
        }
        return $this->params;
    }
}
