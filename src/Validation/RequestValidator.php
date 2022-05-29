<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Validation;

use OAS\Bridge\SymfonyBundle\BodyExtractor\RequestBodyExtractor;
use OAS\Bridge\SymfonyBundle\SchemaNotFound;
use OAS\Bridge\SymfonyBundle\SpecProvider;
use OAS\Document\Error\RetrievalError;
use OAS\Schema;
use OAS\Validator;
use Symfony\Component\HttpFoundation\Request;

class RequestValidator
{
    private SpecProvider $specProvider;
    private RequestBodyExtractor $requestBodyExtractor;
    private Validator $validator;

    public function __construct(
        SpecProvider $provider,
        Validator $validator,
        RequestBodyExtractor $requestBodyExtractor
    ) {
        $this->specProvider = $provider;
        $this->validator = $validator;
        $this->requestBodyExtractor = $requestBodyExtractor;
    }

    public function validate(Request $request, string $operationId = null): void
    {
        $parsedRequestBody = $this->requestBodyExtractor->extract($request);
        $schema = $this->extractRequestBodySchema($request, is_null($parsedRequestBody), $operationId);

        if (!is_null($schema)) {
            $this->validator->validate($parsedRequestBody, $schema);
        }
    }

    private function extractRequestBodySchema(Request $request, bool $isRequestBodyEmpty, string $operationId = null): ?Schema
    {
        $spec = $this->specProvider->get(null, $operationId);

        $operation = is_null($operationId)
            ? $spec->findOperation(
                $request->getMethod(),
                $request->getPathInfo()
            )
            : $spec->getOperationById($operationId);

        if (is_null($operation)) {
            throw new SchemaNotFound(
                $request->getMethod(),
                $request->getPathInfo(),
                'operation definition is missing'
            );
        }

        $requestBodyDefinition = $operation->getRequestBody();

        if (is_null($requestBodyDefinition)) {
            if ($isRequestBodyEmpty) {
                return null;
            }

            throw new SchemaNotFound(
                $request->getMethod(),
                $request->getPathInfo(),
                'request body definition is missing'
            );
        }

        if (!$requestBodyDefinition->isRequired() && $isRequestBodyEmpty) {
            return null;
        }

        // TODO: check if empty
        $requestContentType = $request->headers->get('content-type');

        // extract real mime type: e.g for content type "application/json;UTF-8"
        // the real mime type is "application/json"
        if (false !== ($pos = strpos($requestContentType, ';'))) {
            $requestContentType = substr($requestContentType, 0, $pos);
        }

        try {
            return $operation->getRequestBodySchema($requestContentType);
        } catch (RetrievalError $retrievalError) {
            throw new SchemaNotFound(
                $request->getMethod(),
                $request->getPathInfo(),
                "schema definition for \"$requestContentType\" content type is missing"
            );
        }
    }
}
