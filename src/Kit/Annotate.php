<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Annotation\Aspect;
use Ep\Annotation\Configure;
use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Contract\AnnotationInterface;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Reader;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionFunction;

final class Annotate
{
    private Injector $injector;
    private Reader $reader;
    private CacheInterface $cache;
    private ?array $cacheData = null;

    public function __construct(
        ContainerInterface $container,
        Config $config,
        Reader $reader,
        CacheInterface $cache
    ) {
        $this->injector = new Injector($container);
        $this->reader = $reader;
        $this->cache = $cache;
        if (!$config->debug) {
            $this->cacheData = $cache->get(Constant::CACHE_ANNOTATION_INJECT_DATA) ?: [];
        }
    }

    public function class(object $instance): ?ReflectionClass
    {
        $reflectionClass = null;
        if ($this->cacheData === null) {
            $exists = true;
        } else {
            $exists = isset($this->cacheData[get_class($instance)][Target::TARGET_CLASS]);
        }

        if ($exists) {
            $reflectionClass = new ReflectionClass($instance);
            $annotations = $this->reader->getClassAnnotations($reflectionClass);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AnnotationInterface) {
                    $annotation->process($instance, $reflectionClass);
                }
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
            foreach ($this->cacheData[get_class($instance)][Target::TARGET_PROPERTY] ?? [] as $name => $v) {
                $properties[] = $reflectionClass->getProperty($name);
            }
        }

        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AnnotationInterface) {
                    $annotation->process($instance, $property, $arguments);
                }
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
            if (isset($this->cacheData[get_class($instance)][Target::TARGET_METHOD][$method])) {
                $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
            }
        }

        $callback = fn () => $this->injector->invoke([$instance, $method], $arguments);
        if (isset($reflectionMethod)) {
            $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Aspect) {
                    return $annotation->process($instance, new ReflectionFunction($callback), $arguments);
                } elseif ($annotation instanceof AnnotationInterface) {
                    $annotation->process($instance, $reflectionMethod, $arguments);
                }
            }
        }
        return $callback();
    }

    public function cache(array $classList, callable $callback = null): void
    {
        $injectData = $configureData = [];

        $setData = static function (array $annotations, string $class, string $name, int $type) use (&$injectData, &$configureData): void {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Configure) {
                    switch ($type) {
                        case Target::TARGET_CLASS:
                            $configureData[get_class($annotation)][$class][$type] = $annotation->getValues();
                            break;
                        case Target::TARGET_PROPERTY:
                        case Target::TARGET_METHOD:
                            $configureData[get_class($annotation)][$class][$type][] = array_merge($annotation->getValues(), ['target' => $name]);
                            break;
                    }
                } else {
                    switch ($type) {
                        case Target::TARGET_CLASS:
                            $injectData[$class][$type] = true;
                            break;
                        case Target::TARGET_PROPERTY:
                        case Target::TARGET_METHOD:
                            $injectData[$class][$type][$name] = true;
                            break;
                    }
                }
            }
        };

        foreach ($classList as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            $setData($this->reader->getClassAnnotations($reflectionClass), $class, $class, Target::TARGET_CLASS);

            foreach ($reflectionClass->getProperties() as $property) {
                $setData($this->reader->getPropertyAnnotations($property), $class, $property->getName(), Target::TARGET_PROPERTY);
            }

            foreach ($reflectionClass->getMethods() as $method) {
                $setData($this->reader->getMethodAnnotations($method), $class, $method->getName(), Target::TARGET_METHOD);
            }

            if ($callback !== null) {
                call_user_func($callback, $class);
            }
        }

        $this->cache->set(Constant::CACHE_ANNOTATION_INJECT_DATA, $injectData, 86400 * 365 * 100);
        $this->cache->set(Constant::CACHE_ANNOTATION_CONFIGURE_DATA, $configureData, 86400 * 365 * 100);
    }
}
