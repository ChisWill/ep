<?php

namespace Ep\Tests\Basic\Controller;

use DateInterval;
use Ep;
use Ep\Tests\Basic\Component\Controller;
use Ep\Tests\Basic\Model\User;
use Ep\Tests\Support\XEngine;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;
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

    public function requestAction(ServerRequest $request)
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
        $id = User::find()->orderBy('id DESC')->select('id')->scalar();

        return $this->redirect('form?id=' . $id);
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
        $dipatcher->dispatch(new XEngine(80));
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
        $user = User::findModel($request->getQueryParams()['id'] ?? 0);
        if ($user->load($request)) {
            $trans = Ep::getDb()->beginTransaction();
            if (!$user->validate()) {
                return $this->error($user->getErrors());
            }
            if ($user->save()) {
                $trans->commit();
                return $this->success();
            } else {
                $trans->rollBack();
                return $this->error($user->getErrors());
            }
        }
        return $this->render('form', compact('user'));
    }

    public function wsAction()
    {
        return $this->render('ws');
    }

    public function getCookieAction(ServerRequestInterface $request)
    {
        $cookies = CookieCollection::fromArray($request->getCookieParams());

        return [
            'testcookie' => $cookies->getValue('testcookie')
        ];
    }

    public function setCookieAction()
    {
        $cookie = new Cookie('testcookie', 'testcookie' . mt_rand());
        $cookie = $cookie->withMaxAge(new DateInterval('PT10S'))->withSecure(false);
        $response = $this->string('ok');
        return $cookie->addToResponse($response);
    }

    public function testAction()
    {
    }
}
