<?php

declare(strict_types=1);

use Ep\Auth\AuthRepository;
use Ep\Base\Config;
use Ep\Base\Injector;
use Ep\Contract\ConsoleErrorRendererInterface;
use Ep\Contract\InterceptorInterface;
use Ep\Contract\WebErrorRendererInterface;
use Ep\Tests\App\Component\AuthFailHandler;
use Ep\Tests\App\Component\ConsoleRenderer;
use Ep\Tests\App\Component\WebErrorRenderer;
use Ep\Tests\App\Component\Interceptor;
use Ep\Tests\App\Component\SessionAuthMethod;
use Ep\Tests\App\Component\SessionRepository;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Db\Sqlite\Connection as SqliteConnection;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target;
use Yiisoft\Log\Target\File\FileTarget;

return static fn (Config $config): array => [
    WebErrorRendererInterface::class => WebErrorRenderer::class,
    ConsoleErrorRendererInterface::class => ConsoleRenderer::class,
    InterceptorInterface::class => Interceptor::class,
    IdentityRepositoryInterface::class => SessionRepository::class,
    AuthRepository::class => static function (ContainerInterface $container, Injector $injector): AuthRepository {
        return $injector->make(AuthRepository::class, [
            'authMethods' => [SessionAuthMethod::class],
            'failureHandlers' => [SessionAuthMethod::class => $container->get(AuthFailHandler::class)]
        ]);
    },
    // Log
    Target::class => static fn (Aliases $aliases): FileTarget => new FileTarget($aliases->get($config->runtimeDir . '/logs/' . date('Y-m-d') . '.log')),
    'alert' => static fn (Aliases $aliases): LoggerInterface => new Logger([new FileTarget($aliases->get($config->runtimeDir) . '/alerts/' . date('Ymd') . '.log')]),
    // Sqlite
    'sqlite' => [
        'class' => SqliteConnection::class,
        '__construct()' => ['sqlite:' . dirname(__FILE__) . '/ep.sqlite'],
    ],
    // Redis
    RedisConnection::class => [
        'class' => RedisConnection::class,
        'hostname()' => [$config->params['db']['redis']['hostname']],
        'database()' => [$config->params['db']['redis']['database']],
        'password()' => [$config->params['db']['redis']['password']],
        'port()'     => [$config->params['db']['redis']['port']]
    ],
    // Mysql
    Connection::class => [
        'class' => MysqlConnection::class,
        '__construct()' => [$config->params['db']['mysql']['dsn']],
        'setUsername()' => [$config->params['db']['mysql']['username']],
        'setPassword()' => [$config->params['db']['mysql']['password']]
    ]
];
