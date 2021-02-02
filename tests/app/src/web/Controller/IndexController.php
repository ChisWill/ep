<?php

namespace Ep\Tests\App\web\Controller;

use Ep\Helper\Alias;
use Ep\Tests\App\web\Model\User;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;

class IndexController extends \Ep\Web\Controller
{
    public function indexAction(ServerRequestInterface $request)
    {
        return $this->render('index/index');
    }

    public function error(ServerRequestInterface $request)
    {
        test(123, $request->getUri());
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

    public function redirectAction(ServerRequestInterface $request)
    {
        $url = $request->getAttribute('url');
        if (!$url) {
            return $this->string("");
        }
        return $this->redirect();
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
