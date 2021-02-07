<?php

namespace Ep\Tests\App\web\Controller;

use Ep;
use Ep\Helper\Alias;
use Ep\Helper\Curl;
use Ep\Standard\ContextInterface;
use Ep\Standard\ControllerInterface;
use Ep\Standard\ViewInterface;
use Ep\Tests\App\web\Model\User;
use Ep\Web\View;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\VarDumper\VarDumper;

class IndexController extends \Ep\Web\Controller
{
    public function indexAction(ServerRequestInterface $request)
    {
        $this->setLayout('a');

        return $this->render('index/index');
    }

    public function testAction()
    {
    }

    public function requestAction(ServerRequestInterface $request)
    {
        tes('Method：' . $request->getMethod());
        tes('All GET：', $request->getQueryParams());
        tes('POST String：' . $request->getBody()->getContents());
        tes('Cookies：', $request->getCookieParams());
        tes('Host：' . $request->getUri()->getHost());
        tes('Path：' . $request->getUri()->getPath());
        tes('Post Array：', $request->getParsedBody());
    }

    public function redirectAction(ServerRequestInterface $request)
    {
        $url = $request->getQueryParams()['url'] ?? '';
        if (!$url) {
            return $this->string("");
        }
        return $this->redirect($url);
    }

    public function log($req)
    {
        $targets = [];
        $rotator = new FileRotator(1, 2);
        $filePath = Alias::get('@root/runtime/logs/tmp.log');
        $target = new FileTarget($filePath, $rotator);
        $target->setFormat(function (Message $message, array $commonContext): string {
            return 'ergerg';
        });
        $targets[] = $target;
        $logger = new Logger($targets);
        $logger->info('oh no ', ['lala' => 'wefij']);
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
                return $this->jsonError($user->getErrors());
            }
            if ($user->save()) {
                return $this->jsonSuccess();
            } else {
                return $this->jsonError('wrong');
            }
        }
        return $this->render('');
    }

    public function jsonAction(ServerRequestInterface $request)
    {
        return $this->jsonSuccess(['msg' => 'hello', 'url' => '']);
    }

    public function db()
    {
        $query = User::find();
        $user = $query->where(['id' => 11])->one();
        $user->username = mt_rand();
        $r = $user->save();
        var_dump($r);

        $subQuery = User::find();
        $subQuery->where(['state' => 1]);
        $query = User::find();
        $query->from(['s' => $subQuery])->where(['>', 'age', 0]);
        $sql = $query->getRawSql();
        $result = $query->asArray()->all();
        tes($sql);
        test($result);
    }

    public function yaml()
    {
        $path = '';
        $yaml = file_get_contents($path);
        $r = yaml_parse($yaml);
        test($r);
    }
}
