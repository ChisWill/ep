<?php

namespace Ep\Tests\App\Web\Controller;

use Ep\Helper\Alias;
use Ep\Standard\ServerRequestInterface;
use Ep\Tests\App\web\Model\User;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;

class IndexController extends \Ep\Web\Controller
{
    public function indexAction()
    {
        return $this->render('index/index');
    }

    public function benchAction(ServerRequestInterface $request)
    {
        $start = microtime(true);
        $startMem = memory_get_usage();
        $count = 100;

        for ($i = 0; $i < $count; $i++) {
        }

        $endMem = memory_get_usage();
        $end = microtime(true);
        $result = ($end - $start) * 1000 . '（ms）';
        $result .= "\n" . ($endMem - $startMem);
        test($result);
    }

    public function testAction()
    {
    }

    public function validateAction()
    {
        $user = User::findModel(11);
        test($user);
        $r = $user->validate();
        if ($r) {
            tes('ok');
        } else {
            test($user->getErrors());
        }
    }

    public function form(ServerRequestInterface $request)
    {
        $user = User::findModel($request->getAttribute('id'));
        if ($user->load($request)) {
            if (!$user->validate()) {
                return $this->json($user->getErrors());
            }
            if ($user->save()) {
                return $this->json();
            } else {
                return $this->json(['error' => 1]);
            }
        }
        return $this->render('');
    }
}
