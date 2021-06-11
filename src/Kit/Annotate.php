<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use Ep\Contract\AnnotationInterface;
use Doctrine\Common\Annotations\Reader;
use Ep\Annotation\Aspect;
use Yiisoft\Injector\Injector;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionFunction;

final class Annotate
{
    private Config $config;
    private Reader $reader;
    private CacheItemPoolInterface $cache;
    private Injector $injector;

    public function __construct(Config $config, Reader $reader, CacheItemPoolInterface $cache, Injector $injector)
    {
        $this->config = $config;
        $this->reader = $reader;
        $this->cache = $cache;
        $this->injector = $injector;
    }

    public function property(object $instance, array $arguments = []): void
    {
        if ($this->config->debug) {
            $properties = (new ReflectionClass($instance))->getProperties();
        } else {
            $properties = [];
            $item = $this->cache->getItem(CacheKey::getAnnotationKey(get_class($instance)));
            if ($item->isHit()) {
                $result = $item->get();
                if (isset($result[AnnotationInterface::TYPE_PROPERTY])) {
                    foreach ($result[AnnotationInterface::TYPE_PROPERTY] as $name => $v) {
                        $properties[] = (new ReflectionClass($instance))->getProperty($name);
                    }
                }
            }
        }
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            /** @var AnnotationInterface $annotation */
            foreach ($annotations as $annotation) {
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
            $item = $this->cache->getItem(CacheKey::getAnnotationKey(get_class($instance)));
            if ($item->isHit() && isset($item->get()[AnnotationInterface::TYPE_METHOD][$method])) {
                $reflectionMethod = (new ReflectionClass($instance))->getMethod($method);
            }
        }

        $fn = fn () => $this->injector->invoke([$instance, $method], $arguments);
        if (isset($reflectionMethod)) {
            $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
            /** @var AnnotationInterface $annotation */
            foreach ($annotations as $annotation) {
                $result = $annotation->process($instance, new ReflectionFunction($fn), $arguments);
                if ($annotation instanceof Aspect) {
                    return $result;
                }
            }
        }
        return $fn();
    }
}
