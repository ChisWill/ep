<?php

declare(strict_types=1);

namespace Ep\Console;

final class Service
{
    public function prompt(string $message): string
    {
        fwrite(STDOUT, $message);
        return rtrim(fgets(STDIN), PHP_EOL);
    }
}
