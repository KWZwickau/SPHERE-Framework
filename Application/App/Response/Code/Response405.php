<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * AbstractResponse::HTTP_METHOD_NOT_ALLOWED
 */
class Response405 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_METHOD_NOT_ALLOWED, $context);
    }
}
