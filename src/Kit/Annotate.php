<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Annotation\Aspect;
use Ep\Base\Config;
use Ep\Contract\AnnotationInterface;
use Doctrine\Common\Annotations\Reader;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionFunction;

final class Annotate
{
    private Config $config;
    private Reader $reader;
    private CacheInterface $cache;
    private Injector $injector;

    public function __construct(
        ContainerInterface $container,
        Config $config,
        Reader $reader,
        CacheInterface $cache
    ) {
        $this->config = $config;
        $this->reader = $reader;
        $this->cache = $cache;
        $this->injector = new Injector($container);
    }

    private array $map = [];

    public function class(object $instance): ?ReflectionClass
    {
        $reflectionClass = null;
        if ($this->config->debug) {
            $exists = true;
        } else {
            $class = get_class($instance);
            if (!isset($this->map[$class])) {
                $this->map[$class] = $this->cache->get(self::getAnnotationCacheKey($class), []);
            }
            $exists = isset($this->map[$class][AnnotationInterface::TYPE_CLASS]);
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

        if ($this->config->debug) {
            $properties = $reflectionClass->getProperties();
        } else {
            $class = get_class($instance);
            if (!isset($this->map[$class])) {
                $this->map[$class] = $this->cache->get(self::getAnnotationCacheKey($class), []);
            }
            $properties = [];
            foreach ($this->map[$class][AnnotationInterface::TYPE_PROPERTY] ?? [] as $name => $v) {
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
        if ($this->config->debug) {
            $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
        } else {
            $class = get_class($instance);
            if (!isset($this->map[$class])) {
                $this->map[$class] = $this->cache->get(self::getAnnotationCacheKey($class), []);
            }
            if (isset($this->map[$class][AnnotationInterface::TYPE_METHOD][$method])) {
                $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
            }
        }

        $fn = fn () => $this->injector->invoke([$instance, $method], $arguments);
        if (isset($reflectionMethod)) {
            $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
            foreach ($annotations as $annotation) {
                /** @var AnnotationInterface $annotation */
                $result = $annotation->process($instance, new ReflectionFunction($fn), $arguments);
                if ($result === false) {
                    return false;
                }
                if ($annotation instanceof Aspect) {
                    $returnValue = $result;
                }
            }
            if (isset($returnValue)) {
                return $returnValue;
            }
        }
        return $fn();
    }

    public static function getAnnotationCacheKey(string $class): string
    {
        return 'Ep-Annotation-' . rawurlencode($class);
    }
}
