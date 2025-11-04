<?php

namespace SPHERE\Application\App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
abstract class AbstractResponse extends JsonResponse implements ResponseInterface
{
    public function __construct(mixed $content, int $code, $headers = [])
    {
        parent::__construct($content, $code, $headers);
    }
}
