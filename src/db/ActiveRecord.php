<?php

namespace Ep\db;

use Ep\Exception;
use Ep\Helper\Ep;
use Ep\web\Request;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Validator;

abstract class ActiveRecord extends \Yiisoft\ActiveRecord\ActiveRecord implements DataSetInterface
{
    public function __construct(?ConnectionInterface $db = null)
    {
        if ($db === null) {
            $db = Ep::getDi()->get('db');
        }
        parent::__construct($db);
    }

    protected abstract function rules(): array;

    private $_errors = [];

    public function validate(): bool
    {
        $validator = new Validator($this->rules());
        $results = $validator->validate($this);
        $this->_errors = [];
        foreach ($results as $attribute => $result) {
            if (!$result->isValid()) {
                $this->_errors[$attribute] = current($result->getErrors());
            }
        }
        return empty($this->_errors);
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

    public function getAttributeValue(string $attribute)
    {
        return $this->getAttribute($attribute);
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
