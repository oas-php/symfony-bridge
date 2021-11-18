<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use Symfony\Component\HttpFoundation\Response;

interface ContentTypeSpecificResponseBodyExtractor
{
    /**
     * @param Response $response
     * @throws BodyExtractionFailure
     * @return array|string|int|float|bool|\stdClass|null
     */
    public function extract(Response $response);

    public function supports(string $contentType): bool;
}