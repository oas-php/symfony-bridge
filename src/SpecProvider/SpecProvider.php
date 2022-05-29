<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\SpecProvider;

use OAS\Bridge\SymfonyBundle\Configuration;
use OAS\Document\Factory\Factory;
use OAS\OpenApiDocument;
use OAS\Resolver\Resolver;
use OAS\Bridge\SymfonyBundle\SpecProvider as SpecProviderInterface;

class SpecProvider implements SpecProviderInterface
{
    private Configuration $configuration;
    private Factory $factory;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->factory = new Factory(new Resolver);
    }

    public function get(string $name = null, string $partial = null): OpenApiDocument
    {
        $specMetadata = $this->configuration->getSpecMetadata($name);

        if (is_string($partial)) {
            $cacheFilepathPartial = $this->configuration->getCacheFilepathPartial($specMetadata, $partial);
            $isPartialCached = $specMetadata->cachePartials() && file_exists($cacheFilepathPartial);

            if ($isPartialCached) {
                return include $cacheFilepathPartial;
            }
        }

        $cacheFilepath = $this->configuration->getCacheFilepath($specMetadata);
        $isCached = $specMetadata->cache() && file_exists($cacheFilepath);

        if ($isCached) {
            return include $cacheFilepath;
        }

        $isResolved = $specMetadata->isResolvable() && file_exists($specMetadata->getResolvePath());

        if ($isResolved) {
            return $this->factory->createFromPrimitives(
                file_get_contents(json_decode($specMetadata->getResolvePath()))
            );
        }

        return $this->factory->createFromUri(
            $specMetadata->getIndexPath()
        );
    }
}
