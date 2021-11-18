<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Validation;

use OAS\Bridge\SymfonyBundle\BodyExtractor\ResponseBodyExtractor;
use OAS\Bridge\SymfonyBundle\SchemaNotFound;
use OAS\Bridge\SymfonyBundle\SpecProvider;
use OAS\Document\Error\RetrievalError;
use OAS\Schema;
use OAS\Validator;
use Symfony\Component\HttpFoundation\Response;

class ResponseValidator
{
    private SpecProvider $specProvider;
    private ResponseBodyExtractor $responseBodyExtractor;
    private Validator $validator;

    public function __construct(
        SpecProvider $provider,
        Validator $validator,
        ResponseBodyExtractor $requestBodyExtractor
    ) {
        $this->specProvider = $provider;
        $this->validator = $validator;
        $this->responseBodyExtractor = $requestBodyExtractor;
    }

    /**
     * @throws \OAS\Validator\SchemaConformanceFailure
     */
    public function validate(Response $response, string $method, string $path, string $operationId = null): void
    {
        $parsedRequestBody = $this->responseBodyExtractor->extract($response);

        $schema = $this->extractResponseBodySchema(
            $response,
            is_null($parsedRequestBody),
            $method,
            $path,
            $operationId
        );

        if (!is_null($schema)) {
            $this->validator->validate($parsedRequestBody, $schema);
        }
    }

    private function extractResponseBodySchema(Response $response, bool $isResponseBodyEmpty, string $method, string $path, string $operationId = null): ?Schema
    {
        $spec = $this->specProvider->get();

        $operation = is_null($operationId)
            ? $spec->findOperation($method, $path)
            : $spec->getOperationById($operationId);

        if (is_null($operation)) {
            throw new SchemaNotFound($method, $path, 'operation definition is missing');
        }

        $statusCode = $response->getStatusCode();

        if ($operation->hasResponse($statusCode)) {
            $responseDefinition = $operation->getResponse($statusCode);
        } else {
            if (!$operation->hasDefaultResponse()) {
                throw new SchemaNotFound(
                    $method,
                    $path,
                    "response definition for \"$statusCode\" status code is missing"
                );
            }

            $responseDefinition = $operation->getDefaultResponse();
        }

        // TODO: check if empty
        $responseContentType = $response->headers->get('content-type');

        // extract real mime type: e.g for content type "application/json;UTF-8"
        // the real mime type is "application/json"
        if (false !== ($pos = strpos($responseContentType, ';'))) {
            $responseContentType = substr($responseContentType, 0, $pos);
        }

        try {
            return $responseDefinition->getSchema($responseContentType);
        } catch (RetrievalError $retrievalError) {
            if (!$isResponseBodyEmpty) {
                return null;
            }

            throw new SchemaNotFound(
                $method,
                $path,
                "schema definition for \"$responseContentType\" content type is missing"
            );
        }
    }
}