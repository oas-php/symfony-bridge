<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Annotation\Reader;

use Closure;
use Doctrine\Common\Annotations\Reader as DoctrineReader;
use LogicException;
use OAS\Bridge\SymfonyBundle\Annotation\Reader;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

class AnnotationReader implements Reader
{
    private DoctrineReader $reader;

    public function __construct(DoctrineReader $reader)
    {
        $this->reader = $reader;
    }

    public function getClassAnnotations(ReflectionClass $class): array
    {
        $annotations = $this->reader->getClassAnnotations($class);

        if (80000 <= \PHP_VERSION_ID) {
            $annotations = array_merge(
                $annotations,
                $this->instantiateAttributes(
                    $class->getAttributes()
                )
            );
        }

        return $annotations;
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        $annotation = $this->reader->getClassAnnotation($class, $annotationName);

        // fallback to PHP 8 attributes
        if (is_null($annotation) && 80000 <= \PHP_VERSION_ID) {
            $attributes = $this->filterAttributes(
                $class->getAttributes(),
                $annotationName
            );

            return empty($attributes) ? null : $attributes[0]->newInstance();
        }

        return $annotation;
    }

    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        $annotations = $this->reader->getMethodAnnotations($method);

        if (80000 <= \PHP_VERSION_ID) {
            $annotations = array_merge(
                $annotations,
                $this->instantiateAttributes(
                    $method->getAttributes()
                )
            );
        }

        return $annotations;
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        $annotation = $this->reader->getMethodAnnotation($method, $annotationName);

        // fallback to PHP 8 attributes
        if (is_null($annotation) && 80000 <= \PHP_VERSION_ID) {
            $attributes = $this->filterAttributes(
                $method->getAttributes(),
                $annotationName
            );

            return empty($attributes) ? null : $attributes[0]->newInstance();
        }

        return $annotation;
    }

    public function getPropertyAnnotations(ReflectionProperty $property): array
    {
        $annotations = $this->reader->getPropertyAnnotations($property);

        if (80000 <= \PHP_VERSION_ID) {
            $annotations = array_merge(
                $annotations,
                $this->instantiateAttributes(
                    $property->getAttributes()
                )
            );
        }

        return $annotations;
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        $annotation = $this->reader->getPropertyAnnotation($property, $annotationName);

        // fallback to PHP 8 attributes
        if (is_null($annotation) && 80000 <= \PHP_VERSION_ID) {
            $attributes = $this->filterAttributes(
                $property->getAttributes(),
                $annotationName
            );

            return empty($attributes) ? null : $attributes[0]->newInstance();
        }

        return $annotation;
    }

    public function getCallableAnnotations(callable $callable): array
    {
        switch (true) {
            case is_string($callable) :
                $callableReflection = strpos($callable, '::')
                    ? new ReflectionMethod($callable)
                    : new ReflectionFunction($callable);

                break;

            case $callable instanceof Closure:
                $callableReflection = new ReflectionFunction($callable);
                break;

            case is_object($callable):
                $callableReflection = new ReflectionMethod($callable, '__invoke');
                break;

            case is_array($callable):
                $callableReflection = new ReflectionMethod(...$callable);
                break;

            default:
                // this should never happen!
                throw new LogicException('Unrecognized callable type.');
        }

        if ($callableReflection instanceof ReflectionFunction) {
            $annotations = method_exists($this->reader, 'getFunctionAnnotations')
                ? $this->reader->getFunctionAnnotations($callableReflection)
                : [];

            if (80000 <= \PHP_VERSION_ID) {
                $annotations = array_merge(
                    $annotations,
                    $this->instantiateAttributes(
                        $callableReflection->getAttributes()
                    )
                );
            }

            return $annotations;
        }

        return $this->getMethodAnnotations($callableReflection);
    }

    public function getCallableAnnotation(callable $callable, string $annotationClass)
    {
        foreach ($this->getCallableAnnotations($callable) as $annotation) {
            if ($annotation instanceof $annotationClass) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param ReflectionAttribute[] $attributes
     * @param string $attributeClass
     * @return ReflectionAttribute[]
     */
    private function filterAttributes(array $attributes, string $attributeClass): array
    {
        return array_values(
            array_filter(
                $attributes,
                fn (ReflectionAttribute $attribute ) => $attributeClass == $attribute->getName()
            )
        );
    }

    /**
     * @param ReflectionAttribute[] $attributes
     * @return array
     */
    private function instantiateAttributes(array $attributes): array
    {
        return array_map(
            fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
            $attributes
        );
    }
}