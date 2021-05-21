<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Factory\Exception\NotFoundException;
use InvalidArgumentException;

class Service
{
    protected Connection $db;
    protected string $appNamespace;

    public function init(array $params): void
    {
        $this->appNamespace = $params['common.appNamespace'];

        $db = $params['db'] ?? $params['common.db'] ?? null;
        try {
            $this->db = Ep::getDb($db);
        } catch (NotFoundException $e) {
            $this->invalid('db', $db);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function required(string $option): void
    {
        $this->throw("The \"{$option}\" option is required.");
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function invalid(string $option, string $value): void
    {
        $this->throw("The value \"{$value}\" of the option \"{$option}\" is invalid.");
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function throw(string $message): void
    {
        throw new InvalidArgumentException($message);
    }
}
