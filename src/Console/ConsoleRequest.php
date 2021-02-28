<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use ErrorException;

final class ConsoleRequest implements ConsoleRequestInterface
{
    private array $options;

    public function __construct()
    {
        getopt('', [], $optind);
        $this->options = array_slice($_SERVER['argv'], $optind);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): string
    {
        return '/' . ($this->options[0] ?? '');
    }

    private ?array $params = null;

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        if ($this->params === null) {
            $this->params = [];
            $count = count($this->options);
            if ($count > 1) {
                for ($i = 1; $i < $count; $i++) {
                    try {
                        if (strpos($this->options[$i], '-') === 0) {
                            $this->params[substr($this->options[$i], 1)] = true;
                        } else {
                            [$k, $v] = explode('=', $this->options[$i]);
                            $this->params[$k] = $v;
                        }
                    } catch (ErrorException $e) {
                        echo <<<HELP
Error: invalid param "{$this->options[$i]}"
HELP;
                        exit(1);
                    }
                }
            }
        }
        return $this->params;
    }
}
