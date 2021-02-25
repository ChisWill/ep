<?php

use Ep\Console\ConsoleRequest;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Helper\Alias;
use Ep\Web\MiddlewareStack;
use Ep\Web\ServerRequestFactory;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory as HttpSoftServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\LazyConnectionDependencies;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactory;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactoryInterface;
use Yiisoft\Middleware\Dispatcher\MiddlewareStackInterface;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Event\ListenerCollectionFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;

$config = Ep::getConfig();

return [
    // Console
    ConsoleRequestInterface::class => ConsoleRequest::class,
    // HttpMiddleware
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    MiddlewareStackInterface::class => MiddlewareStack::class,
    // ServerRequest
    ServerRequestFactoryInterface::class => [
        '__class' => ServerRequestFactory::class,
        '__construct()' => [new HttpSoftServerRequestFactory()]
    ],
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    // Response
    ResponseFactoryInterface::class => ResponseFactory::class,
    // Logger
    FileTarget::class => [
        '__class' => FileTarget::class,
        '__construct()' => [
            Alias::get($config->runtimeDir . '/logs/app.log'),
            new FileRotator()
        ]
    ],
    LoggerInterface::class => static fn (FileTarget $fileTarget) => new Logger([$fileTarget]),
    // Cache
    CacheInterface::class => static fn () => new Cache(new FileCache(Alias::get($config->runtimeDir . '/cache'))),
    // Profiler
    ProfilerInterface::class => Profiler::class,
    // Event
    ListenerProviderInterface::class => static fn (ContainerInterface $container) => new Provider($container->get(ListenerCollectionFactory::class)->create($config->getEvents())),
    EventDispatcherInterface::class => Dispatcher::class,
    // Default DB
    Connection::class => static function (ContainerInterface $container) use ($config) {
        $connection = new MysqlConnection(
            $config->mysqlDsn,
            new LazyConnectionDependencies($container)
        );
        $connection->setUsername($config->mysqlUsername);
        $connection->setPassword($config->mysqlPassword);

        return $connection;
    },
    // Default Redis
    RedisConnection::class => [
        '__class' => RedisConnection::class,
        'host()' => [$config->redisHost],
        'port()' => [$config->rediPort],
        'database()' => [$config->redisDatabase],
        'password()' => [$config->redisPassword]
    ]
];