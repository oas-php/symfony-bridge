<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

class Configuration
{
    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function validateAlways(): bool
    {
        return $this->configuration['validation']['validate_always'];
    }

    public function validateMutatingRequestsOnly(): bool
    {
        return $this->configuration['validation']['validate_mutating_requests_only'];
    }

    public function raiseErrorOnMissingSchema(): bool
    {
        return $this->configuration['validation']['raise_error_on_missing_schema'];
    }

    public function getSpecMetadata(string $name = null): SpecMetadata
    {
        $name = $name ?? $this->configuration['default'] ?? array_key_first($this->configuration['specs']);

        if (!array_key_exists($name, $this->configuration['specs'])) {
            throw new \RuntimeException('TODO: write nice exception message!');
        }

        $specMetadataRaw = $this->configuration['specs'][$name];

        return new SpecMetadata(
            $specMetadataRaw['index_path'],
            $specMetadataRaw['resolve_path'] ?? null,
            $specMetadataRaw['cache']
        );
    }

    public function yieldFormatSpecificError(): bool
    {
        return $this->configuration['validation']['validator_options']['yield_format_specific_error'];
    }
}