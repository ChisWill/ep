<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Http\Method;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

abstract class ActiveRecord extends \Yiisoft\ActiveRecord\ActiveRecord implements DataSetInterface
{
    protected static string $pk = 'id';

    public function __construct(?ConnectionInterface $db = null)
    {
        parent::__construct($db ?: Ep::getDb());
    }

    public static function find(?ConnectionInterface $db = null): ActiveQuery
    {
        return new ActiveQuery(static::class, $db ?: Ep::getDb());
    }

    /**
     * @param  int|string|array|ExpressionInterface $condition
     * 
     * @return static
     * @throws RuntimeException
     */
    public static function findModel($condition = null)
    {
        if (empty($condition)) {
            return new static;
        } else {
            if (is_scalar($condition)) {
                $condition = [self::$pk => $condition];
            }
            $model = static::find()->where($condition)->one();
            if ($model === null) {
                throw new RuntimeException("Data is not found.");
            }
            return $model;
        }
    }

    public function load(ServerRequestInterface $request): bool
    {
        if ($request->getMethod() === Method::POST) {
            $this->setAttributes($request->getParsedBody() ?: []);
            return true;
        } else {
            return false;
        }
    }

    private array $_errors = [];

    public function validate(): bool
    {
        $this->_errors = [];
        foreach ((new Validator)->validate($this, $this->rules()) as $attribute => $result) {
            if (!$result->isValid()) {
                $this->_errors[$attribute] = $result->getErrors();
            }
        }
        return empty($this->_errors);
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeValue(string $attribute)
    {
        return $this->getAttribute($attribute);
    }

    /**
     * @return Rule[][] $rules
     */
    abstract protected function rules(): array;
}
