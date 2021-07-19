<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Contract\InterceptorInterface;
use Ep\Contract\WebErrorRendererInterface;
use Ep\Tests\App\Component\ErrorRenderer;
use Ep\Tests\App\Component\Interceptor;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Db\Sqlite\Connection as SqliteConnection;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target;
use Yiisoft\Log\Target\File\FileTarget;

return static fn (Config $config): array => [
    WebErrorRendererInterface::class => ErrorRenderer::class,
    InterceptorInterface::class => Interceptor::class,
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
