<?php

namespace Ep\Tests\App\Web\Controller;

use Ep;
use Yiisoft\Profiler\Profiler;

class IndexController extends \Ep\Web\Controller
{
    public function indexAction()
    {
        return $this->render('index/index');
    }

    public function profilerAction()
    {
        $logger = Ep::getLogger();
        $profile = new Profiler($logger);
        $profile->begin('test');

        $count = 10000;
        for ($i = 0; $i < $count; $i++) {
        }

        $profile->end('test');
        foreach ($profile->getMessages() as $item) {
            tes($item->context());
        }
    }
}
