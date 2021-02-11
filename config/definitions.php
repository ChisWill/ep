<?php

use Ep\Base\Route;
use Ep\Console\ConsoleRequest;
use Ep\Helper\Alias;
use Ep\Standard\ConsoleRequestInterface;
use Ep\Web\ServerRequestFactory;
use Ep\Standard\RouteInterface;
use Ep\Standard\ServerRequestFactoryInterface as EpServerRequestFactoryInterface;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Connection\LazyConnectionDependencies;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Event\ListenerCollectionFactory;
use Yiisoft\Yii\Web\ServerRequestFactory as YiiServerRequestFactory;
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
    // Route
    RouteInterface::class => Route::class,
    // Console
    ConsoleRequestInterface::class => ConsoleRequest::class,
    // ServerRequest
    EpServerRequestFactoryInterface::class => YiiServerRequestFactory::class,
    ServerRequestFactoryInterface::class => ServerRequestFactory::class,
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    // Response
    ResponseFactoryInterface::class => ResponseFactory::class,
    // Logger
    FileTarget::class => static fn () => new FileTarget(Alias::get($config->runtimeDir . '/logs/app.log')),
    LoggerInterface::class => static fn (FileTarget $fileTarget) => new Logger([$fileTarget]),
    // Cache
    CacheInterface::class => static fn () => new Cache(new FileCache(Alias::get($config->runtimeDir . '/cache'))),
    // Profiler
    ProfilerInterface::class => Profiler::class,
    // Event
    ListenerProviderInterface::class => static fn (ContainerInterface $container) => new Provider($container->get(ListenerCollectionFactory::class)->create($config->getEvents())),
    EventDispatcherInterface::class => Dispatcher::class,
    // Default DB
    ConnectionInterface::class => static function (ContainerInterface $container) use ($config) {
        $connection = new Connection(
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
