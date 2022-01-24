<?php

declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\BodyExtractor\Request;

use OAS\Bridge\SymfonyBundle\BodyExtractor\ContentTypeSpecificRequestBodyExtractor;
use Symfony\Component\HttpFoundation\Request;

class MultipartFormDataBodyExtractor implements ContentTypeSpecificRequestBodyExtractor
{
    public function extract(Request $request)
    {
        return array_merge_recursive(
            $request->request->all(),
            $request->files->all()
        );
    }

    public function supports(string $contentType): bool
    {
        return str_starts_with($contentType, 'multipart/form-data');
    }
}
