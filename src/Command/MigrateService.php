<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Ep\Db\Service;

final class MigrateService
{
    public function init(string $dbName): void
    {
        $service = new Service(Ep::getDb($dbName));
    }

    private function createTable(): void
    {
    }
}
