<?php

declare(strict_types=1);

namespace Ep\Tests\App;

use Ep\Contract\ModuleInterface;
use Ep\Web\ServerRequest;

final class Module implements ModuleInterface
{
    public function bootstrap($request): void
    {
        if ($request instanceof ServerRequest) {
            // Web
        } else {
            // Console
        }
    }
}
