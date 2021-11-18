<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor;

use RuntimeException;
use Throwable;

class ContentTypeNotSpecified extends RuntimeException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Content-type is not specified.', $code, $previous);
    }
}