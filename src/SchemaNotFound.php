<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle;

use LogicException;
use Throwable;

class SchemaNotFound extends LogicException
{
    public function __construct(string $method, string $uri, string $reason, $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'BodyExtractor body schema for "%s %s" does not exist in specification: %s',
            $method,
            $uri,
            $reason
        );

        parent::__construct($reason, $code, $previous);
    }
}