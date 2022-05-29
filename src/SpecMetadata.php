<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

class SpecMetadata
{
    private string $name;
    private string $indexPath;
    private ?string $resolvePath;
    private ?string $resourcesDir;
    private bool $cache;
    private bool $cachePartials;

    public function __construct(
        string $name,
        string $indexPath,
        ?string $resolvePath,
        ?string $resourcesDir,
        bool $cache,
        bool $cachePartials
    ) {
        $this->name = $name;
        $this->indexPath = $indexPath;
        $this->resourcesDir = $resourcesDir;
        $this->resolvePath = $resolvePath;
        $this->cache = $cache;
        $this->cachePartials = $cachePartials;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function getResolvePath(): ?string
    {
        return $this->resolvePath;
    }

    public function getResourcesDir(): ?string
    {
        return $this->resourcesDir;
    }

    public function isResolvable(): bool
    {
        return !is_null($this->resolvePath);
    }

    public function cache(): bool
    {
        return $this->cache;
    }

    public function cachePartials(): bool
    {
        return $this->cachePartials;
    }
}
