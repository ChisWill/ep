<?php

namespace ep\db;

use ep\Exception;
use ep\helper\Ep;
use ep\web\Request;
use Yiisoft\ActiveRecord\ActiveQuery;

class ActiveRecord extends \Yiisoft\ActiveRecord\ActiveRecord
{
    public function __construct()
    {
        parent::__construct(Ep::getDi()->get('db'));
    }

    public static function find(): ActiveQuery
    {
        return new ActiveQuery(static::class, Ep::getDi()->get('db'));
    }

    public static function findModel($condition): ActiveRecord
    {
        if (empty($condition)) {
            return new static;
        } else {
            if (is_scalar($condition)) {
                $condition = ['id' => $condition];
            }
            $model = static::find()->where($condition)->one();
            if ($model === null) {
                throw new Exception(Exception::NOT_FOUND_DATA);
            }
            return $model;
        }
    }

    public function load(Request $request)
    {
        $this->setAttributes($request->getBodyParams());

        return $request->isPost();
    }
}
