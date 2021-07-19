<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Base\Container;
use Ep\Base\Injector;
use Ep\Console\Application as ConsoleApplication;
use Ep\Console\CommandLoader;
use Ep\Console\EventDispatcher;
use Ep\Contract\ErrorRendererInterface;
use Ep\Contract\InjectorInterface;
use Ep\Contract\NotFoundHandlerInterface;
use Ep\Web\ErrorRenderer;
use Ep\Web\NotFoundHandler;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoader;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetPublisher;
use Yiisoft\Assets\AssetPublisherInterface;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface as YiiCacheInterface;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\ListenerCollection;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Factory\Definition\Reference;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Yii\Event\ListenerCollectionFactory;
use Psr\Cache\CacheItemPoolInterface;
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

/** @var Config $config */

return [
    // Base
    ContainerInterface::class => Container::class,
    InjectorInterface::class => Injector::class,
    Config::class => $config,
    Aliases::class => new Aliases([
        '@root' => $config->rootPath,
        '@vendor' => $config->vendorPath,
        '@ep' => dirname(__DIR__, 1)
    ] + $config->aliases),
    // Annotation
    Reader::class => static fn (CacheItemPoolInterface $cache): Reader => $config->debug ? new AnnotationReader() : new PsrCachedReader(new AnnotationReader(), $cache, false),
    // Console
    ConsoleApplication::class => [
        'class' => ConsoleApplication::class,
        'setAutoExit()' => [false],
        'setCommandLoader()' => [Reference::to(CommandLoaderInterface::class)],
        'setDispatcher()' => [Reference::to(SymfonyEventDispatcherInterface::class)],
        'setHelperSet()' => [
            new HelperSet([new QuestionHelper()])
        ]
    ],
    CommandLoaderInterface::class => CommandLoader::class,
    SymfonyEventDispatcherInterface::class => EventDispatcher::class,
    InputInterface::class => static fn (): InputInterface => new ArgvInput(null, null),
    OutputInterface::class => ConsoleOutput::class,
    // View
    AssetLoaderInterface::class => AssetLoader::class,
    AssetPublisherInterface::class => AssetPublisher::class,
    // Session
    SessionInterface::class => static fn (): SessionInterface => new Session(['cookie_secure' => 0]),
    // ServerRequest
    ServerRequestFactoryInterface::class => ServerRequestFactory::class,
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    // Response
    ResponseFactoryInterface::class => ResponseFactory::class,
    // Logger
    Target::class => static fn (Aliases $aliases): Target => new FileTarget($aliases->get($config->runtimeDir . '/logs/app.log'), new FileRotator()),
    LoggerInterface::class => [
        'class' => Logger::class,
        '__construct()' => [[Reference::to(Target::class)]]
    ],
    // Cache
    CacheItemPoolInterface::class => static fn (Aliases $aliases): CacheItemPoolInterface => new FilesystemAdapter('item-caches', 0, $aliases->get($config->runtimeDir)),
    CacheInterface::class => static fn (Aliases $aliases): CacheInterface => new FileCache($aliases->get($config->runtimeDir . '/simple-caches')),
    YiiCacheInterface::class => Cache::class,
    // Profiler
    ProfilerInterface::class => Profiler::class,
    // Event
    ListenerCollection::class => static fn (ListenerCollectionFactory $listenerCollectionFactory): ListenerCollection => $listenerCollectionFactory->create($config->events),
    ListenerProviderInterface::class => Provider::class,
    EventDispatcherInterface::class => Dispatcher::class,
    // Default ErrorRenderer
    ErrorRendererInterface::class => ErrorRenderer::class,
    // Default NotFoundHandler
    NotFoundHandlerInterface::class => NotFoundHandler::class,
];
