<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * AbstractResponse::HTTP_BAD_REQUEST
 */
class Response400 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_BAD_REQUEST, $context);
    }
}
