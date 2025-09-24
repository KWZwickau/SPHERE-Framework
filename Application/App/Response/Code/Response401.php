<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * AbstractResponse::HTTP_UNAUTHORIZED
 */
class Response401 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_UNAUTHORIZED, $context);
    }
}
