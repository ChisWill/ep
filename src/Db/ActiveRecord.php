<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Http\Method;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Rule;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Validator;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Result;
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
    public static function findModel($condition)
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

    public function load(ServerRequestInterface $request, ?string $scope = null): bool
    {
        if ($request->getMethod() === Method::POST) {
            $scope ??= StringHelper::baseName(static::class);
            $this->setAttributes($request->getParsedBody()[$scope] ?? []);
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

    protected function rule(callable $callback): Rule
    {
        return new class ($callback) extends Rule
        {
            /**
             * @param callback $callback
             */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            protected function validateValue($value, ?ValidationContext $context = null): Result
            {
                $result = new Result;
                call_user_func($this->callback, $value, $result, $context);
                return $result;
            }
        };
    }

    /**
     * @return Rule[][] $rules
     */
    abstract protected function rules(): array;
}
