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
        $result = [
            'Method' => $request->getMethod(),
            'All GET' => $request->getQueryParams(),
            'All POST' => $request->getParsedBody(),
            'Raw body' => $request->getBody()->getContents(),
            'Cookies' => $request->getCookieParams(),
            'Host' => $request->getUri()->getHost(),
            'Header' => $request->getHeaders(),
            'Path' => $request->getUri()->getPath()
        ];
        return $result;
    }

    public function redirectAction(ServerRequestInterface $request)
    {
        $url = $request->getQueryParams()['url'] ?? 'http://www.baidu.com';

        return $this->redirect($url);
    }

    public function loggerAction()
    {
        $logger = Ep::getLogger();
        $logger->info('halo');
        return $this->string('over');
    }

    public function cacheAction()
    {
        $cache = Ep::getCache();

        $r = $cache->getOrSet('name', fn () => mt_rand(0, 100), 5);

        return $this->string($r);
    }

    public function saveAction()
    {
        $user = new User;
        $user->username = 'Peter' . mt_rand(0, 1000);
        $user->age = mt_rand(0, 100);
        $r1 = $user->insert();


        $user = User::findModel(1);
        $user->username = 'Mary' . mt_rand(0, 1000);
        $r2 = $user->update();

        return compact('r1', 'r2');
    }

    public function queryAction()
    {
        $result = [];
        $query = User::find()->where(['like', 'username', 'Peter%', false]);
        $result['RawSql'] = $query->getRawSql();
        $user = $query->one();
        $result['Model Attributes'] = $user->getAttributes();
        $result['Count'] = $query->count();
        $list = $query->asArray()->all();
        $result['All'] = $list;

        return $result;
    }

    public function eventAction()
    {
        $dipatcher = Ep::getEventDispatcher();
        $dipatcher->dispatch($this);
    }

    public function redisAction()
    {
        $redis = Ep::getRedis();

        $result = [];
        $r = $redis->set('a', mt_rand(0, 100), 'ex', 5, 'nx');
        $result['set'] = $r;
        $r = $redis->get('a');
        $result['get'] = $r;

        return $result;
    }

    public function validateAction()
    {
        $user = User::findModel(1);
        $r = $user->validate();
        if ($r) {
            return 'validate ok';
        } else {
            return $user->getErrors();
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

    public function wsAction()
    {
        return $this->render('ws');
    }

    public function testAction()
    {
        echo 'test string';

        return 'over';
    }
}
