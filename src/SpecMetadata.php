<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

class SpecMetadata
{
    private string $indexPath;
    private ?string $resolvePath;
    private bool $buildCache;

    public function __construct(string $indexPath, ?string $resolvePath, bool $buildCache)
    {
        $this->indexPath = $indexPath;
        $this->resolvePath = $resolvePath;
        $this->buildCache = $buildCache;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function getResolvePath(): ?string
    {
        return $this->resolvePath;
    }

    public function isResolvable(): bool
    {
        return !is_null($this->resolvePath);
    }

    public function isCacheable(): bool
    {
        return $this->buildCache;
    }
}