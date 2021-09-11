<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Annotation\Aspect;
use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Contract\AnnotationInterface;
use Doctrine\Common\Annotations\Reader;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionFunction;

final class Annotate
{
    private Reader $reader;
    private Injector $injector;
    private ?array $cacheData = null;

    public function __construct(
        ContainerInterface $container,
        Config $config,
        Reader $reader,
        CacheInterface $cache
    ) {
        $this->reader = $reader;
        $this->injector = new Injector($container);
        if (!$config->debug) {
            $this->cacheData = $cache->get(Constant::CACHE_ANNOTATION_DATA) ?: [];
        }
    }

    public function class(object $instance): ?ReflectionClass
    {
        $reflectionClass = null;
        if ($this->cacheData === null) {
            $exists = true;
        } else {
            $exists = isset($this->cacheData[get_class($instance)][AnnotationInterface::TYPE_CLASS]);
        }

        if ($exists) {
            $reflectionClass = new ReflectionClass($instance);
            $annotations = $this->reader->getClassAnnotations($reflectionClass);
            foreach ($annotations as $annotation) {
                /** @var AnnotationInterface $annotation */
                $annotation->process($instance, $reflectionClass);
            }
        }

        return $reflectionClass;
    }

    public function property(object $instance, array $arguments = []): void
    {
        $reflectionClass = $this->class($instance) ?? new ReflectionClass($instance);

        if ($this->cacheData === null) {
            $properties = $reflectionClass->getProperties();
        } else {
            $properties = [];
            foreach ($this->cacheData[get_class($instance)][AnnotationInterface::TYPE_PROPERTY] ?? [] as $name => $v) {
                $properties[] = $reflectionClass->getProperty($name);
            }
        }

        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                /** @var AnnotationInterface $annotation */
                $annotation->process($instance, $property, $arguments);
            }
        }
    }

    /**
     * @return mixed
     */
    public function method(object $instance, string $method, array $arguments = [])
    {
        if ($this->cacheData === null) {
            $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
        } else {
            if (isset($this->cacheData[get_class($instance)][AnnotationInterface::TYPE_METHOD][$method])) {
                $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
            }
        }

        $callback = fn () => $this->injector->invoke([$instance, $method], $arguments);
        if (isset($reflectionMethod)) {
            $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
            foreach ($annotations as $annotation) {
                /** @var AnnotationInterface $annotation */
                if ($annotation instanceof Aspect) {
                    return $annotation->process($instance, new ReflectionFunction($callback), $arguments);
                } else {
                    $annotation->process($instance, $reflectionMethod, $arguments);
                }
            }
        }
        return $callback();
    }

    public function getPrepareData(string $id): array
    {
        // todo
        return [];
    }
}
