<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Ep\Contract\NotFoundException;
use Ep\Helper\Date;
use Ep\Helper\Str;
use Ep\Helper\System;
use Ep\Widget\FormTrait;
use Yiisoft\ActiveRecord\ActiveQuery as BaseActiveQuery;
use Yiisoft\ActiveRecord\ActiveRecord as BaseActiveRecord;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Http\Method;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Strings\StringHelper;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\Connection;

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
        return (new ActiveQuery(static::class, $db ?: Ep::getDb()))->alias(static::getAlias());
    }

    /**
     * @param  mixed $pk
     * 
     * @return static|null
     */
    public static function findOne($pk)
    {
        return static::find()
            ->where([static::PK => $pk])
            ->one();
    }

    /**
     * @param  int|string|array|ExpressionInterface $condition
     * 
     * @return static
     * @throws NotFoundException
     */
    public static function findModel($condition, Connection $db = null)
    {
        if (empty($condition)) {
            return new static($db);
        } else {
            if (is_scalar($condition) && is_string(static::PK)) {
                $condition = [static::PK => $condition];
            }
            $model = static::find($db)->where($condition)->one();
            if ($model === null) {
                throw new NotFoundException("Data is not found.");
            }
            return $model;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasOne($class, array $link): BaseActiveQuery
    {
        return parent::hasOne($class, $link)
            ->alias(lcfirst(Str::ltrim(System::getCallerMethod(), 'get')));
    }

    /**
     * {@inheritDoc}
     */
    public function hasMany($class, array $link): BaseActiveQuery
    {
        return parent::hasMany($class, $link)
            ->alias(lcfirst(Str::ltrim(System::getCallerMethod(), 'get')));
    }

    /**
     * {@inheritDoc}
     */
    public function save(?array $attributeNames = null): bool
    {
        if ($this->validate()) {
            return parent::save($attributeNames);
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function insert(?array $attributes = null): bool
    {
        foreach (array_intersect($this->attributes(), [static::CREATED_AT, static::UPDATED_AT]) as $field) {
            $this->$field = Date::fromUnix();
        }
        return parent::insert($attributes);
    }

    /**
     * {@inheritDoc}
     */
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
                $data = $request->getParsedBody();
            } else {
                $scope ??= static::getAlias();
                $data = $request->getParsedBody()[$scope] ?? [];
            }
            $this->setAttributes(array_diff_key($data, array_flip($this->primaryKey())));
            return true;
        } else {
            return false;
        }
    }

    public static function getAlias(): string
    {
        return lcfirst(StringHelper::baseName(static::class));
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeValue(string $attribute)
    {
        return $this->getAttribute($attribute);
    }
}
