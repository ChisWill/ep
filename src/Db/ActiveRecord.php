<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Ep\Helper\Date;
use Ep\Widget\FormTrait;
use Yiisoft\ActiveRecord\ActiveRecord as BaseActiveRecord;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Http\Method;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Strings\StringHelper;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

abstract class ActiveRecord extends BaseActiveRecord implements DataSetInterface
{
    use FormTrait;

    public const PK = 'id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const YES = 1;
    public const NO = -1;

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
     * @throws UnexpectedValueException
     */
    public static function findModel($condition)
    {
        if (empty($condition)) {
            return new static;
        } else {
            if (is_scalar($condition)) {
                $condition = [static::PK => $condition];
            }
            $model = static::find()->where($condition)->one();
            if ($model === null) {
                throw new UnexpectedValueException("Data is not found.");
            }
            return $model;
        }
    }

    public function validateSave(?array $attributeNames = null): bool
    {
        if ($this->validate()) {
            return parent::save($attributeNames);
        } else {
            return false;
        }
    }

    public function insert(?array $attributes = null): bool
    {
        foreach (array_intersect($this->attributes(), [static::CREATED_AT, static::UPDATED_AT]) as $field) {
            $this->$field = Date::fromUnix();
        }
        return parent::insert($attributes);
    }

    public function update(?array $attributeNames = null)
    {
        if (in_array(static::UPDATED_AT, $this->attributes())) {
            $this->{static::UPDATED_AT} = Date::fromUnix();
        }
        return parent::update($attributeNames);
    }

    public function load(ServerRequestInterface $request, string $scope = null): bool
    {
        if ($request->getMethod() === Method::POST) {
            if ($scope === '') {
                $this->setAttributes($request->getParsedBody());
            } else {
                $scope ??= StringHelper::baseName(static::class);
                $this->setAttributes($request->getParsedBody()[$scope] ?? []);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeValue(string $attribute)
    {
        return $this->getAttribute($attribute);
    }
}
