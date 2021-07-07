<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Command;

use Ep\Console\Command;

final class ADGTeCommand extends Command
{
    public function sayAction()
    {
        return $this->success('hi');
    }

    public function callAction()
    {
        ob_start();
        $code = $this->getService()->call('init', ['name' => 'ChisWill']);
        $od = ob_get_clean();
        tt($od, 'over');

        return $this->success('call over, code: ' . $code);
    }
}
