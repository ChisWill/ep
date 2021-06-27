<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use DateInterval;
use Ep;
use Ep\Base\Config;
use Ep\Db\Query;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Facade\Cache as FacadeCache;
use Ep\Tests\App\Facade\Logger;
use Ep\Tests\App\Form\TestForm;
use Ep\Tests\App\Model\User;
use Ep\Web\ServerRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Cache;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionInterface;

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
        return $this->json($result);
    }

    public function redirectAction(ServerRequestInterface $request)
    {
        $id = User::find()->orderBy('id DESC')->select('id')->scalar();

        return $this->redirect('arform?id=' . $id);
    }

    public function loggerAction(LoggerInterface $logger)
    {
        $logger->info('halo');
        return $this->string('over');
    }

    public function cacheAction(Cache $cache)
    {
        $r = $cache->getOrSet('name', fn (): int => mt_rand(0, 100), 5);

        return $this->string((string) $r);
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

        return $this->json(compact('r1', 'r2'));
    }

    public function queryAction(Aliases $aliases, Config $config)
    {
        $result = [];
        $query = User::find()->where(['like', 'username', 'Peter%', false]);
        $result['RawSql'] = $query->getRawSql();
        $user = $query->one();
        if ($user) {
            $result['Model Attributes'] = $user->getAttributes();
        }
        $result['Count'] = $query->count();
        $list = $query->asArray()->all();
        $result['All'] = $list;

        return $this->json($result);
    }

    public function curdAction()
    {
        $insert = 0;
        $update = 0;
        $batchInsert = 0;
        $upsert = 0;
        $delete = 0;

        $insert = Query::find()->insert('user', [
            'pid' => 1,
            'username' => 'a'
        ]);
        $insert = Query::find()->insert('user', Query::find()->from('user')->select(['username', 'age'])->where('id=69'));
        $update =  Query::find()->update('user', ['username' => 'mary-bob-' . mt_rand()], 'id=:id', [':id' => 76]);

        $batchInsert = Query::find()->batchInsert('user', ['username', 'age'], [
            ['a1', 11],
            ['b1', 22],
            ['c1', 33],
        ]);
        $upsert = Query::find()->upsert('user', ['id' => 72, 'username' => 'julia', 'age' => 99], ['age' => 33]);
        $delete = Query::find()->delete('user', ['id' => 75]);

        $increment = Query::find()->increment('user', ['age' => -1, 'name' => 'peter'], 'id=:id', [':id' => 9]);

        return compact('insert', 'update', 'batchInsert', 'upsert', 'delete', 'increment');
    }

    public function eventAction(EventDispatcherInterface $dipatcher)
    {
        $dipatcher->dispatch($this);

        return $this->string();
    }

    public function redisAction(Connection $redis)
    {
        $result = [];
        $r = $redis->set('a', mt_rand(0, 100), 'ex', 5, 'nx');
        $result['set'] = $r;
        $r = $redis->get('a');
        $result['get'] = $r;

        return $this->json($result);
    }

    public function validateAction()
    {
        $user = User::findModel(1);
        $r = $user->validate();
        if ($r) {
            return $this->string('validate ok');
        } else {
            return $this->json($user->getErrors());
        }
    }

    public function getUserAction()
    {
        $data = User::find()
            ->joinWith('parent')
            ->asArray()
            ->one();

        return $this->success($data);
    }

    public function formAction(ServerRequestInterface $request, TestForm $form)
    {
        if ($form->load($request->getParsedBody())) {
            if ($form->validate()) {
                return $this->success($form->getAttributes());
            } else {
                return $this->error($form->getErrors());
            }
        }

        return $this->render('form');
    }

    public function arformAction(ServerRequestInterface $request)
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
        return $this->render('arform', compact('user'));
    }

    public function wsAction()
    {
        return $this->render('ws');
    }

    public function getCookieAction(ServerRequestInterface $request)
    {
        $cookies = CookieCollection::fromArray($request->getCookieParams());

        return $this->json([
            'testcookie' => $cookies->getValue('testcookie')
        ]);
    }

    public function setCookieAction()
    {
        $cookie = new Cookie('testcookie', 'testcookie' . mt_rand());
        $cookie = $cookie->withMaxAge(new DateInterval('PT10S'))->withSecure(false);
        $response = $this->string('ok');
        return $cookie->addToResponse($response);
    }

    public function sessionAction(SessionInterface $session)
    {
        $session->set('title', 'sessionTest');

        $r = $session->get('title');

        return $this->json($r);
    }

    public function paginateAction(ServerRequest $serverRequest)
    {
        $page = $serverRequest->getQueryParams()['page'] ?? 1;
        $query = User::find()->asArray();
        $count = $query->count();

        return $this->json([
            'count' => $count,
            'all' => $query->getPaginator()->all((int) $page, 3),
        ]);
    }

    public function facadeAction()
    {
        FacadeCache::set('a', 123);
        $r = FacadeCache::get('a');
        Logger::alert('alaa');
        $alert = Ep::getLogger('alert');
        Logger::swap($alert);
        Logger::alert('i am alert');
        Logger::clear();
        Logger::alert('i am reset');

        return $this->string($r);
    }

    public function testAction()
    {
        return $this->string();
    }
}
