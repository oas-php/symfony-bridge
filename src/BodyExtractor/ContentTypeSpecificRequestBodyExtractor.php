<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use Symfony\Component\HttpFoundation\Request;

interface ContentTypeSpecificRequestBodyExtractor
{
    /**
     * @throws BodyExtractionFailure
     * @return array|string|int|float|bool|\stdClass|null
     */
    public function extract(Request $request);

    public function supports(string $contentType): bool;
}