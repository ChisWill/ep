<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Auth\AuthRepository;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Component\Identity;
use Ep\Tests\App\Component\SessionAuthMethod;
use Ep\Web\ServerRequest;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\QueryParameter;
use Yiisoft\Auth\Middleware\Authentication;

class UserController extends Controller
{
    public function __construct(AuthRepository $authRepository)
    {
        $this->setMiddlewares([
            $authRepository->find(SessionAuthMethod::class)
        ]);
    }

    public function infoAction(ServerRequest $request)
    {
        /** @var Identity */
        $identity = $request->getAttribute(Authentication::class);

        return $this->json([
            'id' => $identity->getId(),
            'all' => $identity->getAll()
        ]);
    }
}
