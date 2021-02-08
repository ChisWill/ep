<?php

namespace Ep\Tests\App\Web\Controller;

use Ep;
use Ep\Standard\ServerRequestInterface;
use Ep\Tests\App\Web\Model\User;

class DemoController extends \Ep\Web\Controller
{
    public function jsonAction()
    {
        return $this->json(['say' => 'hello', 'hi' => 'world']);
    }

    public function stringAction()
    {
        return $this->string('<h1>hello world</h1>');
    }

    public function requestAction(ServerRequestInterface $request)
    {
        d('Method：' . $request->getMethod());
        tes('');
        d('isPost：', $request->isPost());
        tes('');
        d('IsAjax：', $request->isAjax());
        tes('');
        d('All GET：', $request->getQueryParams());
        tes('');
        d('POST String：' . $request->getBody()->getContents());
        tes('');
        d('Cookies：', $request->getCookieParams());
        tes('');
        d('Host：' . $request->getUri()->getHost());
        tes('');
        d('Path：' . $request->getUri()->getPath());
        tes('');
        d('Post Body：', $request->getParsedBody());
    }

    public function redirectAction(ServerRequestInterface $request)
    {
        $url = $request->getQueryParams()['url'] ?? 'http://www.baidu.com';

        return $this->redirect($url);
    }

    public function errorAction()
    {
        d('error');
    }

    public function loggerAction()
    {
        $logger = Ep::getLogger();
        $logger->info('halo');
        echo 'over';
    }

    public function queryAction()
    {
        $user = new User;
        $r = $user->primaryKey();
        test($r);
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
}
