<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Annotation;

use Attribute;

/** @Annotation  */
#[Attribute(Attribute::TARGET_METHOD)]
class ValidateAgainstSchema
{
    /**
     * If operation (a.k.a endpoint: http method + path) has an id, it's
     * a good practice to provide it to speed up operation search.
     *
     * paths:
     *      /user:
     *          post:
     *              summary: 'Create a new user'
     *              operationId: 'create-user'
     *              ...
     *
     * Without operationId each API operation is checked if it matches the
     * current request method and path (the very first match wins)
     *
     * @see \OAS\OpenApiDocument::getOperationById()
     * @see \OAS\OpenApiDocument::findOperation()
     */
    public ?string $operationId = null;
    public ?string $specification = null;

    public function __construct(string $operationId = null, string $specification = null)
    {
        $this->operationId = $operationId;
        $this->specification = $specification;
    }
}