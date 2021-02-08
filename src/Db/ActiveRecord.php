<?php

namespace Ep\Db;

use Ep;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Validator;
use Yiisoft\Db\Connection\ConnectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

abstract class ActiveRecord extends \Yiisoft\ActiveRecord\ActiveRecord implements DataSetInterface
{
    public function __construct(?ConnectionInterface $db = null)
    {
        parent::__construct($db ?: static::getConnection());
    }

    protected static function getConnection(): ConnectionInterface
    {
        return Ep::getDi()->get(ConnectionInterface::class);
    }

    public static function find(?ConnectionInterface $db = null): ActiveQuery
    {
        return new ActiveQuery(static::class, $db ?: static::getConnection());
    }

    public static function findModel($condition): ActiveRecord
    {
        if (empty($condition)) {
            return new static();
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

    public function load(ServerRequestInterface $request): bool
    {
        $this->setAttributes([]);

        // return $request->isPost;
        return true;
    }
}
