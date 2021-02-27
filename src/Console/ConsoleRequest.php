<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use ErrorException;

final class ConsoleRequest implements ConsoleRequestInterface
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
                        if (strpos($_SERVER['argv'][$i], '-') === 0) {
                            $this->params[substr($_SERVER['argv'][$i], 1)] = true;
                        } else {
                            [$k, $v] = explode('=', $_SERVER['argv'][$i]);
                            $this->params[$k] = $v;
                        }
                    } catch (ErrorException $e) {
                        echo <<<HELP
Error: invalid param "{$_SERVER['argv'][$i]}"
HELP;
                        exit(1);
                    }
                }
            }
        }
        return $this->params;
    }
}
