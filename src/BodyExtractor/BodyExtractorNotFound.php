<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use RuntimeException;
use Throwable;

class BodyExtractorNotFound extends RuntimeException
{
    public function __construct(string $contentType, $code = 0, Throwable $previous = null)
    {
        parent::__construct("BodyExtractor body extractor for \"$contentType\" not found.", $code, $previous);
    }
}