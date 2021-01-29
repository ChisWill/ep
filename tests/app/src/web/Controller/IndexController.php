<?php

namespace Ep\Tests\App\web\Controller;

use Ep\Helper\Alias;
use Ep\Helper\Ep;
use Ep\Tests\Cls\Car;
use ep\web\Request;
use ep\web\Response;
use HttpSoft\Message\Request as MessageRequest;
use HttpSoft\Message\Response as MessageResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\App\model\User;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;

class IndexController extends \Ep\Web\Controller
{
    public function index(RequestInterface $request)
    {
        test($request);
        return $response->render();
    }

    public function error(RequestInterface $request)
    {
        test(123, $request->getUri());
    }

    public function log($req, Response $res)
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

    public function validate()
    {
        $user = User::findModel(11);
        $r = $user->validate();
        if ($r) {
            tes('ok');
        } else {
            test($user->getErrors());
        }
    }

    public function redirect($req, Response $res)
    {
        $act = $req->get('act');
        if (!$act) {
            return $res->string('act is required.');
        }
        return $res->redirect($req->createUrl('index/' . $act));
    }

    public function form(Request $request, Response $response)
    {
        $user = User::findModel($request->get('id'));
        if ($user->load($request)) {
            if (!$user->validate()) {
                return $response->jsonError($user->getErrors());
            }
            if ($user->save()) {
                return $response->jsonSuccess();
            } else {
                return $response->jsonError('wrong');
            }
        }
        return $response->render();
    }

    public function json(Request $request, Response $response)
    {
        return $response->jsonSuccess(['msg' => 'hello', 'url' => $request->createUrl('index/json', ['age' => 1])]);
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
