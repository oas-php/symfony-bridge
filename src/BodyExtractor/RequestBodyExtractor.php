<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use Symfony\Component\HttpFoundation\Request;

class RequestBodyExtractor
{
    /** @var ContentTypeSpecificRequestBodyExtractor[]  */
    private array $requestBodyExtractors;

    public function __construct(iterable $contentTypeSpecificRequestBodyExtractors)
    {
        foreach ($contentTypeSpecificRequestBodyExtractors as $requestBodyExtractor) {
            $this->addContentTypeSpecificRequestBodyExtractor($requestBodyExtractor);
        }
    }

    public function addContentTypeSpecificRequestBodyExtractor(ContentTypeSpecificRequestBodyExtractor $requestBodyExtractor): void
    {
        $this->requestBodyExtractors[] = $requestBodyExtractor;
    }

    /** @return array|string|int|float|bool|\stdClass|null */
    public function extract(Request $request)
    {
        $contentType = strtolower(
            $request->headers->get('content-type', '')
        );

        if (empty($contentType)) {
            throw new ContentTypeNotSpecified();
        }

        foreach ($this->requestBodyExtractors as $requestBodyExtractor) {
            if ($requestBodyExtractor->supports($contentType)) {
                return $requestBodyExtractor->extract($request);
            }
        }

        throw new BodyExtractorNotFound($contentType);
    }
}