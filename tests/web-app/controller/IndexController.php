<?php

namespace webapp\controller;

use ep\web\Request;
use ep\web\Response;
use webapp\model\User;
use Yiisoft\Db\Query\Query;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\Validator;

class IndexController extends \ep\web\Controller
{
    public function index(Request $request, Response $response)
    {
        return $response->render();
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

    public function form(Request $request, Response $response)
    {
        $user = User::findModel($request->get('id'));
        if ($user->load($request)) {
            if ($user->save()) {
                return $response->redirect($request->createUrl('index/form'));
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
        dump($r);
    }
}
