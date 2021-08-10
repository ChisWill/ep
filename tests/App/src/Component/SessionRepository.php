<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Db\Query;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

final class SessionRepository implements IdentityRepositoryInterface
{
    public function findIdentity(string $id): ?IdentityInterface
    {
        $r = Query::find(Ep::getDb('sqlite'))
            ->from('student')
            ->where(['id' => $id])
            ->one();
        if ($r) {
            return new Identity($r);
        } else {
            return null;
        }
    }
}
