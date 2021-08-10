<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Session\SessionInterface;

final class SessionAuthMethod implements AuthenticationMethodInterface
{
    private IdentityRepositoryInterface $identityRepository;
    private SessionInterface $session;

    public function __construct(IdentityRepositoryInterface $identityRepository, SessionInterface $session)
    {
        $this->identityRepository = $identityRepository;
        $this->session = $session;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $id = $this->session->get('id');
        if (!$id) {
            return null;
        }
        return $this->identityRepository->findIdentity($id);
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
