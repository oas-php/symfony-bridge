<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

use OAS\Document\Factory\Factory;
use OAS\OpenApiDocument;
use OAS\Resolver\Resolver;

class SpecProvider
{
    private Configuration $configuration;
    private Factory $factory;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->factory = new Factory(
            new Resolver
        );
    }

    public function get(string $name = null): OpenApiDocument
    {
        $specMetadata = $this->configuration->getSpecMetadata($name);

        // if cacheable? get from cache!
        // if not, build from spec
            // if resolvable? resolve
            // if not, well do not!
        return $this->factory->createFromUri(
            $specMetadata->getIndexPath()
        );
    }
}