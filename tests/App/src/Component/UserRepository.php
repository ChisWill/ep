<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Tests\App\Model\Student;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

final class UserRepository implements IdentityRepositoryInterface
{
    public function findIdentity(string $id): ?IdentityInterface
    {
        return Student::find(Ep::getDb('sqlite'))
            ->where(['id' => $id])
            ->one();
    }
}
