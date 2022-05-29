<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\SpecProvider;

use OAS\OpenApiDocument;
use OAS\Bridge\SymfonyBundle\SpecProvider as SpecProviderInterface;

class CachedSpecProvider extends SpecProvider implements SpecProviderInterface
{
    private static array $cache = [];

    public function get(string $name = null, string $partial = null): OpenApiDocument
    {
        if (!array_key_exists($name, self::$cache)) {
            self::$cache[$name] = parent::get($name, $partial);

        }

        return self::$cache[$name];
    }
}
