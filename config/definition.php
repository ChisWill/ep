<?php

use Ep\Tests\Cls\Car;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\UriFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\StreamFactory;

return [
    ServerRequestFactoryInterface::class => ServerRequestFactory::class,
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    Car::class => Car::class
    // LoggerInterface::class => [
    //     '__class' => Logger::class,
    // ],
    // CacheInterface::class => [
    //     '__class' => Cache::class,
    //     '__construct()' => [new ArrayCache]
    // ],
    // 'db' => function (ContainerInterface $container) {
    //     $connection = new Connection(
    //         $container->get(LoggerInterface::class),
    //         $container->get(Profiler::class),
    //         $container->get(QueryCache::class),
    //         $container->get(SchemaCache::class),
    //         $this->mysql['dsn']
    //     );

    //     $connection->setUsername($this->mysql['username']);
    //     $connection->setPassword($this->mysql['password']);

    //     return $connection;
    // }
];
