<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor\Request;

use JsonException;
use OAS\Bridge\SymfonyBundle\BodyExtractor\BodyExtractionFailure;
use Symfony\Component\HttpFoundation\Request;
use OAS\Bridge\SymfonyBundle\BodyExtractor\ContentTypeSpecificRequestBodyExtractor;

class JsonRequestBodyExtractor implements ContentTypeSpecificRequestBodyExtractor
{
    public function extract(Request $request)
    {
        try {
            return json_decode($request->getContent());
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