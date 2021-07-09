<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Helper\Curl;
use Symfony\Component\Console\Input\InputArgument;

class CurlCommand extends Command
{
    public function __construct()
    {
        $this
            ->createDefinition('single')
            ->addArgument('action', InputArgument::REQUIRED, 'target url');
    }

    public function multiLockAction()
    {
        $url = 'http://ep.cc/test/lock';

        $r = [];
        for ($i = 0; $i < 10; $i++) {
            $r[] = Curl::getMulti($url, '', [], 40);
        }

        t($r);
        return $this->success();
    }

    public function multiTestAction()
    {
        $url = 'http://ep.cc/test/lock';

        $start = microtime(true);

        $count = 0;
        for ($i = 0; $i < 5; $i++) {
            $ret = Curl::getMulti($url, '', [], 100);
            foreach ($ret as $row) {
                if (strpos($row, 'true') !== false) {
                    $count++;
                }
            }
        }

        $end = microtime(true);

        t([
            'count' => $count,
            'time' => ($end - $start) * 1000
        ]);

        return $this->success();
    }

    public function singleAction(ConsoleRequestInterface $request)
    {
        $r = Curl::get('http://ep.cc/test/' . $request->getArgument('action'));

        return $this->success($r);
    }
}
