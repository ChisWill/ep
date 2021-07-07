<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Controller;

use Ep\Tests\App\Component\Controller;

final class AdTestController extends Controller
{
    public function sayAction()
    {
        return $this->render('index');
    }

    public function runAction()
    {
        return $this->string('I am running');
    }

    public function sayGoodByeAction()
    {
        return $this->string('good bye');
    }
}
