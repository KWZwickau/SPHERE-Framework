<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * AbstractResponse::HTTP_FORBIDDEN
 */
class Response403 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_FORBIDDEN, $context);
    }
}
