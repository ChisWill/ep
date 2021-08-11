<?php

declare(strict_types=1);

namespace Ep\Auth;

use Ep\Contract\InjectorInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\Method\HttpBasic;
use Yiisoft\Auth\Method\HttpBearer;
use Yiisoft\Auth\Method\HttpHeader;
use Yiisoft\Auth\Method\QueryParameter;
use Yiisoft\Auth\Middleware\Authentication;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;

final class AuthRepository
{
    private ContainerInterface $container;
    private InjectorInterface $injector;

    public function __construct(
        ContainerInterface $container,
        InjectorInterface $injector
    ) {
        $this->container = $container;
        $this->injector = $injector;
    }

    private array $methods = [
        HttpBasic::class,
        HttpBearer::class,
        HttpHeader::class,
        QueryParameter::class
    ];
    private array $methodInstances = [];

    public function addMethod(string $method, AuthenticationMethodInterface $instance = null): self
    {
        if ($instance !== null) {
            $this->methodInstances[$method] = $instance;
        }
        $this->methods[] = $method;
        return $this;
    }

    private array $failureHandlers = [];

    /**
     * @param string|RequestHandlerInterface $handler
     */
    public function bindFailureHandler(string $method, $handler): self
    {
        $this->failureHandlers[$method] = $handler;
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function findMethod(string $method): AuthenticationMethodInterface
    {
        $this->check($method);

        if (!isset($this->methodInstances[$method])) {
            $this->methodInstances[$method] = $this->container->get($method);
        }
        return $this->methodInstances[$method];
    }

    private array $middlewareInstances = [];

    /**
     * @throws InvalidArgumentException
     */
    public function findMiddleware(string $method): MiddlewareInterface
    {
        $this->check($method);

        if (!isset($this->middlewareInstances[$method])) {
            $arguments = [
                $this->findMethod($method)
            ];
            if (isset($this->failureHandlers[$method])) {
                if (is_string($this->failureHandlers[$method])) {
                    $this->failureHandlers[$method] = $this->container->get($this->failureHandlers[$method]);
                }
                $arguments[] = $this->failureHandlers[$method];
            }
            $this->middlewareInstances[$method] = $this->injector->make(Authentication::class, $arguments);
        }

        return $this->middlewareInstances[$method];
    }

    private function check(string $method): void
    {
        if (!in_array($method, $this->methods)) {
            throw new InvalidArgumentException('Invalid authentication method.');
        }
    }
}
