<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Controller;

use Ep\Tests\App\Component\Controller;

final class AdTestController extends Controller
{
    public function sayAction()
    {
        return $this->string('hi');
    }
}