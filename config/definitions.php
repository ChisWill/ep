<?php

declare(strict_types=1);

use Ep\Base\Route;
use Ep\Console\ConsoleRequest;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ErrorRendererInterface;
use Ep\Contract\NotFoundHandlerInterface;
use Ep\Helper\Alias;
use Ep\Web\ErrorRenderer;
use Ep\Web\NotFoundHandler;
use Ep\Web\ServerRequestFactory;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory as HttpSoftServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\LazyConnectionDependencies;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\ListenerCollection;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
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
use Psr\SimpleCache\CacheInterface;

$config = Ep::getConfig();

return [
    // Console
    ConsoleRequestInterface::class => ConsoleRequest::class,
    // HttpMiddleware
    Route::class => static fn () => new Route($config->getRouteRule(), $config->baseUrl),
    // Session
    SessionInterface::class => [
        '__class' => Session::class,
        '__construct()' => [
            ['cookie_secure' => 0]
        ]
    ],
    // ServerRequest
    ServerRequestFactoryInterface::class => [
        '__class' => ServerRequestFactory::class,
        '__construct()' => [
            new HttpSoftServerRequestFactory()
        ]
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
    LoggerInterface::class => static fn (FileTarget $fileTarget): LoggerInterface => new Logger([$fileTarget]),
    // Cache
    CacheInterface::class => static fn (): CacheInterface => new FileCache(Alias::get($config->runtimeDir . '/cache')),
    YiiCacheInterface::class => Cache::class,
    // Profiler
    ProfilerInterface::class => Profiler::class,
    // Event
    ListenerCollection::class => static fn (ContainerInterface $container): ListenerCollection => $container->get(ListenerCollectionFactory::class)->create($config->events),
    ListenerProviderInterface::class => Provider::class,
    EventDispatcherInterface::class => Dispatcher::class,
    // Default ErrorRenderer
    ErrorRendererInterface::class => ErrorRenderer::class,
    // Default NotFoundHandler
    NotFoundHandlerInterface::class => NotFoundHandler::class,
    // Default DB
    Connection::class => static function (ContainerInterface $container) use ($config): Connection {
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
        'hostname()' => [$config->redisHost],
        'port()' => [$config->rediPort],
        'database()' => [$config->redisDatabase],
        'password()' => [$config->redisPassword]
    ]
];
