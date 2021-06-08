<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep\Console\Command;
use Ep\Helper\Curl;

class CurlCommand extends Command
{
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
}
