<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Base\Constant;
use Ep\Contract\AnnotationInterface;
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

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
    }

    public function annotation(): void
    {
        $classes = array_map([$this, 'getClassNameByFile'], $this->findClassFiles($this->getAppPath(), $this->request->getOption('ignore')));

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
            // todo
            $reflectionClass = new ReflectionClass($class);
            if ($this->reader->getClassAnnotations($reflectionClass)) {
                $data[$class][AnnotationInterface::TYPE_CLASS] = 1;
            }
            foreach ($reflectionClass->getProperties() as $property) {
                if ($this->reader->getPropertyAnnotations($property)) {
                    $data[$class][AnnotationInterface::TYPE_PROPERTY][$property->getName()] = 1;
                }
            }
            foreach ($reflectionClass->getMethods() as $method) {
                if ($this->reader->getMethodAnnotations($method)) {
                    $data[$class][AnnotationInterface::TYPE_METHOD][$method->getName()] = 1;
                }
            }
            $progressBar->advance();
        }

        $this->cache->set(Constant::CACHE_ANNOTATION_DATA, $data, 86400 * 365 * 100);
    }
}
