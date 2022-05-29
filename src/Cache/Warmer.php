<?php

declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Cache;

use OAS\Bridge\SymfonyBundle\Configuration;
use OAS\Bridge\SymfonyBundle\SpecMetadata;
use OAS\Document\Factory\Dumper;
use OAS\Document\Operation;
use OAS\Resolver\Resolver;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

use function iter\all;

/**
 * OpenAPI document may be divided into multiple, connected parts by means of $ref fields. The role of this class is
 * to resolve all $refs, combine them into a single file and save it under configured path.
 *
 * Moreover, if necessary \OAS\OpenApiDocumentation object is dumped to cache directory.
 */
class Warmer implements CacheWarmerInterface
{
    private Configuration $configuration;
    private Resolver $resolver;
    private Dumper $dumper;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->resolver = new Resolver;
        $this->dumper = new Dumper;
    }

    public function isOptional(): bool
    {
        return all(
            function (SpecMetadata $specMetadata): bool {
                $cachedFiles = [];

                if ($specMetadata->isResolvable()) {
                    $cachedFiles[] = $specMetadata->getResolvePath();
                }

                if ($specMetadata->cache()) {
                    $cachedFiles[] = $this->configuration->getCacheFilepath($specMetadata);
                }

                if (!all('is_file', $cachedFiles)) {
                    return false;
                }

                if (!empty($cachedFiles) && is_string($specMetadata->getResourcesDir())) {
                    return (new DirectoryResource($specMetadata->getResourcesDir()))->isFresh(
                        min(array_map('filemtime', $cachedFiles))
                    );
                }

                return true;
            },
            $this->configuration->getSpecsMetadata()
        );
    }

    public function warmUp(string $cacheDir): array
    {
        foreach ($this->configuration->getSpecsMetadata() as $specMetadata) {
            $toResolve = is_string($specMetadata->getResolvePath());

            if ($toResolve || $specMetadata->cache()) {
                $resolved = $this->resolver->resolve($specMetadata->getIndexPath())->denormalize();

                if ($toResolve) {
                    $this->dumpResolved($specMetadata, $resolved);
                }

                if ($specMetadata->cache()) {
                    $this->dumpCached($specMetadata, $resolved);

                    if ($specMetadata->cachePartials()) {
                        $this->dumpCachedPartials($specMetadata, $resolved);
                    }
                }
            }
        }

        return [];
    }

    private function dumpCached(SpecMetadata $specMetadata, mixed $resolved): void
    {
        $cacheDir = $this->configuration->getCacheDir();

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }

        $filepath = $this->configuration->getCacheFilepath($specMetadata);
        $this->dumper->dumpFromPrimitives($resolved, $filepath);

        // test dumped code
        include $filepath;
    }

    /**
     * dump each operation as stand-alone open api specification
     */
    private function dumpCachedPartials(SpecMetadata $specMetadata, mixed $resolved): void
    {
        $cacheDir = $this->configuration->getCacheDirPartials($specMetadata);

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }

        foreach ($resolved['paths'] as $pathName => $path) {
            $pathDetails = $this->extract(['parameters', 'summary', 'description'], $path);

            foreach ($this->extract(Operation::TYPES, $path) as $operationType => $operation) {
                if (!array_key_exists('operationId', $operation)) {
                    continue;
                }

                $resolved['paths'] = [$pathName => array_merge($pathDetails, [$operationType => $operation])];
                $filepath = $this->configuration->getCacheFilepathPartial($specMetadata, $operation['operationId']);

                $this->dumper->dumpFromPrimitives($resolved, $filepath);

                // test dumped code
                include $filepath;
            }
        }
    }

    private function dumpResolved(SpecMetadata $specMetadata, mixed $resolved)
    {
        file_put_contents($specMetadata->getResolvePath(), json_encode($resolved));
    }

    private function extract(array $keys, array $hashMap): array
    {
        return array_reduce(
            $keys,
            function ($subHashMap, $key) use ($hashMap) {
                if (array_key_exists($key, $hashMap)) {
                    $subHashMap[$key] = $hashMap[$key];
                }

                return $subHashMap;
            },
            []
        );
    }
}
