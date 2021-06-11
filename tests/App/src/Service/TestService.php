<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

final class TestService
{
    public function getRandom(): int
    {
        return mt_rand(10, 100);
    }
}
