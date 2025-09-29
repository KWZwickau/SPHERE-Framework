<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * There is some data missing or simply non-existent
 *
 * AbstractResponse::HTTP_NOT_FOUND
 */
class Response404 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_NOT_FOUND, $context);
    }
}
