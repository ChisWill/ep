<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Ep\Exception\NotFoundException;
use Ep\Helper\Date;
use Ep\Helper\Str;
use Ep\Helper\System;
use Ep\Widget\FormTrait;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecord as YiiActiveRecord;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Http\Method;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Strings\StringHelper;
use Psr\Http\Message\ServerRequestInterface;

abstract class ActiveRecord extends YiiActiveRecord implements DataSetInterface
{
    use FormTrait;

    public const PK = 'id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const YES = 1;
    public const NO = -1;

    public function __construct(ConnectionInterface $db = null)
    {
        parent::__construct($db ?? Ep::getDb());
    }

    public static function find(ConnectionInterface $db = null): ActiveQuery
    {
        return (new ActiveQuery(static::class, $db ?? Ep::getDb()))
            ->alias(static::getAlias());
    }

    /**
     * @param  int|string $pk
     * 
     * @return static|null
     */
    public static function findOne($pk, ConnectionInterface $db = null)
    {
        return static::find($db)
            ->where([static::PK => $pk])
            ->one();
    }

    public static function findAll(array $condition, ConnectionInterface $db = null): array
    {
        return static::find($db)
            ->where($condition)
            ->all();
    }

    /**
     * @param  int|string|array|ExpressionInterface $condition
     * 
     * @return static
     * @throws NotFoundException
     */
    public static function findModel($condition, ConnectionInterface $db = null)
    {
        if (empty($condition)) {
            return new static($db);
        } else {
            if (is_scalar($condition) && is_string(static::PK)) {
                $condition = [static::PK => $condition];
            }

            $model = static::find($db)
                ->where($condition)
                ->one();
            if ($model === null) {
                throw new NotFoundException('Data does not exists.');
            }

            return $model;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasOne($class, array $link): ActiveQueryInterface
    {
        return parent::hasOne($class, $link)
            ->alias(lcfirst(Str::ltrim(System::getCallerMethod(), 'get')));
    }

    /**
     * {@inheritDoc}
     */
    public function hasMany($class, array $link): ActiveQueryInterface
    {
        return parent::hasMany($class, $link)
            ->alias(lcfirst(Str::ltrim(System::getCallerMethod(), 'get')));
    }

    /**
     * {@inheritDoc}
     */
    public function save(array $attributeNames = null): bool
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
    public function insert(array $attributes = null): bool
    {
        foreach (array_intersect($this->attributes(), [static::CREATED_AT, static::UPDATED_AT]) as $field) {
            $this->$field = Date::fromUnix();
        }
        return parent::insert($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function update(array $attributeNames = null)
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
