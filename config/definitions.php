<?php

use Ep\Base\Route;
use Ep\Helper\Alias;
use Ep\Web\ServerRequestFactory;
use Ep\Standard\RouteInterface;
use Ep\Standard\ServerRequestFactoryInterface;
use HttpSoft\Message\UriFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\ResponseFactory;
use Yiisoft\Db\Connection\ConnectionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\LazyConnectionDependencies;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileRotatorInterface;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;

return [
    // Route
    RouteInterface::class => Route::class,
    // ServerRequest
    ServerRequestFactoryInterface::class => ServerRequestFactory::class,
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    // Response
    ResponseFactoryInterface::class => ResponseFactory::class,
    // Logger
    FileTarget::class => static fn () => new FileTarget(Alias::get(Ep::getConfig()->runtimeDir . '/app.log')),
    LoggerInterface::class => static fn (FileTarget $fileTarget) => new Logger([$fileTarget]),
    // Default DB
    CacheInterface::class => [
        '__class' => Cache::class,
        '__construct()' => [new ArrayCache]
    ],
    ProfilerInterface::class => Profiler::class,
    ConnectionInterface::class => static function (ContainerInterface $container) {
        $config = Ep::getConfig();
        $connection = new Connection(
            $config->mysqlDsn,
            new LazyConnectionDependencies($container)
        );

        $connection->setUsername($config->mysqlUsername);
        $connection->setPassword($config->mysqlPassword);

        return $connection;
    }
];
