<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Contract\AnnotationInterface;
use Ep\Kit\CacheKey;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class ScanService extends Service
{
    private Reader $reader;
    private CacheItemPoolInterface $cache;
    private CacheKey $cacheKey;

    public function __construct(
        ContainerInterface $container,
        Reader $reader,
        CacheItemPoolInterface $cache,
        CacheKey $cacheKey
    ) {
        parent::__construct($container);

        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    public function annotation(): void
    {
        $classes = array_map([$this, 'getClassNameByFile'], $this->findClassFiles($this->getAppPath()));

        $this->consoleService->progress(fn ($progressBar) => $this->cacheAnnotations($classes, $progressBar), count($classes));

        $this->consoleService->writeln();
    }

    private function cacheAnnotations(array $classes, ProgressBar $progressBar): void
    {
        $data = [];
        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);
            $properties = $reflectionClass->getProperties();
            $methods = $reflectionClass->getMethods();
            foreach ($properties as $property) {
                if ($this->reader->getPropertyAnnotations($property)) {
                    $data[$class][AnnotationInterface::TYPE_PROPERTY][$property->getName()] = 1;
                }
            }
            foreach ($methods as $method) {
                if ($this->reader->getMethodAnnotations($method)) {
                    $data[$class][AnnotationInterface::TYPE_METHOD][$method->getName()] = 1;
                }
            }
            $progressBar->advance();
        }

        foreach ($data as $class => $value) {
            $this->cache->save(
                $this->cache->getItem(
                    $this->cacheKey->classAnnotation($class)
                )->set($value)
            );
        }
    }
}
