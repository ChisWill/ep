<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Contract\AnnotationInterface;
use Ep\Kit\Annotate;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

final class ScanService extends Service
{
    private Reader $reader;
    private CacheInterface $cache;

    public function __construct(
        ContainerInterface $container,
        Reader $reader,
        CacheInterface $cache
    ) {
        parent::__construct($container);

        $this->reader = $reader;
        $this->cache = $cache;
    }

    public function annotation(): void
    {
        $classes = array_map([$this, 'getClassNameByFile'], $this->findClassFiles($this->getAppPath(), $this->options['ignore']));

        $this->consoleService->progress(fn ($progressBar) => $this->cacheAnnotations($classes, $progressBar), count($classes));

        $this->consoleService->writeln();
    }

    private function cacheAnnotations(array $classes, ProgressBar $progressBar): void
    {
        $data = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                continue;
            }
            $reflectionClass = new ReflectionClass($class);
            $properties = $reflectionClass->getProperties();
            $methods = $reflectionClass->getMethods();
            if ($this->reader->getClassAnnotations($reflectionClass)) {
                $data[$class][AnnotationInterface::TYPE_CLASS] = 1;
            }
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
            $this->cache->set(Annotate::getAnnotationCacheKey($class), $value, 86400 * 365 * 50);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getId(): ?string
    {
        return null;
    }
}
