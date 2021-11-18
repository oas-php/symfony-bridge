<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Annotation;

use Doctrine\Common\Annotations\Reader as DoctrineReader;

interface Reader extends DoctrineReader
{
    public function getCallableAnnotations(callable $callable): array;

    public function getCallableAnnotation(callable $callable, string $annotationClass);
}