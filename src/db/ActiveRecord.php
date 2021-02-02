<?php

namespace Ep\Db;

use Ep;
use RuntimeException;
use Psr\Http\Message\RequestInterface;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Validator;
use Yiisoft\Db\Connection\ConnectionInterface;

abstract class ActiveRecord extends \Yiisoft\ActiveRecord\ActiveRecord implements DataSetInterface
{
    public function __construct(?ConnectionInterface $db = null)
    {
        if ($db === null) {
            $db = Ep::getDi()->get(ConnectionInterface::class);
        }
        parent::__construct($db);
    }

    protected abstract function rules(): array;

    private $_errors = [];

    public function validate(): bool
    {
        $validator = new Validator();
        $results = $validator->validate($this, $this->rules());
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
        return new ActiveQuery(static::class, Ep::getDi()->get(ConnectionInterface::class));
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
                throw new RuntimeException("Data is not found.");
            }
            return $model;
        }
    }

    public function load(RequestInterface $request): bool
    {
        $this->setAttributes([]);

        // return $request->isPost;
        return true;
    }
}
