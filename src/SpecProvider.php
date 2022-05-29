<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

use OAS\OpenApiDocument;

interface SpecProvider
{
    public function get(string $name = null, string $partial = null): OpenApiDocument;
}
