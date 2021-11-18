<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use Symfony\Component\HttpFoundation\Response;

class ResponseBodyExtractor
{
    /** @var ContentTypeSpecificRequestBodyExtractor[]  */
    private array $responseBodyExtractors;

    public function __construct(iterable $contentTypeSpecificResponseBodyExtractors)
    {
        foreach ($contentTypeSpecificResponseBodyExtractors as $requestBodyExtractor) {
            $this->addContentTypeSpecificRequestBodyExtractor($requestBodyExtractor);
        }
    }

    public function addContentTypeSpecificRequestBodyExtractor(ContentTypeSpecificResponseBodyExtractor $responseBodyExtractor): void
    {
        $this->responseBodyExtractors[] = $responseBodyExtractor;
    }

    /** @return array|string|int|float|bool|\stdClass|null */
    public function extract(Response $response)
    {
        $contentType = strtolower(
            $response->headers->get('content-type', '')
        );

        if (empty($contentType)) {
            throw new ContentTypeNotSpecified();
        }

        foreach ($this->responseBodyExtractors as $responseBodyExtractor) {
            if ($responseBodyExtractor->supports($contentType)) {
                return $responseBodyExtractor->extract($response);
            }
        }

        throw new BodyExtractorNotFound($contentType);
    }
}