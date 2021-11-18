<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor\Response;

use JsonException;
use OAS\Bridge\SymfonyBundle\BodyExtractor\BodyExtractionFailure;
use OAS\Bridge\SymfonyBundle\BodyExtractor\ContentTypeSpecificResponseBodyExtractor;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseBodyExtractor implements ContentTypeSpecificResponseBodyExtractor
{
    public function extract(Response $response)
    {
        try {
            return json_decode($response->getContent(),false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new BodyExtractionFailure(
                $jsonException->getMessage(),
                $jsonException->getCode(),
                $jsonException
            );
        }
    }

    public function supports(string $contentType): bool
    {
        return 0 === strpos($contentType, 'application/json');
    }
}