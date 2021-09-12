<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use DateInterval;
use Ep;
use Ep\Annotation\Configure;
use Ep\Annotation\Route;
use Ep\Auth\AuthRepository;
use Ep\Auth\Method\HttpSession;
use Ep\Db\Query;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Facade\Cache as FacadeCache;
use Ep\Tests\App\Facade\Logger;
use Ep\Tests\App\Form\TestForm;
use Ep\Tests\App\Model\Student;
use Ep\Web\ServerRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Cache;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionInterface;

/**
 * @Configure
 */
class DemoController extends Controller
{
    private Connection $db;

    public function __construct()
    {
        $this->db = Ep::getDb('sqlite');
    }

    /**
     * @Route("index", method="GET")
     */
    public function indexAction()
    {
        return $this->string('<h1>hello world</h1>');
    }

    /**
     * @Route("json")
     */
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

    public function downloadAction(ServerRequest $request, Aliases $aliases)
    {
        // $name = 'eye.png';
        $name = 'face.jpg';
        $file = $aliases->get('@root/static/image/' . $name);

        $newName = null;
        // $newName = '0!§ $&()=`´{}  []²³@€µ^°_+\' # - _ . , ; ü ä ö ß 9.jpg';

        return $this
            ->getService()
            ->withRequest($request)
            ->download($file, $newName);
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
        $id = Student::find($this->db)->orderBy('id DESC')->select('id')->scalar();

        return $this->redirect('arform?id=' . $id);
    }

    public function logAction(LoggerInterface $logger)
    {
        $logger->info(sprintf('%s logged', __METHOD__));
        return $this->string('logged');
    }

    public function cacheAction(Cache $cache)
    {
        $r = $cache->getOrSet('name', fn (): int => mt_rand(0, 100), 5);

        return $this->string((string) $r);
    }

    public function saveAction()
    {
        $user = new Student($this->db);
        $user->username = '路人甲' . mt_rand(0, 100);
        $user->class_id = 3;
        $user->age = mt_rand(0, 100);
        $r1 = $user->insert();


        $user = Student::findModel(1, $this->db);
        $user->desc = 'desc has been updated' . mt_rand(0, 100);
        $r2 = $user->update();

        return $this->json(compact('r1', 'r2'));
    }

    public function queryAction()
    {
        $result = [];
        $query = Student::find($this->db)
            ->joinWith('class')
            ->where(['like', 'student.name', 'A%', false])
            ->andWhere(['class.id' => 3]);
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

        $insert = Query::find($this->db)->insert('student', [
            'class_id' => 1,
            'name' => '路人乙' . mt_rand(0, 100)
        ]);
        $insert = Query::find($this->db)->insert('student', Query::find($this->db)->from('student')->select(['name', 'age'])->where(['id' => 1]));
        $update =  Query::find($this->db)->update('student', ['desc' => 'code: ' . mt_rand()], 'id=:id', [':id' => 2]);

        $batchInsert = Query::find($this->db)->batchInsert('student', ['name', 'age'], [
            ['a1', 11],
            ['b1', 22],
            ['c1', 33],
        ]);
        $upsert = Query::find($this->db)->upsert('student', ['id' => 72, 'name' => 'julia', 'age' => 99], ['age' => 33]);
        $delete = Query::find($this->db)->delete('student', ['id' => 75]);

        $increment = Query::find($this->db)->increment('student', ['age' => -1, 'name' => 'peter'], 'id=:id', [':id' => 9]);

        return compact('insert', 'update', 'batchInsert', 'upsert', 'delete', 'increment');
    }

    public function eventAction(EventDispatcherInterface $dipatcher)
    {
        $dipatcher->dispatch($this);

        return $this->string();
    }

    public function redisAction(RedisConnection $redis)
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
        $user = Student::findModel(1, $this->db);
        $r = $user->validate();
        if ($r) {
            return $this->string('validate ok');
        } else {
            return $this->json($user->getErrors());
        }
    }

    public function getUserAction()
    {
        $data = Student::find($this->db)
            ->joinWith('class')
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
        $student = Student::findModel($request->getQueryParams()['id'] ?? 0, $this->db);
        if ($student->load($request)) {
            $trans = $this->db->beginTransaction();
            if (!$student->validate()) {
                return $this->error($student->getErrors());
            }
            if ($student->save()) {
                $trans->commit();
                return $this->success();
            } else {
                $trans->rollBack();
                return $this->error($student->getErrors());
            }
        }
        return $this->render('arform', compact('student'));
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

        $cookie2 = new Cookie('testcookie2', 'testcookie2' . mt_rand());
        $cookie2 = $cookie2->withMaxAge(new DateInterval('PT20S'))->withSecure(false);

        $response = $this->string('ok');
        $response = $response->withAddedHeader('t1', 'v1');
        $response = $response->withAddedHeader('t1', 'v2');
        $response = $response->withAddedHeader('t1', 'v3');

        $response = $response->withHeader('z1', 'v1');
        $response = $response->withHeader('z1', 'v2');
        $response = $response->withHeader('z1', 'v3');

        return $cookie->addToResponse($cookie2->addToResponse($response));
    }

    public function sessionAction(SessionInterface $session)
    {
        $session->set('title', 'sessionTest');

        $r = $session->get('title');

        return $this->json($r);
    }

    public function paginateAction(ServerRequest $serverRequest)
    {
        $page = (int) ($serverRequest->getQueryParams()['page'] ?? 1);
        $query = Student::find($this->db)->asArray();
        $count = $query->count();

        return $this->json([
            'count' => $count,
            'all' => $query->getPaginator()->all($page, 3),
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

    public function loginAction(ServerRequestInterface $request, SessionInterface $session, AuthRepository $auth)
    {
        $p = $request->getQueryParams();
        $username = $p['u'] ?? '';
        $password = $p['p'] ?? '';
        if (!$username || !$password) {
            return $this->error('require params u or p');
        }

        $user = Query::find($this->db)->from('student')->where([
            'name' => $username,
            'password' => $password
        ])->one();
        if (!$user) {
            return $this->error('missing user');
        }
        $method = $auth->findMethod('frontend');
        if ($method instanceof HttpSession) {
            $session->set($method->getId(), $user['id']);
        } else {
            return $this->error('Wrong auth method');
        }

        return $this->string('Logined');
    }

    public function testAction()
    {
        return $this->string();
    }
}
