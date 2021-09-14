<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\BackEnd\Command;

use Ep\Console\Command;

final class TestEPCronCommand extends Command
{
    public function indexAction()
    {
        return $this->success('index');
    }

    public function sayAction()
    {
        return $this->success('hi');
    }

    public function callAction()
    {
        ob_start();
        $code = $this->getService()->call('init', ['name' => 'ChisWill']);
        $echo = ob_get_clean();

        return $this->success('call over, code: ' . $code . ', echo: ' . $echo);
    }
}
