<?php

namespace Ep\Tests\Basic\Controller;

use Ep;
use Ep\Tests\Basic\Component\Controller;
use Ep\Tests\Basic\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

class DemoController extends Controller
{
    public string $title = 'Demo';

    public function indexAction()
    {
        return $this->string('<h1>hello world</h1>');
    }

    public function jsonAction(ServerRequestInterface $request)
    {
        if ($request->getMethod() === Method::POST) {
            $post = $request->getParsedBody();
            $get = $request->getQueryParams();
            $return = compact('post', 'get');
        } else {
            $body = $request->getBody()->getContents();
            $get = $request->getQueryParams();
            if ($body) {
                $return = [
                    'body' => $body,
                    'get' => $get
                ];
            } elseif ($get) {
                $return = $get;
            } else {
                $return = ['hello' => 'world'];
            }
        }
        return $this->json($return);
    }

    public function requestAction(ServerRequestInterface $request)
    {
        d('Method：' . $request->getMethod());
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
        tes('');
        d('Upload Files：', $request->getUploadedFiles());
        tes($request->getHeaders());
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

    public function cacheAction()
    {
        $cache = Ep::getCache();

        $r = $cache->getOrSet('name', fn () => mt_rand(0, 100), 5);

        dd('Cache Value：', $r);
    }

    public function saveAction()
    {
        $user = new User;
        $user->username = 'Peter' . mt_rand(0, 1000);
        $user->age = mt_rand(0, 100);
        $r = $user->insert();
        d('Insert：', $r);

        tes('');

        $user = User::findModel(1);
        $user->username = 'Mary' . mt_rand(0, 1000);
        $r = $user->update();
        d('Update Num：', $r);
    }

    public function queryAction()
    {
        $query = User::find()->where(['like', 'username', 'Peter%', false]);
        tes('RawSql：' . $query->getRawSql());
        $user = $query->one();
        tes('Single User：', $user->getAttributes());
        $count = $query->count();
        d('Peter Count：', $count);
        $list = $query->all();
        foreach ($list as $user) {
            /** @var User $user */
            tes($user->getAttributes());
        }
    }

    public function eventAction()
    {
        $dipatcher = Ep::getEventDispatcher();
        $r = $dipatcher->dispatch($this);
        test($r);
    }

    public function redisAction()
    {
        $redis = Ep::getRedis();

        $r = $redis->set('a', mt_rand(0, 100), 'ex', 5, 'nx');
        d($r);

        $r = $redis->get('a');

        tes($r);
    }

    public function validateAction()
    {
        $user = User::findModel(1);
        tes($user->getAttributes());
        $r = $user->validate();
        if ($r) {
            d('validate ok');
        } else {
            d($user->getErrors());
        }
    }

    public function formAction(ServerRequestInterface $request)
    {
        $user = User::findModel($request->getQueryParams()['id'] ?? null);
        if ($user->load($request)) {
            if (!$user->validate()) {
                return $this->error($user->getErrors());
            }
            if ($user->save()) {
                return $this->success();
            } else {
                return $this->error($user->getErrors());
            }
        }
        return $this->render('form');
    }

    public function testAction()
    {

        echo 'test over';
    }
}
